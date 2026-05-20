<?php
/**
 * BoardTrack — Tenant Model
 * app/model/Tenant.php
 */

class Tenant extends Model
{
    protected string $table = 'tenants';

    /**
     * Find tenant by user ID with full details
     */
    public function findByUserId(int $userId): ?array
    {
        $sql = "SELECT t.*,
                       u.name, u.email, u.status as user_status, u.role,
                       r.room_number, r.room_type, r.floor, r.monthly_rent
                FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN rooms r ON t.room_id = r.id
                WHERE t.user_id = :user_id
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get all tenants with optional filters
     */
    public function getAllWithFilters(array $filters = [], string $orderBy = 't.created_at DESC'): array
    {
        $where = ['u.role = :role'];
        $params = [':role' => 'tenant'];

        if (!empty($filters['status'])) {
            $where[] = 'u.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['room_type'])) {
            $where[] = 't.room_type_preference = :room_type';
            $params[':room_type'] = $filters['room_type'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(u.name LIKE :search OR u.email LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['has_room'])) {
            $where[] = 't.room_id IS NOT NULL';
        }

        if (!empty($filters['no_room'])) {
            $where[] = 't.room_id IS NULL';
        }

        $sql = "SELECT t.*,
                       u.name, u.email, u.status as user_status, u.created_at as registered_at,
                       r.room_number, r.room_type as assigned_room_type
                FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN rooms r ON t.room_id = r.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY u.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get pending tenants awaiting approval
     */
    public function getPendingTenants(): array
    {
        return $this->getAllWithFilters(['status' => 'pending']);
    }

    /**
     * Get tenants on waiting list
     */
    public function getWaitingListTenants(): array
    {
        return $this->getAllWithFilters(['status' => 'waiting_list']);
    }

    /**
     * Get active tenants with room assignments
     */
    public function getActiveTenants(): array
    {
        return $this->getAllWithFilters(['status' => 'active', 'has_room' => true]);
    }

    /**
     * Get tenants by room ID
     */
    public function getByRoomId(int $roomId): array
    {
        $sql = "SELECT t.*, u.name, u.email, u.status
                FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                WHERE t.room_id = :room_id
                ORDER BY t.move_in_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':room_id' => $roomId]);
        return $stmt->fetchAll();
    }

    /**
     * Create tenant profile after user registration
     */
    public function createProfile(int $userId, array $data): int
    {
        $row = [
            'user_id'              => $userId,
            'room_type_preference' => $data['room_type_preference'] ?? $data['room_preference'] ?? null,
            'id_document_path'     => $data['id_document_path'] ?? $data['id_file_path'] ?? null,
            'guardian_name'        => $data['guardian_name'] ?? null,
            'guardian_email'       => $data['guardian_email'] ?? null,
            'guardian_purpose'     => $data['guardian_purpose'] ?? null,
        ];
        // Optional legacy column on some installs
        if (!empty($data['guardian_name']) && $this->hasColumn('emergency_contact_name')) {
            $row['emergency_contact_name'] = $data['guardian_name'];
        }
        return $this->insert($row);
    }

    /**
     * Assign room to tenant
     */
    public function assignRoom(int $tenantId, int $roomId, ?string $moveInDate = null): bool
    {
        $data = ['room_id' => $roomId];
        if ($moveInDate) {
            $data['move_in_date'] = $moveInDate;
        }
        
        $affected = $this->update($data, ['id' => $tenantId]);
        return $affected > 0;
    }

    /**
     * Remove tenant from room
     */
    public function removeFromRoom(int $tenantId, ?string $moveOutDate = null): bool
    {
        $data = ['room_id' => null];
        if ($moveOutDate) {
            $data['move_out_date'] = $moveOutDate;
        }
        
        $affected = $this->update($data, ['id' => $tenantId]);
        return $affected > 0;
    }

    /**
     * Mark personality as completed
     */
    public function markPersonalityCompleted(int $tenantId): bool
    {
        $affected = $this->update([
            'personality_completed' => 1,
        ], ['id' => $tenantId]);
        return $affected > 0;
    }

    /**
     * Flag suspicious personality answers (stores reason in notes field)
     */
    public function flagPersonality(int $tenantId, string $reason): bool
    {
        $affected = $this->update([
            'notes' => 'PERSONALITY FLAGGED: ' . $reason,
        ], ['id' => $tenantId]);
        return $affected > 0;
    }

    /**
     * Get tenant count by status
     */
    public function countByStatus(string $status): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                WHERE u.status = :status AND u.role = 'tenant'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get tenant onboarding status (derived from user status + personality + room)
     */
    public function getOnboardingStatus(int $userId): string
    {
        $tenant = $this->findByUserId($userId);
        if (!$tenant) return 'registered';
        $status = 'registered';
        if ($tenant['personality_completed']) $status = 'quiz_done';
        if ($status === 'quiz_done' && $tenant['user_status'] === 'active' && !$tenant['room_id']) $status = 'approved';
        if ($tenant['room_id']) $status = 'complete';
        return $status;
    }

    /**
     * Update tenant notes (general-purpose status tracking)
     */
    public function setTenantStatus(int $tenantId, string $status): bool
    {
        return $this->update(['notes' => 'status:' . $status], ['id' => $tenantId]);
    }

    /**
     * Check if onboarding complete (has room assigned)
     */
    public function isOnboardingComplete(int $tenantId): bool
    {
        $tenant = $this->find($tenantId);
        return $tenant && $tenant['room_id'] !== null;
    }

    /**
     * Get tenant with personality
     */
    public function getWithPersonality(int $tenantId): ?array
    {
        $sql = "SELECT t.*,
                       u.name, u.email, u.status AS user_status, u.created_at as registered_at,
                       r.room_number, r.room_type, r.floor, r.monthly_rent,
                       GROUP_CONCAT(CONCAT(pa.question_id, ':', pa.answer_value) SEPARATOR '|') as answers
                FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN rooms r ON t.room_id = r.id
                LEFT JOIN personality_answers pa ON pa.tenant_id = t.id
                WHERE t.id = :tenant_id
                GROUP BY t.id
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

