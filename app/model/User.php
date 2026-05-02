<?php
/**
 * BoardTrack — User Model
 * app/model/User.php
 *
 * FIXED:
 *  - Added countByWhere() method (used by LandlordController::dashboard())
 *  - findByEmail() selects password_hash (DB column name)
 *  - createUser() inserts into password_hash column
 */

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $sql  = "SELECT id, name, email, password_hash, role, status FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $sql  = "SELECT id, name, email, role, status, last_login, created_at FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function emailExists(string $email): bool
    {
        $sql  = "SELECT 1 FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * FIX: countByWhere() — raw WHERE clause count.
     * Base model count() signature may differ; this is safe and explicit.
     */
    public function countByWhere(string $where): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE {$where}");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * FIX: inserts into password_hash column (matches DB schema from setup.php)
     */
    public function createUser(array $data): int
    {
        $sql = "INSERT INTO users (name, email, password_hash, role, status, created_at)
                VALUES (:name, :email, :password_hash, :role, :status, NOW())";
        $stmt = $this->db->prepare($sql);
        $ok   = $stmt->execute([
            ':name'          => $data['name'],
            ':email'         => $data['email'],
            ':password_hash' => $data['password'],  // caller passes hashed value
            ':role'          => $data['role']   ?? 'tenant',
            ':status'        => $data['status'] ?? 'pending',
        ]);
        return $ok ? (int)$this->db->lastInsertId() : 0;
    }

    public function updateStatus(int $userId, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET status = :s, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([':s' => $status, ':id' => $userId]);
    }

    public function updatePassword(int $userId, string $hashed): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET password_hash = :ph, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([':ph' => $hashed, ':id' => $userId]);
    }
}