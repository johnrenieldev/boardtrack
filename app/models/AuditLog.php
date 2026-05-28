<?php
/**
 * BoardTrack — AuditLog Model
 * app/models/AuditLog.php
 */

class AuditLog extends Model
{
    protected string $table = 'audit_logs';

    public function log(
        ?int   $userId,
        string $action,
        string $entityType,
        int    $entityId,
        ?array $oldValues,
        ?array $newValues,
        string $description = ''
    ): int {
        // Merge description into new_values JSON (description column does not exist in DB)
        $merged = $newValues ?? [];
        if ($description !== '') {
            $merged['_description'] = $description;
        }

        return $this->insert([
            'user_id'     => $userId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'old_values'  => $oldValues  ? json_encode($oldValues)  : null,
            'new_values'  => !empty($merged) ? json_encode($merged) : null,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    public function getAll(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['action'])) {
            $where[]            = 'al.action = :action';
            $params[':action']  = $filters['action'];
        }
        if (!empty($filters['entity'])) {
            $where[]            = 'al.entity_type = :entity';
            $params[':entity']  = $filters['entity'];
        }
        if (!empty($filters['date_from'])) {
            $where[]              = 'DATE(al.created_at) >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]            = 'DATE(al.created_at) <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }
        $limit  = (int)($filters['limit'] ?? 100);
        $page   = max(1, (int)($filters['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        $sql = "SELECT al.*, u.name AS user_name, u.role AS user_role
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY al.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count all audit log entries matching filters (for pagination)
     */
    public function countAll(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['action'])) {
            $where[]            = 'action = :action';
            $params[':action']  = $filters['action'];
        }
        if (!empty($filters['entity'])) {
            $where[]            = 'entity_type = :entity';
            $params[':entity']  = $filters['entity'];
        }
        if (!empty($filters['date_from'])) {
            $where[]              = 'DATE(created_at) >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]            = 'DATE(created_at) <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}