<?php
/**
 * BoardTrack — Announcement Model
 * app/model/Announcement.php
 */

class Announcement extends Model
{
    protected string $table = 'announcements';

    /**
     * Get active announcements
     */
    public function getActive(int $limit = null): array
    {
        $sql = "SELECT a.*, u.name as author_name
                FROM {$this->table} a
                JOIN users u ON a.created_by = u.id
                WHERE a.is_active = 1
                AND (a.expires_at IS NULL OR a.expires_at > NOW())
                ORDER BY a.priority DESC, a.created_at DESC";
        
        if ($limit) {
            $limit = (int)$limit;
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all announcements with author
     */
    public function getAllWithAuthor(): array
    {
        $sql = "SELECT a.*, u.name as author_name
                FROM {$this->table} a
                JOIN users u ON a.created_by = u.id
                ORDER BY a.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Create announcement
     */
    public function createAnnouncement(array $data, int $createdBy): int
    {
        return $this->insert([
            'title' => $data['title'],
            'content' => $data['content'] ?? '',
            'event_date' => $data['event_date'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'created_by' => $createdBy,
            'expires_at' => $data['expires_at'] ?? null,
        ]);
    }

    /**
     * Toggle announcement active status
     */
    public function toggleActive(int $id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET is_active = NOT is_active 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get recent announcements for dashboard
     */
    public function getRecent(int $limit = 5): array
    {
        return $this->getActive($limit);
    }

    /**
     * Get announcement statistics
     */
    public function getStatistics(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(is_active = 1) as active,
                    SUM(is_active = 0) as inactive,
                    SUM(is_active = 0) as expired
                FROM {$this->table}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch() ?: [];
    }
}

