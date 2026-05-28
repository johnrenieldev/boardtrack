<?php
/**
 * BoardTrack — Landlord Model
 * app/models/Landlord.php
 * 
 * Works with the shared `users` table, filtering by role = 'landlord'
 */

class Landlord extends Model
{
    protected string $table = 'users';

    /**
     * Find landlord by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT id, name, email, role, status, last_login, created_at, updated_at
                FROM {$this->table}
                WHERE id = :id AND role = 'landlord'
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find landlord by email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT id, name, email, password_hash, role, status, last_login, created_at
                FROM {$this->table}
                WHERE email = :email AND role = 'landlord'
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get landlord profile
     */
    public function getProfile(int $id): ?array
    {
        return $this->findById($id);
    }

    /**
     * Update landlord profile
     */
    public function updateProfile(int $id, array $data): bool
    {
        $allowedFields = ['name', 'email'];
        $updates = [];
        $params = [':id' => $id];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE {$this->table} 
                SET " . implode(', ', $updates) . ", updated_at = NOW()
                WHERE id = :id AND role = 'landlord'";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Update landlord password
     */
    public function updatePassword(int $id, string $hashedPassword): bool
    {
        $sql = "UPDATE {$this->table}
                SET password_hash = :password_hash, updated_at = NOW()
                WHERE id = :id AND role = 'landlord'";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password_hash' => $hashedPassword,
            ':id' => $id
        ]);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $id): bool
    {
        $sql = "UPDATE {$this->table}
                SET last_login = NOW()
                WHERE id = :id AND role = 'landlord'";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get all landlords
     */
    public function getAll(): array
    {
        $sql = "SELECT id, name, email, status, last_login, created_at
                FROM {$this->table}
                WHERE role = 'landlord'
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
