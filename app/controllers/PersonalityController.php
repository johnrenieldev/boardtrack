<?php
/**
 * BoardTrack | PersonalityController (Phase 1 Fixed)
 * Tenant personality questionnaire for roommate matching.
 */
class PersonalityController extends Controller
{
    private object $personalityModel;
    private object $tenantModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->personalityModel = $this->model('PersonalityAnswer');
        $this->tenantModel      = $this->model('Tenant');
    }

    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/tenants' : 'tenant/personality');
    }
    /**
     * TENANT: Personality questionnaire
     */
    public function personality(): void
    {
        $tenant = $this->requireTenantProfile();
        
        // Redirect if personality quiz is already completed
        if ($tenant['personality_completed']) {
            $this->flash('info', 'Personality questionnaire already completed.');
            $this->redirect('tenant/dashboard');
        }
        
        // Also redirect if tenant is already fully onboarded (approved with room)
        $user = $this->model('User')->findById((int) $_SESSION['user_id']);
        if (($user['status'] ?? '') === 'approved' && !empty($tenant['room_id'])) {
            $this->flash('info', 'You are already approved and assigned to a room.');
            $this->redirect('tenant/dashboard');
        }
        
        $questions = $this->personalityModel->getAllQuestions();
        $this->view('tenant/personality', [
            'pageTitle' => 'Personality Questionnaire | BoardTrack',
            'questions' => $questions,
        ], 'tenant');
    }
    /**
     * TENANT: Submit personality answers
     */
    public function submitPersonality(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirect('tenant/personality');
            }
            
            $tenant = $this->requireTenantProfile();
            
            if ($tenant['personality_completed']) {
                $this->redirect('tenant/dashboard');
            }
            $answers = $_POST['answers'] ?? [];
            $activeQuestionIds = $this->personalityModel->getActiveQuestionIds();
            if (empty($activeQuestionIds)) {
                $this->flash('error', 'The personality questionnaire is not available yet. Please contact the landlord.');
                $this->redirect('tenant/dashboard');
            }

            $submittedQuestionIds = array_map('intval', array_keys($answers));
            sort($submittedQuestionIds);
            $expectedQuestionIds = $activeQuestionIds;
            sort($expectedQuestionIds);

            if ($submittedQuestionIds !== $expectedQuestionIds) {
                $this->flash('error', 'Please answer all questions.');
                $this->redirect('tenant/personality');
            }

            // Save answers
            foreach ($activeQuestionIds as $qid) {
                $answer = (int) ($answers[$qid] ?? 0);
                if ($answer < 1 || $answer > 5) {
                    $this->flash('error', 'Please choose a valid answer for every question.');
                    $this->redirect('tenant/personality');
                }
                $this->personalityModel->saveAnswer($tenant['id'], $qid, $answer);
            }
            // Mark complete and check suspicious patterns
            $this->tenantModel->markPersonalityCompleted($tenant['id']);
            
            // Refresh compatibility cache for this tenant
            try {
                require_once APP_PATH . '/services/CompatibilityService.php';
                $compatibilityService = new CompatibilityService();
                $compatibilityService->clearTenantCache((int) $tenant['id']);
                $compatibilityService->refreshTenantCache($tenant['id']);
            } catch (Throwable $e) {
                error_log("Compatibility cache refresh failed: " . $e->getMessage());
            }

            if ($this->personalityModel->checkSuspiciousPattern($tenant['id'])) {
                $this->tenantModel->flagPersonality($tenant['id'], 'Suspicious answer pattern detected');
            }
            $this->flash('success', 'Questionnaire completed. Awaiting approval.');
            $this->redirect('tenant/dashboard');
        } catch (Throwable $e) {
            error_log('[PersonalityController] submitPersonality failed: ' . $e->getMessage());
            $this->flash('error', 'We could not save your questionnaire. Please try again.');
            $this->redirect('tenant/personality');
        }
    }
}
?>
