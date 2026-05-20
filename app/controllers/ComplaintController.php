<?php
/**
 * BoardTrack — ComplaintController (Phase 1 Fixed)
 */
class ComplaintController extends Controller
{
    private object $complaintModel;
    private object $tenantModel;
    private object $notificationModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->complaintModel   = $this->model('Complaint');
        $this->tenantModel      = $this->model('Tenant');
        $this->notificationModel= $this->model('Notification');
    }

    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/complaints' : 'tenant/complaints');
    }
    /**
     * LANDLORD: List complaints
     */
    public function complaints(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('tenant/complaints');
        }
        $filters = [
            'status'   => $_GET['status'] ?? null,
            'category' => $_GET['category'] ?? null
        ];
        $complaints = $this->complaintModel->getAllWithTenants($filters);
        $this->view('landlord/complaints', [
            'pageTitle'  => 'Complaints — BoardTrack',
            'complaints' => $complaints,
            'filters'    => $filters,
        ], 'landlord');
    }
    /**
     * TENANT: List complaints
     */
    public function tenantComplaints(): void
    {
        $tenant = $this->tenantModel->findByUserId((int)$_SESSION['user_id']);
        $complaints = $this->complaintModel->getByTenantId($tenant['id']);
        $this->view('tenant/complaints', [
            'pageTitle'  => 'My Complaints — BoardTrack',
            'complaints' => $complaints,
        ], 'tenant');
    }
    /**
     * TENANT: Create complaint
     */
    public function createComplaint(): void
    {
        $this->view('tenant/complaintForm', [
            'pageTitle' => 'Submit Complaint — BoardTrack',
        ], 'tenant');
    }
    /**
     * TENANT: Save complaint
     */
    public function saveComplaint(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/complaints');
        }
        $tenant = $this->tenantModel->findByUserId((int)$_SESSION['user_id']);
        $data = [
            'category'     => $_POST['category'] ?? 'other',
            'title'        => trim($_POST['title'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'is_anonymous' => !empty($_POST['is_anonymous']) && ($_POST['category'] ?? 'other') === 'roommate_conflict' ? 1 : 0,
        ];
        if (empty($data['title']) || empty($data['description'])) {
            $this->flash('error', 'Title and description required.');
            $this->redirect('tenant/createComplaint');
        }
        $this->complaintModel->submit($tenant['id'], $data);
        $this->flash('success', 'Complaint submitted.');
        $this->redirect('tenant/complaints');
    }
}
?>

