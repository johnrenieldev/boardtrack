<?php
/**
 * BoardTrack — Notification Model
 * app/model/Notification.php
 */

class Notification extends Model
{
    protected string $table = 'notifications';

    public function createNotification(int $userId, string $type, string $title, string $message, string $link = ''): int
    {
        return $this->insert([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'is_read' => 0,
            'link_url'=> $link,
        ]);
    }

    public function createNotificationsBulk(array $userIds, string $type, string $title, string $message, string $link = ''): int
    {
        if (empty($userIds)) return 0;
        
        $columns = ['user_id', 'title', 'message', 'type', 'is_read', 'link_url'];
        $rows = [];
        
        foreach ($userIds as $uid) {
            $rows[] = [
                'user_id' => $uid,
                'title'   => $title,
                'message' => $message,
                'type'    => $type,
                'is_read' => 0,
                'link_url'=> $link
            ];
        }
        
        return $this->bulkInsert($columns, $rows);
    }

    public function getForUser(int $userId, bool $unreadOnly = false, int $limit = 20): array
    {
        $limit = (int)$limit;
        $where = 'user_id = :uid';
        if ($unreadOnly) $where .= ' AND is_read = 0';
        $sql  = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function getUnreadCount(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :uid AND is_read = 0");
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function markRead(int $id, int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_read = 1 WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => $id, ':uid' => $userId]);
    }

    public function markAllRead(int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_read = 1 WHERE user_id = :uid");
        $stmt->execute([':uid' => $userId]);
    }

    public function getAnnouncements(int $userId, int $limit = 5): array
    {
        $limit = (int)$limit;
        $sql   = "SELECT * FROM {$this->table} WHERE user_id = :uid AND type = 'announcement' ORDER BY created_at DESC LIMIT {$limit}";
        $stmt  = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }
}