<?php
/**
 * BoardTrack — CompatibilityService
 * app/services/CompatibilityService.php
 *
 * Intelligent roommate matching engine based on weighted personality traits.
 */

class CompatibilityService
{
    private PDO $db;
    private PersonalityAnswer $personalityModel;
    private Tenant $tenantModel;
    private Room $roomModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Ensure models are loaded
        require_once APP_PATH . '/models/PersonalityAnswer.php';
        require_once APP_PATH . '/models/Tenant.php';
        require_once APP_PATH . '/models/Room.php';
        
        $this->personalityModel = new PersonalityAnswer();
        $this->tenantModel = new Tenant();
        $this->roomModel = new Room();
    }

    /**
     * Calculate compatibility for a tenant against a specific room.
     * Averages compatibility across all current roommates in that room.
     */
    public function calculateCompatibility(int $tenantId, int $roomId, bool $useCache = true): array
    {
        $tenant = $this->tenantModel->find($tenantId);
        $room = $this->roomModel->find($roomId);
        if (!$tenant || !$room) {
            return $this->formatResponse(0, "Unavailable", "gray", ["Tenant or room data could not be loaded."]);
        }

        // 1. Check if tenant has completed personality before trusting cache.
        if (!$this->personalityModel->isCompleted($tenantId)) {
            $this->clearTenantRoomCache($tenantId, $roomId);
            return $this->formatResponse(0, "Incomplete Profile", "gray", ["Complete the personality questionnaire before comparing room fit."]);
        }

        if ($useCache) {
            $cached = $this->getCompatibilityFromCache($tenantId, $roomId);
            if ($cached && !$this->isIncompleteCacheStatus($cached['status'] ?? '')) {
                return $cached;
            }
            $this->clearTenantRoomCache($tenantId, $roomId);
        }

        // 1b. Check gender compatibility
        if ($room && $room['allowed_gender'] !== 'any' && $tenant['gender'] !== $room['allowed_gender']) {
            $res = $this->formatResponse(0, "Gender Mismatch", "red", ["This room is reserved for " . $room['allowed_gender'] . "s only."]);
            $this->updateCache($tenantId, $roomId, $res);
            return $res;
        }

        // 2. Get all roommates in the room (approved users only)
        $sql = "SELECT t.id 
                FROM tenants t
                JOIN users u ON t.user_id = u.id
                WHERE t.room_id = :room_id AND u.status = 'approved' AND t.id != :tenant_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':room_id' => $roomId, ':tenant_id' => $tenantId]);
        $roommates = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // 3. If room is empty, report that honestly instead of inventing a match.
        if (empty($roommates)) {
            $res = $this->formatResponse(100, "Empty Room", "blue", ["No current roommates. Compatibility scoring is not needed yet."]);
            $this->updateCache($tenantId, $roomId, $res);
            return $res;
        }

        // 4. Calculate score against each roommate
        $totalScore = 0;
        $validRoommates = 0;
        $allReasons = [];
        $roommateBreakdown = [];

        foreach ($roommates as $roommateId) {
            if (!$this->personalityModel->isCompleted((int)$roommateId)) {
                continue; // Skip roommates with incomplete profiles
            }

            $comparison = $this->compareTwoTenants($tenantId, (int)$roommateId);
            $totalScore += $comparison['score'];
            $validRoommates++;
            
            $roommateBreakdown[] = [
                'tenant_id' => $roommateId,
                'score' => $comparison['score'],
                'status' => $comparison['status']
            ];

            // Collect reasons (unique ones)
            $allReasons = array_unique(array_merge($allReasons, $comparison['explanation']));
        }

        // 5. If no roommates have profiles, return neutral
        if ($validRoommates === 0) {
            $res = $this->formatResponse(0, "Incomplete Roommate Data", "gray", ["Current roommates have not completed their profiles yet."]);
            $this->clearTenantRoomCache($tenantId, $roomId);
            return $res;
        }

        $finalScore = round($totalScore / $validRoommates, 2);
        $statusData = $this->getStatusData($finalScore);

        $res = $this->formatResponse(
            $finalScore, 
            $statusData['status'], 
            $statusData['color'], 
            array_slice($allReasons, 0, 3), 
            $roommateBreakdown
        );

        $this->updateCache($tenantId, $roomId, $res);
        return $res;
    }

    /**
     * Get compatibility from cache.
     */
    public function getCompatibilityFromCache(int $tenantId, int $roomId): ?array
    {
        $sql = "SELECT * FROM tenant_compatibility_cache WHERE tenant_id = :tid AND room_id = :rid LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $tenantId, ':rid' => $roomId]);
        $cached = $stmt->fetch();

        if (!$cached) return null;

        return [
            'score' => (float)$cached['compatibility_score'],
            'status' => $cached['compatibility_status'],
            'color' => $this->getColorForStatus($cached['compatibility_status'], (float)$cached['compatibility_score']),
            'explanation' => json_decode($cached['reasons'] ?? '[]', true) ?: [],
            'roommate_breakdown' => [] // Cache doesn't store breakdown for simplicity
        ];
    }

    /**
     * Update or create cache entry.
     */
    private function updateCache(int $tenantId, int $roomId, array $data): void
    {
        $sql = "INSERT INTO tenant_compatibility_cache 
                (tenant_id, room_id, compatibility_score, compatibility_status, reasons)
                VALUES (:tid, :rid, :score, :status, :reasons)
                ON DUPLICATE KEY UPDATE 
                compatibility_score = VALUES(compatibility_score),
                compatibility_status = VALUES(compatibility_status),
                reasons = VALUES(reasons),
                updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tid' => $tenantId,
            ':rid' => $roomId,
            ':score' => $data['score'],
            ':status' => $data['status'],
            ':reasons' => json_encode($data['explanation'])
        ]);
    }

    /**
     * Remove all cached room matches for one tenant.
     */
    public function clearTenantCache(int $tenantId): void
    {
        $stmt = $this->db->prepare("DELETE FROM tenant_compatibility_cache WHERE tenant_id = :tid");
        $stmt->execute([':tid' => $tenantId]);
    }

    /**
     * Remove all cached tenant matches for one room.
     */
    public function clearRoomCache(int $roomId): void
    {
        $stmt = $this->db->prepare("DELETE FROM tenant_compatibility_cache WHERE room_id = :rid");
        $stmt->execute([':rid' => $roomId]);
    }

    private function clearTenantRoomCache(int $tenantId, int $roomId): void
    {
        $stmt = $this->db->prepare("DELETE FROM tenant_compatibility_cache WHERE tenant_id = :tid AND room_id = :rid");
        $stmt->execute([':tid' => $tenantId, ':rid' => $roomId]);
    }

    /**
     * Refresh all cache entries for a room.
     */
    public function refreshRoomCache(int $roomId): void
    {
        $this->clearRoomCache($roomId);

        // Get all tenants who might be interested in this room or are in it
        $sql = "SELECT t.id FROM tenants t
                JOIN users u ON t.user_id = u.id
                WHERE u.status = 'approved' OR t.room_id = :rid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':rid' => $roomId]);
        $tenants = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tenants as $tenantId) {
            $this->calculateCompatibility((int)$tenantId, $roomId, false);
        }
    }

    /**
     * Refresh all cache entries for a tenant.
     */
    public function refreshTenantCache(int $tenantId): void
    {
        $this->clearTenantCache($tenantId);

        // 1. Get tenant's current room if any
        $sql = "SELECT room_id FROM tenants WHERE id = :tid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $tenantId]);
        $currentRoomId = $stmt->fetchColumn();

        // 2. Get all available shared rooms
        $sql = "SELECT id FROM rooms WHERE room_type = 'shared' AND status = 'available'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rooms = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Add current room to the list to refresh its cache too
        if ($currentRoomId && !in_array($currentRoomId, $rooms)) {
            $rooms[] = $currentRoomId;
        }

        foreach ($rooms as $roomId) {
            $this->calculateCompatibility($tenantId, (int)$roomId, false);
        }
    }

    /**
     * Compare two tenants and return similarity score + reasons.
     */
    public function compareTwoTenants(int $tenantId1, int $tenantId2): array
    {
        $sql = "SELECT 
                    pq.id as question_id,
                    pq.question_text,
                    pq.category,
                    pq.weight,
                    pa1.answer_value as val1,
                    pa2.answer_value as val2
                FROM personality_answers pa1
                JOIN personality_answers pa2 ON pa1.question_id = pa2.question_id
                JOIN personality_questions pq ON pa1.question_id = pq.id
                WHERE pa1.tenant_id = :t1 AND pa2.tenant_id = :t2";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':t1' => $tenantId1, ':t2' => $tenantId2]);
        $matches = $stmt->fetchAll();

        if (empty($matches)) {
            return $this->formatResponse(0, "Incomplete Data", "gray", ["Insufficient data for comparison."]);
        }

        $totalWeightedScore = 0;
        $totalWeight = 0;
        $categoryScores = [];

        foreach ($matches as $m) {
            // difference = abs(answerA - answerB)
            // 0 difference = 100% (similarity 1.0)
            // 1 difference = 75%  (similarity 0.75)
            // 2 difference = 50%  (similarity 0.5)
            // 3 difference = 25%  (similarity 0.25)
            // 4 difference = 0%   (similarity 0.0)
            $diff = abs($m['val1'] - $m['val2']);
            $similarity = max(0, 1 - ($diff * 0.25));
            
            $weightedScore = $similarity * $m['weight'];
            $totalWeightedScore += $weightedScore;
            $totalWeight += $m['weight'];

            // Track category for reasons
            if (!isset($categoryScores[$m['category']])) {
                $categoryScores[$m['category']] = ['score' => 0, 'weight' => 0];
            }
            $categoryScores[$m['category']]['score'] += $weightedScore;
            $categoryScores[$m['category']]['weight'] += $m['weight'];
        }

        $score = ($totalWeight > 0) ? round(($totalWeightedScore / $totalWeight) * 100, 2) : 0;
        $statusData = $this->getStatusData($score);
        $explanation = $this->generateCompatibilityReasons($categoryScores);

        return $this->formatResponse($score, $statusData['status'], $statusData['color'], $explanation);
    }

    /**
     * Rank all shared rooms for a tenant.
     */
    public function rankRecommendedRooms(int $tenantId): array
    {
        $tenant = $this->tenantModel->find($tenantId);
        $gender = $tenant['gender'] ?? null;
        
        $where = ["r.room_type = 'shared'", "r.status = 'available'"];
        $params = [];
        
        if ($gender) {
            $where[] = "(r.allowed_gender = 'any' OR r.allowed_gender = :gender)";
            $params[':gender'] = $gender;
        }
        
        $whereSql = implode(' AND ', $where);

        // Get all approved shared rooms that aren't full
        $sql = "SELECT r.*,
                       (SELECT COUNT(*)
                          FROM tenants t
                          JOIN users u ON t.user_id = u.id
                         WHERE t.room_id = r.id AND u.status = 'approved') AS current_occupants
                FROM rooms r
                WHERE {$whereSql}
                ORDER BY r.room_number ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rooms = $stmt->fetchAll();

        $recommendations = [];
        foreach ($rooms as $room) {
            // Skip if full
            if ($room['current_occupants'] >= $room['max_occupants']) {
                continue;
            }

            $comp = $this->calculateCompatibility($tenantId, (int)$room['id'], false);
            
            // Get current roommate names
            $roommateSql = "SELECT u.name FROM tenants t JOIN users u ON t.user_id = u.id WHERE t.room_id = :rid AND u.status = 'approved'";
            $rmStmt = $this->db->prepare($roommateSql);
            $rmStmt->execute([':rid' => $room['id']]);
            $roommateNames = $rmStmt->fetchAll(PDO::FETCH_COLUMN);

            $recommendations[] = [
                'room_id' => $room['id'],
                'room_number' => $room['room_number'],
                'room_type' => $room['room_type'] ?? 'shared',
                'max_occupants' => (int)($room['max_occupants'] ?? 0),
                'allowed_gender' => $room['allowed_gender'] ?? 'any',
                'air_conditioned' => (int)($room['air_conditioned'] ?? 0),
                'monthly_rent' => (float)($room['monthly_rent'] ?? 0),
                'compatibility_score' => $comp['score'],
                'status' => $comp['status'],
                'color' => $comp['color'],
                'reasons' => $comp['explanation'],
                'current_occupants' => $room['current_occupants'],
                'current_roommates' => $roommateNames
            ];
        }

        // Sort by score DESC
        usort($recommendations, fn($a, $b) => $b['compatibility_score'] <=> $a['compatibility_score']);

        return $recommendations;
    }

    /**
     * Helper to get status text and color based on score.
     */
    private function getStatusData(float $score): array
    {
        if ($score >= 90) return ['status' => 'Excellent Match', 'color' => 'green'];
        if ($score >= 75) return ['status' => 'Good Match', 'color' => 'blue'];
        if ($score >= 50) return ['status' => 'Moderate Match', 'color' => 'orange'];
        return ['status' => 'Poor Match', 'color' => 'red'];
    }

    private function getColorForStatus(string $status, float $score): string
    {
        return match ($status) {
            'Empty Room' => 'blue',
            'Gender Mismatch' => 'red',
            'Incomplete Profile', 'Incomplete Data', 'Incomplete Roommate Data' => 'gray',
            default => $this->getStatusData($score)['color'],
        };
    }

    private function isIncompleteCacheStatus(string $status): bool
    {
        return in_array($status, ['Incomplete Profile', 'Incomplete Data', 'Incomplete Roommate Data'], true);
    }

    /**
     * Helper to format service response.
     */
    private function formatResponse(float $score, string $status, string $color, array $explanation, array $roommateBreakdown = []): array
    {
        return [
            'score' => $score,
            'status' => $status,
            'color' => $color,
            'explanation' => $explanation,
            'roommate_breakdown' => $roommateBreakdown
        ];
    }

    /**
     * Generate human-readable reasons based on category similarity.
     */
    private function generateCompatibilityReasons(array $categoryScores): array
    {
        $reasons = [];
        $labels = [
            'sleep_schedule' => 'Similar sleep schedule',
            'cleanliness'    => 'Compatible cleanliness habits',
            'noise_tolerance' => 'Aligned noise preferences',
            'study_habits'   => 'Study habits align',
            'social_preference' => 'Compatible social energy'
        ];

        foreach ($categoryScores as $cat => $data) {
            $catScore = ($data['weight'] > 0) ? ($data['score'] / $data['weight']) : 0;
            if ($catScore >= 0.8) { // 80% similarity in this category
                $reasons[] = $labels[$cat] ?? "Good " . str_replace('_', ' ', $cat) . " match";
            }
        }

        if (empty($reasons)) {
            $reasons[] = "General lifestyle compatibility";
        }

        return $reasons;
    }
}
