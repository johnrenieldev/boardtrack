<?php
/**
 * BoardTrack — Complaint Message Model
 * app/models/ComplaintMessage.php
 * 
 * Handles conversation messages for complaints
 */
class ComplaintMessage extends Model
{
    protected string $table = 'complaint_messages';

    /**
     * Get all messages for a complaint (conversation thread)
     */
    public function getByComplaintId(int $complaintId): array
    {
        $sql = "SELECT cm.*, u.name as user_name
                FROM {$this->table} cm
                JOIN users u ON cm.user_id = u.id
                WHERE cm.complaint_id = :cid
                ORDER BY cm.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid' => $complaintId]);
        return $stmt->fetchAll();
    }

    /**
     * Add a message to a complaint
     */
    public function addMessage(int $complaintId, int $userId, string $userType, string $message): int
    {
        $messageId = $this->insert([
            'complaint_id' => $complaintId,
            'user_id' => $userId,
            'user_type' => $userType,
            'message' => $message,
        ]);

        // Update complaint's last_message_at timestamp
        if ($messageId) {
            $complaintModel = new Complaint();
            $complaintModel->update([
                'last_message_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['id' => $complaintId]);
        }

        return $messageId;
    }

    /**
     * Get message count for a complaint
     */
    public function getMessageCount(int $complaintId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE complaint_id = :cid"
        );
        $stmt->execute([':cid' => $complaintId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get last message for a complaint
     */
    public function getLastMessage(int $complaintId): ?array
    {
        $sql = "SELECT cm.*, u.name as user_name
                FROM {$this->table} cm
                JOIN users u ON cm.user_id = u.id
                WHERE cm.complaint_id = :cid
                ORDER BY cm.created_at DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid' => $complaintId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
