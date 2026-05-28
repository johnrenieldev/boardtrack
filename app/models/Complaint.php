<?php
/**
 * BoardTrack — Complaint Model
 * app/models/Complaint.php
 */

class Complaint extends Model
{
    protected string $table = 'complaints';

    public function getAllWithTenants(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[]           = 'c.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $where[]             = 'c.category = :cat';
            $params[':cat']      = $filters['category'];
        }
        $sql = "SELECT c.*,
                       CASE WHEN c.is_anonymous = 1 THEN 'Anonymous' ELSE u.name END AS display_name,
                       u.id AS user_id, r.room_number
                FROM {$this->table} c
                JOIN tenants t ON c.tenant_id = t.id
                JOIN users   u ON t.user_id   = u.id
                LEFT JOIN rooms r ON t.room_id = r.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByTenantId(int $tenantId): array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE tenant_id = :tid ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function getWithTenant(int $id): ?array
    {
        $sql = "SELECT c.*,
                       CASE WHEN c.is_anonymous = 1 THEN 'Anonymous' ELSE u.name END AS display_name,
                       u.id AS user_id, u.email AS tenant_email, r.room_number
                FROM {$this->table} c
                JOIN tenants t ON c.tenant_id = t.id
                JOIN users   u ON t.user_id   = u.id
                LEFT JOIN rooms r ON t.room_id = r.id
                WHERE c.id = :id LIMIT 1";
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
     * Submit a new complaint
     */
    public function submit(int $tenantId, array $data): int
    {
        return $this->insert([
            'tenant_id'    => $tenantId,
            'title'        => $data['title'],
            'category'     => $data['category'] ?? 'other',
            'description'  => $data['description'],
            'is_anonymous' => !empty($data['is_anonymous']) ? 1 : 0,
            'status'       => 'pending',
        ]);
    }

    /**
     * Update a complaint — only if it belongs to the tenant and is still pending
     */
    public function updateByTenant(int $id, int $tenantId, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET category = :category, title = :title, description = :description,
                 is_anonymous = :is_anonymous, updated_at = NOW()
             WHERE id = :id AND tenant_id = :tenant_id AND status = 'pending'"
        );
        return $stmt->execute([
            ':category'     => $data['category'],
            ':title'        => $data['title'],
            ':description'  => $data['description'],
            ':is_anonymous' => $data['is_anonymous'],
            ':id'           => $id,
            ':tenant_id'    => $tenantId,
        ]);
    }

    /**
     * Delete a complaint — only if it belongs to the tenant and is still pending
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
     * Hard delete by landlord (any status)
     */
    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getRecent(int $limit = 5): array
    {
        $limit = (int)$limit;
        $sql  = "SELECT c.*,
                       CASE WHEN c.is_anonymous = 1 THEN NULL ELSE u.name END AS tenant_name
                FROM {$this->table} c
                JOIN tenants t ON c.tenant_id = t.id
                JOIN users   u ON t.user_id   = u.id
                WHERE c.status != 'resolved'
                ORDER BY c.created_at DESC
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
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
                FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch() ?: [];
    }
}
