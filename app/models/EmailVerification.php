<?php
/**
 * BoardTrack | EmailVerification Model
 * app/models/EmailVerification.php
 */
class EmailVerification extends Model
{
    protected string $table = 'email_verifications';

    public function createToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + VERIFY_TOKEN_TTL);

        $sql = "INSERT INTO {$this->table} (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':token' => $token,
            ':expires_at' => $expiresAt
        ]);

        return $token;
    }

    public function verifyToken(string $token): ?int
    {
        $sql = "SELECT user_id FROM {$this->table} 
                WHERE token = :token 
                AND used_at IS NULL 
                AND expires_at > NOW() 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch();

        if ($row) {
            $this->markAsUsed($token);
            return (int) $row['user_id'];
        }

        return null;
    }

    private function markAsUsed(string $token): void
    {
        $sql = "UPDATE {$this->table} SET used_at = NOW() WHERE token = :token";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
    }
}
