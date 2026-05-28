<?php
/**
 * BoardTrack | BillController
 */
class BillController extends Controller
{
    private object $billModel;
    private object $tenantModel;
    private object $roomModel;
    private object $notificationModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->billModel        = $this->model('Bill');
        $this->tenantModel      = $this->model('Tenant');
        $this->roomModel        = $this->model('Room');
        $this->notificationModel= $this->model('Notification');
    }

    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/bills' : 'tenant/bills');
    }

    public function bills(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('tenant/bills');
        }
        $filters = ['status' => $_GET['status'] ?? null];
        $bills = $this->billModel->getAllForLandlord($filters);
        $stats = $this->billModel->getStatistics();
        $this->view('landlord/bills', [
            'pageTitle'     => 'Billing | BoardTrack',
            'bills'         => $bills,
            'stats'         => $stats,
            'statistics'    => $stats,
            'filters'       => $filters,
            'billableRooms' => $this->roomModel->getBillableRooms(),
            'activeTenants' => $this->tenantModel->getActiveTenants(),
        ], 'landlord');
    }

    public function createBill(): void
    {
        $this->redirect('landlord/create-bill');
    }

    public function tenantBills(): void
    {
        $tenant = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);
        if (!$tenant) {
            $this->invalidSession('Your tenant profile could not be loaded.');
        }
        $roomId = !empty($tenant['room_id']) ? (int) $tenant['room_id'] : null;
        $this->view('tenant/bills', [
            'pageTitle'  => 'My Bills | BoardTrack',
            'bills'      => $this->billModel->getForTenant((int) $tenant['id'], $roomId),
            'statistics' => $this->billModel->getTenantBillStatistics((int) $tenant['id'], $roomId),
        ], 'tenant');
    }
}
