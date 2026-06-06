<?php
/**
 * BoardTrack | Tenant Controller
 * app/controllers/TenantController.php
 *
 * All routes are role-guarded | tenant only.
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
    private object $auditLogModel;
    private object $testimonialModel;

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
        $this->auditLogModel = $this->model('AuditLog');
        $this->testimonialModel = $this->model('Testimonial');
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

        // Strict sequential flow enforcement
        $isApproved = ($user['status'] === 'approved');
        $hasRoom = !empty($tenant['room_id']);

        // Redirect to holding page if not fully approved or no room assigned yet
        if (!$isApproved || !$hasRoom) {
            $this->view('tenant/pending', [
                'pageTitle' => 'Registration Progress | BoardTrack',
                'user'      => $user,
                'tenant'    => $tenant
            ], 'tenant');
            return;
        }

        // Get dashboard statistics
        $roomId = !empty($tenant['room_id']) ? (int) $tenant['room_id'] : null;
        $stats = [
            'unpaidBills'     => $this->billModel->countUnpaidForTenant((int) $tenant['id'], $roomId),
            'partialBills'    => $this->billModel->countByStatusForTenant('partial', (int) $tenant['id'], $roomId),
            'overdueBills'    => $this->billModel->countByStatusForTenant('overdue', (int) $tenant['id'], $roomId),
            'pendingPayments' => $this->paymentModel->count("tenant_id = :tid AND status = 'pending'", [':tid' => $tenant['id']]),
            'openComplaints'  => $this->complaintModel->count("tenant_id = :tid AND status IN ('pending', 'in_progress')", [':tid' => $tenant['id']]),
            'totalAmountDue'  => $this->billModel->getTotalRemainingForTenant((int) $tenant['id'], $roomId),
        ];

        // Get recent data
        $recentBills = array_slice(
            $this->billModel->getForTenant((int) $tenant['id'], $roomId),
            0,
            5
        );
        $recentAnnouncements = $this->notificationModel->getAnnouncements($user['id'], 3);
        $notifications = $this->notificationModel->getForUser($user['id'], false, 5);
        $unreadNotificationCount = $this->notificationModel->getUnreadCount((int) $user['id']);

        // Get roommates if assigned to shared room
        $roommates = [];
        if ($tenant['room_id'] && ($tenant['room_type'] ?? null) === 'shared') {
            $roommates = $this->tenantModel->getByRoomId($tenant['room_id']);
            $roommates = array_filter($roommates, fn($r) => $r['user_id'] != $user['id']);
        }

        $this->view('tenant/dashboard', [
            'pageTitle' => 'My Dashboard | BoardTrack',
            'user' => $user,
            'tenant' => $tenant,
            'stats' => $stats,
            'recentBills' => $recentBills,
            'recentAnnouncements' => $recentAnnouncements,
            'notifications' => $notifications,
            'unreadNotificationCount' => $unreadNotificationCount,
            'roommates' => $roommates,
        ], 'tenant');
    }

    // BILLS & PAYMENTS

    /** GET /?url=tenant/bills */
    public function bills(): void
    {
        $tenant = $this->requireApprovedTenant();
        $roomId = !empty($tenant['room_id']) ? (int) $tenant['room_id'] : null;
        $bills = $this->billModel->getForTenant((int) $tenant['id'], $roomId);
        $statistics = $this->billModel->getTenantBillStatistics((int) $tenant['id'], $roomId);

        // Get payment history for each bill
        foreach ($bills as &$bill) {
            $bill['payment_history'] = $this->paymentModel->getPaymentHistory((int)$bill['id']);
            $bill['total_paid'] = $this->paymentModel->getTotalPaidForBill((int)$bill['id']);
            $bill['remaining_balance'] = max(0, (float)$bill['amount'] - (float)$bill['total_paid']);
        }

        if (!$roomId && empty($bills)) {
            $this->view('tenant/bills', [
                'pageTitle'     => 'My Bills | BoardTrack',
                'bills'         => [],
                'statistics'    => $statistics,
                'noRoom'        => true,
                'landlordGcash' => $this->getLandlordGcashInfo(),
            ], 'tenant');
            return;
        }

        $this->view('tenant/bills', [
            'pageTitle'     => 'My Bills | BoardTrack',
            'bills'         => $bills,
            'statistics'    => $statistics,
            'landlordGcash' => $this->getLandlordGcashInfo(),
        ], 'tenant');
    }

    /** GET /?url=tenant/bill/pay/\d+ */
    public function payBill(int $billId): void
    {
        $tenant = $this->requireApprovedTenant();
        $bill = $this->billModel->find($billId);

        if (!$this->billModel->tenantCanAccess($billId, (int) $tenant['id'], $tenant['room_id'] ? (int) $tenant['room_id'] : null)) {
            $this->flash('error', 'Bill not found.');
            $this->redirect('tenant/bills');
        }

        // Check if bill is fully paid by status
        if ($bill['status'] === 'paid') {
            $this->flash('info', 'This bill has already been fully paid.');
            $this->redirect('tenant/bills');
        }

        // Check if bill is fully paid by amount (even if status is still 'partial')
        $billTotal = (float)($bill['amount'] ?? 0);
        $amountPaid = (float)($bill['amount_paid'] ?? 0);
        $remainingBalance = max(0, $billTotal - $amountPaid);
        
        if ($remainingBalance <= 0) {
            $this->flash('info', 'This bill has already been fully paid.');
            $this->redirect('tenant/bills');
        }

        // Allow payment for unpaid, partial, and overdue bills
        if (!in_array($bill['status'], ['unpaid', 'partial', 'overdue'])) {
            $this->flash('error', 'This bill cannot be paid at this time.');
            $this->redirect('tenant/bills');
        }

        $this->view('tenant/payBill', [
            'pageTitle'     => 'Pay Bill | BoardTrack',
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

        $this->verifyCsrf();

        $tenant = $this->requireApprovedTenant();

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

        // Check for existing pending payment
        $existing = $this->paymentModel->rawQueryOne(
            "SELECT id FROM payments WHERE bill_id = :bill_id AND status = 'pending' LIMIT 1",
            [':bill_id' => $billId]
        );
        if ($existing) {
            $this->flash('error', 'A payment for this bill is already pending verification.');
            $this->redirect($redirectPay);
        }

        // Validate amount_paid
        $paymentAmount = (float) ($_POST['amount_paid'] ?? 0);
        $billTotal = (float) ($bill['amount'] ?? 0);
        $alreadyPaid = (float) ($bill['amount_paid'] ?? 0);
        $remainingBalance = max(0, $billTotal - $alreadyPaid);

        if ($paymentAmount <= 0) {
            $this->flash('error', 'Payment amount must be greater than zero.');
            $this->redirect($redirectPay);
        }

        if ($paymentAmount > $remainingBalance) {
            $this->flash('error', 'Payment amount cannot exceed remaining balance of ₱' . number_format($remainingBalance, 2));
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

        $isPartial = $paymentAmount < $remainingBalance;

        $paymentData = [
            'amount_paid'     => $paymentAmount,
            'payment_method'  => $method,
            'proof_file_path' => $filename,
            'proof_file_name' => $file['name'],
            'notes'           => trim($_POST['notes'] ?? '') ?: null,
            'is_partial'      => $isPartial,
        ];

        $landlord = $this->userModel->getLandlordAccount();
        $methodLabel = match ($method) {
            'gcash' => 'GCash',
            'cash' => 'Cash (in person)',
            'bank_transfer' => 'Bank transfer',
            default => ucfirst($method),
        };

        try {
            $this->paymentModel->beginTransaction();
            $paymentId = $this->paymentModel->submitPartialPayment($billId, (int) $tenant['id'], $paymentData);

            // Update bill status to 'pending_verification' ONLY - do NOT update amount_paid yet
            // Amount_paid will be updated only when landlord approves the payment
            $this->billModel->update([
                'status' => 'pending_verification',
            ], ['id' => $billId]);

            if ($landlord) {
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

            // Audit Log
            $this->auditLogModel->log(
                (int) $_SESSION['user_id'],
                'payment_submitted',
                'payment',
                $paymentId,
                null,
                $paymentData,
                "Tenant submitted a " . ($isPartial ? 'partial ' : '') . "payment of ₱" . number_format($paymentAmount, 2) . " for \"{$bill['bill_name']}\""
            );

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
            $this->redirect($redirectPay);
        }

        // Email landlord about new payment submission (proof uploaded)
        if ($landlord && isset($paymentId)) {
            require_once ROOT_PATH . '/app/helpers/BoardTrackMail.php';
            BoardTrackMail::paymentSubmittedToLandlord(
                $landlord['email'],
                $landlord['name'] ?? 'Landlord',
                $tenant['name'] ?? 'Tenant',
                $tenant['email'] ?? '',
                (string) ($bill['bill_name'] ?? 'Bill'),
                (float) $paymentAmount,
                (string) $methodLabel,
                Router::url('landlord/view-payment/' . $paymentId)
            );
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
        $tenant = $this->requireApprovedTenant();
        $complaints = $this->complaintModel->getByTenantId((int) $tenant['id']);

        $this->view('tenant/complaints', [
            'pageTitle' => 'Complaints | BoardTrack',
            'complaints' => $complaints,
        ], 'tenant');
    }

    /** GET /?url=tenant/complaint/create */
    public function createComplaint(): void
    {
        $this->requireApprovedTenant();
        $this->view('tenant/complaintForm', [
            'pageTitle' => 'Submit Complaint | BoardTrack',
            'complaint' => null,
        ], 'tenant');
    }

    /** POST /?url=tenant/complaint/save */
    public function saveComplaint(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/complaints');
        }

        $tenant = $this->requireApprovedTenant();

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

            // Email landlord about new complaint
            require_once ROOT_PATH . '/app/helpers/BoardTrackMail.php';
            BoardTrackMail::complaintSubmittedToLandlord(
                $landlord['email'],
                $landlord['name'] ?? 'Landlord',
                $tenant['name'] ?? 'Tenant',
                $tenant['email'] ?? '',
                (string) ($data['category'] ?? 'other'),
                (string) ($data['title'] ?? ''),
                Router::url('landlord/view-complaint/' . $complaintId)
            );
        }

        $this->flash('success', 'Complaint submitted successfully.');
        $this->redirect('tenant/complaints');

    }

    /** GET /?url=tenant/complaint/view/\d+ */
    public function viewComplaint(int $id): void
    {
        $tenant = $this->requireApprovedTenant();
        $complaint = $this->complaintModel->find($id);

        if (!$complaint || $complaint['tenant_id'] != $tenant['id']) {
            $this->flash('error', 'Complaint not found.');
            $this->redirect('tenant/complaints');
        }

        $this->view('tenant/complaintView', [
            'pageTitle' => 'View Complaint | BoardTrack',
            'complaint' => $complaint,
        ], 'tenant');
    }

    /** POST /?url=tenant/update-complaint */
    public function updateComplaint(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/complaints');
        }

        $tenant    = $this->requireApprovedTenant();
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

        $tenant    = $this->requireApprovedTenant();
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

    /** POST /?url=tenant/respond-complaint */
    public function respondComplaint(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/complaints');
        }

        $tenant    = $this->requireApprovedTenant();
        $id        = (int) ($_POST['complaint_id'] ?? 0);
        $response  = trim($_POST['response'] ?? '');
        $complaint = $this->complaintModel->find($id);

        if (!$complaint || $complaint['tenant_id'] != $tenant['id']) {
            $this->flash('error', 'Complaint not found.');
            $this->redirect('tenant/complaints');
        }

        if (empty($complaint['landlord_response'])) {
            $this->flash('error', 'You can only respond after the landlord has responded.');
            $this->redirect('tenant/complaints');
        }

        if (empty($response)) {
            $this->flash('error', 'Response is required.');
            $this->redirect('tenant/view-complaint/' . $id);
        }

        $this->complaintModel->update([
            'tenant_response' => $response
        ], ['id' => $id]);

        // Get landlord user_id for notification
        $landlord = $this->userModel->getLandlordAccount();
        if ($landlord && isset($landlord['id'])) {
            $this->notificationModel->createNotification(
                $landlord['id'], 'complaint', 'Tenant Responded to Complaint',
                'Tenant has responded to complaint: "' . $complaint['title'] . '". Response: ' . $response,
                'landlord/view-complaint/' . $id
            );
        }

        $this->auditLogModel->log(
            $_SESSION['user_id'], 'tenant_responded_complaint', 'complaint', $id,
            ['tenant_response' => null], ['tenant_response' => $response],
            'Tenant responded to complaint'
        );

        $this->flash('success', 'Your response has been sent to the landlord.');
        $this->redirect('tenant/view-complaint/' . $id);
    }

    /** POST /?url=tenant/confirm-resolution */
    public function confirmResolution(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/complaints');
        }

        $tenant    = $this->requireApprovedTenant();
        $id        = (int) ($_POST['complaint_id'] ?? 0);
        $complaint = $this->complaintModel->find($id);

        if (!$complaint || $complaint['tenant_id'] != $tenant['id']) {
            $this->flash('error', 'Complaint not found.');
            $this->redirect('tenant/complaints');
        }

        if ($complaint['status'] !== 'resolved') {
            $this->flash('error', 'This complaint has not been marked as resolved yet.');
            $this->redirect('tenant/view-complaint/' . $id);
        }

        // Mark as closed
        $this->complaintModel->update([
            'status' => 'closed'
        ], ['id' => $id]);

        // Notify landlord
        $landlord = $this->userModel->getLandlordAccount();
        if ($landlord && isset($landlord['id'])) {
            $this->notificationModel->createNotification(
                $landlord['id'], 'complaint', 'Tenant Confirmed Resolution',
                'Tenant has confirmed that complaint "' . $complaint['title'] . '" has been resolved.',
                'landlord/view-complaint/' . $id
            );
        }

        $this->auditLogModel->log(
            $_SESSION['user_id'], 'tenant_confirmed_resolution', 'complaint', $id,
            ['status' => 'resolved'], ['status' => 'closed'],
            'Tenant confirmed complaint resolution'
        );

        $this->flash('success', 'Thank you for confirming. This complaint is now closed.');
        $this->redirect('tenant/complaints');
    }

    // ANNOUNCEMENTS

    /** GET /?url=tenant/announcements | redirected to notifications */
    public function announcements(): void
    {
        $this->redirect('tenant/notifications');
    }

    // PERSONALITY QUESTIONNAIRE

    /** GET /?url=tenant/personality */
    public function personality(): void
    {
        $this->redirect('personality/personality');
    }

    /** POST /?url=tenant/personality/submit */
    public function submitPersonality(): void
    {
        // Redirect to PersonalityController::submitPersonality
        // But since it's a POST, we should probably handle it or tell the user to use the new URL.
        // Actually, the form action in views/tenant/personality.php uses Router::url('tenant/submit-personality')
        // So I should keep handling it or update the view.
        $this->redirect('personality/submitPersonality');
    }

    // ACCOUNT SECURITY

    /** GET /?url=tenant/change-password */
    public function changePassword(): void
    {
        $tenant = $this->requireTenantProfile();
        $user = $this->userModel->findById((int) $_SESSION['user_id']);
        
        $this->view('tenant/change-password', [
            'pageTitle' => 'Change Password | BoardTrack',
            'has2FA'    => (bool) ($user['totp_enabled'] ?? false),
        ], 'tenant');
    }

    /** POST /?url=tenant/change-password-post */
    public function changePasswordPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/changePassword');
        }

        $this->requireAuth();
        $this->verifyCsrf();

        $userId      = (int) $_SESSION['user_id'];
        $currentPw   = $_POST['current_password'] ?? '';
        $newPw       = $_POST['new_password']      ?? '';
        $confirmPw   = $_POST['confirm_password']  ?? '';
        $totpCode    = trim($_POST['totp_code']    ?? '');

        $userRow = $this->userModel->findById($userId);
        $fullUser = $this->userModel->findByEmail($userRow['email'] ?? '');

        // 1. Verify current password
        if (!$fullUser || !password_verify($currentPw, $fullUser['password_hash'] ?? '')) {
            $this->flash('error', 'Current password is incorrect.');
            $this->redirect('tenant/changePassword');
        }

        // 2. If 2FA is enabled, require TOTP code
        if (!empty($fullUser['totp_enabled'])) {
            require_once ROOT_PATH . '/app/helpers/TOTP.php';
            $secret = $this->userModel->get2FASecret($userId);
            if (!$secret || !TOTP::verify($secret, $totpCode)) {
                $this->flash('error', 'Invalid authenticator code.');
                $this->redirect('tenant/changePassword');
            }
        }

        // 3. Validate new password
        if (strlen($newPw) < 8) {
            $this->flash('error', 'New password must be at least 8 characters.');
            $this->redirect('tenant/changePassword');
        }

        if ($newPw !== $confirmPw) {
            $this->flash('error', 'Passwords do not match.');
            $this->redirect('tenant/changePassword');
        }

        if ($newPw === $currentPw) {
            $this->flash('error', 'New password must be different from current password.');
            $this->redirect('tenant/changePassword');
        }

        // 4. Update
        $hashed = password_hash($newPw, PASSWORD_BCRYPT);
        $this->userModel->updatePassword($userId, $hashed);

        $this->auditLogModel->log($userId, 'password_changed', 'user', $userId, null, null, 'User changed password via tenant portal');

        $this->flash('success', 'Password changed successfully.');
        $this->redirect('tenant/profile');
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
            'csrf'          => $this->csrf(),
        ], 'tenant');
    }

    /** POST /?url=tenant/notification/mark-read */
    /**
     * POST tenant/markNotificationRead
     * Called by notifications.js (data-mark-notif-read-url) when user clicks an unread card.
     * Ownership enforced in model: WHERE id = :id AND user_id = :uid.
     */
    public function markNotificationRead(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/notifications');
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

        // Check if user has submitted a review
        $hasSubmittedReview = $this->testimonialModel->hasUserSubmittedTestimonial($userId);
        $userReview = null;
        if ($hasSubmittedReview) {
            $reviews = $this->testimonialModel->getTestimonialsByUserId($userId);
            $userReview = $reviews[0] ?? null;
        }

        $this->view('tenant/profile', [
            'pageTitle' => 'My Profile — BoardTrack',
            'user'      => $user,
            'tenant'    => $tenant,
            'hasSubmittedReview' => $hasSubmittedReview,
            'userReview' => $userReview,
        ], 'tenant');
    }

    /**
     * POST /?url=tenant/updateProfile
     *
     * Updates tenant profile details only.
     * Password changes are handled by auth/changePassword.
     */
    public function updateProfile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/profile');
        }

        $this->verifyCsrf();

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

        // SECURITY: Only allow tenant to update personal preferences
        // NEVER allow: room_id, room_number, occupancy, landlord-assigned fields
        $tenantData = [
            'gender'                     => $_POST['gender'] ?? $tenant['gender'],
            'guardian_name'              => $guardianName,
            'guardian_email'             => $guardianEmail,
            'guardian_purpose'           => $guardianPurpose,
        ];
        
        // SECURITY: If tenant has a room assigned, don't allow changing room preferences
        // Only landlord can manage room assignments
        if (!empty($tenant['room_id'])) {
            // Tenant has a room — keep existing preferences, ignore POST values
            $tenantData['room_type_preference']       = $tenant['room_type_preference'];
            $tenantData['air_conditioned_preference'] = $tenant['air_conditioned_preference'];
        } else {
            // No room assigned yet — allow updating preferences
            $tenantData['room_type_preference']       = $_POST['room_type_preference'] ?? $tenant['room_type_preference'];
            $tenantData['air_conditioned_preference'] = isset($_POST['air_conditioned_preference']) ? 1 : 0;
        }
        
        // SECURITY CHECK: Ensure no room assignment fields are in POST data
        $protectedFields = ['room_id', 'room_number', 'move_in_date', 'move_out_date', 'status'];
        foreach ($protectedFields as $field) {
            if (isset($_POST[$field])) {
                $this->auditLogModel->log(
                    $userId, 'security_violation', 'tenant', $tenant['id'],
                    null, ['attempted_field' => $field],
                    'Attempted to modify protected room assignment field'
                );
                $this->flash('error', 'Security violation: Unauthorized field modification attempt.');
                $this->redirect('tenant/profile');
            }
        }
        
        $this->tenantModel->update($tenantData, ['id' => $tenant['id']]);

        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;

        // System notification & email to landlord about tenant profile changes
        $landlord = $this->userModel->getLandlordAccount();
        if ($landlord) {
            // In-app notification
            $this->notificationModel->createNotification(
                (int) $landlord['id'],
                'profile',
                'Tenant Profile Updated',
                "Tenant profile information has been updated.",
                'landlord/view-tenant/' . $tenant['id']
            );

            // Email notification
            if (!empty($landlord['email'])) {
                require_once ROOT_PATH . '/app/helpers/BoardTrackMail.php';

                BoardTrackMail::tenantProfileUpdatedToLandlord(
                    $landlord['email'],
                    $landlord['name'] ?? 'Landlord',
                    $name,
                    $email,
                    (string) ($tenantData['room_type_preference'] ?? $tenantData['room_preference'] ?? 'shared'),
                    $guardianEmail,
                    $guardianPurpose
                );
            }
        }


        $this->flash('success', 'Profile updated successfully.');
        $this->redirect('tenant/profile');
    }


    // REVIEW / TESTIMONIAL

    /** GET /?url=tenant/review */
    public function review(): void
    {
        $tenant = $this->requireApprovedTenant();
        $userId = (int) $_SESSION['user_id'];
        
        // Check if user has already submitted a review
        $hasSubmitted = $this->testimonialModel->hasUserSubmittedTestimonial($userId);

        $this->view('tenant/review', [
            'pageTitle' => 'Submit Review',
            'hasSubmitted' => $hasSubmitted,
        ], 'tenant');
    }

    /** POST /?url=tenant/submit-review */
    public function submitReview(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/profile');
        }

        $tenant = $this->requireApprovedTenant();
        $userId = (int) $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        // Check if user has already submitted a review
        if ($this->testimonialModel->hasUserSubmittedTestimonial($userId)) {
            $this->flash('error', 'You have already submitted a review.');
            $this->redirect('tenant/profile');
        }


        $rating = (int) ($_POST['rating'] ?? 0);
        $reviewText = trim($_POST['review_text'] ?? '');

        // If no rating and no text, just redirect (optional feature)
        if ($rating === 0 && empty($reviewText)) {
            $this->flash('info', 'No review submitted.');
            $this->redirect('tenant/profile');
        }

        // Validate - if rating is provided, review text is required
        if ($rating > 0 && strlen($reviewText) < 10) {
            $this->flash('error', 'Review must be at least 10 characters long when submitting a rating.');
            $this->redirect('tenant/profile');
        }

        // Validate rating range
        if ($rating < 0 || $rating > 5) {
            $this->flash('error', 'Please select a valid rating (1-5 stars).');
            $this->redirect('tenant/profile');
        }

        // Create testimonial
        $this->testimonialModel->createTestimonial([
            'user_id' => $userId,
            'tenant_id' => $tenant['id'],
            'rating' => $rating,
            'review_text' => $reviewText
        ]);

        // Log the action
        $testimonialId = $this->testimonialModel->rawQueryOne("SELECT LAST_INSERT_ID() as id")['id'] ?? 0;
        $this->auditLogModel->log($userId, 'submitted', 'testimonial', (int)$testimonialId, null, null, 'Submitted a review/testimonial');

        // Create notification for landlord
        $landlordId = $this->getLandlordId();
        if ($landlordId) {
            $this->notificationModel->createNotification(
                $landlordId,
                'review',
                'New Review Submitted',
                "{$user['name']} has submitted a {$rating}-star review for BoardTrack.",
                'landlord/reviews'
            );
        }

        $this->flash('success', 'Your review has been submitted successfully. It will appear on the landing page.');
        $this->redirect('tenant/profile');
    }

    /**
     * Get the landlord ID for notifications
     */
    private function getLandlordId(): ?int
    {
        // Get the first landlord user
        $landlord = $this->userModel->findBy('role', 'landlord');
        return $landlord ? (int) $landlord['id'] : null;
    }

    /** POST /?url=tenant/update-review */
    public function updateReview(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('tenant/profile');
        }

        $userId = (int) $_SESSION['user_id'];
        $testimonialId = (int) ($_POST['testimonial_id'] ?? 0);
        $rating = (int) ($_POST['rating'] ?? 0);
        $reviewText = trim($_POST['review_text'] ?? '');

        // Validate
        if ($rating < 1 || $rating > 5) {
            $this->flash('error', 'Please select a valid rating (1-5 stars).');
            $this->redirect('tenant/profile');
        }

        if (strlen($reviewText) < 10) {
            $this->flash('error', 'Review must be at least 10 characters long.');
            $this->redirect('tenant/profile');
        }

        // Verify the testimonial belongs to the current user
        $reviews = $this->testimonialModel->getTestimonialsByUserId($userId);
        $userTestimonial = null;
        foreach ($reviews as $review) {
            if ((int) $review['id'] === $testimonialId) {
                $userTestimonial = $review;
                break;
            }
        }

        if (!$userTestimonial) {
            $this->flash('error', 'Review not found or you do not have permission to edit it.');
            $this->redirect('tenant/profile');
        }

        // Update the testimonial (keep it approved)
        $this->testimonialModel->update([
            'rating' => $rating,
            'review_text' => $reviewText,
            'is_approved' => 1
        ], ['id' => $testimonialId]);

        // Log the action
        $this->auditLogModel->log($userId, 'updated', 'testimonial', $testimonialId, null, null, 'Updated a review/testimonial');

        // Notify landlord about the update
        $landlordId = $this->getLandlordId();
        if ($landlordId) {
            $user = $this->userModel->findById($userId);
            $this->notificationModel->createNotification(
                $landlordId,
                'review',
                'Review Updated',
                "{$user['name']} has updated their review for BoardTrack.",
                'landlord/reviews'
            );
        }

        $this->flash('success', 'Your review has been updated successfully. It will appear on the landing page.');
        $this->redirect('tenant/profile');
    }
}