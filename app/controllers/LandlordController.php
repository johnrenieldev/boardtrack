<?php
/**
 * BoardTrack — Landlord Controller
 * app/controllers/LandlordController.php
 *
 * FIXED:
 *  - Added waitingList() method (sidebar link target)
 *  - Added auditLog() method (sidebar link target)
 *  - Fixed stats count() calls — use raw WHERE strings
 *  - All placeholder die() methods replaced
 */

class LandlordController extends Controller
{
    private object $userModel;
    private object $tenantModel;
    private object $roomModel;
    private object $billModel;
    private object $paymentModel;
    private object $complaintModel;
    private object $announcementModel;
    private object $auditLogModel;
    private object $waitingListModel;
    private object $notificationModel;

    public function __construct()
    {
        $this->requireRole('landlord');
        $this->userModel         = $this->model('User');
        $this->tenantModel       = $this->model('Tenant');
        $this->roomModel         = $this->model('Room');
        $this->billModel         = $this->model('Bill');
        $this->paymentModel      = $this->model('Payment');
        $this->complaintModel    = $this->model('Complaint');
        $this->announcementModel = $this->model('Announcement');
        $this->auditLogModel     = $this->model('AuditLog');
        $this->waitingListModel  = $this->model('WaitingList');
        $this->notificationModel = $this->model('Notification');
    }

    // ── Dashboard ──────────────────────────────────────────────
    public function dashboard(): void
    {
        $user         = $this->userModel->findById((int)$_SESSION['user_id']);
        $roomStats    = $this->roomModel->getStatistics();
        $paymentStats = $this->paymentModel->getStatistics();

        $stats = [
            'pendingCount'    => $this->userModel->countByWhere("role = 'tenant' AND status = 'pending'"),
            'activeCount'     => $this->userModel->countByWhere("role = 'tenant' AND status = 'active'"),
            'waitingCount'    => $this->userModel->countByWhere("role = 'tenant' AND status = 'waiting_list'"),
            'rejectedCount'   => $this->userModel->countByWhere("role = 'tenant' AND status = 'rejected'"),
            'totalRooms'      => $roomStats['total_rooms']    ?? 0,
            'availableRooms'  => $roomStats['available']      ?? 0,
            'maintenanceRooms'=> $roomStats['maintenance']    ?? 0,
            'unpaidBills'     => $this->billModel->countByWhere("status = 'unpaid'"),
            'pendingPayments' => $paymentStats['pending']     ?? 0,
            'approvedPayments'=> $paymentStats['approved']    ?? 0,
            'openComplaints'  => $this->complaintModel->getPendingCount(),
        ];

        $this->view('landlord/dashboard', [
            'pageTitle'           => 'Dashboard — BoardTrack',
            'user'                => $user,
            'stats'               => $stats,
            'recentComplaints'    => $this->complaintModel->getRecent(5),
            'recentAnnouncements' => $this->announcementModel->getRecent(5),
            'pendingPayments'     => $this->paymentModel->getPending(),
        ], 'landlord');
    }

    // ── Tenants ────────────────────────────────────────────────
    public function tenants(): void
    {
        $filters = [
            'status'    => $_GET['status']    ?? null,
            'search'    => $_GET['search']    ?? null,
            'room_type' => $_GET['room_type'] ?? null,
        ];
        $tenants   = $this->tenantModel->getAllWithFilters(array_filter($filters));
        $availableRooms = $this->roomModel->getAvailable();

        $this->view('landlord/tenants', [
            'pageTitle'      => 'Tenants — BoardTrack',
            'tenants'        => $tenants,
            'filters'        => $filters,
            'availableRooms' => $availableRooms,
        ], 'landlord');
    }

    public function viewTenant(int $id): void
    {
        $tenant = $this->tenantModel->getWithPersonality($id);
        if (!$tenant) {
            $this->flash('error', 'Tenant not found.');
            $this->redirect('landlord/tenants');
        }
        $personalityModel = $this->model('PersonalityAnswer');
        $this->view('landlord/tenantView', [
            'pageTitle'          => 'Tenant Details — BoardTrack',
            'tenant'             => $tenant,
            'personalityAnswers' => $personalityModel->getAnswersForTenant($id),
            'bills'              => $this->billModel->getByTenantId($id),
            'complaints'         => $this->complaintModel->getByTenantId($id),
            'isSuspicious'       => $personalityModel->checkSuspiciousPattern($id),
            'availableRooms'     => $this->roomModel->getAvailable(),
        ], 'landlord');
    }

    public function approveTenant(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/tenants'); }
        $tenantId = (int)($_POST['tenant_id'] ?? 0);
        $roomId   = !empty($_POST['room_id']) ? (int)$_POST['room_id'] : null;
        $tenant   = $this->tenantModel->find($tenantId);
        if (!$tenant) { $this->flash('error', 'Tenant not found.'); $this->redirect('landlord/tenants'); }

        $newStatus = $roomId ? 'active' : 'waiting_list';
        $this->userModel->update(['status' => $newStatus], ['id' => $tenant['user_id']]);

        if ($roomId) {
            $this->tenantModel->assignRoom($tenantId, $roomId, date('Y-m-d'));
            $this->roomModel->updateOccupancy($roomId);
            $this->notificationModel->createNotification(
                $tenant['user_id'], 'room', 'Room Assigned',
                'You have been approved and assigned to a room.', 'tenant/dashboard'
            );
            $this->auditLogModel->log($_SESSION['user_id'], 'tenant_approved_room',
                'tenant', $tenantId, ['status'=>'pending'], ['status'=>'active','room_id'=>$roomId],
                'Tenant approved and assigned room');
            $this->flash('success', 'Tenant approved and room assigned.');
        } else {
            $this->waitingListModel->addTenant($tenantId, $tenant['room_type_preference'] ?? 'shared');
            $this->notificationModel->createNotification(
                $tenant['user_id'], 'room', 'Account Approved',
                'Your account is approved. You have been placed on the waiting list.', 'tenant/dashboard'
            );
            $this->auditLogModel->log($_SESSION['user_id'], 'tenant_approved_waiting',
                'tenant', $tenantId, ['status'=>'pending'], ['status'=>'waiting_list'],
                'Tenant approved, added to waiting list');
            $this->flash('success', 'Tenant approved and added to waiting list.');
        }
        $this->redirect('landlord/tenants');
    }

    public function rejectTenant(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/tenants'); }
        $tenantId = (int)($_POST['tenant_id'] ?? 0);
        $reason   = trim($_POST['reason'] ?? '');
        if (empty($reason)) { $this->flash('error', 'Rejection reason is required.'); $this->redirect('landlord/tenants'); }
        $tenant = $this->tenantModel->find($tenantId);
        if (!$tenant) { $this->flash('error', 'Tenant not found.'); $this->redirect('landlord/tenants'); }
        $this->userModel->update(['status' => 'rejected'], ['id' => $tenant['user_id']]);
        $this->notificationModel->createNotification(
            $tenant['user_id'], 'system', 'Application Rejected',
            "Your registration was rejected. Reason: {$reason}", 'tenant/dashboard'
        );
        $this->auditLogModel->log($_SESSION['user_id'], 'tenant_rejected',
            'tenant', $tenantId, ['status'=>'pending'], ['status'=>'rejected'], $reason);
        $this->flash('success', 'Tenant registration rejected.');
        $this->redirect('landlord/tenants');
    }

    public function moveOutTenant(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/tenants'); }
        $tenantId = (int)($_POST['tenant_id'] ?? 0);
        $tenant = $this->tenantModel->find($tenantId);
        if (!$tenant) { $this->flash('error', 'Tenant not found.'); $this->redirect('landlord/tenants'); }
        $oldRoomId = $tenant['room_id'] ?? null;
        $this->tenantModel->removeFromRoom($tenantId, date('Y-m-d'));
        $this->userModel->update(['status' => 'moved_out'], ['id' => $tenant['user_id']]);
        if ($oldRoomId) {
            $this->roomModel->updateOccupancy($oldRoomId);
        }
        $this->notificationModel->createNotification(
            $tenant['user_id'], 'room', 'Moved Out',
            'You have been marked as moved out.', 'tenant/dashboard'
        );
        $this->auditLogModel->log($_SESSION['user_id'], 'tenant_moved_out',
            'tenant', $tenantId, ['status' => 'active', 'room_id' => $oldRoomId],
            ['status' => 'moved_out', 'room_id' => null], 'Tenant moved out');
        $this->flash('success', 'Tenant marked as moved out.');
        $this->redirect('landlord/tenants');
    }

    // ── Rooms ──────────────────────────────────────────────────
    public function rooms(): void
    {
        $rooms = $this->roomModel->getAllWithOccupancy();
        $statistics = $this->roomModel->getStatistics();
        $this->view('landlord/rooms', [
            'pageTitle' => 'Rooms — BoardTrack',
            'rooms'     => $rooms,
            'statistics' => $statistics,
        ], 'landlord');
    }

    public function viewRoom(int $id): void
    {
        $room = $this->roomModel->find($id);
        if (!$room) {
            $this->flash('error', 'Room not found.');
            $this->redirect('landlord/rooms');
        }
        // Get occupants for this room
        $occupants = $this->tenantModel->getByRoomId($id);
        $room['occupants'] = $occupants;
        $room['actual_occupants'] = count($occupants);
        $this->view('landlord/roomView', [
            'pageTitle' => 'Room ' . $room['room_number'] . ' — BoardTrack',
            'room'      => $room,
        ], 'landlord');
    }

    public function addRoom(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/rooms'); }
        $data = [
            'room_number'   => trim($_POST['room_number']   ?? ''),
            'floor'         => (int)($_POST['floor']        ?? 1),
            'room_type'     => $_POST['room_type']          ?? 'single',
            'max_occupants' => (int)($_POST['max_occupants']?? 1),
            'monthly_rent'  => (float)($_POST['monthly_rent']?? 0),
            'status'        => 'available',
            'description'   => trim($_POST['description']   ?? ''),
        ];
        if (empty($data['room_number'])) { $this->flash('error', 'Room number is required.'); $this->redirect('landlord/rooms'); }
        if ($data['max_occupants'] < 1) { $this->flash('error', 'Max occupants must be at least 1.'); $this->redirect('landlord/rooms'); }
        if ($data['monthly_rent'] <= 0) { $this->flash('error', 'Monthly rent must be greater than zero.'); $this->redirect('landlord/rooms'); }
        $this->roomModel->insert($data);
        $this->auditLogModel->log($_SESSION['user_id'], 'room_created', 'room', 0, null, $data, "Room {$data['room_number']} created");
        $this->flash('success', "Room {$data['room_number']} added successfully.");
        $this->redirect('landlord/rooms');
    }

    public function editRoom(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/rooms'); }
        $room = $this->roomModel->find($id);
        if (!$room) { $this->flash('error', 'Room not found.'); $this->redirect('landlord/rooms'); }
        $data = [
            'floor'         => (int)($_POST['floor']         ?? $room['floor']),
            'room_type'     => $_POST['room_type']            ?? $room['room_type'],
            'max_occupants' => (int)($_POST['max_occupants']  ?? $room['max_occupants']),
            'monthly_rent'  => (float)($_POST['monthly_rent'] ?? $room['monthly_rent']),
            'status'        => $_POST['status']               ?? $room['status'],
            'description'   => trim($_POST['description']     ?? ''),
        ];
        if ($data['max_occupants'] < 1) { $this->flash('error', 'Max occupants must be at least 1.'); $this->redirect('landlord/rooms'); }
        if ($data['monthly_rent'] <= 0) { $this->flash('error', 'Monthly rent must be greater than zero.'); $this->redirect('landlord/rooms'); }
        $this->roomModel->update($data, ['id' => $id]);
        $this->auditLogModel->log($_SESSION['user_id'], 'room_updated', 'room', $id, $room, $data, "Room {$room['room_number']} updated");
        $this->flash('success', 'Room updated successfully.');
        $this->redirect('landlord/rooms');
    }

    // ── Billing ────────────────────────────────────────────────
    public function bills(): void
    {
        $filters = ['status' => $_GET['status'] ?? null, 'search' => $_GET['search'] ?? null];
        $bills   = $this->billModel->getAllWithTenants(array_filter($filters));
        $statistics   = $this->billModel->getStatistics();
        $activeTenants = $this->tenantModel->getActiveTenants();
        $this->view('landlord/bills', [
            'pageTitle'     => 'Billing — BoardTrack',
            'bills'         => $bills,
            'statistics'         => $statistics,
            'filters'       => $filters,
            'activeTenants' => $activeTenants,
        ], 'landlord');
    }

    public function createBill(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/bills'); }
        $data = [
            'tenant_id'            => (int)($_POST['tenant_id']  ?? 0),
            'bill_name'            => trim($_POST['bill_name']    ?? ''),
            'billing_period_start' => $_POST['period_start']     ?? date('Y-m-01'),
            'billing_period_end'   => $_POST['period_end']       ?? date('Y-m-t'),
            'amount'               => (float)($_POST['amount']   ?? 0),
            'due_date'             => $_POST['due_date']         ?? '',
            'status'               => 'unpaid',
            'created_by'           => (int)$_SESSION['user_id'],
            'notes'                => trim($_POST['notes']        ?? ''),
        ];
        if (!$data['tenant_id'] || empty($data['bill_name']) || $data['amount'] <= 0 || empty($data['due_date'])) {
            $this->flash('error', 'All required fields must be filled in and amount must be greater than zero.');
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
        $billId = $this->billModel->insert($data);
        $tenant = $this->tenantModel->find($data['tenant_id']);
        if ($tenant) {
            $this->notificationModel->createNotification(
                $tenant['user_id'], 'billing', 'New Bill Created',
                "A new bill \"{$data['bill_name']}\" of ₱" . number_format($data['amount'],2) . " has been issued.",
                'tenant/bills'
            );
        }
        $this->auditLogModel->log($_SESSION['user_id'], 'bill_created', 'bill', $billId, null, $data, "Bill created: {$data['bill_name']}");
        $this->flash('success', 'Bill created and tenant notified.');
        $this->redirect('landlord/bills');
    }

    public function deleteBill(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/bills'); }
        $billId = (int)($_POST['bill_id'] ?? 0);
        $bill = $this->billModel->find($billId);
        if (!$bill) { $this->flash('error', 'Bill not found.'); $this->redirect('landlord/bills'); }
        $this->billModel->delete($billId);
        $this->auditLogModel->log($_SESSION['user_id'], 'bill_deleted', 'bill', $billId, $bill, null, "Bill deleted: {$bill['bill_name']}");
        $this->flash('success', 'Bill deleted successfully.');
        $this->redirect('landlord/bills');
    }

    // ── Payments ───────────────────────────────────────────────
    public function payments(): void
    {
        $filters  = ['status' => $_GET['status'] ?? null];
        $payments = $this->paymentModel->getAllWithDetails(array_filter($filters));
        $statistics = $this->paymentModel->getStatistics();
        $this->view('landlord/payments', [
            'pageTitle' => 'Payments — BoardTrack',
            'payments'  => $payments,
            'filters'   => $filters,
            'statistics' => $statistics,
        ], 'landlord');
    }

    public function viewPayment(int $id): void
    {
        $payment = $this->paymentModel->getWithDetails($id);
        if (!$payment) {
            $this->flash('error', 'Payment not found.');
            $this->redirect('landlord/payments');
        }
        $this->view('landlord/paymentView', [
            'pageTitle' => 'Review Payment — BoardTrack',
            'payment'   => $payment,
        ], 'landlord');
    }

    public function approvePayment(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/payments'); }
        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $payment   = $this->paymentModel->find($paymentId);
        if (!$payment) { $this->flash('error', 'Payment not found.'); $this->redirect('landlord/payments'); }
        $this->paymentModel->update(['status'=>'approved','reviewed_by'=>(int)$_SESSION['user_id'],'reviewed_at'=>date('Y-m-d H:i:s')], ['id'=>$paymentId]);
        $this->billModel->update(['status'=>'paid'], ['id'=>$payment['bill_id']]);
        $tenant = $this->tenantModel->find($payment['tenant_id']);
        if ($tenant) {
            $this->notificationModel->createNotification($tenant['user_id'], 'billing', 'Payment Approved', 'Your payment has been verified and approved.', 'tenant/bills');
        }
        $this->auditLogModel->log($_SESSION['user_id'],'payment_approved','payment',$paymentId,['status'=>'pending'],['status'=>'approved'],'Payment approved');
        $this->flash('success', 'Payment approved and bill marked as paid.');
        $this->redirect('landlord/payments');
    }

    public function rejectPayment(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/payments'); }
        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $reason    = trim($_POST['reason'] ?? '');
        if (empty($reason)) { $this->flash('error', 'Rejection reason is required.'); $this->redirect('landlord/payments'); }
        $payment   = $this->paymentModel->find($paymentId);
        if (!$payment) { $this->flash('error', 'Payment not found.'); $this->redirect('landlord/payments'); }
        $this->paymentModel->update(['status'=>'rejected','reviewed_by'=>(int)$_SESSION['user_id'],'reviewed_at'=>date('Y-m-d H:i:s'),'review_notes'=>$reason], ['id'=>$paymentId]);
        $this->billModel->update(['status'=>'unpaid'], ['id'=>$payment['bill_id']]);
        $tenant = $this->tenantModel->find($payment['tenant_id']);
        if ($tenant) {
            $this->notificationModel->createNotification($tenant['user_id'], 'billing', 'Payment Rejected', "Your payment was rejected. Reason: {$reason}", 'tenant/bills');
        }
        $this->auditLogModel->log($_SESSION['user_id'],'payment_rejected','payment',$paymentId,['status'=>'pending'],['status'=>'rejected'],$reason);
        $this->flash('success', 'Payment rejected and tenant notified.');
        $this->redirect('landlord/payments');
    }

    // ── Complaints ─────────────────────────────────────────────
    public function complaints(): void
    {
        $filters    = ['status'=>$_GET['status']??null, 'category'=>$_GET['category']??null];
        $complaints = $this->complaintModel->getAllWithTenants(array_filter($filters));
        $statistics = $this->complaintModel->getStatistics();
        $this->view('landlord/complaints', [
            'pageTitle'  => 'Complaints — BoardTrack',
            'complaints' => $complaints,
            'filters'    => $filters,
            'statistics' => $statistics,
        ], 'landlord');
    }

    public function viewComplaint(int $id): void
    {
        $complaint = $this->complaintModel->getWithTenant($id);
        if (!$complaint) { $this->flash('error', 'Complaint not found.'); $this->redirect('landlord/complaints'); }
        $this->view('landlord/complaintView', [
            'pageTitle' => 'Complaint Detail — BoardTrack',
            'complaint' => $complaint,
        ], 'landlord');
    }

    public function respondComplaint(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/complaints'); }
        $id       = (int)($_POST['complaint_id'] ?? 0);
        $response = trim($_POST['response'] ?? '');
        $status   = $_POST['status'] ?? 'in_progress';
        $valid    = ['pending','in_progress','resolved'];
        if (!in_array($status, $valid)) $status = 'in_progress';
        $complaint = $this->complaintModel->find($id);
        if (!$complaint) { $this->flash('error', 'Complaint not found.'); $this->redirect('landlord/complaints'); }
        $updateData = ['status'=>$status,'landlord_response'=>$response];
        if ($status === 'resolved') {
            $updateData['resolved_by'] = (int)$_SESSION['user_id'];
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        }
        $this->complaintModel->update($updateData, ['id'=>$id]);
        // Look up the tenant to get user_id for notification
        $tenant = $this->tenantModel->find($complaint['tenant_id']);
        if ($tenant) {
            $this->notificationModel->createNotification($tenant['user_id'],'complaint','Complaint Updated',"Your complaint status updated to: ".ucfirst(str_replace('_',' ',$status)),'tenant/complaints');
        }
        $this->auditLogModel->log($_SESSION['user_id'],'complaint_updated','complaint',$id,['status'=>$complaint['status']],['status'=>$status],'Complaint status updated');
        $this->flash('success', 'Complaint updated and tenant notified.');
        $this->redirect('landlord/complaints');
    }

    public function deleteComplaint(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/complaints');
        }
        $id        = (int) ($_POST['complaint_id'] ?? 0);
        $complaint = $this->complaintModel->find($id);
        if (!$complaint) {
            $this->flash('error', 'Complaint not found.');
            $this->redirect('landlord/complaints');
        }
        $this->complaintModel->deleteById($id);
        $this->auditLogModel->log(
            $_SESSION['user_id'], 'complaint_deleted', 'complaint', $id,
            ['title' => $complaint['title']], null, 'Complaint deleted by landlord'
        );
        $this->flash('success', 'Complaint deleted.');
        $this->redirect('landlord/complaints');
    }

    // ── Announcements ──────────────────────────────────────────
    public function announcements(): void
    {
        $announcements = $this->announcementModel->getAllWithAuthor();
        $statistics = $this->announcementModel->getStatistics();
        $this->view('landlord/announcements', [
            'pageTitle'     => 'Announcements — BoardTrack',
            'announcements' => $announcements,
            'statistics' => $statistics,
        ], 'landlord');
    }

    public function createAnnouncement(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/announcements'); }
        $data = [
            'title'      => trim($_POST['title']   ?? ''),
            'content'    => trim($_POST['content']  ?? ''),
            'priority'   => $_POST['priority']     ?? 'normal',
            'event_date' => !empty($_POST['event_date']) ? $_POST['event_date'] : null,
            'is_active'  => 1,
            'created_by' => (int)$_SESSION['user_id'],
        ];
        if (empty($data['title']) || empty($data['content'])) {
            $this->flash('error', 'Title and content are required.');
            $this->redirect('landlord/announcements');
        }
        $this->announcementModel->insert($data);
        // Notify all active tenants
        $activeTenants = $this->tenantModel->getActiveTenants();
        foreach ($activeTenants as $t) {
            $this->notificationModel->createNotification($t['user_id'],'announcement',$data['title'],"New announcement: {$data['title']}",'tenant/announcements');
        }
        $this->flash('success', 'Announcement posted and tenants notified.');
        $this->redirect('landlord/announcements');
    }

    public function editAnnouncement(int $id): void
    {
        // For now, redirect to announcements page — edit is done inline
        $this->flash('info', 'Please use the announcements page to manage announcements.');
        $this->redirect('landlord/announcements');
    }

    public function toggleAnnouncement(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/announcements'); }
        $id = (int)($_POST['announcement_id'] ?? 0);
        $announcement = $this->announcementModel->find($id);
        if (!$announcement) { $this->flash('error', 'Announcement not found.'); $this->redirect('landlord/announcements'); }
        $newStatus = $announcement['is_active'] ? 0 : 1;
        $this->announcementModel->update(['is_active' => $newStatus], ['id' => $id]);
        $label = $newStatus ? 'activated' : 'deactivated';
        $this->auditLogModel->log($_SESSION['user_id'], 'announcement_toggled', 'announcement', $id,
            ['is_active' => $announcement['is_active']], ['is_active' => $newStatus], "Announcement {$label}");
        $this->flash('success', "Announcement {$label}.");
        $this->redirect('landlord/announcements');
    }

    public function deleteAnnouncement(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/announcements'); }
        $id = (int)($_POST['announcement_id'] ?? 0);
        if (!$id) { $this->flash('error', 'Invalid announcement.'); $this->redirect('landlord/announcements'); }
        $this->announcementModel->delete($id);
        $this->auditLogModel->log($_SESSION['user_id'], 'announcement_deleted', 'announcement', $id, null, null, 'Announcement deleted');
        $this->flash('success', 'Announcement deleted.');
        $this->redirect('landlord/announcements');
    }

    // ── Waiting List ── FIX: was missing ──────────────────────
    public function waitingList(): void
    {
        $queue        = $this->waitingListModel->getQueueWithDetails();
        $availableRooms = $this->roomModel->getAvailable();
        $statistics = [
            'total_waiting' => $this->waitingListModel->countByWhere("status = 'waiting'"),
            'single_preference' => $this->waitingListModel->countByWhere("status = 'waiting' AND room_type_preference = 'single'"),
            'shared_preference' => $this->waitingListModel->countByWhere("status = 'waiting' AND room_type_preference = 'shared'")
        ];
        $this->view('landlord/waitingList', [
            'pageTitle'     => 'Waiting List — BoardTrack',
            'queue'         => $queue,
            'availableRooms'=> $availableRooms,
            'statistics' => $statistics,
        ], 'landlord');
    }

    public function assignFromWaiting(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/waitingList'); }
        $waitId  = (int)($_POST['waiting_id'] ?? 0);
        $roomId  = (int)($_POST['room_id']    ?? 0);
        $waiting = $this->waitingListModel->find($waitId);
        if (!$waiting || !$roomId) { $this->flash('error', 'Invalid selection.'); $this->redirect('landlord/waitingList'); }
        $this->tenantModel->assignRoom($waiting['tenant_id'], $roomId, date('Y-m-d'));
        $this->roomModel->updateOccupancy($roomId);
        $this->waitingListModel->update(['status'=>'assigned'], ['id'=>$waitId]);
        $tenant = $this->tenantModel->find($waiting['tenant_id']);
        if ($tenant) {
            $this->userModel->update(['status'=>'active'], ['id'=>$tenant['user_id']]);
            $this->notificationModel->createNotification($tenant['user_id'],'room','Room Assigned','You have been assigned to a room from the waiting list.','tenant/dashboard');
        }
        $this->auditLogModel->log($_SESSION['user_id'],'room_assigned_from_waiting','tenant',$waiting['tenant_id'],['status'=>'waiting_list'],['status'=>'active','room_id'=>$roomId],'Assigned from waiting list');
        $this->flash('success', 'Tenant assigned from waiting list.');
        $this->redirect('landlord/waitingList');
    }

    // ── Audit Log ── FIX: was missing ─────────────────────────
    public function auditLog(): void
    {
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $limit = 100;
        $filters = [
            'action'    => $_GET['action']    ?? null,
            'entity'    => $_GET['entity']    ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to'   => $_GET['date_to']   ?? null,
            'limit'     => $limit,
            'page'      => $page,
        ];
        $logs = $this->auditLogModel->getAll($filters);
        $totalLogs  = $this->auditLogModel->countAll(array_filter([
            'action'    => $filters['action'],
            'entity'    => $filters['entity'],
            'date_from' => $filters['date_from'],
            'date_to'   => $filters['date_to'],
        ]));
        $totalPages = max(1, (int)ceil($totalLogs / $limit));
        $this->view('landlord/auditlog', [
            'pageTitle'  => 'Audit Log — BoardTrack',
            'logs'       => $logs,
            'filters'    => $filters,
            'pagination' => [
                'current_page' => $page,
                'total_pages'  => $totalPages,
                'total'        => $totalLogs,
            ],
        ], 'landlord');
    }

    // ── Profile ─────────────────────────────────────────────────
    public function profile(): void
    {
        $user = $this->userModel->findById((int)$_SESSION['user_id']);
        $this->view('landlord/profile', [
            'pageTitle' => 'My Profile — BoardTrack',
            'user'      => $user,
        ], 'landlord');
    }

    public function updateProfile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/profile'); }
        $userId = (int)$_SESSION['user_id'];
        $name   = trim($_POST['name']  ?? '');
        $email  = trim($_POST['email'] ?? '');
        if (empty($name) || empty($email)) {
            $this->flash('error', 'Name and email are required.');
            $this->redirect('landlord/profile');
        }
        $data = ['name' => $name, 'email' => $email];
        if (!empty($_POST['new_password'])) {
            $data['password_hash'] = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        }
        $this->userModel->update($data, ['id' => $userId]);
        $_SESSION['user_name'] = $name;
        $this->flash('success', 'Profile updated successfully.');
        $this->redirect('landlord/profile');
    }
}