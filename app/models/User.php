<?php
/**
 * User data access model.
 *
 * Provides account lifecycle, authentication support, and 2FA persistence methods.
 */

class User extends Model
{
    protected string $table = 'users';

    // Lookup queries.

    /**
     * Find user by email.
     * Returns columns needed for login + 2FA.
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT id, name, email, password_hash, role, status,
                       totp_enabled, totp_secret, totp_verified_at, recovery_codes
                FROM users
                WHERE email = :email
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find user by ID.
     * Includes 2FA status fields (secret is NOT returned — fetch it separately with get2FASecret).
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT id, name, email, role, status,
                       totp_enabled, totp_verified_at,
                       last_login, created_at
                FROM users
                WHERE id = :id
                LIMIT 1";
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

    // Create and update operations.

    /**
     * Create a new user. Inserts into password_hash column.
     */
    public function createUser(array $data): int
    {
        $sql = "INSERT INTO users (name, email, password_hash, role, status, created_at)
                VALUES (:name, :email, :password_hash, :role, :status, NOW())";
        $stmt = $this->db->prepare($sql);
        $ok   = $stmt->execute([
            ':name'          => $data['name'],
            ':email'         => $data['email'],
            ':password_hash' => $data['password'],   // caller passes bcrypt hash
            ':role'          => $data['role']   ?? 'tenant',
            ':status'        => $data['status'] ?? 'pending',
        ]);
        return $ok ? (int) $this->db->lastInsertId() : 0;
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

    /**
     * Used by LandlordController::dashboard()
     */
    public function countByWhere(string $where): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE {$where}");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /** Primary landlord account (for tenant payment QR, etc.) */
    public function getLandlordAccount(): ?array
    {
        $sql = "SELECT id, name, email, gcash_qr_path FROM users WHERE role = 'landlord' ORDER BY id ASC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateGcashQr(int $userId, ?string $filename): bool
    {
        return $this->update(['gcash_qr_path' => $filename], ['id' => $userId]) > 0;
    }

    // 2FA setup operations.

    /**
     * Save a newly generated TOTP secret for a user (2FA not yet enabled — pending verification).
     * The secret is stored encrypted-at-rest via the app layer (plain Base32 stored in DB).
     * Never expose this value in HTML output directly.
     */
    public function savePendingTotpSecret(int $userId, string $secret): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET totp_secret = :s, totp_enabled = 0, updated_at = NOW() WHERE id = :id"
        );
        return $stmt->execute([':s' => $secret, ':id' => $userId]);
    }

    /**
     * Enable 2FA after user has verified they can generate a valid TOTP code.
     * Stores hashed recovery codes as JSON.
     */
    public function enableTotp(int $userId, string $secret, array $hashedRecoveryCodes): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users
             SET totp_secret      = :s,
                 totp_enabled     = 1,
                 totp_verified_at = NOW(),
                 recovery_codes   = :rc,
                 updated_at       = NOW()
             WHERE id = :id"
        );
        return $stmt->execute([
            ':s'  => $secret,
            ':rc' => json_encode($hashedRecoveryCodes),
            ':id' => $userId,
        ]);
    }

    /**
     * Disable and reset 2FA for a user.
     */
    public function disableTotp(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users
             SET totp_secret      = NULL,
                 totp_enabled     = 0,
                 totp_verified_at = NULL,
                 recovery_codes   = NULL,
                 updated_at       = NOW()
             WHERE id = :id"
        );
        return $stmt->execute([':id' => $userId]);
    }

    // 2FA verification and credential retrieval.

    /**
     * Fetch only the TOTP secret for a user (server-side only, never sent to HTML).
     */
    public function get2FASecret(int $userId): ?string
    {
        $stmt = $this->db->prepare("SELECT totp_secret FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return $row ? ($row['totp_secret'] ?? null) : null;
    }

    /**
     * Fetch recovery codes JSON for a user.
     */
    public function getRecoveryCodes(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT recovery_codes FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        if (!$row || !$row['recovery_codes']) return null;
        return json_decode($row['recovery_codes'], true);
    }

    /**
     * Save updated (used-code-removed) recovery codes back to DB.
     */
    public function saveRecoveryCodes(int $userId, array $hashedCodes): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET recovery_codes = :rc, updated_at = NOW() WHERE id = :id"
        );
        return $stmt->execute([':rc' => json_encode($hashedCodes), ':id' => $userId]);
    }

    /**
     * Check whether 2FA is enabled for a user (fast, no secret fetch).
     */
    public function is2FAEnabled(int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT totp_enabled FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return $row && (bool) $row['totp_enabled'];
    }

    /**
     * Fetch the pending (not yet enabled) totp_secret for QR display during setup.
     * Returns null if no secret has been generated yet.
     */
    public function getPendingTotpSecret(int $userId): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT totp_secret FROM users WHERE id = :id AND totp_enabled = 0 LIMIT 1"
        );
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return $row ? ($row['totp_secret'] ?? null) : null;
    }
}