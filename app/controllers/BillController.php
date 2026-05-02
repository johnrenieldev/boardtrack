<?php
/**
 * BoardTrack — BillController (Phase 1 Fixed)
 */
class BillController extends Controller
{
    private object $billModel;
    private object $tenantModel;
    private object $notificationModel;
    private object $userModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->billModel        = $this->model('Bill');
        $this->tenantModel      = $this->model('Tenant');
        $this->notificationModel= $this->model('Notification');
        $this->userModel        = $this->model('User');
    }

    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/bills' : 'tenant/bills');
    }

    // ── LANDLORD: List bills ───────────────────────────────────
    public function bills(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('tenant/bills');
        }
        $filters = [
            'status' => $_GET['status'] ?? null,
            'search' => trim($_GET['search'] ?? '')
        ];
        $bills = $this->billModel->getAllWithTenants($filters);
        $stats = $this->billModel->getStatistics();
        $activeTenants = $this->tenantModel->getActiveTenants();
        $this->view('landlord/bills', [
            'pageTitle'     => 'Billing — BoardTrack',
            'bills'         => $bills,
            'stats'         => $stats,
            'filters'       => $filters,
            'activeTenants' => $activeTenants,
        ], 'landlord');
    }

    // ── LANDLORD: Create bill ──────────────────────────────────
    public function createBill(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('landlord/bills');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/bills');
        }
        $data = [
            'tenant_id'            => (int)($_POST['tenant_id'] ?? 0),
            'bill_name'            => trim($_POST['bill_name'] ?? ''),
            'billing_period_start' => $_POST['period_start'] ?? date('Y-m-01'),
            'billing_period_end'   => $_POST['period_end'] ?? date('Y-m-t'),
            'amount'               => (float)($_POST['amount'] ?? 0),
            'due_date'             => $_POST['due_date'] ?? '',
            'status'               => 'unpaid',
            'notes'                => trim($_POST['notes'] ?? ''),
            'created_by'           => (int)$_SESSION['user_id']
        ];
        if (!$data['tenant_id'] || empty($data['bill_name']) || $data['amount'] <= 0 || empty($data['due_date'])) {
            $this->flash('error', 'Required fields missing or invalid amount.');
            $this->redirect('landlord/bills');
        }
        if ($data['due_date'] < date('Y-m-d')) {
            $this->flash('error', 'Due date cannot be in the past.');
            $this->redirect('landlord/bills');
        }
        if ($data['billing_period_start'] >= $data['billing_period_end']) {
            $this->flash('error', 'Billing period start must be before end date.');
            $this->redirect('landlord/bills');
        }
        $tenant = $this->tenantModel->find($data['tenant_id']);
        if (!$tenant) {
            $this->flash('error', 'Tenant not found.');
            $this->redirect('landlord/bills');
        }
        $this->billModel->insert($data);
        $this->notificationModel->createNotification(
            $tenant['user_id'], 'billing', $data['bill_name'],
            'New bill issued: ₱' . number_format($data['amount'], 2), 'tenant/bills'
        );
        $this->flash('success', 'Bill created and tenant notified.');
        $this->redirect('landlord/bills');
    }

    // ── TENANT: List bills ─────────────────────────────────────
    public function tenantBills(): void
    {
        $tenantId = $this->tenantModel->findByUserId((int)$_SESSION['user_id'])['id'] ?? 0;
        $bills = $this->billModel->getByTenantId($tenantId);
        $stats = $this->billModel->getTenantStatistics($tenantId);
        $this->view('tenant/bills', [
            'pageTitle'  => 'My Bills — BoardTrack',
            'bills'      => $bills,
            'statistics' => $stats
        ], 'tenant');
    }
}
?>

