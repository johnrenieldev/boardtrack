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
        $sql = "SELECT b.*, r.room_number, r.room_type, r.floor, r.monthly_rent,
                       ut.name AS billed_tenant_name,
                       GROUP_CONCAT(DISTINCT CASE WHEN b.billing_type = 'room_based' AND u.status = 'approved'
                            THEN u.name END ORDER BY u.name SEPARATOR ', ') AS room_tenant_names,
                       COALESCE(ut.name, GROUP_CONCAT(DISTINCT CASE WHEN b.billing_type = 'room_based' AND u.status = 'approved'
                            THEN u.name END ORDER BY u.name SEPARATOR ', ')) AS tenant_name,
                       CASE WHEN b.due_date < CURDATE() AND b.status IN ('unpaid','pending_verification')
                            THEN 'overdue' ELSE b.status END AS computed_status
                FROM {$this->table} b
                LEFT JOIN rooms r ON b.room_id = r.id
                LEFT JOIN tenants t_ind ON b.tenant_id = t_ind.id AND b.billing_type = 'individual'
                LEFT JOIN users ut ON t_ind.user_id = ut.id
                LEFT JOIN tenants t_room ON t_room.room_id = r.id AND b.billing_type = 'room_based'
                LEFT JOIN users u ON t_room.user_id = u.id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY b.id
                ORDER BY b.created_at DESC";
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
                       CASE WHEN b.due_date < CURDATE() AND b.status IN ('unpaid','pending_verification')
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
                    SUM(status='unpaid') AS unpaid,
                    SUM(status='pending_verification') AS pending,
                    SUM(status='paid') AS paid,
                    SUM(due_date < CURDATE() AND status IN ('unpaid','pending_verification')) AS overdue,
                    SUM(CASE WHEN status='unpaid' THEN amount ELSE 0 END) AS total_unpaid,
                    SUM(CASE WHEN status='paid'   THEN amount ELSE 0 END) AS total_collected
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
                    SUM(status='unpaid') AS unpaid,
                    SUM(status='pending_verification') AS pending,
                    SUM(status='paid')   AS paid,
                    SUM(CASE WHEN status='unpaid' THEN amount ELSE 0 END) AS total_unpaid,
                    SUM(CASE WHEN status='paid'   THEN amount ELSE 0 END) AS total_paid
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
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE status = 'unpaid' AND ("
            . implode(' OR ', $conditions) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
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
}
