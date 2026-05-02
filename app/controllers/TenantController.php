<?php
/**
 * BoardTrack — Tenant Controller
 * app/controllers/TenantController.php
 *
 * All routes are role-guarded — tenant only.
 * Full functionality for tenant portal.
 */

class TenantController extends Controller
{
    private object $userModel;
    private object $tenantModel;
    private object $billModel;
    private object $paymentModel;
    private object $complaintModel;
    private object $announcementModel;
    private object $notificationModel;
    private object $personalityModel;
    private object $roomModel;

    public function __construct()
    {
        $this->requireRole('tenant');
        $this->userModel = $this->model('User');
        $this->tenantModel = $this->model('Tenant');
        $this->billModel = $this->model('Bill');
        $this->paymentModel = $this->model('Payment');
        $this->complaintModel = $this->model('Complaint');
        $this->announcementModel = $this->model('Announcement');
        $this->notificationModel = $this->model('Notification');
        $this->personalityModel = $this->model('PersonalityAnswer');
        $this->roomModel = $this->model('Room');
    }

    // ─────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────

    /** GET /?url=tenant/dashboard */
    public function dashboard(): void
    {
        $user = $this->userModel->find((int) $_SESSION['user_id']);
        $tenant = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);

        if (!$user || !$tenant) {
            $this->flash('error', 'Session expired. Please log in again.');
            $this->redirect('auth/logout');
        }

        // Pending/unverified tenants see a holding page
        if (in_array($user['status'], ['pending', 'unverified'])) {
            $this->view('tenant/pending', [
                'pageTitle' => 'Account Pending — BoardTrack',
                'user' => $user,
                'tenant' => $tenant,
            ], 'tenant');
            return;
        }

        // Get dashboard statistics
        $stats = [
            'unpaidBills' => $this->billModel->count("tenant_id = :tid AND status = 'unpaid'", [':tid' => $tenant['id']]),
            'pendingPayments' => $this->paymentModel->count("tenant_id = :tid AND status = 'pending'", [':tid' => $tenant['id']]),
            'openComplaints' => $this->complaintModel->count("tenant_id = :tid AND status IN ('pending', 'in_progress')", [':tid' => $tenant['id']]),
            'unreadNotifications' => $this->notificationModel->getUnreadCount($user['id']),
        ];

        // Get recent data
        $recentBills = array_slice($this->billModel->getByTenantId($tenant['id']), 0, 5);
        $recentAnnouncements = $this->announcementModel->getRecent(3);
        $notifications = $this->notificationModel->getForUser($user['id'], false, 5);

        // Get roommates if assigned to shared room
        $roommates = [];
        if ($tenant['room_id'] && ($tenant['room_type'] ?? null) === 'shared') {
            $roommates = $this->tenantModel->getByRoomId($tenant['room_id']);
            // Remove current tenant from list
            $roommates = array_filter($roommates, fn($r) => $r['user_id'] != $user['id']);
        }

        $this->view('tenant/dashboard', [
            'pageTitle' => 'My Dashboard — BoardTrack',
            'user' => $user,
            'tenant' => $tenant,
            'stats' => $stats,
            'recentBills' => $recentBills,
            'recentAnnouncements' => $recentAnnouncements,
            'notifications' => $notifications,
            'roommates' => $roommates,
        ], 'tenant');
    }

    // ─────────────────────────────────────────────
    // BILLS & PAYMENTS
    // ─────────────────────────────────────────────

    /** GET /?url=tenant/bills */
    public function bills(): void
    {
        $tenant = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);
        if (!$tenant) { $this->redirect('auth/logout'); }
        $bills = $this->billModel->getByTenantId($tenant['id']);
        $statistics = $this->billModel->getTenantStatistics($tenant['id']);

        $this->view('tenant/bills', [
            'pageTitle' => 'My Bills — BoardTrack',
            'bills' => $bills,
            'statistics' => $statistics,
        ], 'tenant');
    }

    /** GET /?url=tenant/bill/pay/\d+ */
    public function payBill(int $billId): void
    {
        $tenant = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);
        if (!$tenant) { $this->redirect('auth/logout'); }
        $bill = $this->billModel->find($billId);

        if (!$bill || $bill['tenant_id'] != $tenant['id']) {
            $this->flash('error', 'Bill not found.');
            $this->redirect('tenant/bills');
        }

        if ($bill['status'] === 'paid') {
            $this->flash('info', 'This bill has already been paid.');
            $this->redirect('tenant/bills');
        }

        $this->view('tenant/payBill', [
            'pageTitle' => 'Pay Bill — BoardTrack',
            'bill' => $bill,
        ], 'tenant');
    }

    /** POST /?url=tenant/payment/submit */
    public function submitPayment(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/bills');
        }

        $tenant = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);
        if (!$tenant) { $this->redirect('auth/logout'); }
        $billId = (int) ($_POST['bill_id'] ?? 0);
        $bill = $this->billModel->find($billId);

        if (!$bill || $bill['tenant_id'] != $tenant['id']) {
            $this->flash('error', 'Bill not found.');
            $this->redirect('tenant/bills');
        }

        // Check for duplicate pending payment
        $existing = $this->paymentModel->findBy('bill_id', $billId);
        if ($existing && $existing['status'] === 'pending') {
            $this->flash('error', 'A payment for this bill is already pending verification.');
            $this->redirect('tenant/bills');
        }

        // Handle file upload
        if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Please upload payment proof.');
            $this->redirect('tenant/pay-bill/' . $billId);
        }

        $file = $_FILES['payment_proof'];
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowedTypes)) {
            $this->flash('error', 'Only JPG and PNG files are allowed.');
            $this->redirect('tenant/pay-bill/' . $billId);
        }

        if ($file['size'] > UPLOAD_MAX_SIZE) {
            $this->flash('error', 'File size must be less than 2MB.');
            $this->redirect('tenant/pay-bill/' . $billId);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $filepath = UPLOAD_PAYMENTS . '/' . $filename;

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->flash('error', 'Failed to upload file.');
            $this->redirect('tenant/pay-bill/' . $billId);
        }

        // Create payment record
        $paymentData = [
            'amount_paid' => $bill['amount'],
            'payment_method' => $_POST['payment_method'] ?? 'other',
            'proof_file_path' => $filename,
            'proof_file_name' => $file['name'],
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        $this->paymentModel->submitPayment($billId, $tenant['id'], $paymentData);

        // Update bill status
        $this->billModel->updateStatus($billId, 'pending_verification');

        $this->flash('success', 'Payment submitted successfully. Awaiting verification.');
        $this->redirect('tenant/bills');
    }

    // ─────────────────────────────────────────────
    // COMPLAINTS
    // ─────────────────────────────────────────────

    /** GET /?url=tenant/complaints */
    public function complaints(): void
    {
        $tenant = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);
        $complaints = $this->complaintModel->getByTenantId($tenant['id']);

        $this->view('tenant/complaints', [
            'pageTitle' => 'Complaints — BoardTrack',
            'complaints' => $complaints,
        ], 'tenant');
    }

    /** GET /?url=tenant/complaint/create */
    public function createComplaint(): void
    {
        $this->view('tenant/complaintForm', [
            'pageTitle' => 'Submit Complaint — BoardTrack',
            'complaint' => null,
        ], 'tenant');
    }

    /** POST /?url=tenant/complaint/save */
    public function saveComplaint(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/complaints');
        }

        $tenant = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);
        
        $data = [
            'category' => $_POST['category'] ?? 'other',
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'is_anonymous' => !empty($_POST['is_anonymous']) && ($_POST['category'] ?? 'other') === 'roommate_conflict' ? 1 : 0,
        ];

        if (empty($data['title']) || empty($data['description'])) {
            $this->flash('error', 'Title and description are required.');
            $this->redirect('tenant/create-complaint');
        }

        $this->complaintModel->submit($tenant['id'], $data);

        $this->flash('success', 'Complaint submitted successfully.');
        $this->redirect('tenant/complaints');
    }

    /** GET /?url=tenant/complaint/view/\d+ */
    public function viewComplaint(int $id): void
    {
        $tenant = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);
        $complaint = $this->complaintModel->find($id);

        if (!$complaint || $complaint['tenant_id'] != $tenant['id']) {
            $this->flash('error', 'Complaint not found.');
            $this->redirect('tenant/complaints');
        }

        $this->view('tenant/complaintView', [
            'pageTitle' => 'View Complaint — BoardTrack',
            'complaint' => $complaint,
        ], 'tenant');
    }

    /** POST /?url=tenant/update-complaint */
    public function updateComplaint(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/complaints');
        }

        $tenant    = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);
        $id        = (int) ($_POST['complaint_id'] ?? 0);
        $complaint = $this->complaintModel->find($id);

        if (!$complaint || $complaint['tenant_id'] != $tenant['id']) {
            $this->flash('error', 'Complaint not found.');
            $this->redirect('tenant/complaints');
        }

        if ($complaint['status'] !== 'pending') {
            $this->flash('error', 'Only pending complaints can be edited.');
            $this->redirect('tenant/complaints');
        }

        $category = $_POST['category'] ?? 'other';
        $data = [
            'category'     => $category,
            'title'        => trim($_POST['title'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'is_anonymous' => (!empty($_POST['is_anonymous']) && $category === 'roommate_conflict') ? 1 : 0,
        ];

        if (empty($data['title']) || empty($data['description'])) {
            $this->flash('error', 'Title and description are required.');
            $this->redirect('tenant/complaints');
        }

        $this->complaintModel->updateByTenant($id, $tenant['id'], $data);
        $this->flash('success', 'Complaint updated successfully.');
        $this->redirect('tenant/complaints');
    }

    /** POST /?url=tenant/delete-complaint */
    public function deleteComplaint(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/complaints');
        }

        $tenant    = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);
        $id        = (int) ($_POST['complaint_id'] ?? 0);
        $complaint = $this->complaintModel->find($id);

        if (!$complaint || $complaint['tenant_id'] != $tenant['id']) {
            $this->flash('error', 'Complaint not found.');
            $this->redirect('tenant/complaints');
        }

        if ($complaint['status'] !== 'pending') {
            $this->flash('error', 'Only pending complaints can be deleted.');
            $this->redirect('tenant/complaints');
        }

        $this->complaintModel->deleteByTenant($id, $tenant['id']);
        $this->flash('success', 'Complaint deleted.');
        $this->redirect('tenant/complaints');
    }

    // ─────────────────────────────────────────────
    // ANNOUNCEMENTS
    // ─────────────────────────────────────────────

    /** GET /?url=tenant/announcements */
    public function announcements(): void
    {
        $announcements = $this->announcementModel->getActive();

        $this->view('tenant/announcements', [
            'pageTitle' => 'Announcements — BoardTrack',
            'announcements' => $announcements,
        ], 'tenant');
    }

    // ─────────────────────────────────────────────
    // PERSONALITY QUESTIONNAIRE
    // ─────────────────────────────────────────────

    /** GET /?url=tenant/personality */
    public function personality(): void
    {
        $tenant = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);
        
        // Check if already completed
        if ($tenant['personality_completed']) {
            $this->flash('info', 'You have already completed the personality questionnaire.');
            $this->redirect('tenant/dashboard');
        }

        $questions = $this->personalityModel->getAllQuestions();

        $this->view('tenant/personality', [
            'pageTitle' => 'Personality Questionnaire — BoardTrack',
            'questions' => $questions,
        ], 'tenant');
    }

    /** POST /?url=tenant/personality/submit */
    public function submitPersonality(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/personality');
        }

        $tenant = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);
        $answers = $_POST['answers'] ?? [];

        if (empty($answers)) {
            $this->flash('error', 'Please answer all questions.');
            $this->redirect('tenant/personality');
        }

        // Save answers
        foreach ($answers as $questionId => $answerValue) {
            $this->personalityModel->saveAnswer($tenant['id'], (int) $questionId, (int) $answerValue);
        }

        // Mark as completed
        $this->tenantModel->markPersonalityCompleted($tenant['id']);

        // Check for suspicious pattern
        if ($this->personalityModel->checkSuspiciousPattern($tenant['id'])) {
            $this->tenantModel->flagPersonality($tenant['id'], 'Suspicious pattern: majority of answers are identical');
        }

        $this->flash('success', 'Personality questionnaire completed successfully.');
        $this->redirect('tenant/dashboard');
    }

    // ─────────────────────────────────────────────
    // NOTIFICATIONS
    // ─────────────────────────────────────────────

    /** GET /?url=tenant/notifications */
    public function notifications(): void
    {
        $user = $this->userModel->find((int) $_SESSION['user_id']);
        $notifications = $this->notificationModel->getForUser($user['id']);

        $this->view('tenant/notifications', [
            'pageTitle' => 'Notifications — BoardTrack',
            'notifications' => $notifications,
        ], 'tenant');
    }

    /** POST /?url=tenant/notification/mark-read */
    public function markNotificationRead(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/notifications');
        }

        $notificationId = (int) ($_POST['notification_id'] ?? 0);
        $this->notificationModel->markRead($notificationId, (int)$_SESSION['user_id']);

        $this->json(['success' => true]);
    }

    /** POST /?url=tenant/notifications/mark-all-read */
    public function markAllNotificationsRead(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/notifications');
        }

        $this->notificationModel->markAllRead((int)$_SESSION['user_id']);

        $this->flash('success', 'All notifications marked as read.');
        $this->redirect('tenant/notifications');
    }

    // ─────────────────────────────────────────────
    // PROFILE
    // ─────────────────────────────────────────────

    /** GET /?url=tenant/profile */
    public function profile(): void
    {
        $user = $this->userModel->find((int) $_SESSION['user_id']);
        $tenant = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);

        $this->view('tenant/profile', [
            'pageTitle' => 'My Profile — BoardTrack',
            'user' => $user,
            'tenant' => $tenant,
        ], 'tenant');
    }

    /** POST /?url=tenant/profile/update */
    public function updateProfile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/profile');
        }

        $userId = (int) $_SESSION['user_id'];
        $tenant = $this->tenantModel->findByUserId($userId);

        // Update user info
        $userData = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? '')
        ];
        
        // Handle password change if provided
        if (!empty($_POST['new_password'])) {
            $userData['password_hash'] = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        }
        
        if (empty($userData['name']) || empty($userData['email'])) {
            $this->flash('error', 'Name and Custom Email are required fields.');
            $this->redirect('tenant/profile');
        }

        $this->userModel->update($userData, ['id' => $userId]);

        // Update tenant info
        $tenantData = [
            'room_type_preference' => $_POST['room_type_preference'] ?? $_POST['room_preference'] ?? 'shared',
        ];
        $this->tenantModel->update($tenantData, ['id' => $tenant['id']]);

        // Update session name for topbar
        $_SESSION['user_name'] = $userData['name'];

        $this->flash('success', 'Profile updated successfully.');
        $this->redirect('tenant/profile');
    }
}