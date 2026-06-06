<?php
/**
 * BoardTrack | Landlord Controller
 * app/controllers/LandlordController.php
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
    private object $testimonialModel;

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
        $this->testimonialModel  = $this->model('Testimonial');
    }
    /**
     * Dashboard
     */
    public function dashboard(): void
    {
        $user         = $this->userModel->findById((int)$_SESSION['user_id']);
        $roomStats    = $this->roomModel->getStatistics();
        $paymentStats = $this->paymentModel->getStatistics();
        $activeQuestionsSql = "(SELECT COUNT(*) FROM personality_questions WHERE is_active = 1)";
        $answeredQuestionsSql = "(SELECT COUNT(DISTINCT pa.question_id)
            FROM personality_answers pa
            JOIN personality_questions pq ON pq.id = pa.question_id AND pq.is_active = 1
            WHERE pa.tenant_id = t.id)";
        $readyForReviewWhere = "role = 'tenant'
            AND status = 'pending'
            AND EXISTS (
                SELECT 1
                FROM tenants t
                WHERE t.user_id = users.id
                  AND t.personality_completed = 1
                  AND t.id_document_path IS NOT NULL
                  AND t.id_document_path != ''
                  AND {$activeQuestionsSql} > 0
                  AND {$answeredQuestionsSql} >= {$activeQuestionsSql}
            )";
        $pendingReviewCount = $this->userModel->countByWhere($readyForReviewWhere);
        $pendingTenantCount = $this->userModel->countByWhere("role = 'tenant' AND status = 'pending'");

        // Get unpaid bills count - includes unpaid, partial, and overdue bills
        $unpaidBillsResult = $this->billModel->rawQueryOne(
            "SELECT COUNT(*) as count FROM bills WHERE status IN ('unpaid', 'partial', 'overdue')"
        );
        $unpaidBillsCount = (int)($unpaidBillsResult['count'] ?? 0);

        $stats = [
            'pendingCount'     => $pendingReviewCount,
            'totalPendingCount' => $pendingTenantCount,
            'incompleteCount'  => max(0, $pendingTenantCount - $pendingReviewCount),
            'approvedCount'    => $this->userModel->countByWhere("role = 'tenant' AND status = 'approved'"),
            'activeCount'      => $this->tenantModel->countActive(),
            'waitingCount'     => $this->tenantModel->countWaiting(),
            'rejectedCount'    => $this->userModel->countByWhere("role = 'tenant' AND status = 'rejected'"),
            'totalRooms'       => (int)($roomStats['total_rooms']  ?? 0),
            'availableRooms'   => (int)($roomStats['available']    ?? 0),
            'occupiedRooms'    => (int)($roomStats['occupied']     ?? 0),
            'maintenanceRooms' => (int)($roomStats['maintenance']  ?? 0),
            'unpaidBills'      => $unpaidBillsCount,
            'pendingPayments'  => (int)($paymentStats['pending']   ?? 0),
            'approvedPayments' => (int)($paymentStats['approved']  ?? 0),
            'openComplaints'   => $this->complaintModel->getPendingCount(),
        ];

        $this->view('landlord/dashboard', [
            'pageTitle'           => 'Dashboard | BoardTrack',
            'user'                => $user,
            'stats'               => $stats,
            'recentComplaints'    => $this->complaintModel->getRecent(5),
            'recentAnnouncements' => $this->announcementModel->getRecent(5),
            'pendingPayments'     => $this->paymentModel->getPending(),
        ], 'landlord');
    }
    /**
     * Tenants
     */
    public function tenants(): void
    {
        $filters = [
            'status'        => $_GET['status']        ?? null,
            'search'        => $_GET['search']        ?? null,
            'room_type'     => $_GET['room_type']     ?? null,
            'gender'        => $_GET['gender']        ?? null,
            'compatibility' => $_GET['compatibility'] ?? null,
        ];
        $tenants        = $this->tenantModel->getAllWithFilters(array_filter($filters));
        $availableRooms = $this->roomModel->getAvailable();

        // Unfiltered counts for stat cards
        $tenantStats = [
            'total'        => $this->userModel->countByWhere("role = 'tenant'"),
            'pending'      => $this->userModel->countByWhere("role = 'tenant' AND status = 'pending'"),
            'active'       => $this->tenantModel->countActive(),
            'waiting_list' => $this->waitingListModel->countByWhere("status = 'waiting'"),
        ];

        $this->view('landlord/tenants', [
            'pageTitle'      => 'Tenants | BoardTrack',
            'tenants'        => $tenants,
            'filters'        => $filters,
            'availableRooms' => $availableRooms,
            'tenantStats'    => $tenantStats,
        ], 'landlord');
    }

    /** GET landlord/compatibility-preview */
    public function compatibilityPreview(): void
    {
        $tenantId = (int)($_GET['tenant_id'] ?? 0);
        $roomId   = (int)($_GET['room_id']   ?? 0);

        if (!$tenantId || !$roomId) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid parameters']);
            exit;
        }

        $compatibilityService = new CompatibilityService();
        $result = $compatibilityService->calculateCompatibility($tenantId, $roomId);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function viewTenant(int $id): void
    {
        $tenant = $this->tenantModel->getWithPersonality($id);
        if (!$tenant) {
            $this->flash('error', 'Tenant not found.');
            $this->redirect('landlord/tenants');
        }
        
        $personalityModel = $this->model('PersonalityAnswer');
        $compatibilityService = new CompatibilityService();
        
        $recommendations = $compatibilityService->rankRecommendedRooms($id);
        $currentCompatibility = null;
        if (!empty($tenant['room_id'])) {
            $currentCompatibility = $compatibilityService->calculateCompatibility($id, (int)$tenant['room_id'], false);
        }

        $this->view('landlord/tenantView', [
            'pageTitle'          => 'Tenant Details | BoardTrack',
            'tenant'             => $tenant,
            'personalityAnswers' => $personalityModel->getAnswersForTenant($id),
            'bills'              => $this->billModel->getByTenantId($id),
            'complaints'         => $this->complaintModel->getByTenantId($id),
            'isSuspicious'       => $personalityModel->checkSuspiciousPattern($id),
            'availableRooms'     => $this->roomModel->getAvailable($tenant['gender'] ?? null),
            'recommendations'    => $recommendations,
            'currentCompatibility' => $currentCompatibility,
        ], 'landlord');
    }

    public function approveTenant(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/tenants'); }

        $this->verifyCsrf();

        $tenantId = (int)($_POST['tenant_id'] ?? 0);
        $roomId   = !empty($_POST['room_id']) ? (int)$_POST['room_id'] : null;
        $tenant   = $this->tenantModel->find($tenantId);

        if (!$tenant) {
            $this->flash('error', 'Tenant not found.');
            $this->redirect('landlord/tenants');
        }

        // Restrict approval if personality quiz is not completed
        $personalityModel = $this->model('PersonalityAnswer');
        if (!$personalityModel->isCompleted($tenantId)) {
            $this->flash('error', 'Cannot approve tenant: Personality questionnaire is not completed.');
            $this->redirect('landlord/view-tenant/' . $tenantId);
        }

        // If room is selected, check compatibility for shared rooms
        if ($roomId) {
            $room = $this->roomModel->find($roomId);
            
            if (!$room) {
                $this->flash('error', 'Selected room not found.');
                $this->redirect('landlord/view-tenant/' . $tenantId);
            }

            // VALIDATION: Check if room is full
            $occupants = $this->tenantModel->getByRoomId($roomId);
            $currentOccupancy = count($occupants);
            if ($currentOccupancy >= $room['max_occupants']) {
                $this->flash('error', "Cannot assign tenant: Room {$room['room_number']} is already full ({$currentOccupancy}/{$room['max_occupants']} occupants).");
                $this->redirect('landlord/view-tenant/' . $tenantId);
            }
            
            // Gender check
            if ($room && $room['allowed_gender'] !== 'any' && $tenant['gender'] !== $room['allowed_gender']) {
                // Allow 'prefer_not_to_say' to be assigned to any gender-restricted room
                if ($tenant['gender'] !== 'prefer_not_to_say') {
                    $this->flash('error', "Cannot assign room: Room is for {$room['allowed_gender']}s, but tenant is " . ($tenant['gender'] ?? 'unspecified') . ".");
                    $this->redirect('landlord/view-tenant/' . $tenantId);
                }
            }

            if ($room && $room['room_type'] === 'shared') {
                $compatibilityService = new CompatibilityService();
                $compResult = $compatibilityService->calculateCompatibility($tenantId, $roomId);
                $compatibilityScore = $compResult['score'];
                if ($compatibilityScore < 50) {
                    $this->flash('warning', "Warning: Low compatibility score ({$compatibilityScore}%) with current roommates. Consider assigning to a different room.");
                }
            }

            // Air-conditioning preference check (soft warning; still allows assignment)
            $tenantWantsAircon = !empty($tenant['air_conditioned_preference']);
            $roomHasAircon     = !empty($room['air_conditioned']);
            if ($room && $tenantWantsAircon !== $roomHasAircon) {
                $roomAcStatus = $roomHasAircon ? 'air-conditioned' : 'not air-conditioned';
                $tenantPrefers = $tenantWantsAircon ? 'prefers air-conditioning' : 'does not prefer air-conditioning';
                $this->flash(
                    'warning',
                    "Warning: This room is {$roomAcStatus} but the tenant {$tenantPrefers}. You can still assign, but the tenant may be uncomfortable."
                );
            }
        }

        try {
            $this->userModel->beginTransaction();

            $this->userModel->update(['status' => 'approved'], ['id' => $tenant['user_id']]);

            if ($roomId) {
                $this->tenantModel->assignRoom($tenantId, $roomId, date('Y-m-d'));
                $this->roomModel->updateOccupancy($roomId);
                
                // Refresh compatibility cache for this room
                $compatibilityService = new CompatibilityService();
                $compatibilityService->clearTenantCache($tenantId);
                $compatibilityService->refreshRoomCache($roomId);
                
                // Calculate compatibility for notification
                $compatibilityMsg = '';
                if ($room && $room['room_type'] === 'shared') {
                    $compResult = $compatibilityService->calculateCompatibility($tenantId, $roomId);
                    $compatibilityScore = $compResult['score'];
                    $compatibilityMsg = ($compResult['status'] ?? '') === 'Empty Room'
                        ? ' Roommate compatibility: no current roommates yet.'
                        : " Roommate compatibility: {$compatibilityScore}%";
                }

                // Append air-conditioning preference note
                $tenantWantsAircon = !empty($tenant['air_conditioned_preference']);
                $roomHasAircon     = !empty($room['air_conditioned']);
                if ($room && $tenantWantsAircon !== $roomHasAircon) {
                    $compatibilityMsg .= ' Air-conditioning preference: not matched.';
                } elseif ($room) {
                    $compatibilityMsg .= ' Air-conditioning preference: matched.';
                }
                
                $this->notificationModel->createNotification(
                    $tenant['user_id'], 'room', 'Room Assigned',
                    'You have been approved and assigned to a room.' . $compatibilityMsg, 'tenant/dashboard'
                );
                $this->auditLogModel->log(
                    $_SESSION['user_id'], 'tenant_approved_room', 'tenant', $tenantId,
                    ['status' => 'pending'], ['status' => 'approved', 'room_id' => $roomId],
                    'Tenant approved and assigned room'
                );
                $this->flash('success', 'Tenant approved and room assigned.');
            } else {
                $this->waitingListModel->addTenant($tenantId, $tenant['room_type_preference'] ?? 'shared');
                $this->notificationModel->createNotification(
                    $tenant['user_id'], 'room', 'Account Approved',
                    'Your account is approved. You have been placed on the waiting list.', 'tenant/dashboard'
                );
                $this->auditLogModel->log(
                    $_SESSION['user_id'], 'tenant_approved_waiting', 'tenant', $tenantId,
                    ['status' => 'pending'], ['status' => 'waiting_list'],
                    'Tenant approved, added to waiting list'
                );
                $this->flash('success', 'Tenant approved and added to waiting list.');
            }
            $this->userModel->commit();
        } catch (Exception $e) {
            $this->userModel->rollback();
            $this->flash('error', 'An error occurred while approving the tenant.');
            $this->redirect('landlord/tenants');
        }

        require_once ROOT_PATH . '/app/helpers/BoardTrackMail.php';

        $tenantRow    = $this->tenantModel->find($tenantId) ?? $tenant;
        $approvedUser = $this->userModel->find((int) $tenant['user_id']);
        $statusMsg    = $roomId
            ? 'You have been approved and assigned to a room. You can view your room details in your dashboard.'
            : 'You have been approved and placed on the waiting list. We will notify you when a room is available.';

        if ($approvedUser && !empty($approvedUser['email'])) {
            BoardTrackMail::tenantApproved($approvedUser['email'], $approvedUser['name'], $statusMsg);
        }

        if (!empty($tenantRow['guardian_email'])) {
            BoardTrackMail::guardianTenantApproved(
                $tenantRow['guardian_email'],
                $tenantRow['guardian_name'] ?? 'Guardian',
                $approvedUser['name'] ?? 'Tenant',
                $tenantRow['guardian_purpose'] ?? 'Emergency contact on file.'
            );
        }

        $this->redirect('landlord/tenants');
    }

    public function rejectTenant(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/tenants'); }

        $this->verifyCsrf();

        $tenantId = (int)($_POST['tenant_id'] ?? 0);
        $reason   = trim($_POST['reason'] ?? '');

        if (empty($reason)) {
            $this->flash('error', 'Rejection reason is required.');
            $this->redirect('landlord/tenants');
        }

        $tenant = $this->tenantModel->find($tenantId);
        if (!$tenant) {
            $this->flash('error', 'Tenant not found.');
            $this->redirect('landlord/tenants');
        }

        $this->userModel->update(['status' => 'rejected'], ['id' => $tenant['user_id']]);
        $this->notificationModel->createNotification(
            $tenant['user_id'], 'system', 'Application Rejected',
            "Your registration was rejected. Reason: {$reason}", 'tenant/dashboard'
        );
        $this->auditLogModel->log(
            $_SESSION['user_id'], 'tenant_rejected', 'tenant', $tenantId,
            ['status' => 'pending'], ['status' => 'rejected'], $reason
        );
        $this->flash('success', 'Tenant registration rejected.');
        $this->redirect('landlord/tenants');
    }

    public function moveOutTenant(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/tenants'); }

        $this->verifyCsrf();

        $tenantId = (int)($_POST['tenant_id'] ?? 0);
        $tenant   = $this->tenantModel->find($tenantId);

        if (!$tenant) {
            $this->flash('error', 'Tenant not found.');
            $this->redirect('landlord/tenants');
        }

        // Check if tenant is already moved out
        $user = $this->userModel->find($tenant['user_id']);
        if ($user['status'] === 'moved_out') {
            $this->flash('warning', 'Tenant is already marked as moved out.');
            $this->redirect('landlord/tenants');
        }

        // Validation checks
        $warnings = [];

        // Check for unpaid bills
        $unpaidBills = $this->billModel->count("tenant_id = :tid AND status = 'unpaid'", [':tid' => $tenantId]);
        if ($unpaidBills > 0) {
            $warnings[] = "Tenant has {$unpaidBills} unpaid bill(s).";
        }

        // Check for open complaints
        $openComplaints = $this->complaintModel->count("tenant_id = :tid AND status IN ('pending', 'in_progress')", [':tid' => $tenantId]);
        if ($openComplaints > 0) {
            $warnings[] = "Tenant has {$openComplaints} open complaint(s).";
        }

        // Check for pending payments
        $pendingPayments = $this->paymentModel->count("tenant_id = :tid AND status = 'pending'", [':tid' => $tenantId]);
        if ($pendingPayments > 0) {
            $warnings[] = "Tenant has {$pendingPayments} pending payment(s) awaiting verification.";
        }

        // If there are warnings and force_move_out is not set, show warning
        if (!empty($warnings) && !isset($_POST['force_move_out'])) {
            $warningMessage = implode(' ', $warnings) . ' Are you sure you want to proceed?';
            $this->flash('warning', $warningMessage);
            // Store warnings in session for the confirmation dialog
            $_SESSION['move_out_warnings'] = $warnings;
            $_SESSION['move_out_tenant_id'] = $tenantId;
            $this->redirect('landlord/tenants');
        }

        $oldRoomId = $tenant['room_id'] ?? null;
        $oldStatus = $user['status'];

        $this->tenantModel->removeFromRoom($tenantId, date('Y-m-d'));
        $this->userModel->update(['status' => 'moved_out'], ['id' => $tenant['user_id']]);

        if ($oldRoomId) {
            $this->roomModel->updateOccupancy($oldRoomId);
            
            // Refresh compatibility cache for this room
            $compatibilityService = new CompatibilityService();
            $compatibilityService->clearTenantCache($tenantId);
            $compatibilityService->refreshRoomCache($oldRoomId);
        } else {
            $compatibilityService = new CompatibilityService();
            $compatibilityService->clearTenantCache($tenantId);
        }

        $this->notificationModel->createNotification(
            $tenant['user_id'], 'room', 'Moved Out',
            'You have been marked as moved out.', 'tenant/dashboard'
        );
        $this->auditLogModel->log(
            $_SESSION['user_id'], 'tenant_moved_out', 'tenant', $tenantId,
            ['status' => $oldStatus, 'room_id' => $oldRoomId],
            ['status' => 'moved_out', 'room_id' => null], 'Tenant moved out'
        );

        // Clear session warnings
        unset($_SESSION['move_out_warnings']);
        unset($_SESSION['move_out_tenant_id']);

        $this->flash('success', 'Tenant marked as moved out.');
        $this->redirect('landlord/tenants');
    }

    public function undoMoveOut(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/tenants'); }

        $tenantId = (int)($_POST['tenant_id'] ?? 0);
        $tenant   = $this->tenantModel->find($tenantId);

        if (!$tenant) {
            $this->flash('error', 'Tenant not found.');
            $this->redirect('landlord/tenants');
        }

        $user = $this->userModel->find($tenant['user_id']);

        // Check if tenant is moved out
        if ($user['status'] !== 'moved_out') {
            $this->flash('warning', 'Tenant is not marked as moved out.');
            $this->redirect('landlord/tenants');
        }

        // Revert status to approved
        $this->userModel->update(['status' => 'approved'], ['id' => $tenant['user_id']]);

        // Clear move_out_date
        $this->tenantModel->update(['move_out_date' => null], ['id' => $tenantId]);

        $this->notificationModel->createNotification(
            $tenant['user_id'], 'room', 'Move Out Reverted',
            'Your move-out status has been reverted. Please contact the landlord for room assignment.', 'tenant/dashboard'
        );
        $this->auditLogModel->log(
            $_SESSION['user_id'], 'move_out_undone', 'tenant', $tenantId,
            ['status' => 'moved_out'],
            ['status' => 'approved'], 'Move out status reverted'
        );

        $this->flash('success', 'Move out status has been reverted. Tenant is now active.');
        $this->redirect('landlord/tenants');
    }
    /**
     * Rooms
     */
    public function rooms(): void
    {
        $airConditionedFilter = $_GET['air_conditioned'] ?? '';
        $allowedGenderFilter  = $_GET['allowed_gender']   ?? '';
        
        $filters = [];
        if ($airConditionedFilter !== '') $filters['air_conditioned'] = $airConditionedFilter;
        if ($allowedGenderFilter !== '')  $filters['allowed_gender']  = $allowedGenderFilter;

        $rooms = $this->roomModel->getAllWithOccupancy($filters);
        $statistics = $this->roomModel->getStatistics();
        
        $this->view('landlord/rooms', [
            'pageTitle'  => 'Rooms | BoardTrack',
            'rooms'      => $rooms,
            'statistics' => $statistics,
            'filters'    => $filters,
        ], 'landlord');
    }

    public function viewRoom(int $id): void
    {
        $room = $this->roomModel->find($id);
        if (!$room) {
            $this->flash('error', 'Room not found.');
            $this->redirect('landlord/rooms');
        }
        $occupants             = $this->tenantModel->getByRoomId($id);
        $room['occupants']     = $occupants;
        $room['actual_occupants'] = count($occupants);
        $this->view('landlord/roomView', [
            'pageTitle' => 'Room ' . $room['room_number'] . ' | BoardTrack',
            'room'      => $room,
        ], 'landlord');
    }

    public function addRoom(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/rooms'); }

        $data = [
            'room_number'    => trim($_POST['room_number']    ?? ''),
            'floor'          => (int)($_POST['floor']         ?? 1),
            'room_type'      => $_POST['room_type']           ?? 'single',
            'allowed_gender' => $_POST['allowed_gender']      ?? 'any',
            'max_occupants'  => (int)($_POST['max_occupants'] ?? 1),
            'monthly_rent'   => (float)($_POST['monthly_rent']?? 0),
            'status'         => 'available',
            'description'    => trim($_POST['description']    ?? ''),
        ];
        if ($this->roomModel->hasColumn('air_conditioned')) {
            $data['air_conditioned'] = !empty($_POST['air_conditioned']) ? 1 : 0;
        }

        if (empty($data['room_number'])) { $this->flash('error', 'Room number is required.'); $this->redirect('landlord/rooms'); }
        if ($data['max_occupants'] < 1)  { $this->flash('error', 'Max occupants must be at least 1.'); $this->redirect('landlord/rooms'); }
        if ($data['monthly_rent'] <= 0)  { $this->flash('error', 'Monthly rent must be greater than zero.'); $this->redirect('landlord/rooms'); }

        // VALIDATION: Room type must match max occupants
        if ($data['room_type'] === 'single' && $data['max_occupants'] != 1) {
            $this->flash('error', 'Single rooms must have exactly 1 max occupant.');
            $this->redirect('landlord/rooms');
        }
        if ($data['room_type'] === 'shared' && $data['max_occupants'] < 2) {
            $this->flash('error', 'Shared rooms must have at least 2 max occupants.');
            $this->redirect('landlord/rooms');
        }

        $this->roomModel->insert($data);
        $this->auditLogModel->log($_SESSION['user_id'], 'room_created', 'room', 0, null, $data, "Room {$data['room_number']} created");
        $this->flash('success', "Room {$data['room_number']} added successfully.");
        $this->redirect('landlord/rooms');
    }

    public function editRoom(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/rooms'); }

        $id = (int)($_POST['room_id'] ?? 0);
        if (!$id) { $this->flash('error', 'Room ID is required.'); $this->redirect('landlord/rooms'); }

        $room = $this->roomModel->find($id);
        if (!$room) { $this->flash('error', 'Room not found.'); $this->redirect('landlord/rooms'); }

        $data = [
            'floor'          => (int)($_POST['floor']          ?? $room['floor']),
            'room_type'      => $_POST['room_type']             ?? $room['room_type'],
            'allowed_gender' => $_POST['allowed_gender']       ?? $room['allowed_gender'] ?? 'any',
            'max_occupants'  => (int)($_POST['max_occupants']   ?? $room['max_occupants']),
            'monthly_rent'   => (float)($_POST['monthly_rent']  ?? $room['monthly_rent']),
            'status'         => $_POST['status']                ?? $room['status'],
            'description'    => trim($_POST['description']      ?? ''),
        ];
        if ($this->roomModel->hasColumn('air_conditioned')) {
            $data['air_conditioned'] = !empty($_POST['air_conditioned']) ? 1 : 0;
        }

        if ($data['max_occupants'] < 1) { $this->flash('error', 'Max occupants must be at least 1.'); $this->redirect('landlord/rooms'); }
        if ($data['monthly_rent'] <= 0) { $this->flash('error', 'Monthly rent must be greater than zero.'); $this->redirect('landlord/rooms'); }

        // VALIDATION: Room type must match max occupants
        if ($data['room_type'] === 'single' && $data['max_occupants'] != 1) {
            $this->flash('error', 'Single rooms must have exactly 1 max occupant.');
            $this->redirect('landlord/rooms');
        }
        if ($data['room_type'] === 'shared' && $data['max_occupants'] < 2) {
            $this->flash('error', 'Shared rooms must have at least 2 max occupants.');
            $this->redirect('landlord/rooms');
        }

        // VALIDATION: Cannot reduce max_occupants below current occupancy
        $occupants = $this->tenantModel->getByRoomId($id);
        $currentOccupancy = count($occupants);
        if ($data['max_occupants'] < $currentOccupancy) {
            $this->flash('error', "Cannot reduce max occupants to {$data['max_occupants']}. Room currently has {$currentOccupancy} tenant(s).");
            $this->redirect('landlord/rooms');
        }

        $this->roomModel->update($data, ['id' => $id]);
        $compatibilityService = new CompatibilityService();
        $compatibilityService->refreshRoomCache($id);
        $this->auditLogModel->log($_SESSION['user_id'], 'room_updated', 'room', $id, $room, $data, "Room {$room['room_number']} updated");
        $this->flash('success', 'Room updated successfully.');
        $this->redirect('landlord/rooms');
    }

    public function deleteRoom(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/rooms'); }

        $roomId = (int)($_POST['room_id'] ?? 0);
        $room   = $this->roomModel->find($roomId);

        if (!$room) {
            $this->flash('error', 'Room not found.');
            $this->redirect('landlord/rooms');
        }

        // VALIDATION: Check for active tenants
        $occupants = $this->tenantModel->getByRoomId($roomId);
        if (!empty($occupants)) {
            $count = count($occupants);
            $this->flash('error', "Cannot delete room: {$count} active tenant(s) currently assigned. Move them out first.");
            $this->redirect('landlord/rooms');
        }

        // VALIDATION: Check for bills linked to this room (room-based bills)
        $roomBills = $this->billModel->count("room_id = :rid AND status != 'cancelled'", [':rid' => $roomId]);
        if ($roomBills > 0) {
            $this->flash('error', "Cannot delete room: {$roomBills} bill(s) exist for this room. Delete or cancel them first.");
            $this->redirect('landlord/rooms');
        }

        $this->roomModel->delete($roomId);
        
        // Clear compatibility cache for this room
        $compatibilityService = new CompatibilityService();
        $compatibilityService->refreshRoomCache($roomId);
        
        $this->auditLogModel->log($_SESSION['user_id'], 'room_deleted', 'room', $roomId, $room, null, "Room {$room['room_number']} deleted");
        $this->flash('success', "Room {$room['room_number']} deleted successfully.");
        $this->redirect('landlord/rooms');
    }
    /**
     * Billing
     */
    public function bills(): void
    {
        $filters       = ['status' => $_GET['status'] ?? null, 'search' => $_GET['search'] ?? null];
        $bills         = $this->billModel->getAllForLandlord(array_filter($filters));
        $statistics    = $this->billModel->getStatistics();
        $billableRooms = $this->roomModel->getBillableRooms();
        $activeTenants = $this->tenantModel->getActiveTenants();

        $this->view('landlord/bills', [
            'pageTitle'     => 'Billing | BoardTrack',
            'bills'         => $bills,
            'statistics'    => $statistics,
            'filters'       => $filters,
            'billableRooms' => $billableRooms,
            'activeTenants' => $activeTenants,
        ], 'landlord');
    }

    public function createBill(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/bills'); }

        $billingType = ($_POST['billing_type'] ?? 'room_based') === 'individual' ? 'individual' : 'room_based';
        $billName    = trim($_POST['bill_name'] ?? '');
        $periodStart = $_POST['period_start'] ?? date('Y-m-01');
        $periodEnd   = $_POST['period_end'] ?? date('Y-m-t');
        $amount      = (float) ($_POST['amount'] ?? 0);
        $dueDate     = $_POST['due_date'] ?? '';
        $notes       = trim($_POST['notes'] ?? '');
        $chargeCategory = in_array($_POST['charge_category'] ?? 'rent', ['rent', 'utility', 'maintenance', 'penalty', 'other'], true)
            ? $_POST['charge_category'] : 'rent';

        if (empty($billName) || $amount <= 0 || empty($dueDate)) {
            $this->flash('error', 'Fill in all required fields and enter a valid amount.');
            $this->redirect('landlord/bills');
        }
        
        // Maximum bill amount validation (₱5,000 limit)
        if ($amount > 5000) {
            $this->flash('error', 'Bill amount cannot exceed ₱5,000.00. Please create multiple bills if needed.');
            $this->redirect('landlord/bills');
        }
        
        if ($dueDate < date('Y-m-d')) {
            $this->flash('error', 'Due date cannot be in the past.');
            $this->redirect('landlord/bills');
        }
        if ($periodStart >= $periodEnd) {
            $this->flash('error', 'Billing period start must be before end date.');
            $this->redirect('landlord/bills');
        }

        if ($billingType === 'individual') {
            $created = $this->createIndividualBills($billName, $periodStart, $periodEnd, $amount, $dueDate, $notes, $chargeCategory);
            if ($created === 0) {
                $this->flash('error', 'No bills created. Select one or more active tenants.');
                $this->redirect('landlord/bills');
            }
            $msg = $created === 1
                ? 'Individual bill created and tenant notified.'
                : "{$created} individual bills created and tenants notified.";
        } else {
            $created = $this->createRoomBills($billName, $periodStart, $periodEnd, $amount, $dueDate, $notes, $chargeCategory);
            if ($created === 0) {
                // Check if rooms have tenants but they're not approved
                $roomIds = [];
                if (!empty($_POST['room_ids']) && is_array($_POST['room_ids'])) {
                    $roomIds = array_map('intval', $_POST['room_ids']);
                } elseif (!empty($_POST['room_id'])) {
                    $roomIds = [(int) $_POST['room_id']];
                }
                
                $hasUnapprovedTenants = false;
                foreach ($roomIds as $roomId) {
                    $allTenants = $this->tenantModel->rawQuery(
                        "SELECT t.*, u.status FROM tenants t JOIN users u ON t.user_id = u.id WHERE t.room_id = :rid",
                        [':rid' => $roomId]
                    );
                    if (!empty($allTenants)) {
                        foreach ($allTenants as $t) {
                            if ($t['status'] !== 'approved') {
                                $hasUnapprovedTenants = true;
                                break 2;
                            }
                        }
                    }
                }
                
                if ($hasUnapprovedTenants) {
                    $this->flash('error', 'No bills created. Selected rooms have tenants, but they are not approved yet. Please approve tenants first.');
                } else {
                    $this->flash('error', 'No bills created. Select rooms that have active tenants.');
                }
                $this->redirect('landlord/bills');
            }
            $msg = $created === 1
                ? 'Room bill created and occupants notified.'
                : "{$created} room bills created and occupants notified.";
        }

        $this->flash('success', $msg);
        $this->redirect('landlord/bills');
    }

    private function createRoomBills(
        string $billName, string $periodStart, string $periodEnd,
        float $amount, string $dueDate, string $notes, string $chargeCategory
    ): int {
        $roomIds = [];
        if (!empty($_POST['room_ids']) && is_array($_POST['room_ids'])) {
            $roomIds = array_map('intval', $_POST['room_ids']);
        } elseif (!empty($_POST['room_id'])) {
            $roomIds = [(int) $_POST['room_id']];
        }
        $roomIds = array_values(array_filter($roomIds));
        if (empty($roomIds)) {
            return 0;
        }

        $created = 0;
        foreach ($roomIds as $roomId) {
            $room = $this->roomModel->find($roomId);
            if (!$room) {
                continue;
            }

            // Duplicate prevention
            $isDuplicate = $this->billModel->count(
                "room_id = :rid AND bill_name = :name AND billing_period_start = :start AND billing_period_end = :end AND status != 'cancelled'",
                [':rid' => $roomId, ':name' => $billName, ':start' => $periodStart, ':end' => $periodEnd]
            );
            if ($isDuplicate > 0) {
                continue;
            }

            $tenants = $this->tenantModel->getByRoomId($roomId);
            if (empty($tenants)) {
                // Debug: Log why no tenants found
                error_log("No active tenants found for room ID: {$roomId}");
                continue;
            }

            $data = [
                'room_id'              => $roomId,
                'tenant_id'            => null,
                'billing_type'         => 'room_based',
                'charge_category'      => $chargeCategory,
                'bill_name'            => $billName,
                'billing_period_start' => $periodStart,
                'billing_period_end'   => $periodEnd,
                'amount'               => $amount,
                'due_date'             => $dueDate,
                'status'               => 'unpaid',
                'created_by'           => (int) $_SESSION['user_id'],
                'notes'                => $notes ?: null,
            ];

            $billId = $this->billModel->insert($data);
            $created++;

            foreach ($tenants as $tenant) {
                if (($tenant['user_status'] ?? '') !== 'approved') {
                    continue;
                }
                $this->notificationModel->createNotification(
                    $tenant['user_id'], 'billing', 'New Bill for Room ' . ($room['room_number'] ?? ''),
                    "A new room bill \"{$billName}\" of ₱" . number_format($amount, 2) . " has been issued.",
                    'tenant/bills'
                );
            }
            $this->auditLogModel->log(
                $_SESSION['user_id'], 'bill_created', 'bill', $billId, null, $data,
                "Room bill for {$room['room_number']}: {$billName}"
            );
        }
        return $created;
    }

    private function createIndividualBills(
        string $billName, string $periodStart, string $periodEnd,
        float $amount, string $dueDate, string $notes, string $chargeCategory
    ): int {
        $tenantIds = [];
        if (!empty($_POST['tenant_ids']) && is_array($_POST['tenant_ids'])) {
            $tenantIds = array_map('intval', $_POST['tenant_ids']);
        } elseif (!empty($_POST['tenant_id'])) {
            $tenantIds = [(int) $_POST['tenant_id']];
        }
        $tenantIds = array_values(array_filter($tenantIds));
        if (empty($tenantIds)) {
            return 0;
        }

        $created = 0;
        foreach ($tenantIds as $tenantId) {
            $tenant = $this->tenantModel->find($tenantId);
            if (!$tenant || ($tenant['user_status'] ?? '') !== 'approved' || empty($tenant['room_id'])) {
                continue;
            }

            // Duplicate prevention
            $isDuplicate = $this->billModel->count(
                "tenant_id = :tid AND bill_name = :name AND billing_period_start = :start AND billing_period_end = :end AND status != 'cancelled'",
                [':tid' => $tenantId, ':name' => $billName, ':start' => $periodStart, ':end' => $periodEnd]
            );
            if ($isDuplicate > 0) {
                continue;
            }

            $data = [
                'room_id'              => $tenant['room_id'] ?? null,
                'tenant_id'            => $tenantId,
                'billing_type'         => 'individual',
                'charge_category'      => $chargeCategory,
                'bill_name'            => $billName,
                'billing_period_start' => $periodStart,
                'billing_period_end'   => $periodEnd,
                'amount'               => $amount,
                'due_date'             => $dueDate,
                'status'               => 'unpaid',
                'created_by'           => (int) $_SESSION['user_id'],
                'notes'                => $notes ?: null,
            ];

            $billId = $this->billModel->insert($data);
            $created++;

            $this->notificationModel->createNotification(
                (int) $tenant['user_id'], 'billing', 'New Bill Assigned',
                "A new bill \"{$billName}\" of ₱" . number_format($amount, 2) . ' has been issued to you.',
                'tenant/bills'
            );
            $this->auditLogModel->log(
                $_SESSION['user_id'], 'bill_created', 'bill', $billId, null, $data,
                "Individual bill for tenant #{$tenantId}: {$billName}"
            );
        }
        return $created;
    }

    public function deleteBill(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/bills');
        }

        $billId = (int)($_POST['bill_id'] ?? 0);
        
        if (!$billId) {
            $this->flash('error', 'Invalid bill ID.');
            $this->redirect('landlord/bills');
        }
        
        $bill = $this->billModel->find($billId);

        if (!$bill) {
            $this->flash('error', 'Bill not found.');
            $this->redirect('landlord/bills');
        }

        try {
            $this->billModel->beginTransaction();
            
            // Check if bill has any payments
            $payments = $this->paymentModel->rawQuery(
                "SELECT COUNT(*) as count FROM payments WHERE bill_id = :bid",
                [':bid' => $billId]
            );
            $paymentCount = $payments[0]['count'] ?? 0;
            
            if ($paymentCount > 0) {
                // Delete associated payments first
                $this->paymentModel->rawQuery(
                    "DELETE FROM payments WHERE bill_id = :bid",
                    [':bid' => $billId]
                );
            }
            
            // Delete the bill
            $this->billModel->delete($billId);
            
            // Log the deletion
            $this->auditLogModel->log(
                $_SESSION['user_id'],
                'bill_deleted',
                'bill',
                $billId,
                $bill,
                null,
                "Bill deleted: {$bill['bill_name']}" . ($paymentCount > 0 ? " (with {$paymentCount} payment(s))" : "")
            );
            
            $this->billModel->commit();
            
            $message = $paymentCount > 0 
                ? "Bill and {$paymentCount} associated payment(s) deleted successfully."
                : "Bill deleted successfully.";
            
            $this->flash('success', $message);
            
        } catch (Exception $e) {
            $this->billModel->rollback();
            error_log("Error deleting bill {$billId}: " . $e->getMessage());
            $this->flash('error', 'Failed to delete bill. Please try again or contact support if the problem persists.');
        }
        
        $this->redirect('landlord/bills');
    }

    public function updateBill(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/bills');
        }

        $billId = (int)($_POST['bill_id'] ?? 0);
        $bill   = $this->billModel->find($billId);

        if (!$bill) {
            $this->flash('error', 'Bill not found.');
            $this->redirect('landlord/bills');
        }

        // Guard: 'pending_verification' is system-controlled only (set when tenant submits payment)
        $allowedStatuses = ['unpaid', 'partial', 'paid', 'overdue', 'cancelled'];
        $newStatus = $_POST['status'] ?? $bill['status'];
        if (!in_array($newStatus, $allowedStatuses, true)) {
            $this->flash('error', 'Invalid status value. That status can only be set by the system.');
            $this->redirect('landlord/bills');
        }

        $data = [
            'bill_name'            => trim($_POST['bill_name'] ?? $bill['bill_name']),
            'charge_category'      => $_POST['charge_category'] ?? $bill['charge_category'],
            'billing_period_start' => $_POST['period_start'] ?? $bill['billing_period_start'],
            'billing_period_end'   => $_POST['period_end'] ?? $bill['billing_period_end'],
            'amount'               => (float)($_POST['amount'] ?? $bill['amount']),
            'due_date'             => $_POST['due_date'] ?? $bill['due_date'],
            'notes'                => trim($_POST['notes'] ?? $bill['notes']),
            'status'               => $newStatus,
        ];

        if (empty($data['bill_name']) || $data['amount'] <= 0 || empty($data['due_date'])) {
            $this->flash('error', 'Bill name, amount, and due date are required.');
            $this->redirect('landlord/bills');
        }

        // VALIDATION: Prevent setting amount below already paid amount
        $currentAmountPaid = (float)($bill['amount_paid'] ?? 0);
        if ($data['amount'] < $currentAmountPaid) {
            $this->flash('error', 'Cannot set bill amount to ₱' . number_format($data['amount'], 2) . 
                ' — tenant has already paid ₱' . number_format($currentAmountPaid, 2) . '. ' .
                'New amount must be at least ₱' . number_format($currentAmountPaid, 2) . '.');
            $this->redirect('landlord/bills');
        }

        $this->billModel->update($data, ['id' => $billId]);
        $this->auditLogModel->log($_SESSION['user_id'], 'bill_updated', 'bill', $billId, $bill, $data, "Bill updated: {$data['bill_name']}");
        $this->flash('success', 'Bill updated successfully.');
        $this->redirect('landlord/bills');
    }

    /**
     * Payments
     */
    public function payments(): void
    {
        $filters    = ['status' => $_GET['status'] ?? null];
        $payments   = $this->paymentModel->getAllWithDetails(array_filter($filters));
        $statistics = $this->paymentModel->getStatistics();

        $this->view('landlord/payments', [
            'pageTitle'  => 'Payments | BoardTrack',
            'payments'   => $payments,
            'filters'    => $filters,
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
        
        // Get bill details for payment history and remaining balance
        $bill = $this->billModel->find((int)$payment['bill_id']);
        
        $this->view('landlord/paymentView', [
            'pageTitle' => 'Review Payment | BoardTrack',
            'payment'   => $payment,
            'bill'      => $bill,
        ], 'landlord');
    }

    public function approvePayment(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/payments'); }

        $this->verifyCsrf();

        require_once ROOT_PATH . '/app/helpers/BoardTrackMail.php';

        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $landlordNote = trim($_POST['landlord_note'] ?? '');
        
        $payment   = $this->paymentModel->find($paymentId);
        if (!$payment) { $this->flash('error', 'Payment not found.'); $this->redirect('landlord/payments'); }
        if ($payment['status'] !== 'pending') {
            $this->flash('error', 'Payment has already been processed.');
            $this->redirect('landlord/payments');
        }

        $paymentDetails = $this->paymentModel->getWithDetails($paymentId);
        $bill           = $this->billModel->find((int) $payment['bill_id']);
        $billName       = $bill['bill_name'] ?? ($paymentDetails['bill_name'] ?? 'Bill');
        $amount         = (float) ($payment['amount_paid'] ?? 0);
        $methodLabel    = BoardTrackMail::paymentMethodLabel($payment['payment_method'] ?? null);

        try {
            $this->paymentModel->beginTransaction();
            
            // Update payment record
            $this->paymentModel->update([
                'status'        => 'approved',
                'reviewed_by'   => (int)$_SESSION['user_id'],
                'reviewed_at'   => date('Y-m-d H:i:s'),
                'landlord_note' => $landlordNote ?: null,
            ], ['id' => $paymentId]);

            // Update bill's amount_paid
            $currentAmountPaid = (float) ($bill['amount_paid'] ?? 0);
            $newAmountPaid = $currentAmountPaid + $amount;
            $billTotal = (float) ($bill['amount'] ?? 0);
            $remainingBalance = max(0, $billTotal - $newAmountPaid);

            // Determine bill status based on remaining balance
            if ($remainingBalance <= 0) {
                // CASE B: Fully paid
                $this->billModel->update([
                    'amount_paid'            => $newAmountPaid,
                    'partial_payment_status' => 'full',
                    'status'                 => 'paid',
                    'paid_at'                => date('Y-m-d H:i:s'),
                    'last_payment_date'      => date('Y-m-d'),
                ], ['id' => $payment['bill_id']]);
            } else {
                // CASE A: Partially paid
                $this->billModel->update([
                    'amount_paid'            => $newAmountPaid,
                    'partial_payment_status' => 'partial',
                    'status'                 => 'partial',
                    'last_payment_date'      => date('Y-m-d'),
                ], ['id' => $payment['bill_id']]);
            }

            $tenant = $this->tenantModel->find((int) $payment['tenant_id']);
            if ($tenant) {
                $message = $remainingBalance <= 0
                    ? 'Your payment of ₱' . number_format($amount, 2) . ' for "' . $billName . '" was approved. Bill is now fully paid!'
                    : 'Your payment of ₱' . number_format($amount, 2) . ' for "' . $billName . '" was approved. Remaining balance: ₱' . number_format($remainingBalance, 2);
                
                if ($landlordNote) {
                    $message .= ' Landlord note: ' . $landlordNote;
                }
                
                $this->notificationModel->createNotification(
                    (int) $tenant['user_id'],
                    'payment',
                    'Payment Approved',
                    $message,
                    'tenant/bills'
                );
            }
            
            $this->auditLogModel->log($_SESSION['user_id'], 'payment_approved', 'payment', $paymentId,
                ['status' => 'pending'], ['status' => 'approved'], 'Payment approved' . ($landlordNote ? ': ' . $landlordNote : ''));
            
            $this->paymentModel->commit();
            
            $successMsg = $remainingBalance <= 0 
                ? 'Payment approved. Bill is now fully paid.' 
                : 'Payment approved. Remaining balance: ₱' . number_format($remainingBalance, 2);
            $this->flash('success', $successMsg);
        } catch (Exception $e) {
            $this->paymentModel->rollback();
            $this->flash('error', 'Error approving payment.');
            $this->redirect('landlord/payments');
        }

        $tenant = $this->tenantModel->find((int) $payment['tenant_id']);
        $tenantUser = $tenant
            ? $this->userModel->find((int) $tenant['user_id'])
            : null;

        if ($tenantUser && !empty($tenantUser['email'])) {
            BoardTrackMail::tenantPaymentApproved(
                $tenantUser['email'],
                $tenantUser['name'],
                $billName,
                $amount,
                $methodLabel,
                $remainingBalance  // Pass remaining balance
            );
            
            // Send additional partial payment reminder if balance remains
            if ($remainingBalance > 0 && $bill) {
                BoardTrackMail::tenantPaymentPartialReminder(
                    $tenantUser['email'],
                    $tenantUser['name'],
                    $billName,
                    $billTotal,
                    $newAmountPaid,
                    $remainingBalance,
                    $bill['due_date'] ?? date('Y-m-d', strtotime('+7 days'))
                );
            }
        }

        if ($tenant && !empty($tenant['guardian_email'])) {
            BoardTrackMail::guardianPaymentApproved(
                $tenant['guardian_email'],
                $tenant['guardian_name'] ?? 'Guardian',
                $tenantUser['name'] ?? 'Tenant',
                $billName,
                $amount,
                $tenant['guardian_purpose'] ?? 'Emergency contact on file.',
                $remainingBalance  // Pass remaining balance
            );
            
            // Send additional partial payment reminder to guardian if balance remains
            if ($remainingBalance > 0 && $bill) {
                BoardTrackMail::guardianPaymentPartialReminder(
                    $tenant['guardian_email'],
                    $tenant['guardian_name'] ?? 'Guardian',
                    $tenantUser['name'] ?? 'Tenant',
                    $billName,
                    $billTotal,
                    $newAmountPaid,
                    $remainingBalance,
                    $bill['due_date'] ?? date('Y-m-d', strtotime('+7 days')),
                    $tenant['guardian_purpose'] ?? 'Emergency contact on file.'
                );
            }
        }

        $this->redirect('landlord/payments');
    }

    public function rejectPayment(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/payments'); }

        $this->verifyCsrf();

        require_once ROOT_PATH . '/app/helpers/BoardTrackMail.php';

        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $reason    = trim($_POST['reason'] ?? '');
        $landlordNote = trim($_POST['landlord_note'] ?? $reason); // Use reason as landlord_note if not provided separately
        
        if (empty($reason)) { $this->flash('error', 'Rejection reason is required.'); $this->redirect('landlord/payments'); }

        $payment = $this->paymentModel->find($paymentId);
        if (!$payment) { $this->flash('error', 'Payment not found.'); $this->redirect('landlord/payments'); }
        if ($payment['status'] !== 'pending') {
            $this->flash('error', 'Payment has already been processed.');
            $this->redirect('landlord/payments');
        }

        $bill      = $this->billModel->find((int)$payment['bill_id']);
        $billName  = $bill['bill_name'] ?? 'Bill';

        try {
            $this->paymentModel->beginTransaction();
            $this->paymentModel->update([
                'status'        => 'rejected',
                'reviewed_by'   => (int)$_SESSION['user_id'],
                'reviewed_at'   => date('Y-m-d H:i:s'),
                'review_notes'  => $reason,
                'landlord_note' => $landlordNote,
            ], ['id' => $paymentId]);
            
            // When payment is rejected, revert bill status back to unpaid/partial/overdue
            // Check if bill has any approved payments to determine correct status
            $currentAmountPaid = (float) ($bill['amount_paid'] ?? 0);
            $billTotal = (float) ($bill['amount'] ?? 0);
            $remainingBalance = max(0, $billTotal - $currentAmountPaid);
            
            // Determine correct status after rejection
            $newStatus = 'unpaid';
            if ($currentAmountPaid > 0 && $remainingBalance > 0) {
                $newStatus = 'partial';
            } elseif ($currentAmountPaid >= $billTotal) {
                $newStatus = 'paid'; // Should not happen, but handle it
            }
            
            // Check if overdue
            if ($newStatus !== 'paid' && strtotime($bill['due_date']) < time()) {
                $newStatus = 'overdue';
            }
            
            $this->billModel->update([
                'status' => $newStatus,
            ], ['id' => $payment['bill_id']]);

            $tenant = $this->tenantModel->find((int)$payment['tenant_id']);
            if ($tenant) {
                // System notification
                $this->notificationModel->createNotification(
                    (int)$tenant['user_id'], 'payment', 'Payment Rejected',
                    "Your payment of ₱" . number_format($payment['amount_paid'], 2) . " for \"{$billName}\" was rejected. Reason: {$reason}",
                    'tenant/bills'
                );
            }
            $this->auditLogModel->log($_SESSION['user_id'], 'payment_rejected', 'payment', $paymentId,
                ['status' => 'pending'], ['status' => 'rejected'], $reason);
            $this->paymentModel->commit();
            $this->flash('success', 'Payment rejected and tenant notified.');
        } catch (Exception $e) {
            $this->paymentModel->rollback();
            $this->flash('error', 'Error rejecting payment.');
            $this->redirect('landlord/payments');
        }

        // Email sending (completely outside the transaction)
        $tenant = $this->tenantModel->find($payment['tenant_id']);
        if ($tenant) {
            // Email to tenant
            BoardTrackMail::tenantPaymentRejected(
                $tenant['email'],
                $tenant['name'],
                $billName,
                (float)$payment['amount_paid'],
                $reason
            );

            // Email to guardian
            if (!empty($tenant['guardian_email'])) {
                BoardTrackMail::guardianPaymentRejected(
                    $tenant['guardian_email'],
                    $tenant['guardian_name'] ?? 'Guardian',
                    $tenant['name'] ?? 'Tenant',
                    $billName,
                    (float)$payment['amount_paid'],
                    $reason,
                    $tenant['guardian_purpose'] ?? 'Emergency contact on file.'
                );
            }
        }

        $this->redirect('landlord/payments');
    }
    /**
     * Complaints
     */
    public function complaints(): void
    {
        $filters    = ['status' => $_GET['status'] ?? null, 'category' => $_GET['category'] ?? null];
        $complaints = $this->complaintModel->getAllWithTenants(array_filter($filters));
        $statistics = $this->complaintModel->getStatistics();

        $this->view('landlord/complaints', [
            'pageTitle'  => 'Complaints | BoardTrack',
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
            'pageTitle' => 'Complaint Detail | BoardTrack',
            'complaint' => $complaint,
        ], 'landlord');
    }

    public function respondComplaint(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/complaints'); }

        $id       = (int)($_POST['complaint_id'] ?? 0);
        $response = trim($_POST['response'] ?? '');
        $status   = $_POST['status'] ?? 'in_progress';
        $valid    = ['pending', 'in_progress', 'resolved'];
        if (!in_array($status, $valid)) $status = 'in_progress';

        $complaint = $this->complaintModel->find($id);
        if (!$complaint) { $this->flash('error', 'Complaint not found.'); $this->redirect('landlord/complaints'); }

        $updateData = ['status' => $status, 'landlord_response' => $response];
        if ($status === 'resolved') {
            $updateData['resolved_by'] = (int)$_SESSION['user_id'];
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        }
        $this->complaintModel->update($updateData, ['id' => $id]);

        $tenant = $this->tenantModel->find($complaint['tenant_id']);
        if ($tenant) {
            $message = 'Your complaint status updated to: ' . ucfirst(str_replace('_', ' ', $status));
            if (!empty($response)) {
                $message .= '. Landlord response: ' . $response;
            }
            $this->notificationModel->createNotification(
                $tenant['user_id'], 'complaint', 'Complaint Updated',
                $message,
                'tenant/view-complaint/' . $id
            );
        }
        $this->auditLogModel->log($_SESSION['user_id'], 'complaint_updated', 'complaint', $id,
            ['status' => $complaint['status']], ['status' => $status], 'Complaint status updated');
        $this->flash('success', 'Complaint updated and tenant notified.');
        $this->redirect('landlord/complaints');
    }

    public function deleteComplaint(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/complaints'); }

        $id        = (int)($_POST['complaint_id'] ?? 0);
        $complaint = $this->complaintModel->find($id);
        if (!$complaint) { $this->flash('error', 'Complaint not found.'); $this->redirect('landlord/complaints'); }

        $this->complaintModel->deleteById($id);
        $this->auditLogModel->log($_SESSION['user_id'], 'complaint_deleted', 'complaint', $id,
            ['title' => $complaint['title']], null, 'Complaint deleted by landlord');
        $this->flash('success', 'Complaint deleted.');
        $this->redirect('landlord/complaints');
    }
    /**
     * Announcements
     */
    public function announcements(): void
    {
        $announcements = $this->announcementModel->getAllWithAuthor();
        $statistics    = $this->announcementModel->getStatistics();
        $this->view('landlord/announcements', [
            'pageTitle'     => 'Announcements | BoardTrack',
            'announcements' => $announcements,
            'statistics'    => $statistics,
        ], 'landlord');
    }

    public function createAnnouncement(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/announcements'); }

        $data = [
            'title'      => trim($_POST['title']   ?? ''),
            'message'    => trim($_POST['content'] ?? ''),
            'priority'   => $_POST['priority']     ?? 'normal',
            'event_date' => !empty($_POST['event_date']) ? $_POST['event_date'] : null,
            'is_active'  => 1,
            'created_by' => (int)$_SESSION['user_id'],
        ];

        if (empty($data['title']) || empty($data['message'])) {
            $this->flash('error', 'Title and content are required.');
            $this->redirect('landlord/announcements');
        }

        $this->announcementModel->insert($data);

        // Notify all approved tenants
        $approvedTenants = $this->tenantModel->getAllWithFilters(['status' => 'approved']);
        $userIds = array_column($approvedTenants, 'user_id');
        if (!empty($userIds)) {
            $this->notificationModel->createNotificationsBulk(
                $userIds, 'announcement', $data['title'],
                $data['message'], 'tenant/notifications'
            );
        }
        $this->flash('success', 'Announcement posted and tenants notified.');
        $this->redirect('landlord/announcements');
    }

    public function editAnnouncement(int $id): void
    {
        $this->flash('info', 'Please use the announcements page to manage announcements.');
        $this->redirect('landlord/announcements');
    }

    public function toggleAnnouncement(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/announcements'); }

        $id           = (int)($_POST['announcement_id'] ?? 0);
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
        $this->auditLogModel->log($_SESSION['user_id'], 'announcement_deleted', 'announcement', $id,
            null, null, 'Announcement deleted');
        $this->flash('success', 'Announcement deleted.');
        $this->redirect('landlord/announcements');
    }
    /**
     * Waiting List
     */
    public function waitingList(): void
    {
        $queue          = $this->waitingListModel->getQueueWithDetails();
        $availableRooms = $this->roomModel->getAvailable();
        $statistics     = [
            'total_waiting'      => $this->waitingListModel->countByWhere("status = 'waiting'"),
            'single_preference'  => $this->waitingListModel->countByWhere("status = 'waiting' AND room_type_preference = 'single'"),
            'shared_preference'  => $this->waitingListModel->countByWhere("status = 'waiting' AND room_type_preference = 'shared'"),
        ];
        $this->view('landlord/waitingList', [
            'pageTitle'      => 'Waiting List | BoardTrack',
            'queue'          => $queue,
            'availableRooms' => $availableRooms,
            'statistics'     => $statistics,
        ], 'landlord');
    }

    public function assignFromWaiting(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/waitingList'); }

        $this->verifyCsrf();

        $waitId  = (int)($_POST['waiting_id'] ?? 0);
        $roomId  = (int)($_POST['room_id']    ?? 0);
        $waiting = $this->waitingListModel->find($waitId);

        if (!$waiting || !$roomId) { $this->flash('error', 'Invalid selection.'); $this->redirect('landlord/waitingList'); }

        $tenant = $this->tenantModel->find($waiting['tenant_id']);
        $room = $this->roomModel->find($roomId);
        
        if (!$tenant || !$room) {
            $this->flash('error', 'Tenant or room not found.');
            $this->redirect('landlord/waitingList');
        }

        // VALIDATION: Check if room is full
        $occupants = $this->tenantModel->getByRoomId($roomId);
        $currentOccupancy = count($occupants);
        if ($currentOccupancy >= $room['max_occupants']) {
            $this->flash('error', "Cannot assign tenant: Room {$room['room_number']} is already full ({$currentOccupancy}/{$room['max_occupants']} occupants).");
            $this->redirect('landlord/waitingList');
        }

        // VALIDATION: Gender check
        if ($room['allowed_gender'] !== 'any' && $tenant['gender'] !== $room['allowed_gender']) {
            if ($tenant['gender'] !== 'prefer_not_to_say') {
                $this->flash('error', "Cannot assign room: Room is for {$room['allowed_gender']}s, but tenant is " . ($tenant['gender'] ?? 'unspecified') . ".");
                $this->redirect('landlord/waitingList');
            }
        }

        // Soft air-conditioning preference warning (does not block assignment)
        if ($tenant && $room) {
            $tenantWantsAircon = !empty($tenant['air_conditioned_preference']);
            $roomHasAircon     = !empty($room['air_conditioned']);
            if ($tenantWantsAircon !== $roomHasAircon) {
                $this->flash('warning', 'Air-conditioning preference mismatch. Assignment will still proceed.');
            }
        }

        $this->tenantModel->assignRoom($waiting['tenant_id'], $roomId, date('Y-m-d'));
        $this->roomModel->updateOccupancy($roomId);
        
        // Refresh compatibility cache
        $compatibilityService = new CompatibilityService();
        $compatibilityService->clearTenantCache((int) $waiting['tenant_id']);
        $compatibilityService->refreshRoomCache($roomId);

        $this->waitingListModel->update(['status' => 'assigned', 'assigned_at' => date('Y-m-d H:i:s')], ['id' => $waitId]);

        if ($tenant) {
            $this->userModel->update(['status' => 'approved'], ['id' => $tenant['user_id']]);
            $this->notificationModel->createNotification(
                $tenant['user_id'], 'room', 'Room Assigned',
                'You have been assigned to a room from the waiting list.', 'tenant/dashboard'
            );
        }
        $this->auditLogModel->log($_SESSION['user_id'], 'room_assigned_from_waiting', 'tenant', $waiting['tenant_id'],
            ['status' => 'approved'], ['status' => 'approved', 'room_id' => $roomId], 'Assigned from waiting list');
        $this->flash('success', 'Tenant assigned from waiting list.');
        $this->redirect('landlord/waitingList');
    }

    /**
     * Automatic waiting list assignment based on preferences and availability
     */
    public function autoAssignWaitingList(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/waitingList'); }

        $this->verifyCsrf();

        $assignedCount = 0;
        $tenantAirconSelect = $this->tenantModel->hasColumn('air_conditioned_preference')
            ? 't.air_conditioned_preference AS air_conditioned_preference'
            : '0 AS air_conditioned_preference';
        $waitingTenants = $this->waitingListModel->rawQuery(
            "SELECT wl.*, t.room_type_preference, {$tenantAirconSelect}, t.id as tenant_id, t.user_id
             FROM waiting_list wl
             JOIN tenants t ON wl.tenant_id = t.id
             JOIN users u ON t.user_id = u.id
             WHERE wl.status = 'waiting' AND u.status = 'approved' AND t.room_id IS NULL
             ORDER BY wl.requested_at ASC, wl.priority_order ASC"
        );

        foreach ($waitingTenants as $waiting) {
            $preference = $waiting['room_type_preference'] ?? 'shared';
            $tenantAirconPref = (int)($waiting['air_conditioned_preference'] ?? 0);

            $hasAirconColumn = $this->roomModel->hasColumn('air_conditioned');

            // Try to match tenant preference first.
            $params = [':room_type' => $preference];
            $airconClause = '';
            if ($hasAirconColumn) {
                $airconClause = " AND r.air_conditioned = :air_conditioned";
                $params[':air_conditioned'] = $tenantAirconPref;
            }

            $availableRoomsMatched = $this->roomModel->rawQuery(
                "SELECT r.*, (r.max_occupants - r.current_occupants) as available_spots
                 FROM rooms r
                 WHERE r.status = 'available'
                 AND r.room_type = :room_type
                 AND r.current_occupants < r.max_occupants
                 {$airconClause}
                 ORDER BY r.floor ASC, r.room_number ASC",
                $params
            );

            // Fallback: ignore air-conditioning preference if no rooms match.
            $availableRooms = $availableRoomsMatched;
            if (empty($availableRoomsMatched)) {
                $availableRooms = $this->roomModel->rawQuery(
                    "SELECT r.*, (r.max_occupants - r.current_occupants) as available_spots
                     FROM rooms r
                     WHERE r.status = 'available'
                     AND r.room_type = :room_type
                     AND r.current_occupants < r.max_occupants
                     ORDER BY r.floor ASC, r.room_number ASC",
                    [':room_type' => $preference]
                );
            }

            if (!empty($availableRooms)) {
                $room = $availableRooms[0];
                $compatibilityService = new CompatibilityService();
                
                // Check compatibility if shared room
                if ($room['room_type'] === 'shared') {
                    $compResult = $compatibilityService->calculateCompatibility(
                        (int) $waiting['tenant_id'],
                        (int) $room['id']
                    );
                    $compatibilityScore = $compResult['score'];
                    
                    // Only assign if compatibility is acceptable (>= 60%)
                    if ($compatibilityScore < 60) {
                        continue; // Skip this tenant, try next
                    }
                }

                // Assign the room
                // Double-check capacity before assignment
                $occupants = $this->tenantModel->getByRoomId((int) $room['id']);
                $currentOccupancy = count($occupants);
                if ($currentOccupancy >= $room['max_occupants']) {
                    continue; // Room became full, skip to next tenant
                }

                $this->tenantModel->assignRoom((int) $waiting['tenant_id'], (int) $room['id'], date('Y-m-d'));
                $this->roomModel->updateOccupancy((int) $room['id']);
                
                // Refresh compatibility cache
                $compatibilityService->clearTenantCache((int) $waiting['tenant_id']);
                $compatibilityService->refreshRoomCache((int)$room['id']);
                $this->waitingListModel->update(
                    ['status' => 'assigned', 'assigned_at' => date('Y-m-d H:i:s')],
                    ['id' => $waiting['id']]
                );
                $this->userModel->update(['status' => 'approved'], ['id' => (int) $waiting['user_id']]);
                
                // Log per-tenant audit entry
                $this->auditLogModel->log(
                    $_SESSION['user_id'],
                    'auto_assign_room',
                    'tenant',
                    (int) $waiting['tenant_id'],
                    ['room_id' => null],
                    ['room_id' => (int) $room['id']],
                    "Auto-assigned tenant #{$waiting['tenant_id']} to Room {$room['room_number']}"
                );
                
                // Notify tenant
                $this->notificationModel->createNotification(
                    (int) $waiting['user_id'],
                    'room',
                    'Room Assigned',
                    'You have been automatically assigned to Room ' . $room['room_number'] . ' from the waiting list.',
                    'tenant/dashboard'
                );
                
                $assignedCount++;
            }
        }

        if ($assignedCount > 0) {
            $this->auditLogModel->log(
                $_SESSION['user_id'],
                'auto_assign_waiting_list',
                'waiting_list',
                0,
                null,
                ['assigned_count' => $assignedCount],
                "Auto-assigned {$assignedCount} tenants from waiting list"
            );
            $this->flash('success', "Automatically assigned {$assignedCount} tenants from the waiting list.");
        } else {
            $this->flash('info', 'No tenants could be automatically assigned. No matching rooms available or compatibility issues.');
        }

        $this->redirect('landlord/waitingList');
    }

    /**
     * Payment Reminders
     */
    public function sendPaymentReminders(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/bills'); }

        require_once ROOT_PATH . '/app/helpers/BoardTrackMail.php';

        $reminderType = $_POST['reminder_type'] ?? 'upcoming';
        $daysAhead = (int) ($_POST['days_ahead'] ?? 3);
        $sentCount = 0;

        if ($reminderType === 'upcoming') {
            // Send reminders for bills due within X days
            $bills = $this->billModel->getBillsDueForReminder($daysAhead);
            
            foreach ($bills as $bill) {
                $tenantId = (int) $bill['tenant_id'];
                $tenant = $this->tenantModel->find($tenantId);
                
                if ($tenant && !empty($bill['email'])) {
                    // Determine reminder level
                    $reminderLevel = 1;
                    if ($bill['reminder_sent_1']) $reminderLevel = 2;
                    if ($bill['reminder_sent_2']) $reminderLevel = 3;
                    
                    if ($reminderLevel <= 3) {
                        $daysUntilDue = (new DateTime($bill['due_date']))->diff(new DateTime())->days;
                        
                        BoardTrackMail::paymentReminder(
                            $bill['email'],
                            $bill['tenant_name'],
                            $bill['bill_name'],
                            (float) $bill['amount'],
                            $bill['due_date'],
                            $daysUntilDue,
                            $reminderLevel
                        );
                        
                        $this->notificationModel->createNotification(
                            (int) $tenant['user_id'],
                            'billing',
                            'Payment Due Soon',
                            "Your bill \"{$bill['bill_name']}\" of ₱" . number_format((float) $bill['amount'], 2) . 
                            " is due in {$daysUntilDue} day(s).",
                            'tenant/bills'
                        );
                        
                        $this->billModel->markReminderSent((int) $bill['id'], $reminderLevel);
                        $sentCount++;
                    }
                }
            }
            
            $this->auditLogModel->log(
                $_SESSION['user_id'],
                'payment_reminders_sent',
                'bill',
                0,
                null,
                ['type' => 'upcoming', 'days_ahead' => $daysAhead, 'sent_count' => $sentCount],
                "Sent {$sentCount} upcoming payment reminders"
            );
            
        } elseif ($reminderType === 'overdue') {
            // Send reminders for overdue bills
            $bills = $this->billModel->getOverdueBills();
            
            foreach ($bills as $bill) {
                $tenantId = (int) $bill['tenant_id'];
                $tenant = $this->tenantModel->find($tenantId);
                
                if ($tenant && !empty($bill['email'])) {
                    $daysOverdue = (new DateTime())->diff(new DateTime($bill['due_date']))->days;
                    
                    BoardTrackMail::paymentOverdue(
                        $bill['email'],
                        $bill['tenant_name'],
                        $bill['bill_name'],
                        (float) $bill['amount'],
                        $bill['due_date'],
                        $daysOverdue
                    );
                    
                    $this->notificationModel->createNotification(
                        (int) $tenant['user_id'],
                        'billing',
                        'Payment Overdue',
                        "Your bill \"{$bill['bill_name']}\" of ₱" . number_format((float) $bill['amount'], 2) . 
                        " is overdue by {$daysOverdue} day(s).",
                        'tenant/bills'
                    );
                    
                    $sentCount++;
                }
            }
            
            $this->auditLogModel->log(
                $_SESSION['user_id'],
                'payment_reminders_sent',
                'bill',
                0,
                null,
                ['type' => 'overdue', 'sent_count' => $sentCount],
                "Sent {$sentCount} overdue payment reminders"
            );
        }

        if ($sentCount > 0) {
            $this->flash('success', "Successfully sent {$sentCount} payment reminders.");
        } else {
            $this->flash('info', 'No payment reminders were sent. No eligible bills found.');
        }

        $this->redirect('landlord/bills');
    }

    /**
     * Audit Log
     */
    public function auditLog(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $limit   = 100;
        $filters = [
            'action'    => $_GET['action']    ?? null,
            'entity'    => $_GET['entity']    ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to'   => $_GET['date_to']   ?? null,
            'limit'     => $limit,
            'page'      => $page,
        ];

        $logs      = $this->auditLogModel->getAll($filters);
        $totalLogs = $this->auditLogModel->countAll(array_filter([
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
    /** GET landlord/notifications */
    public function notifications(): void
    {
        $userId = (int) $_SESSION['user_id'];
        $notifications = $this->notificationModel->getForUser($userId);
        $this->view('landlord/notifications', [
            'pageTitle'     => 'Notifications — BoardTrack',
            'notifications' => $notifications,
            'csrf'          => $this->csrf(),
        ], 'landlord');
    }

    /** POST landlord/notification/mark-read */
    /**
     * POST landlord/markNotificationRead
     * Called by notifications.js (data-mark-notif-read-url) when user clicks an unread card.
     * Ownership enforced in model: WHERE id = :id AND user_id = :uid.
     * X-Requested-With header check adds CSRF defence-in-depth alongside session auth.
     */
    public function markNotificationRead(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/notifications');
            return;
        }

        $notificationId = (int) ($_POST['notification_id'] ?? 0);
        $userId         = (int) $_SESSION['user_id'];

        if ($notificationId > 0) {
            $this->notificationModel->markRead($notificationId, $userId);
        }

        $this->json(['success' => true]);
    }

    // markAllNotificationsRead removed — "Mark All as Read" feature has been removed.

    /**
     * Profile
     */
    public function profile(): void
    {
        $userId = (int) $_SESSION['user_id'];
        $user   = $this->userModel->find($userId);
        if (!$user) {
            $this->invalidSession('Your account could not be loaded. Please log in again.');
        }

        $_SESSION['user_email'] = $user['email'] ?? '';

        $this->view('landlord/profile', [
            'pageTitle' => 'My Profile — BoardTrack',
            'user'      => $user,
        ], 'landlord');
    }

    /** POST landlord/upload-gcash-qr */
    public function uploadGcashQr(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/profile');
        }

        $userId = (int) $_SESSION['user_id'];
        $user   = $this->userModel->find($userId);
        if (!$user || ($user['role'] ?? '') !== 'landlord') {
            $this->redirect('landlord/profile');
        }

        if (!isset($_FILES['gcash_qr']) || $_FILES['gcash_qr']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Please select a GCash QR image to upload.');
            $this->redirect('landlord/profile');
        }

        $file = $_FILES['gcash_qr'];
        if (!in_array($file['type'], UPLOAD_ALLOWED, true)) {
            $this->flash('error', 'QR code must be a JPG or PNG image.');
            $this->redirect('landlord/profile');
        }
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            $this->flash('error', 'Image must be less than 2MB.');
            $this->redirect('landlord/profile');
        }

        if (!is_dir(UPLOAD_GCASH)) {
            mkdir(UPLOAD_GCASH, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, UPLOAD_ALLOWED_EXT, true)) {
            $ext = $file['type'] === 'image/png' ? 'png' : 'jpg';
        }
        $filename = 'gcash_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $filepath = UPLOAD_GCASH . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->flash('error', 'Failed to upload QR code. Please try again.');
            $this->redirect('landlord/profile');
        }

        if (!empty($user['gcash_qr_path'])) {
            $old = UPLOAD_GCASH . '/' . $user['gcash_qr_path'];
            if (is_file($old)) {
                @unlink($old);
            }
        }

        $this->userModel->updateGcashQr($userId, $filename);
        $this->flash('success', 'GCash QR code uploaded. Tenants can now scan it when paying via GCash.');
        $this->redirect('landlord/profile');
    }

    /** POST landlord/remove-gcash-qr */
    public function removeGcashQr(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/profile');
        }

        $userId = (int) $_SESSION['user_id'];
        $user   = $this->userModel->find($userId);
        if ($user && !empty($user['gcash_qr_path'])) {
            $path = UPLOAD_GCASH . '/' . $user['gcash_qr_path'];
            if (is_file($path)) {
                @unlink($path);
            }
            $this->userModel->updateGcashQr($userId, null);
        }
        $this->flash('success', 'GCash QR code removed.');
        $this->redirect('landlord/profile');
    }

    /**
     * POST landlord/updateProfile
     *
     * Updates landlord profile identity fields only.
     * Password changes are handled by auth/changePassword.
     */
    public function updateProfile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('landlord/profile'); }

        $this->verifyCsrf();

        $userId = (int)$_SESSION['user_id'];
        $name   = trim($_POST['name']  ?? '');
        $email  = trim($_POST['email'] ?? '');

        if (empty($name) || empty($email)) {
            $this->flash('error', 'Name and email are required.');
            $this->redirect('landlord/profile');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Please enter a valid email address.');
            $this->redirect('landlord/profile');
        }

        // Update name + email only (no password_hash here)
        $this->userModel->update(['name' => $name, 'email' => $email], ['id' => $userId]);

        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;

        $this->flash('success', 'Profile updated successfully.');
        $this->redirect('landlord/profile');
    }

    // =====================================================
    // OVERDUE PENALTY MANAGEMENT
    // =====================================================

    /**
     * View penalty dashboard
     */
    public function penalties(): void
    {
        $eligibleBills = $this->billModel->getBillsEligibleForPenalty();
        $billsWithPenalties = $this->billModel->getBillsWithPendingPenaltyNotifications();

        $this->view('landlord/penalties', [
            'pageTitle' => 'Overdue Penalties | BoardTrack',
            'eligibleBills' => $eligibleBills,
            'billsWithPenalties' => $billsWithPenalties,
        ], 'landlord');
    }

    /**
     * Manually process overdue penalties
     */
    public function processNow(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/penalties');
        }

        try {
            // Process penalties
            $results = $this->billModel->processOverduePenalties();

            // Send notifications
            $billsWithPenalties = $this->billModel->getBillsWithPendingPenaltyNotifications();
            $notificationsSent = 0;

            foreach ($billsWithPenalties as $bill) {
                try {
                    $penaltyDetails = $this->billModel->getPenaltyDetails((int) $bill['id']);
                    
                    // Create in-app notification
                    $this->notificationModel->insert([
                        'user_id' => $bill['tenant_id'],
                        'type' => 'penalty_applied',
                        'title' => 'Overdue Penalty Applied',
                        'message' => "A 10% penalty has been applied to your overdue bill: {$bill['bill_name']}. " .
                                    "Original: ₱" . number_format($penaltyDetails['original_amount'], 2) . ", " .
                                    "Penalty: ₱" . number_format($penaltyDetails['penalty_amount'], 2) . ", " .
                                    "Total Due: ₱" . number_format($penaltyDetails['current_amount'], 2),
                        'link' => '/tenant/bills',
                    ]);

                    // Mark notification as sent
                    $this->billModel->markPenaltyNotificationSent((int) $bill['id']);
                    $notificationsSent++;

                } catch (Exception $e) {
                    error_log("Failed to send penalty notification for bill #{$bill['id']}: " . $e->getMessage());
                }
            }

            $_SESSION['success'] = "Penalties processed successfully! " .
                                  "Processed: {$results['processed']}, " .
                                  "Notifications sent: {$notificationsSent}, " .
                                  "Total penalty: ₱" . number_format($results['total_penalty'], 2);

        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to process penalties: " . $e->getMessage();
        }

        $this->redirect('landlord/penalties');
    }

    // =====================================================
    // REVIEWS / TESTIMONIALS
    // =====================================================

    /** GET landlord/reviews */
    public function reviews(): void
    {
        $reviews = $this->testimonialModel->getAllWithTenantNames();
        
        $this->view('landlord/reviews', [
            'pageTitle' => 'Tenant Reviews | BoardTrack',
            'reviews' => $reviews,
        ], 'landlord');
    }

    /** POST landlord/approve-review/{id} */
    public function approveReview(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/reviews');
        }

        // Get review details before approving
        $review = $this->testimonialModel->find($id);
        
        if (!$review) {
            $this->flash('error', 'Review not found.');
            $this->redirect('landlord/reviews');
        }

        if ($this->testimonialModel->approveTestimonial($id)) {
            // Notify tenant that their review was approved
            if (!empty($review['user_id'])) {
                $this->notificationModel->createNotification(
                    (int) $review['user_id'],
                    'review',
                    'Your Review Was Approved',
                    'Your review has been approved and is now displayed on the landing page. Thank you for your feedback!',
                    'tenant/profile'
                );
            }
            
            // Log the action
            $this->auditLogModel->log(
                $_SESSION['user_id'],
                'review_approved',
                'testimonial',
                $id,
                ['is_approved' => 0],
                ['is_approved' => 1],
                'Landlord approved tenant review'
            );
            
            $this->flash('success', 'Review approved successfully. Tenant has been notified.');
        } else {
            $this->flash('error', 'Failed to approve review.');
        }

        $this->redirect('landlord/reviews');
    }

    /** POST landlord/delete-review/{id} */
    public function deleteReview(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/reviews');
        }

        // Get review details before deleting
        $review = $this->testimonialModel->find($id);
        
        if (!$review) {
            $this->flash('error', 'Review not found.');
            $this->redirect('landlord/reviews');
        }

        if ($this->testimonialModel->deleteTestimonial($id)) {
            // Notify tenant that their review was removed
            if (!empty($review['user_id'])) {
                $this->notificationModel->createNotification(
                    (int) $review['user_id'],
                    'review',
                    'Your Review Was Removed',
                    'Your review has been removed by the landlord. If you have questions, please contact the landlord directly.',
                    'tenant/profile'
                );
            }
            
            // Log the action
            $this->auditLogModel->log(
                $_SESSION['user_id'],
                'review_deleted',
                'testimonial',
                $id,
                $review,
                null,
                'Landlord deleted tenant review'
            );
            
            $this->flash('success', 'Review deleted successfully. Tenant has been notified.');
        } else {
            $this->flash('error', 'Failed to delete review.');
        }

        $this->redirect('landlord/reviews');
    }
}
