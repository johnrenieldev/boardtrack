<?php
/**
 * BoardTrack — PersonalityAnswer Model
 * app/models/PersonalityAnswer.php
 */

class PersonalityAnswer extends Model
{
    protected string $table = 'personality_answers';

    /**
     * Get all questions with options
     */
    public function getAllQuestions(): array
    {
        $sql = "SELECT * FROM personality_questions 
                WHERE is_active = 1 
                ORDER BY display_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $questions = $stmt->fetchAll();

        // Add answer options to each question
        $optionsMap = $this->getQuestionOptions();
        foreach ($questions as &$question) {
            $question['options'] = $optionsMap[$question['display_order']] ?? [];
        }

        return $questions;
    }

    /**
     * Get answer options for each question
     */
    private function getQuestionOptions(): array
    {
        return [
            1 => [
                'Spend time alone quietly',
                'Talk with close friends',
                'Depends on my mood and energy',
                'I enjoy being around many people'
            ],
            2 => [
                'Very uncomfortable',
                'Slightly uncomfortable',
                'Comfortable when needed',
                'Very comfortable'
            ],
            3 => [
                'Quiet and peaceful',
                'Balanced and calm',
                'Social but manageable',
                'Active and lively'
            ],
            4 => [
                'I value personal space most',
                'I interact occasionally',
                'I balance alone time and socializing',
                'I enjoy frequent interaction'
            ],
            5 => [
                'Rarely',
                'Sometimes',
                'Often if invited',
                'Very often'
            ],
            6 => [
                'I would rather avoid the interaction',
                'I\'m okay with it occasionally',
                'It depends on my mood',
                'I would probably join them'
            ],
            7 => [
                'I think before speaking and keep to myself',
                'I open up only to certain people',
                'I can adapt socially depending on the situation',
                'I naturally express myself openly'
            ],
            8 => [
                'Prefer listening instead of speaking',
                'Speak only when necessary',
                'Participate comfortably',
                'Usually lead or energize the group'
            ],
            9 => [
                'Quiet and independent',
                'Respectful and balanced',
                'Flexible and understanding',
                'Friendly and socially active'
            ],
            10 => [
                'Mostly Introverted',
                'Slightly Introverted',
                'Balanced / Ambivert',
                'Mostly Extroverted',
                'Depends heavily on mood and environment (Omnivert)'
            ]
        ];
    }

    /**
     * Get questions by category
     */
    public function getQuestionsByCategory(string $category): array
    {
        $sql = "SELECT * FROM personality_questions 
                WHERE category = :category AND is_active = 1 
                ORDER BY display_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':category' => $category]);
        return $stmt->fetchAll();
    }

    /**
     * Save answer
     */
    public function saveAnswer(int $tenantId, int $questionId, int $answerValue): int
    {
        if ($answerValue < 1 || $answerValue > 5) {
            throw new InvalidArgumentException('Personality answer values must be between 1 and 5.');
        }

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
        $sql = "SELECT pa.*,
                       pq.category,
                       pq.question_text,
                       pq.weight,
                       pq.display_order
                FROM {$this->table} pa
                JOIN personality_questions pq ON pa.question_id = pq.id
                WHERE pa.tenant_id = :tenant_id
                ORDER BY pq.display_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        $answers = $stmt->fetchAll();
        $optionsMap = $this->getQuestionOptions();

        foreach ($answers as &$answer) {
            $displayOrder = (int) ($answer['display_order'] ?? 0);
            $value = (int) ($answer['answer_value'] ?? 0);
            $answer['answer_text'] = $optionsMap[$displayOrder][$value - 1] ?? (string) $value;
        }

        return $answers;
    }

    /**
     * Active question IDs used for strict form validation.
     */
    public function getActiveQuestionIds(): array
    {
        $sql = "SELECT id FROM personality_questions WHERE is_active = 1 ORDER BY display_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
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
