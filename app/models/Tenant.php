<?php
/**
 * BoardTrack — Tenant Model
 * app/models/Tenant.php
 */

class Tenant extends Model
{
    protected string $table = 'tenants';

    /**
     * Find a single row by primary key (overridden to join users)
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT t.*, u.name, u.email, u.status as user_status, u.role
                FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                WHERE t.id = :id
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

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
    public function getAllWithFilters(array $filters = []): array
    {
        $where = ['u.role = :role'];
        $params = [':role' => 'tenant'];

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $where[] = "u.status = 'approved' AND t.room_id IS NOT NULL";
            } elseif ($filters['status'] === 'waiting_list') {
                $where[] = "u.status = 'approved' AND t.room_id IS NULL";
            } elseif ($filters['status'] === 'approved') {
                $where[] = "u.status = 'approved'";
            } elseif ($filters['status'] === 'ready_for_review') {
                $where[] = "u.status = 'pending' AND t.personality_completed = 1 AND t.id_document_path IS NOT NULL AND t.id_document_path != ''";
            } else {
                $where[] = 'u.status = :status';
                $params[':status'] = $filters['status'];
            }
        }

        if (!empty($filters['room_type'])) {
            $where[] = 't.room_type_preference = :room_type';
            $params[':room_type'] = $filters['room_type'];
        }

        if (!empty($filters['gender'])) {
            $where[] = 't.gender = :gender';
            $params[':gender'] = $filters['gender'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(u.name LIKE :search_name OR u.email LIKE :search_email)';
            $params[':search_name'] = '%' . $filters['search'] . '%';
            $params[':search_email'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['has_room'])) {
            $where[] = 't.room_id IS NOT NULL';
        }

        if (!empty($filters['no_room'])) {
            $where[] = 't.room_id IS NULL';
        }

        if (!empty($filters['compatibility'])) {
            $comp = $filters['compatibility'];
            if ($comp === 'excellent') $where[] = 'tcc.compatibility_score >= 90';
            elseif ($comp === 'good') $where[] = 'tcc.compatibility_score >= 75 AND tcc.compatibility_score < 90';
            elseif ($comp === 'moderate') $where[] = 'tcc.compatibility_score >= 50 AND tcc.compatibility_score < 75';
            elseif ($comp === 'poor') $where[] = 'tcc.compatibility_score < 50';
        }

        $sql = "SELECT t.*,
                       u.name, u.email, u.status as user_status, u.created_at as registered_at,
                       r.room_number, r.room_type as assigned_room_type,
                       tcc.compatibility_score, tcc.compatibility_status
                FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN rooms r ON t.room_id = r.id
                LEFT JOIN tenant_compatibility_cache tcc ON t.id = tcc.tenant_id 
                     AND (tcc.room_id = t.room_id OR (t.room_id IS NULL AND tcc.compatibility_score = (SELECT MAX(compatibility_score) FROM tenant_compatibility_cache WHERE tenant_id = t.id)))
                WHERE " . implode(' AND ', $where) . "
                GROUP BY t.id
                ORDER BY u.created_at DESC";

        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            throw $e;
        }
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
        $sql = "SELECT t.*, u.name, u.email, u.status as user_status
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
            'air_conditioned_preference' => (int)($data['air_conditioned_preference'] ?? 0),
            'id_document_path'     => $data['id_document_path'] ?? $data['id_file_path'] ?? null,
            'guardian_name'        => $data['guardian_name'] ?? null,
            'guardian_email'       => $data['guardian_email'] ?? null,
            'guardian_purpose'     => $data['guardian_purpose'] ?? null,
        ];

        // Backward compatibility: older deployments may not yet have this column.
        if (!$this->hasColumn('air_conditioned_preference')) {
            unset($row['air_conditioned_preference']);
        }

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
     * Count active approved tenants (assigned a room)
     */
    public function countActive(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                WHERE u.status = 'approved' AND u.role = 'tenant' AND t.room_id IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Count waiting approved tenants (placed on waiting list)
     */
    public function countWaiting(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                WHERE u.status = 'approved' AND u.role = 'tenant' AND t.room_id IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
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
        if ($status === 'quiz_done' && $tenant['user_status'] === 'approved' && !$tenant['room_id']) $status = 'approved';
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

