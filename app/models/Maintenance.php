<?php
/**
 * BoardTrack — Maintenance Model
 * app/models/Maintenance.php
 */

class Maintenance extends Model
{
    protected string $table = 'maintenance_requests';

    public function getAllWithDetails(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[]           = 'm.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $where[]              = 'm.priority = :priority';
            $params[':priority'] = $filters['priority'];
        }
        if (!empty($filters['category'])) {
            $where[]             = 'm.category = :category';
            $params[':category'] = $filters['category'];
        }
        $sql = "SELECT m.*, u.name AS tenant_name, u.email AS tenant_email, r.room_number
                FROM {$this->table} m
                JOIN users u ON m.tenant_id = u.id
                LEFT JOIN rooms r ON m.room_id = r.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY m.requested_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByTenantId(int $tenantId): array
    {
        $sql  = "SELECT m.*, r.room_number
                FROM {$this->table} m
                LEFT JOIN rooms r ON m.room_id = r.id
                WHERE m.tenant_id = :tid
                ORDER BY m.requested_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT m.*, u.name AS tenant_name, u.email AS tenant_email, r.room_number
                FROM {$this->table} m
                JOIN users u ON m.tenant_id = u.id
                LEFT JOIN rooms r ON m.room_id = r.id
                WHERE m.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getPendingCount(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status IN ('pending','in_progress')");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Submit a new maintenance request
     */
    public function submit(int $tenantId, int $roomId, array $data): int
    {
        return $this->insert([
            'tenant_id'    => $tenantId,
            'room_id'      => $roomId,
            'title'        => $data['title'],
            'description'  => $data['description'],
            'category'     => $data['category'] ?? 'other',
            'priority'     => $data['priority'] ?? 'medium',
            'status'       => 'pending',
        ]);
    }

    /**
     * Update maintenance request status (by landlord)
     */
    public function updateStatus(int $id, string $status, array $data = []): bool
    {
        $fields = ['status = :status', 'updated_at = NOW()'];
        $params = [':status' => $status, ':id' => $id];

        if (!empty($data['assigned_to'])) {
            $fields[] = 'assigned_to = :assigned_to';
            $params[':assigned_to'] = $data['assigned_to'];
        }
        if (!empty($data['scheduled_at'])) {
            $fields[] = 'scheduled_at = :scheduled_at';
            $params[':scheduled_at'] = $data['scheduled_at'];
        }
        if (!empty($data['estimated_cost'])) {
            $fields[] = 'estimated_cost = :estimated_cost';
            $params[':estimated_cost'] = $data['estimated_cost'];
        }
        if ($status === 'completed' && !empty($data['actual_cost'])) {
            $fields[] = 'actual_cost = :actual_cost';
            $fields[] = 'completed_at = NOW()';
            $params[':actual_cost'] = $data['actual_cost'];
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Update a maintenance request — only if it belongs to the tenant and is still pending
     */
    public function updateByTenant(int $id, int $tenantId, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET title = :title, description = :description, category = :category,
                 priority = :priority, updated_at = NOW()
             WHERE id = :id AND tenant_id = :tenant_id AND status = 'pending'"
        );
        return $stmt->execute([
            ':title'       => $data['title'],
            ':description' => $data['description'],
            ':category'    => $data['category'],
            ':priority'    => $data['priority'],
            ':id'          => $id,
            ':tenant_id'   => $tenantId,
        ]);
    }

    /**
     * Delete a maintenance request — only if it belongs to the tenant and is still pending
     */
    public function deleteByTenant(int $id, int $tenantId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table}
             WHERE id = :id AND tenant_id = :tenant_id AND status = 'pending'"
        );
        return $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
    }

    /**
     * Link maintenance request to a bill
     */
    public function linkToBill(int $id, int $billId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET bill_id = :bill_id WHERE id = :id"
        );
        return $stmt->execute([':bill_id' => $billId, ':id' => $id]);
    }

    public function getRecent(int $limit = 5): array
    {
        $limit = (int)$limit;
        $sql  = "SELECT m.*, u.name AS tenant_name, r.room_number
                FROM {$this->table} m
                JOIN users u ON m.tenant_id = u.id
                LEFT JOIN rooms r ON m.room_id = r.id
                WHERE m.status NOT IN ('completed', 'cancelled', 'rejected')
                ORDER BY m.requested_at DESC
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStatistics(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch() ?: [];
    }
}
