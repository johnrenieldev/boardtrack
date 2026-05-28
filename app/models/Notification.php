<?php
/**
 * BoardTrack — Notification Model
 * app/models/Notification.php
 */

class Notification extends Model
{
    protected string $table = 'notifications';

    /**
     * Create a single notification for one user.
     */
    public function createNotification(int $userId, string $type, string $title, string $message, string $link = ''): int
    {
        return $this->insert([
            'user_id'  => $userId,
            'title'    => $title,
            'message'  => $message,
            'type'     => $type,
            'is_read'  => 0,
            'link_url' => $link,
        ]);
    }

    /**
     * Create notifications for multiple users in one operation.
     */
    public function createNotificationsBulk(array $userIds, string $type, string $title, string $message, string $link = ''): int
    {
        if (empty($userIds)) return 0;

        $columns = ['user_id', 'title', 'message', 'type', 'is_read', 'link_url'];
        $rows    = [];

        foreach ($userIds as $uid) {
            $rows[] = [
                'user_id'  => (int) $uid,
                'title'    => $title,
                'message'  => $message,
                'type'     => $type,
                'is_read'  => 0,
                'link_url' => $link,
            ];
        }

        return $this->bulkInsert($columns, $rows);
    }

    /**
     * Fetch notifications for a user, newest first.
     *
     * @param int  $userId
     * @param bool $unreadOnly  When true, returns only unread notifications.
     * @param int  $limit       Maximum number of records to return.
     */
    public function getForUser(int $userId, bool $unreadOnly = false, int $limit = 50): array
    {
        $limit = max(1, (int) $limit);
        $where = 'user_id = :uid';
        if ($unreadOnly) {
            $where .= ' AND is_read = 0';
        }

        $sql  = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Returns true when the user has at least one unread notification.
     * Used by Controller::view() to set the hasUnreadNotifications flag
     * for the navbar/sidebar red dot.
     */
    public function hasUnread(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT EXISTS(SELECT 1 FROM {$this->table} WHERE user_id = :uid AND is_read = 0)"
        );
        $stmt->execute([':uid' => $userId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Mark a single notification as read.
     * Ownership enforced: WHERE id = :id AND user_id = :uid.
     */
    public function markRead(int $id, int $userId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET is_read = 1, read_at = NOW() WHERE id = :id AND user_id = :uid"
        );
        $stmt->execute([':id' => $id, ':uid' => $userId]);
    }

    /**
     * Fetch announcement-type notifications for a user.
     */
    public function getAnnouncements(int $userId, int $limit = 5): array
    {
        $limit = max(1, (int) $limit);
        $sql   = "SELECT * FROM {$this->table}
                  WHERE user_id = :uid AND type = 'announcement'
                  ORDER BY created_at DESC LIMIT {$limit}";
        $stmt  = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Delete a single notification belonging to the given user.
     * Ownership enforced: WHERE id = :id AND user_id = :uid.
     *
     * @return bool True if a row was deleted, false if not found or not owned.
     */
    public function deleteForUser(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE id = :id AND user_id = :uid"
        );
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        return $stmt->rowCount() > 0;
    }
}   