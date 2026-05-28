<?php
/**
 * BoardTrack | MaintenanceController
 */
class MaintenanceController extends Controller
{
    private object $maintenanceModel;
    private object $tenantModel;
    private object $roomModel;
    private object $billModel;
    private object $notificationModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->maintenanceModel = $this->model('Maintenance');
        $this->tenantModel     = $this->model('Tenant');
        $this->roomModel       = $this->model('Room');
        $this->billModel       = $this->model('Bill');
        $this->notificationModel = $this->model('Notification');
    }

    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/maintenance' : 'tenant/maintenance');
    }

    /**
     * LANDLORD: List maintenance requests
     */
    public function maintenance(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('tenant/maintenance');
        }
        $filters = [
            'status'   => $_GET['status'] ?? null,
            'priority' => $_GET['priority'] ?? null,
            'category' => $_GET['category'] ?? null
        ];
        $requests = $this->maintenanceModel->getAllWithDetails($filters);
        $stats = $this->maintenanceModel->getStatistics();
        $this->view('landlord/maintenance', [
            'pageTitle' => 'Maintenance Requests | BoardTrack',
            'requests'  => $requests,
            'filters'   => $filters,
            'stats'     => $stats,
        ], 'landlord');
    }

    /**
     * LANDLORD: View maintenance request details
     */
    public function viewMaintenance(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('tenant/maintenance');
        }
        $id = (int)($_GET['id'] ?? 0);
        $request = $this->maintenanceModel->getById($id);
        if (!$request) {
            $this->flash('error', 'Maintenance request not found.');
            $this->redirect('landlord/maintenance');
        }
        $this->view('landlord/maintenanceView', [
            'pageTitle' => 'Maintenance Request | BoardTrack',
            'request'   => $request,
        ], 'landlord');
    }

    /**
     * LANDLORD: Update maintenance request status
     */
    public function updateMaintenance(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('tenant/maintenance');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/maintenance');
        }
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $data = [
            'assigned_to'   => $_POST['assigned_to'] ?? null,
            'scheduled_at'  => $_POST['scheduled_at'] ?? null,
            'estimated_cost' => $_POST['estimated_cost'] ?? null,
            'actual_cost'   => $_POST['actual_cost'] ?? null,
        ];
        
        $this->maintenanceModel->updateStatus($id, $status, $data);
        
        // If completed and has actual cost, create a bill
        if ($status === 'completed' && !empty($data['actual_cost'])) {
            $request = $this->maintenanceModel->getById($id);
            if ($request && !$request['bill_id']) {
                $billData = [
                    'tenant_id' => $request['tenant_id'],
                    'room_id'   => $request['room_id'],
                    'bill_name' => 'Maintenance: ' . $request['title'],
                    'amount'    => $data['actual_cost'],
                    'due_date'  => date('Y-m-d', strtotime('+30 days')),
                    'status'    => 'unpaid',
                ];
                $billId = $this->billModel->create($billData);
                $this->maintenanceModel->linkToBill($id, $billId);
                
                // Send notification to tenant
                $this->notificationModel->create([
                    'user_id' => $request['tenant_id'],
                    'title'   => 'Maintenance Bill Created',
                    'message' => 'A bill of ₱' . number_format($data['actual_cost'], 2) . ' has been created for your maintenance request: ' . $request['title'],
                    'type'    => 'billing',
                ]);
            }
        }
        
        $this->flash('success', 'Maintenance request updated.');
        $this->redirect('landlord/maintenance');
    }

    /**
     * TENANT: List maintenance requests
     */
    public function tenantMaintenance(): void
    {
        $tenant = $this->requireApprovedTenant();
        $requests = $this->maintenanceModel->getByTenantId($tenant['id']);
        $this->view('tenant/maintenance', [
            'pageTitle' => 'My Maintenance Requests | BoardTrack',
            'requests'  => $requests,
        ], 'tenant');
    }

    /**
     * TENANT: Create maintenance request
     */
    public function createMaintenance(): void
    {
        $tenant = $this->requireApprovedTenant();
        $room = null;
        if (!empty($tenant['room_id'])) {
            $room = $this->roomModel->getById($tenant['room_id']);
        }
        $this->view('tenant/maintenanceForm', [
            'pageTitle' => 'Request Maintenance | BoardTrack',
            'room'      => $room,
        ], 'tenant');
    }

    /**
     * TENANT: Save maintenance request
     */
    public function saveMaintenance(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/maintenance');
        }
        $tenant = $this->requireApprovedTenant();
        
        $data = [
            'title'       => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category'    => $_POST['category'] ?? 'other',
            'priority'    => $_POST['priority'] ?? 'medium',
        ];
        
        if (empty($data['title']) || empty($data['description'])) {
            $this->flash('error', 'Title and description are required.');
            $this->redirect('tenant/createMaintenance');
        }
        
        $this->maintenanceModel->submit($tenant['id'], $tenant['room_id'], $data);
        
        // Send notification to landlord
        $landlordUsers = $this->tenantModel->getAllLandlords();
        foreach ($landlordUsers as $landlord) {
            $this->notificationModel->create([
                'user_id' => $landlord['id'],
                'title'   => 'New Maintenance Request',
                'message' => 'A new maintenance request has been submitted: ' . $data['title'],
                'type'    => 'maintenance',
            ]);
        }
        
        $this->flash('success', 'Maintenance request submitted successfully.');
        $this->redirect('tenant/maintenance');
    }

    /**
     * TENANT: Update maintenance request
     */
    public function updateTenantMaintenance(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/maintenance');
        }
        $tenant = $this->tenantModel->findByUserId((int)$_SESSION['user_id']);
        $id = (int)($_POST['id'] ?? 0);
        
        $data = [
            'title'       => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category'    => $_POST['category'] ?? 'other',
            'priority'    => $_POST['priority'] ?? 'medium',
        ];
        
        if (empty($data['title']) || empty($data['description'])) {
            $this->flash('error', 'Title and description are required.');
            $this->redirect('tenant/maintenance');
        }
        
        $this->maintenanceModel->updateByTenant($id, $tenant['id'], $data);
        $this->flash('success', 'Maintenance request updated.');
        $this->redirect('tenant/maintenance');
    }

    /**
     * TENANT: Delete maintenance request
     */
    public function deleteMaintenance(): void
    {
        $tenant = $this->tenantModel->findByUserId((int)$_SESSION['user_id']);
        $id = (int)($_GET['id'] ?? 0);
        
        if ($this->maintenanceModel->deleteByTenant($id, $tenant['id'])) {
            $this->flash('success', 'Maintenance request deleted.');
        } else {
            $this->flash('error', 'Cannot delete this request. It may already be in progress or completed.');
        }
        $this->redirect('tenant/maintenance');
    }
}
