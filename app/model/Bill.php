<?php
/**
 * BoardTrack — Bill Model
 * app/model/Bill.php
 */

class Bill extends Model
{
    protected string $table = 'bills';

    public function getAllWithTenants(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'b.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['tenant_id'])) {
            $where[] = 'b.tenant_id = :tid';
            $params[':tid'] = $filters['tenant_id'];
        }
        $sql = "SELECT b.*, u.name AS tenant_name, u.email, r.room_number,
                       CASE WHEN b.due_date < CURDATE() AND b.status IN ('unpaid','pending_verification')
                            THEN 'overdue' ELSE b.status END AS computed_status
                FROM {$this->table} b
                JOIN tenants t ON b.tenant_id = t.id
                JOIN users   u ON t.user_id   = u.id
                LEFT JOIN rooms r ON t.room_id = r.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY b.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByTenantId(int $tenantId): array
    {
        $sql = "SELECT b.*,
                       CASE WHEN b.due_date < CURDATE() AND b.status IN ('unpaid','pending_verification')
                            THEN 'overdue' ELSE b.status END AS computed_status
                FROM {$this->table} b
                WHERE b.tenant_id = :tid
                ORDER BY b.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $tenantId]);
        return $stmt->fetchAll();
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

    public function getTenantStatistics(int $tenantId): array
    {
        $sql  = "SELECT COUNT(*) AS total,
                    SUM(status='unpaid') AS unpaid,
                    SUM(status='pending_verification') AS pending,
                    SUM(status='paid')   AS paid,
                    SUM(CASE WHEN status='unpaid' THEN amount ELSE 0 END) AS total_unpaid,
                    SUM(CASE WHEN status='paid'   THEN amount ELSE 0 END) AS total_paid
                 FROM {$this->table} WHERE tenant_id = :tid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $tenantId]);
        return $stmt->fetch() ?: [];
    }

    /** FIX: explicit WHERE string version for Controller::countByWhere() pattern */
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

    /**
     * Update bill status (with paid_at when marking as paid)
     */
    public function updateStatus(int $billId, string $status): bool
    {
        $data = ['status' => $status];
        return $this->update($data, ['id' => $billId]) > 0;
    }
}