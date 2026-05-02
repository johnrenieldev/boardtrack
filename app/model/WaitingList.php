<?php
/**
 * BoardTrack — WaitingList Model
 * app/model/WaitingList.php
 */

class WaitingList extends Model
{
    protected string $table = 'waiting_list';

    public function countByWhere(string $where): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$where}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function addTenant(int $tenantId, string $roomType): int
    {
        // Remove any existing waiting entry for this tenant first
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE tenant_id = :tid");
        $stmt->execute([':tid' => $tenantId]);
        return $this->insert([
            'tenant_id'            => $tenantId,
            'room_type_preference' => $roomType,
            'status'               => 'waiting',
        ]);
    }

    public function getQueueWithDetails(): array
    {
        $sql = "SELECT wl.*, u.name AS tenant_name, u.email AS tenant_email, u.status AS user_status,
                       wl.room_type_preference, t.id AS tenant_id,
                       wl.created_at AS approved_at,
                       DATEDIFF(NOW(), wl.created_at) AS wait_days
                FROM {$this->table} wl
                JOIN tenants t ON wl.tenant_id = t.id
                JOIN users   u ON t.user_id    = u.id
                WHERE wl.status = 'waiting'
                ORDER BY wl.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
