<?php
/**
 * BoardTrack — PersonalityController (Phase 1 Fixed)
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
        if ($_SESSION['user_role'] !== 'tenant') {
            $this->redirect('landlord/tenants');
        }
        $tenant = $this->tenantModel->findByUserId((int)$_SESSION['user_id']);
        if (!$tenant || $tenant['personality_completed']) {
            $this->flash('info', 'Personality questionnaire already completed.');
            $this->redirect('tenant/dashboard');
        }
        $questions = $this->personalityModel->getAllQuestions();
        $this->view('tenant/personality', [
            'pageTitle' => 'Personality Questionnaire — BoardTrack',
            'questions' => $questions,
        ], 'tenant');
    }
    /**
     * TENANT: Submit personality answers
     */
    public function submitPersonality(): void
    {
        if ($_SESSION['user_role'] !== 'tenant' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/personality');
        }
        $tenant = $this->tenantModel->findByUserId((int)$_SESSION['user_id']);
        if (!$tenant || $tenant['personality_completed']) {
            $this->redirect('tenant/dashboard');
        }
        $answers = $_POST['answers'] ?? [];
        if (empty($answers) || count($answers) < 10) {
            $this->flash('error', 'Please answer all questions.');
            $this->redirect('tenant/personality');
        }
        // Save answers
        foreach ($answers as $qid => $answer) {
            $this->personalityModel->saveAnswer($tenant['id'], (int)$qid, (int)$answer);
        }
        // Mark complete and check suspicious patterns
        $this->tenantModel->markPersonalityCompleted($tenant['id']);
        if ($this->personalityModel->checkSuspiciousPattern($tenant['id'])) {
            $this->tenantModel->flagPersonality($tenant['id'], 'Suspicious answer pattern detected');
        }
        $this->flash('success', 'Questionnaire completed. Awaiting approval.');
        $this->redirect('tenant/dashboard');
    }
}
?>

