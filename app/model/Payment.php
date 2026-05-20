<?php
/**
 * BoardTrack — Payment Model
 * app/model/Payment.php
 */

class Payment extends Model
{
    protected string $table = 'payments';

    public function getAllWithDetails(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[]           = 'p.status = :status';
            $params[':status'] = $filters['status'];
        }
        $sql = "SELECT p.*, p.amount_paid AS amount, p.uploaded_at AS payment_date,
                       p.proof_file_path AS proof_file,
                       u.name AS tenant_name, b.bill_name, b.amount AS bill_amount, r.room_number
                FROM {$this->table} p
                JOIN tenants t ON p.tenant_id = t.id
                JOIN users   u ON t.user_id   = u.id
                JOIN bills   b ON p.bill_id   = b.id
                LEFT JOIN rooms r ON b.room_id = r.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.uploaded_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getPending(): array
    {
        return $this->getAllWithDetails(['status' => 'pending']);
    }

    /**
     * Get a single payment with full details (for view page)
     */
    public function getWithDetails(int $id): ?array
    {
        $sql = "SELECT p.*, p.amount_paid, p.uploaded_at AS payment_date,
                       p.proof_file_path AS proof_file,
                       u.name AS tenant_name, t.id AS tenant_id, b.bill_name,
                       b.amount AS bill_amount, r.room_number,
                       p.notes, p.review_notes AS rejection_reason,
                       p.payment_method
                FROM {$this->table} p
                JOIN tenants t ON p.tenant_id = t.id
                JOIN users   u ON t.user_id   = u.id
                JOIN bills   b ON p.bill_id   = b.id
                LEFT JOIN rooms r ON b.room_id = r.id
                WHERE p.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Submit a payment for a bill
     */
    public function submitPayment(int $billId, int $tenantId, array $data): int
    {
        return $this->insert([
            'bill_id'         => $billId,
            'tenant_id'       => $tenantId,
            'amount_paid'     => $data['amount_paid'],
            'payment_method'  => $data['payment_method'] ?? null,
            'proof_file_path' => $data['proof_file_path'] ?? null,
            'proof_file_name' => $data['proof_file_name'] ?? null,
            'status'          => 'pending',
            'notes'           => $data['notes'] ?? null,
        ]);
    }

    public function getStatistics(): array
    {
        $sql  = "SELECT SUM(status='pending') AS pending, SUM(status='approved') AS approved,
                        SUM(status='rejected') AS rejected, COUNT(*) AS total
                 FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch() ?: [];
    }

    /**
     * Get payments for a specific tenant
     */
    public function getByTenantId(int $tenantId): array
    {
        $sql = "SELECT p.*, b.bill_name, b.amount AS bill_amount
                FROM {$this->table} p
                JOIN bills b ON p.bill_id = b.id
                WHERE p.tenant_id = :tid
                ORDER BY p.uploaded_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $tenantId]);
        return $stmt->fetchAll();
    }

    // ── Partial Payment Support ────────────────────────────────────────────────

    /**
     * Submit a payment with custom amount (for partial payments)
     */
    public function submitPartialPayment(int $billId, int $tenantId, array $data): int
    {
        return $this->insert([
            'bill_id'         => $billId,
            'tenant_id'       => $tenantId,
            'amount_paid'     => $data['amount_paid'],
            'payment_method'  => $data['payment_method'] ?? null,
            'proof_file_path' => $data['proof_file_path'] ?? null,
            'proof_file_name' => $data['proof_file_name'] ?? null,
            'status'          => 'pending',
            'notes'           => $data['notes'] ?? null,
            'is_partial'      => $data['is_partial'] ?? false,
            'payment_plan_id' => $data['payment_plan_id'] ?? null,
        ]);
    }

    /**
     * Get total amount paid for a bill
     */
    public function getTotalPaidForBill(int $billId): float
    {
        $sql = "SELECT COALESCE(SUM(amount_paid), 0) as total
                FROM {$this->table}
                WHERE bill_id = :bid AND status = 'approved'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':bid' => $billId]);
        return (float) ($stmt->fetch()['total'] ?? 0);
    }

    /**
     * Get payment history for a bill
     */
    public function getPaymentHistory(int $billId): array
    {
        $sql = "SELECT p.*, u.name as reviewed_by_name
                FROM {$this->table} p
                LEFT JOIN users u ON p.reviewed_by = u.id
                WHERE p.bill_id = :bid
                ORDER BY p.uploaded_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':bid' => $billId]);
        return $stmt->fetchAll();
    }
}