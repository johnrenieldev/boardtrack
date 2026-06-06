<?php
/**
 * BoardTrack — Bill Model
 * app/models/Bill.php
 */

class Bill extends Model
{
    protected string $table = 'bills';

    public function getAllForLandlord(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'b.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['billing_type'])) {
            $where[] = 'b.billing_type = :btype';
            $params[':btype'] = $filters['billing_type'];
        }
        if (!empty($filters['room_id'])) {
            $where[] = 'b.room_id = :rid';
            $params[':rid'] = $filters['room_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(ut.name LIKE :search OR u.name LIKE :search2 OR b.bill_name LIKE :search3)';
            $params[':search']  = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
            $params[':search3'] = '%' . $filters['search'] . '%';
        }
        
        // For room-based bills, show one row per tenant
        // For individual bills, show one row per bill
        $sql = "SELECT b.*, r.room_number, r.room_type, r.floor, r.monthly_rent,
                       CASE 
                           WHEN b.billing_type = 'individual' THEN ut.name
                           WHEN b.billing_type = 'room_based' THEN u.name
                       END AS tenant_name,
                       CASE 
                           WHEN b.billing_type = 'individual' THEN t_ind.id
                           WHEN b.billing_type = 'room_based' THEN t_room.id
                       END AS display_tenant_id,
                       CASE WHEN b.due_date < CURDATE() AND b.status IN ('unpaid','pending_verification','partial')
                            THEN 'overdue' ELSE b.status END AS computed_status
                FROM {$this->table} b
                LEFT JOIN rooms r ON b.room_id = r.id
                LEFT JOIN tenants t_ind ON b.tenant_id = t_ind.id AND b.billing_type = 'individual'
                LEFT JOIN users ut ON t_ind.user_id = ut.id
                LEFT JOIN tenants t_room ON t_room.room_id = r.id AND b.billing_type = 'room_based' AND t_room.user_id IS NOT NULL
                LEFT JOIN users u ON t_room.user_id = u.id AND u.status = 'approved'
                WHERE " . implode(' AND ', $where) . "
                  AND (
                      (b.billing_type = 'individual' AND ut.id IS NOT NULL) OR
                      (b.billing_type = 'room_based' AND u.id IS NOT NULL)
                  )
                ORDER BY b.created_at DESC, r.room_number ASC, tenant_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAllWithRooms(array $filters = []): array
    {
        return $this->getAllForLandlord($filters);
    }

    public function getAllWithTenants(array $filters = []): array
    {
        return $this->getAllForLandlord($filters);
    }

    /** Bills visible to a tenant (room-based for their room + individual for them) */
    public function getForTenant(int $tenantId, ?int $roomId): array
    {
        $where = ["(b.billing_type = 'individual' AND b.tenant_id = :tid)"];
        $params = [':tid' => $tenantId];
        if ($roomId) {
            $where[] = "(b.billing_type = 'room_based' AND b.room_id = :rid)";
            $params[':rid'] = $roomId;
        }
        $sql = "SELECT b.*,
                       CASE WHEN b.due_date < CURDATE() AND b.status IN ('unpaid','pending_verification','partial')
                            THEN 'overdue' ELSE b.status END AS computed_status
                FROM {$this->table} b
                WHERE " . implode(' OR ', $where) . "
                ORDER BY b.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByRoomId(int $roomId, bool $roomBasedOnly = true): array
    {
        $where = 'b.room_id = :rid';
        if ($roomBasedOnly) {
            $where .= " AND b.billing_type = 'room_based'";
        }
        $sql = "SELECT b.*,
                       CASE WHEN b.due_date < CURDATE() AND b.status IN ('unpaid','pending_verification')
                            THEN 'overdue' ELSE b.status END AS computed_status
                FROM {$this->table} b
                WHERE {$where}
                ORDER BY b.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':rid' => $roomId]);
        return $stmt->fetchAll();
    }

    public function getByTenantId(int $tenantId): array
    {
        $sql = "SELECT t.room_id FROM tenants t WHERE t.id = :tid LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $tenantId]);
        $row = $stmt->fetch();
        $roomId = !empty($row['room_id']) ? (int) $row['room_id'] : null;
        return $this->getForTenant($tenantId, $roomId);
    }

    public function tenantCanAccess(int $billId, int $tenantId, ?int $roomId): bool
    {
        $bill = $this->find($billId);
        if (!$bill) {
            return false;
        }
        if (($bill['billing_type'] ?? '') === 'individual') {
            return isset($bill['tenant_id']) && (int) $bill['tenant_id'] === $tenantId;
        }
        return $roomId && !empty($bill['room_id']) && (int) $bill['room_id'] === (int) $roomId;
    }

    public function getStatistics(): array
    {
        $sql  = "SELECT
                    COUNT(*) AS total_bills,
                    SUM(CASE WHEN status IN ('unpaid', 'overdue') THEN 1 ELSE 0 END) AS unpaid,
                    SUM(CASE WHEN status = 'partial' THEN 1 ELSE 0 END) AS partial,
                    SUM(CASE WHEN status = 'pending_verification' THEN 1 ELSE 0 END) AS pending_review,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid,
                    SUM(CASE WHEN due_date < CURDATE() AND status IN ('unpaid', 'partial') THEN 1 ELSE 0 END) AS overdue,
                    SUM(CASE WHEN status IN ('unpaid','partial','overdue','pending_verification')
                        THEN (amount - COALESCE(amount_paid, 0)) ELSE 0 END) AS total_unpaid,
                    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS total_collected
                 FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch() ?: [];
    }

    public function getTenantBillStatistics(int $tenantId, ?int $roomId): array
    {
        $where = ["(billing_type = 'individual' AND tenant_id = :tid)"];
        $params = [':tid' => $tenantId];
        if ($roomId) {
            $where[] = "(billing_type = 'room_based' AND room_id = :rid)";
            $params[':rid'] = $roomId;
        }
        $sql  = "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status IN ('unpaid', 'overdue') THEN 1 ELSE 0 END) AS unpaid,
                    SUM(CASE WHEN status = 'partial' THEN 1 ELSE 0 END) AS partial,
                    SUM(CASE WHEN status = 'pending_verification' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid,
                    SUM(CASE WHEN status IN ('unpaid', 'partial', 'overdue', 'pending_verification') THEN (amount - COALESCE(amount_paid, 0)) ELSE 0 END) AS total_unpaid,
                    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS total_paid,
                    SUM(CASE WHEN status = 'partial' THEN (amount - COALESCE(amount_paid,0)) ELSE 0 END) AS total_remaining
                 FROM {$this->table}
                 WHERE " . implode(' OR ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: [];
    }

    public function getRoomStatistics(int $roomId): array
    {
        $sql  = "SELECT COUNT(*) AS total,
                    SUM(status='unpaid') AS unpaid,
                    SUM(status='pending_verification') AS pending,
                    SUM(status='paid')   AS paid,
                    SUM(CASE WHEN status='unpaid' THEN amount ELSE 0 END) AS total_unpaid,
                    SUM(CASE WHEN status='paid'   THEN amount ELSE 0 END) AS total_paid
                 FROM {$this->table}
                 WHERE room_id = :rid AND billing_type = 'room_based'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':rid' => $roomId]);
        return $stmt->fetch() ?: [];
    }

    public function getTenantStatistics(int $tenantId): array
    {
        $sql = "SELECT room_id FROM tenants WHERE id = :tid LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $tenantId]);
        $row = $stmt->fetch();
        $roomId = !empty($row['room_id']) ? (int) $row['room_id'] : null;
        return $this->getTenantBillStatistics($tenantId, $roomId);
    }

    public function countUnpaidForTenant(int $tenantId, ?int $roomId): int
    {
        $conditions = ["(billing_type = 'individual' AND tenant_id = :tid)"];
        $params = [':tid' => $tenantId];
        if ($roomId) {
            $conditions[] = "(billing_type = 'room_based' AND room_id = :rid)";
            $params[':rid'] = $roomId;
        }
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE status IN ('unpaid', 'partial', 'overdue') AND ("
            . implode(' OR ', $conditions) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function countByStatusForTenant(string $status, int $tenantId, ?int $roomId): int
    {
        $conditions = ["(billing_type = 'individual' AND tenant_id = :tid)"];
        $params = [':tid' => $tenantId, ':status' => $status];
        if ($roomId) {
            $conditions[] = "(billing_type = 'room_based' AND room_id = :rid)";
            $params[':rid'] = $roomId;
        }
        $sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE status = :status AND (" . implode(' OR ', $conditions) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getTotalRemainingForTenant(int $tenantId, ?int $roomId): float
    {
        $conditions = ["(billing_type = 'individual' AND tenant_id = :tid)"];
        $params = [':tid' => $tenantId];
        if ($roomId) {
            $conditions[] = "(billing_type = 'room_based' AND room_id = :rid)";
            $params[':rid'] = $roomId;
        }
        $sql = "SELECT COALESCE(SUM(amount - COALESCE(amount_paid,0)), 0) AS remaining
                FROM {$this->table}
                WHERE status IN ('unpaid','partial','overdue')
                AND (" . implode(' OR ', $conditions) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    public function countByWhere(string $where): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function markOverdue(): int
    {
        $sql  = "UPDATE {$this->table} SET status='overdue'
                 WHERE due_date < CURDATE() AND status IN ('unpaid','pending_verification')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function updateStatus(int $billId, string $status): bool
    {
        return $this->update(['status' => $status], ['id' => $billId]) > 0;
    }

    // Partial payment operations.

    /**
     * Update amount paid for a bill
     */
    public function updateAmountPaid(int $billId, float $amount): bool
    {
        $bill = $this->find($billId);
        if (!$bill) return false;

        $newAmountPaid = ($bill['amount_paid'] ?? 0) + $amount;
        $totalAmount = (float) ($bill['amount'] ?? 0);

        $partialStatus = 'none';
        if ($newAmountPaid >= $totalAmount) {
            $partialStatus = 'full';
        } elseif ($newAmountPaid > 0) {
            $partialStatus = 'partial';
        }

        return $this->update([
            'amount_paid' => $newAmountPaid,
            'partial_payment_status' => $partialStatus,
            'last_payment_date' => date('Y-m-d'),
        ], ['id' => $billId]) > 0;
    }

    /**
     * Get payment progress for a bill
     */
    public function getPaymentProgress(int $billId): array
    {
        $bill = $this->find($billId);
        if (!$bill) return ['percentage' => 0, 'remaining' => 0, 'paid' => 0];

        $total = (float) ($bill['amount'] ?? 0);
        $paid = (float) ($bill['amount_paid'] ?? 0);
        $remaining = max(0, $total - $paid);
        $percentage = $total > 0 ? round(($paid / $total) * 100, 2) : 0;

        return [
            'percentage' => $percentage,
            'remaining' => $remaining,
            'paid' => $paid,
            'total' => $total,
            'partial_status' => $bill['partial_payment_status'] ?? 'none',
        ];
    }

    /**
     * Check if bill can accept partial payment
     */
    public function canAcceptPartialPayment(int $billId): bool
    {
        $bill = $this->find($billId);
        if (!$bill) return false;

        $status = $bill['status'] ?? '';
        $amountPaid = (float) ($bill['amount_paid'] ?? 0);
        $totalAmount = (float) ($bill['amount'] ?? 0);

        // Partial payments are allowed only while an outstanding balance remains.
        return in_array($status, ['unpaid', 'partial']) && $amountPaid < $totalAmount;
    }

    // Payment reminder queries.

    /**
     * Get bills due for reminder (within X days)
     */
    public function getBillsDueForReminder(int $daysAhead = 3): array
    {
        $sql = "SELECT b.*, u.email, u.name as tenant_name, t.id as tenant_id
                FROM {$this->table} b
                JOIN rooms r ON b.room_id = r.id
                LEFT JOIN tenants t ON (b.billing_type = 'individual' AND b.tenant_id = t.id)
                                   OR (b.billing_type = 'room_based' AND t.room_id = r.id)
                LEFT JOIN users u ON t.user_id = u.id
                WHERE b.status IN ('unpaid', 'partial')
                AND b.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                AND (b.reminder_sent_1 = 0 OR b.reminder_sent_2 = 0 OR b.reminder_sent_3 = 0)
                AND u.status = 'approved'
                GROUP BY b.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':days' => $daysAhead]);
        return $stmt->fetchAll();
    }

    /**
     * Get overdue bills
     */
    public function getOverdueBills(): array
    {
        $sql = "SELECT b.*, u.email, u.name as tenant_name, t.id as tenant_id
                FROM {$this->table} b
                JOIN rooms r ON b.room_id = r.id
                LEFT JOIN tenants t ON (b.billing_type = 'individual' AND b.tenant_id = t.id)
                                   OR (b.billing_type = 'room_based' AND t.room_id = r.id)
                LEFT JOIN users u ON t.user_id = u.id
                WHERE b.due_date < CURDATE()
                AND b.status IN ('unpaid', 'partial')
                AND u.status = 'approved'
                GROUP BY b.id
                ORDER BY b.due_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Mark reminder as sent
     */
    public function markReminderSent(int $billId, int $reminderLevel): bool
    {
        $column = match($reminderLevel) {
            1 => 'reminder_sent_1',
            2 => 'reminder_sent_2',
            3 => 'reminder_sent_3',
            default => null
        };

        if (!$column) return false;

        // Get current reminder dates
        $bill = $this->find($billId);
        $reminderDates = [];
        if (!empty($bill['reminder_dates'])) {
            $reminderDates = json_decode($bill['reminder_dates'], true) ?: [];
        }

        $reminderDates[$column] = date('Y-m-d H:i:s');

        return $this->update([
            $column => 1,
            'reminder_dates' => json_encode($reminderDates),
        ], ['id' => $billId]) > 0;
    }

    /**
     * Get bills with payment plans
     */
    public function getBillsWithPaymentPlans(): array
    {
        $sql = "SELECT b.*, pp.id as plan_id, pp.status as plan_status,
                       pp.number_of_installments, pp.installment_amount,
                       pp.next_payment_date, pp.amount_paid as plan_amount_paid
                FROM {$this->table} b
                LEFT JOIN payment_plans pp ON b.payment_plan_id = pp.id
                WHERE b.payment_plan_id IS NOT NULL
                ORDER BY b.due_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // =====================================================
    // OVERDUE PENALTY AUTOMATION
    // =====================================================

    /**
     * Get bills eligible for penalty application
     * 
     * Eligibility criteria:
     * 1. Status is NOT 'paid' (exclude fully paid bills)
     * 2. Due date has passed
     * 3. At least 1 full month overdue
     * 4. Has remaining balance > 0
     * 5. Tenant is approved
     * 6. Not penalized this month yet
     */
    public function getBillsEligibleForPenalty(): array
    {
        $sql = "SELECT b.*, u.email, u.name as tenant_name, t.id as tenant_id, 
                       t.guardian_email, t.guardian_name, r.room_number,
                       TIMESTAMPDIFF(MONTH, b.due_date, CURDATE()) as months_overdue,
                       (b.amount - COALESCE(b.amount_paid, 0)) as remaining_balance
                FROM {$this->table} b
                JOIN rooms r ON b.room_id = r.id
                LEFT JOIN tenants t ON (b.billing_type = 'individual' AND b.tenant_id = t.id)
                                   OR (b.billing_type = 'room_based' AND t.room_id = r.id)
                LEFT JOIN users u ON t.user_id = u.id
                WHERE b.status != 'paid'
                AND b.due_date < CURDATE()
                AND TIMESTAMPDIFF(MONTH, b.due_date, CURDATE()) >= 1
                AND (b.amount - COALESCE(b.amount_paid, 0)) > 0
                AND u.status = 'approved'
                AND (
                    b.last_penalty_applied_at IS NULL 
                    OR DATE_FORMAT(b.last_penalty_applied_at, '%Y-%m') < DATE_FORMAT(CURDATE(), '%Y-%m')
                )
                ORDER BY b.due_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Apply 10% penalty to a bill - COMPOUNDING MONTHLY
     * 
     * Business Rules:
     * 1. Penalty COMPOUNDS each month: amount = amount × 1.10
     * 2. Month 1: 1000 × 1.10 = 1100
     * 3. Month 2: 1100 × 1.10 = 1210 (NOT 1200)
     * 4. Each bill accumulates its OWN penalty independently
     * 5. Penalty applies exactly 1 month after due date
     * 6. Fully paid bills (status='paid') are excluded
     * 7. Bills with zero remaining balance are excluded
     * 
     * Formula: New Amount = Current Amount × 1.10 (compounded)
     */
    public function applyPenalty(int $billId): bool
    {
        $bill = $this->find($billId);
        if (!$bill) return false;

        // SAFETY CHECK #1: Skip if bill is already paid
        if (($bill['status'] ?? '') === 'paid') {
            return false;
        }

        // Set original_amount if not set (for existing bills)
        // This is saved ONCE and never changes
        $originalAmount = !empty($bill['original_amount']) 
            ? (float) $bill['original_amount'] 
            : (float) $bill['amount'];

        // Get current amount (may already have penalties applied)
        $currentAmount = (float) $bill['amount'];
        
        // Get current amount_paid
        $amountPaid = (float) ($bill['amount_paid'] ?? 0);

        // Calculate months overdue using SQL TIMESTAMPDIFF
        // TIMESTAMPDIFF(MONTH, '2026-04-10', '2026-05-10') = 1
        // This means penalty starts exactly 1 month after due date
        $sql = "SELECT TIMESTAMPDIFF(MONTH, :due_date, CURDATE()) AS months_overdue";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':due_date' => $bill['due_date']]);
        $result = $stmt->fetch();
        $monthsOverdue = (int) ($result['months_overdue'] ?? 0);

        // Only apply penalty if at least 1 month overdue
        if ($monthsOverdue < 1) return false;

        // Get previous penalty count
        $previousPenaltyCount = (int) ($bill['penalty_count'] ?? 0);
        
        // Calculate how many NEW penalty cycles to apply
        // This prevents re-applying penalties for months already processed
        $newCyclesToApply = $monthsOverdue - $previousPenaltyCount;
        
        // If no new cycles, skip
        if ($newCyclesToApply <= 0) return false;

        // COMPOUNDING CALCULATION
        // Apply 10% penalty for each new cycle
        // Example: If 2 new cycles, amount = amount × 1.10 × 1.10
        $penaltyRate = 1.10; // 10% increase
        $newAmount = $currentAmount;
        
        for ($i = 0; $i < $newCyclesToApply; $i++) {
            $newAmount = $newAmount * $penaltyRate;
        }
        
        // Round to 2 decimal places
        $newAmount = round($newAmount, 2);
        
        // Calculate total penalty amount (for display purposes)
        $totalPenaltyAmount = $newAmount - $originalAmount;
        
        // Calculate remaining balance: Total - Approved Payments
        $remainingBalance = $newAmount - $amountPaid;

        // SAFETY CHECK #2: Skip if remaining balance is zero or negative
        if ($remainingBalance <= 0) {
            return false;
        }

        // Update bill with compounded penalty
        $updated = $this->update([
            'original_amount' => $originalAmount,
            'penalty_amount' => $totalPenaltyAmount,
            'penalty_count' => $monthsOverdue, // Track total cycles processed
            'amount' => $newAmount,
            'last_penalty_applied_at' => date('Y-m-d H:i:s'),
            'status' => 'overdue',
        ], ['id' => $billId]);

        return $updated > 0;
    }

    /**
     * Get penalty details for a bill
     */
    public function getPenaltyDetails(int $billId): array
    {
        $bill = $this->find($billId);
        if (!$bill) return [];

        $originalAmount = !empty($bill['original_amount']) 
            ? (float) $bill['original_amount'] 
            : (float) $bill['amount'];
        
        $penaltyAmount = (float) ($bill['penalty_amount'] ?? 0);
        $penaltyCount = (int) ($bill['penalty_count'] ?? 0);
        $currentAmount = (float) ($bill['amount'] ?? 0);
        $amountPaid = (float) ($bill['amount_paid'] ?? 0);
        
        // CONSISTENT CALCULATION: Remaining = Current Amount - Paid
        $remainingBalance = $currentAmount - $amountPaid;

        return [
            'original_amount' => $originalAmount,
            'penalty_amount' => $penaltyAmount,
            'penalty_count' => $penaltyCount,
            'current_amount' => $currentAmount,
            'amount_paid' => $amountPaid,
            'remaining_balance' => max(0, $remainingBalance), // Never negative
            'penalty_rate' => 10, // 10% per month
            'last_penalty_applied' => $bill['last_penalty_applied_at'] ?? null,
            'is_compounded' => true, // Flag to indicate compounding calculation
        ];
    }

    /**
     * Get remaining balance for a bill
     * Formula: Current Amount - Approved Payments
     */
    public function getRemainingBalance(int $billId): float
    {
        $bill = $this->find($billId);
        if (!$bill) return 0;

        $currentAmount = (float) ($bill['amount'] ?? 0);
        $amountPaid = (float) ($bill['amount_paid'] ?? 0);
        
        // CONSISTENT FORMULA
        $remaining = $currentAmount - $amountPaid;
        
        return max(0, $remaining); // Never return negative
    }

    /**
     * Mark penalty notification as sent for current cycle
     * Stores the cycle number instead of boolean flag
     */
    public function markPenaltyNotificationSent(int $billId): bool
    {
        $bill = $this->find($billId);
        if (!$bill) return false;
        
        $penaltyCount = (int) ($bill['penalty_count'] ?? 0);
        
        return $this->update([
            'last_penalty_notification_cycle' => $penaltyCount,
            'last_penalty_notification_at' => date('Y-m-d H:i:s'),
        ], ['id' => $billId]) > 0;
    }

    /**
     * Get bills with pending penalty notifications
     * Bills that have penalties but notification not sent for current cycle
     */
    public function getBillsWithPendingPenaltyNotifications(): array
    {
        $sql = "SELECT b.*, u.email, u.name as tenant_name, t.id as tenant_id,
                       t.guardian_email, t.guardian_name, r.room_number
                FROM {$this->table} b
                JOIN rooms r ON b.room_id = r.id
                LEFT JOIN tenants t ON (b.billing_type = 'individual' AND b.tenant_id = t.id)
                                   OR (b.billing_type = 'room_based' AND t.room_id = r.id)
                LEFT JOIN users u ON t.user_id = u.id
                WHERE b.penalty_amount > 0
                AND b.status != 'paid'
                AND (
                    b.last_penalty_notification_cycle IS NULL
                    OR b.last_penalty_notification_cycle < b.penalty_count
                )
                AND u.status = 'approved'
                ORDER BY b.last_penalty_applied_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Process all overdue bills and apply penalties
     * Returns array with statistics
     */
    public function processOverduePenalties(): array
    {
        $eligibleBills = $this->getBillsEligibleForPenalty();
        $processed = 0;
        $failed = 0;
        $totalPenalty = 0;

        foreach ($eligibleBills as $bill) {
            if ($this->applyPenalty((int) $bill['id'])) {
                $processed++;
                $penaltyDetails = $this->getPenaltyDetails((int) $bill['id']);
                $totalPenalty += $penaltyDetails['penalty_amount'];
            } else {
                $failed++;
            }
        }

        return [
            'eligible' => count($eligibleBills),
            'processed' => $processed,
            'failed' => $failed,
            'total_penalty' => $totalPenalty,
        ];
    }
}
