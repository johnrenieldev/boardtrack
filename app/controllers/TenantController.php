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

    // DASHBOARD

    /** GET /?url=tenant/dashboard */
    public function dashboard(): void
    {
        $user = $this->userModel->find((int) $_SESSION['user_id']);
        $tenant = $this->tenantModel->findByUserId((int) $_SESSION['user_id']);

        if (!$user || !$tenant) {
            $this->invalidSession('Session expired or profile missing. Please log in again.');
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
        $roomId = !empty($tenant['room_id']) ? (int) $tenant['room_id'] : null;
        $stats = [
            'unpaidBills' => $this->billModel->countUnpaidForTenant((int) $tenant['id'], $roomId),
            'pendingPayments' => $this->paymentModel->count("tenant_id = :tid AND status = 'pending'", [':tid' => $tenant['id']]),
            'openComplaints' => $this->complaintModel->count("tenant_id = :tid AND status IN ('pending', 'in_progress')", [':tid' => $tenant['id']]),
            'unreadNotifications' => $this->notificationModel->getUnreadCount($user['id']),
        ];

        // Get recent data
        $recentBills = array_slice(
            $this->billModel->getForTenant((int) $tenant['id'], $roomId),
            0,
            5
        );
        $recentAnnouncements = $this->notificationModel->getAnnouncements($user['id'], 3);
        $notifications = $this->notificationModel->getForUser($user['id'], false, 5);

        // Get roommates if assigned to shared room
        $roommates = [];
        if ($tenant['room_id'] && ($tenant['room_type'] ?? null) === 'shared') {
            $roommates = $this->tenantModel->getByRoomId($tenant['room_id']);
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

    // BILLS & PAYMENTS

    /** GET /?url=tenant/bills */
    public function bills(): void
    {
        $tenant = $this->requireTenantProfile();
        $roomId = !empty($tenant['room_id']) ? (int) $tenant['room_id'] : null;
        $bills = $this->billModel->getForTenant((int) $tenant['id'], $roomId);
        $statistics = $this->billModel->getTenantBillStatistics((int) $tenant['id'], $roomId);

        if (!$roomId && empty($bills)) {
            $this->view('tenant/bills', [
                'pageTitle'     => 'My Bills — BoardTrack',
                'bills'         => [],
                'statistics'    => $statistics,
                'noRoom'        => true,
                'landlordGcash' => $this->getLandlordGcashInfo(),
            ], 'tenant');
            return;
        }

        $this->view('tenant/bills', [
            'pageTitle'     => 'My Bills — BoardTrack',
            'bills'         => $bills,
            'statistics'    => $statistics,
            'landlordGcash' => $this->getLandlordGcashInfo(),
        ], 'tenant');
    }

    /** GET /?url=tenant/bill/pay/\d+ */
    public function payBill(int $billId): void
    {
        $tenant = $this->requireTenantProfile();
        $bill = $this->billModel->find($billId);

        if (!$this->billModel->tenantCanAccess($billId, (int) $tenant['id'], $tenant['room_id'] ? (int) $tenant['room_id'] : null)) {
            $this->flash('error', 'Bill not found.');
            $this->redirect('tenant/bills');
        }

        if ($bill['status'] === 'paid') {
            $this->flash('info', 'This bill has already been paid.');
            $this->redirect('tenant/bills');
        }

        if ($bill['status'] === 'pending_verification') {
            $this->flash('error', 'A payment for this bill is already awaiting landlord verification.');
            $this->redirect('tenant/bills');
        }

        $this->view('tenant/payBill', [
            'pageTitle'     => 'Pay Bill — BoardTrack',
            'bill'          => $bill,
            'landlordGcash' => $this->getLandlordGcashInfo(),
        ], 'tenant');
    }

    /** POST /?url=tenant/submit-payment */
    public function submitPayment(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/bills');
        }

        $tenant = $this->requireTenantProfile();

        $billId = (int) ($_POST['bill_id'] ?? 0);
        $bill   = $this->billModel->find($billId);
        $redirectPay = 'tenant/bills';

        if (!$bill || !$this->billModel->tenantCanAccess($billId, (int) $tenant['id'], $tenant['room_id'] ? (int) $tenant['room_id'] : null)) {
            $this->flash('error', 'Bill not found.');
            $this->redirect($redirectPay);
        }

        if ($bill['status'] === 'paid') {
            $this->flash('info', 'This bill has already been paid.');
            $this->redirect($redirectPay);
        }

        if ($bill['status'] === 'pending_verification') {
            $this->flash('error', 'A payment for this bill is already awaiting landlord verification.');
            $this->redirect($redirectPay);
        }

        $existing = $this->paymentModel->rawQueryOne(
            "SELECT id FROM payments WHERE bill_id = :bill_id AND status = 'pending' LIMIT 1",
            [':bill_id' => $billId]
        );
        if ($existing) {
            $this->flash('error', 'A payment for this bill is already pending verification.');
            $this->redirect($redirectPay);
        }

        $method = $_POST['payment_method'] ?? '';
        $allowedMethods = ['gcash', 'cash', 'other'];
        if (!in_array($method, $allowedMethods, true)) {
            $this->flash('error', 'Please select a valid payment method.');
            $this->redirect($redirectPay);
        }

        if ($method === 'gcash') {
            $landlord = $this->userModel->getLandlordAccount();
            if (empty($landlord['gcash_qr_path'])) {
                $this->flash('error', 'GCash is not set up yet. Pay in person (Cash) or ask your landlord to upload their GCash QR.');
                $this->redirect($redirectPay);
            }
        }

        if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Please upload a screenshot or photo of your payment receipt.');
            $this->redirect($redirectPay);
        }

        $file = $_FILES['payment_proof'];
        if (!in_array($file['type'], UPLOAD_ALLOWED, true)) {
            $this->flash('error', 'Receipt must be a JPG or PNG image.');
            $this->redirect($redirectPay);
        }
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            $this->flash('error', 'Receipt image must be less than 2MB.');
            $this->redirect($redirectPay);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, UPLOAD_ALLOWED_EXT, true)) {
            $ext = $file['type'] === 'image/png' ? 'png' : 'jpg';
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        if (!is_dir(UPLOAD_PAYMENTS)) {
            mkdir(UPLOAD_PAYMENTS, 0755, true);
        }
        if (!move_uploaded_file($file['tmp_name'], UPLOAD_PAYMENTS . '/' . $filename)) {
            $this->flash('error', 'Failed to upload receipt. Please try again.');
            $this->redirect($redirectPay);
        }

        // Support partial payments
        $paymentAmount = (float) ($_POST['amount'] ?? $bill['amount']);
        $isPartial = $paymentAmount < (float) $bill['amount'];
        $canAcceptPartial = $this->billModel->canAcceptPartialPayment($billId);

        if ($isPartial && !$canAcceptPartial) {
            $this->flash('error', 'Partial payments are not allowed for this bill.');
            $this->redirect($redirectPay);
        }

        if ($paymentAmount <= 0) {
            $this->flash('error', 'Payment amount must be greater than zero.');
            $this->redirect($redirectPay);
        }

        $paymentData = [
            'amount_paid'     => $paymentAmount,
            'payment_method'  => $method,
            'proof_file_path' => $filename,
            'proof_file_name' => $file['name'],
            'notes'           => trim($_POST['notes'] ?? '') ?: null,
            'is_partial'      => $isPartial,
        ];

        try {
            $this->paymentModel->beginTransaction();
            $paymentId = $this->paymentModel->submitPartialPayment($billId, (int) $tenant['id'], $paymentData);

            // Update bill status based on payment type
            if ($isPartial) {
                $this->billModel->update(['status' => 'pending_verification'], ['id' => $billId]);
            } else {
                $this->billModel->update(['status' => 'pending_verification'], ['id' => $billId]);
            }

            $landlord = $this->userModel->getLandlordAccount();
            if ($landlord) {
                $methodLabel = match ($method) {
                    'gcash' => 'GCash',
                    'cash' => 'Cash (in person)',
                    'bank_transfer' => 'Bank transfer',
                    default => ucfirst($method),
                };
                $paymentType = $isPartial ? 'partial' : '';
                $this->notificationModel->createNotification(
                    (int) $landlord['id'],
                    'payment',
                    'New Payment to Review',
                    ($tenant['name'] ?? 'A tenant') . " submitted a {$paymentType} {$methodLabel} payment of ₱"
                        . number_format($paymentAmount, 2) . " for \"{$bill['bill_name']}\".",
                    'landlord/view-payment/' . $paymentId
                );
            }

            $this->paymentModel->commit();
            $message = $isPartial 
                ? 'Partial payment receipt submitted. Your landlord will verify it shortly.' 
                : 'Payment receipt submitted. Your landlord will verify it shortly.';
            $this->flash('success', $message);
        } catch (Exception $e) {
            $this->paymentModel->rollback();
            if (is_file(UPLOAD_PAYMENTS . '/' . $filename)) {
                @unlink(UPLOAD_PAYMENTS . '/' . $filename);
            }
            $this->flash('error', 'Could not submit payment. Please try again.');
        }

        $this->redirect($redirectPay);
    }

    /** GCash QR and landlord payment info for tenant pay screens */
    private function getLandlordGcashInfo(): array
    {
        $landlord = $this->userModel->getLandlordAccount();
        if (!$landlord) {
            return ['has_qr' => false, 'qr_url' => null, 'landlord_name' => 'Landlord'];
        }
        return [
            'has_qr'        => !empty($landlord['gcash_qr_path']),
            'qr_url'        => !empty($landlord['gcash_qr_path'])
                ? Router::upload('gcash', $landlord['gcash_qr_path'])
                : null,
            'landlord_name' => $landlord['name'] ?? 'Landlord',
        ];
    }

    // COMPLAINTS

    /** GET /?url=tenant/complaints */
    public function complaints(): void
    {
        $tenant = $this->requireTenantProfile();
        $complaints = $this->complaintModel->getByTenantId((int) $tenant['id']);

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

        $tenant = $this->requireTenantProfile();

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

        $complaintId = $this->complaintModel->submit((int) $tenant['id'], $data);

        $landlord = $this->userModel->getLandlordAccount();
        if ($landlord) {
            $this->notificationModel->createNotification(
                (int) $landlord['id'],
                'complaint',
                'New Complaint',
                ($tenant['name'] ?? 'A tenant') . ' submitted: ' . $data['title'],
                'landlord/view-complaint/' . $complaintId
            );
        }

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

    // ANNOUNCEMENTS

    /** GET /?url=tenant/announcements — redirected to notifications */
    public function announcements(): void
    {
        $this->redirect('tenant/notifications');
    }

    // PERSONALITY QUESTIONNAIRE

    /** GET /?url=tenant/personality */
    public function personality(): void
    {
        $tenant = $this->requireTenantProfile();

        if (!empty($tenant['personality_completed'])) {
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

        foreach ($answers as $questionId => $answerValue) {
            $this->personalityModel->saveAnswer($tenant['id'], (int) $questionId, (int) $answerValue);
        }

        $this->tenantModel->markPersonalityCompleted($tenant['id']);

        if ($this->personalityModel->checkSuspiciousPattern($tenant['id'])) {
            $this->tenantModel->flagPersonality($tenant['id'], 'Suspicious pattern: majority of answers are identical');
        }

        $this->flash('success', 'Personality questionnaire completed successfully.');
        $this->redirect('tenant/dashboard');
    }

    // NOTIFICATIONS

    /** GET /?url=tenant/notifications */
    public function notifications(): void
    {
        $user = $this->userModel->find((int) $_SESSION['user_id']);
        $notifications = $this->notificationModel->getForUser($user['id']);

        $this->view('tenant/notifications', [
            'pageTitle' => 'Notifications — BoardTrack',
            'notifications' => $notifications,
            'markAllUrl' => 'tenant/notifications/mark-all-read',
        ], 'tenant');
    }

    /** POST /?url=tenant/notification/mark-read */
    public function markNotificationRead(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/notifications');
        }

        $notificationId = (int) ($_POST['notification_id'] ?? 0);
        $userId = (int) $_SESSION['user_id'];
        if ($notificationId > 0) {
            $this->notificationModel->markRead($notificationId, $userId);
        }

        $this->json([
            'success'      => true,
            'unread_count' => $this->notificationModel->getUnreadCount($userId),
        ]);
    }

    /** POST /?url=tenant/notifications/mark-all-read */
    public function markAllNotificationsRead(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/notifications');
        }

        $userId = (int) $_SESSION['user_id'];
        $this->notificationModel->markAllRead($userId);

        if ($this->wantsJson()) {
            $this->json(['success' => true, 'unread_count' => 0]);
        }

        $this->flash('success', 'All notifications marked as read.');
        $this->redirect('tenant/notifications');
    }

    // PROFILE

    /** GET /?url=tenant/profile */
    public function profile(): void
    {
        $userId = (int) $_SESSION['user_id'];
        // Use findByEmail via findById equivalent — use User::findById which returns totp_enabled
        $user   = $this->userModel->findById($userId);
        $tenant = $this->tenantModel->findByUserId($userId);

        // Keep email in session for disable2FA lookup
        $_SESSION['user_email'] = $user['email'] ?? '';

        $this->view('tenant/profile', [
            'pageTitle' => 'My Profile — BoardTrack',
            'user'      => $user,
            'tenant'    => $tenant,
        ], 'tenant');
    }

    /**
     * POST /?url=tenant/updateProfile
     *
     * UPDATED (Prompt 2): Password change removed from this form.
     * Password changes now go through auth/changePassword (requires current
     * password + TOTP if 2FA is enabled).
     */
    public function updateProfile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/profile');
        }

        $userId = (int) $_SESSION['user_id'];
        $tenant = $this->tenantModel->findByUserId($userId);

        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($name) || empty($email)) {
            $this->flash('error', 'Name and email are required fields.');
            $this->redirect('tenant/profile');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Please enter a valid email address.');
            $this->redirect('tenant/profile');
        }

        $guardianName    = trim($_POST['guardian_name'] ?? '');
        $guardianEmail   = trim($_POST['guardian_email'] ?? '');
        $guardianPurpose = trim($_POST['guardian_purpose'] ?? '');

        if (empty($guardianName) || empty($guardianEmail) || empty($guardianPurpose)) {
            $this->flash('error', 'Guardian/Emergency Contact details are required.');
            $this->redirect('tenant/profile');
        }

        if (!filter_var($guardianEmail, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Please enter a valid guardian email address.');
            $this->redirect('tenant/profile');
        }

        if (strlen($guardianPurpose) < 10) {
            $this->flash('error', 'Guardian contact purpose must be at least 10 characters.');
            $this->redirect('tenant/profile');
        }

        // Update name + email only (no password_hash here)
        $this->userModel->update(['name' => $name, 'email' => $email], ['id' => $userId]);

        $tenantData = [
            'room_type_preference' => $_POST['room_type_preference'] ?? $_POST['room_preference'] ?? 'shared',
            'guardian_name'        => $guardianName,
            'guardian_email'       => $guardianEmail,
            'guardian_purpose'     => $guardianPurpose,
        ];
        $this->tenantModel->update($tenantData, ['id' => $tenant['id']]);

        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;

        $this->flash('success', 'Profile updated successfully.');
        $this->redirect('tenant/profile');
    }
}