<?php
/**
 * BoardTrack — WaitingList Model
 * app/models/WaitingList.php
 */

class WaitingList extends Model
{
    protected string $table = 'waiting_list';

    /**
     * Check if a column exists on the provided table.
     * Used for backward compatibility when new migrations are not yet applied.
     */
    private function columnExists(string $table, string $column): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table
               AND COLUMN_NAME = :col'
        );
        $stmt->execute([':table' => $table, ':col' => $column]);
        return (int) $stmt->fetchColumn() > 0;
    }

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
        $airconSelect = $this->columnExists('tenants', 'air_conditioned_preference')
            ? 't.air_conditioned_preference AS air_conditioned_preference'
            : '0 AS air_conditioned_preference';

        $sql = "SELECT wl.*, u.name AS tenant_name, u.email AS tenant_email, u.status AS user_status,
                       wl.room_type_preference, {$airconSelect}, t.id AS tenant_id,
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
