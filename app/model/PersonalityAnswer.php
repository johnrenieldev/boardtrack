<?php
/**
 * BoardTrack — PersonalityAnswer Model
 * app/model/PersonalityAnswer.php
 */

class PersonalityAnswer extends Model
{
    protected string $table = 'personality_answers';

    /**
     * Get all questions
     */
    public function getAllQuestions(): array
    {
        $sql = "SELECT *, question AS question_text FROM personality_questions 
                WHERE is_active = 1 
                ORDER BY sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get questions by category
     */
    public function getQuestionsByCategory(string $category): array
    {
        $sql = "SELECT *, question AS question_text FROM personality_questions 
                WHERE category = :category AND is_active = 1 
                ORDER BY sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':category' => $category]);
        return $stmt->fetchAll();
    }

    /**
     * Save answer
     */
    public function saveAnswer(int $tenantId, int $questionId, int $answerValue): int
    {
        // Check if answer exists
        $sql = "SELECT id FROM {$this->table} 
                WHERE tenant_id = :tenant_id AND question_id = :question_id 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':question_id' => $questionId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update
            $this->update(['answer_value' => $answerValue], ['id' => $existing['id']]);
            return $existing['id'];
        }

        // Insert
        return $this->insert([
            'tenant_id' => $tenantId,
            'question_id' => $questionId,
            'answer_value' => $answerValue,
        ]);
    }

    /**
     * Get answers for tenant
     */
    public function getAnswersForTenant(int $tenantId): array
    {
        $sql = "SELECT pa.*, pq.category, pq.question, pq.question AS question_text, pq.weight
                FROM {$this->table} pa
                JOIN personality_questions pq ON pa.question_id = pq.id
                WHERE pa.tenant_id = :tenant_id
                ORDER BY pq.sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if tenant has completed questionnaire
     */
    public function isCompleted(int $tenantId): bool
    {
        $sql = "SELECT COUNT(DISTINCT pa.question_id) as answered,
                       (SELECT COUNT(*) FROM personality_questions WHERE is_active = 1) as total
                FROM {$this->table} pa
                WHERE pa.tenant_id = :tenant_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        $result = $stmt->fetch();
        return $result['answered'] >= $result['total'] && $result['total'] > 0;
    }

    /**
     * Calculate compatibility score between two tenants
     */
    public function calculateCompatibility(int $tenantId1, int $tenantId2): float
    {
        $sql = "SELECT 
                    pa1.answer_value as val1,
                    pa2.answer_value as val2,
                    pq.weight,
                    pq.category
                FROM {$this->table} pa1
                JOIN {$this->table} pa2 ON pa1.question_id = pa2.question_id
                JOIN personality_questions pq ON pa1.question_id = pq.id
                WHERE pa1.tenant_id = :tenant1 AND pa2.tenant_id = :tenant2";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant1' => $tenantId1, ':tenant2' => $tenantId2]);
        $answers = $stmt->fetchAll();

        if (empty($answers)) {
            return 0;
        }

        $totalScore = 0;
        $totalWeight = 0;

        foreach ($answers as $answer) {
            // Calculate difference (0-4 scale inverted to 4-0 for similarity)
            $diff = abs($answer['val1'] - $answer['val2']);
            $similarity = (4 - $diff) / 4; // 0 to 1
            
            $totalScore += $similarity * $answer['weight'];
            $totalWeight += $answer['weight'];
        }

        return $totalWeight > 0 ? round(($totalScore / $totalWeight) * 100, 2) : 0;
    }

    /**
     * Get compatibility scores for tenant with all other tenants
     */
    public function getCompatibilityScores(int $tenantId): array
    {
        $sql = "SELECT DISTINCT t.id as tenant_id, u.name
                FROM tenants t
                JOIN users u ON t.user_id = u.id
                WHERE t.id != :tenant_id AND u.status = 'active' AND t.room_id IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        $otherTenants = $stmt->fetchAll();

        $scores = [];
        foreach ($otherTenants as $other) {
            $score = $this->calculateCompatibility($tenantId, $other['tenant_id']);
            $scores[] = [
                'tenant_id' => $other['tenant_id'],
                'name' => $other['name'],
                'compatibility_score' => $score,
            ];
        }

        // Sort by compatibility score descending
        usort($scores, fn($a, $b) => $b['compatibility_score'] <=> $a['compatibility_score']);

        return $scores;
    }

    /**
     * Get category scores for tenant
     */
    public function getCategoryScores(int $tenantId): array
    {
        $sql = "SELECT 
                    pq.category,
                    AVG(pa.answer_value) as avg_score,
                    SUM(pq.weight) as total_weight
                FROM {$this->table} pa
                JOIN personality_questions pq ON pa.question_id = pq.id
                WHERE pa.tenant_id = :tenant_id
                GROUP BY pq.category";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Check for suspicious patterns (all same answers)
     */
    public function checkSuspiciousPattern(int $tenantId): bool
    {
        $sql = "SELECT answer_value, COUNT(*) as count
                FROM {$this->table}
                WHERE tenant_id = :tenant_id
                GROUP BY answer_value
                ORDER BY count DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        $result = $stmt->fetch();

        if (!$result) return false;

        // If more than 70% of answers are the same, flag as suspicious
        $totalSql = "SELECT COUNT(*) FROM {$this->table} WHERE tenant_id = :tenant_id";
        $totalStmt = $this->db->prepare($totalSql);
        $totalStmt->execute([':tenant_id' => $tenantId]);
        $total = $totalStmt->fetchColumn();

        return $total > 0 && ($result['count'] / $total) > 0.7;
    }
}

