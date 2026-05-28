<?php
/**
 * Authentication controller.
 *
 * Handles login, registration, email verification, password reset, and TOTP-based 2FA.
 */

class AuthController extends Controller
{
    private object $user;

    public function __construct()
    {
        $this->user = $this->model('User');
        require_once ROOT_PATH . '/app/helpers/TOTP.php';
    }

    // Public login endpoints.

    /** GET auth/login */
    public function login(): void
    {
        $this->guestOnly();
        $this->view('auth/login', ['pageTitle' => 'Sign In | BoardTrack'], 'main');
    }

    /**
     * POST auth/loginPost
     *
     * Step 1: Validate email + password only.
     * Step 2: If 2FA is enabled → store pending_user in session → redirect to TOTP step.
     *         If 2FA is NOT enabled → complete login immediately.
     *
     * 2FA is enforced only for accounts with TOTP enabled.
     */
    public function loginPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/login');
        }

        $this->verifyCsrf();

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $roleHint = $_POST['role_hint']     ?? 'tenant';

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Email and password are required.');
            $this->redirect('auth/login');
        }

        $user = $this->user->findByEmail($email);

        // Password check
        if (!$user || !password_verify($password, $user['password_hash'] ?? '')) {
            $this->flash('error', 'Incorrect email or password.');
            $this->redirect('auth/login');
        }

        // Role check
        if ($user['role'] !== $roleHint) {
            $this->flash('error', 'Account not found for the selected role.');
            $this->redirect('auth/login');
        }

        // Status check
        if ($user['status'] === 'rejected') {
            $this->flash('error', 'Your registration was rejected. Please contact the landlord.');
            $this->redirect('auth/login');
        }

        // Route to second-factor verification when 2FA is enabled.
        if (!empty($user['totp_enabled'])) {
            // Store the verified-password user in session pending TOTP confirmation.
            // Do NOT regenerate session ID yet — that happens after TOTP is confirmed.
            $_SESSION['2fa_pending_user']    = $user;
            $_SESSION['2fa_pending_expires'] = time() + 300; // 5-minute window
            $this->redirect('auth/totpVerify');
        }

        // Complete login immediately when 2FA is not enabled.
        $this->completeLogin($user);
    }

    // Public TOTP verification endpoints.

    /** GET auth/totpVerify */
    public function totpVerify(): void
    {
        $this->guestOnly();
        $this->guard2FAPending();

        $this->view('auth/totp_verify', [
            'pageTitle' => 'Two-Factor Verification | BoardTrack',
        ], 'main');
    }

    /**
     * POST auth/totpVerifyPost
     * Accepts either a 6-digit TOTP code or a recovery code.
     */
    public function totpVerifyPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/login');
        }

        $this->guard2FAPending();
        $this->verifyCsrf();

        $user = $_SESSION['2fa_pending_user'];
        $code = trim($_POST['totp_code'] ?? '');

        // Validate the TOTP code
        $secret = $this->user->get2FASecret((int) $user['id']);
        if (!$secret) {
            $this->flash('error', 'Two-factor authentication configuration error. Please contact support.');
            $this->redirect('auth/login');
        }

        if (TOTP::verify($secret, $code)) {
            $this->finalize2FALogin($user);
            return;
        }

        $this->flash('error', 'Invalid authenticator code. Please try again.');
        $this->redirect('auth/totpVerify');
    }

    /**
     * POST auth/totpRecovery
     * Use a backup recovery code instead of TOTP.
     */
    public function totpRecovery(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/totpVerify');
        }

        $this->guard2FAPending();
        $this->verifyCsrf();

        $user         = $_SESSION['2fa_pending_user'];
        $recoveryInput = trim($_POST['recovery_code'] ?? '');

        $hashedCodes = $this->user->getRecoveryCodes((int) $user['id']);
        if (!$hashedCodes) {
            $this->flash('error', 'No recovery codes found. Please contact support.');
            $this->redirect('auth/totpVerify');
        }

        $updatedCodes = TOTP::useRecoveryCode($recoveryInput, $hashedCodes);
        if ($updatedCodes === false) {
            $this->flash('error', 'Invalid recovery code. Please try again.');
            $this->redirect('auth/totpVerify');
        }

        // Code matched — remove used code, save remaining
        $this->user->saveRecoveryCodes((int) $user['id'], $updatedCodes);

        if (count($updatedCodes) <= 2) {
            $this->flash('warning', 'You have ' . count($updatedCodes) . ' recovery codes remaining. Please generate new ones.');
        }

        $this->finalize2FALogin($user);
    }

    // Authenticated 2FA setup and management endpoints.

    /**
     * GET auth/setup2FA
     * Shows the 2FA management page (enable / disable / QR).
     * Requires the user to be logged in.
     */
    public function setup2FA(): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];
        $user   = $this->user->findById($userId);

        $pendingSecret = null;
        $qrUrl         = null;

        // If a setup was initiated (POST to setup2FAInit), the secret is stored but not enabled
        if (!$user['totp_enabled']) {
            $pendingSecret = $this->user->getPendingTotpSecret($userId);
            if ($pendingSecret) {
                // Build QR URL server-side only; secret is NOT echoed directly into HTML
                $qrUrl = TOTP::getQRCodeUrl($user['email'] ?? '', $pendingSecret, 'BoardTrack');
            }
        }

        $role   = $_SESSION['user_role'] ?? 'tenant';
        $layout = ($role === 'landlord') ? 'landlord' : 'tenant';

        $this->view('auth/setup_2fa', [
            'pageTitle'     => 'Two-Factor Authentication Setup | BoardTrack',
            'user'          => $user,
            'has2FA'        => (bool) $user['totp_enabled'],
            'pendingSecret' => $pendingSecret,  // only passed to view for manual entry (not QR)
            'qrUrl'         => $qrUrl,          // Google Charts URL (secret inside otpauth://)
        ], $layout);
    }

    /**
     * POST auth/setup2FAInit
     * Generates a new TOTP secret and stores it (unverified) in the DB.
     * Does NOT enable 2FA yet — user must confirm with a valid code first.
     */
    public function setup2FAInit(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/setup2FA');
        }

        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int) $_SESSION['user_id'];

        // Generate and store new secret (2FA stays disabled until confirm)
        $secret = TOTP::generateSecret();
        $this->user->savePendingTotpSecret($userId, $secret);

        $this->flash('info', 'Scan the QR code with Google Authenticator, then enter the code below to confirm.');
        $this->redirect('auth/setup2FA');
    }

    /**
     * POST auth/setup2FAConfirm
     * User enters a TOTP code to confirm the QR was scanned correctly.
     * On success: 2FA is enabled and recovery codes are displayed once.
     */
    public function setup2FAConfirm(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/setup2FA');
        }

        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int) $_SESSION['user_id'];
        $code   = trim($_POST['totp_code'] ?? '');

        // Fetch the pending (unverified) secret
        $secret = $this->user->getPendingTotpSecret($userId);
        if (!$secret) {
            $this->flash('error', 'No pending 2FA setup found. Please click "Enable 2FA" first.');
            $this->redirect('auth/setup2FA');
        }

        if (!TOTP::verify($secret, $code)) {
            $this->flash('error', 'Invalid code. Please check Google Authenticator and try again.');
            $this->redirect('auth/setup2FA');
        }

        // Code is valid — generate recovery codes and enable 2FA
        $codes = TOTP::generateRecoveryCodes();
        $this->user->enableTotp($userId, $secret, $codes['hashed']);

        // Store plain-text codes in session for ONE-TIME display
        $_SESSION['2fa_recovery_codes_display'] = $codes['plain'];

        $this->redirect('auth/setup2FASuccess');
    }

    /**
     * GET auth/setup2FASuccess
     * Shows recovery codes exactly once after successful 2FA setup.
     */
    public function setup2FASuccess(): void
    {
        $this->requireAuth();

        if (empty($_SESSION['2fa_recovery_codes_display'])) {
            $this->redirect('auth/setup2FA');
        }

        $recoveryCodes = $_SESSION['2fa_recovery_codes_display'];
        unset($_SESSION['2fa_recovery_codes_display']); // show only once

        $role   = $_SESSION['user_role'] ?? 'tenant';
        $layout = ($role === 'landlord') ? 'landlord' : 'tenant';

        $this->view('auth/setup_2fa_success', [
            'pageTitle'     => '2FA Enabled | BoardTrack',
            'recoveryCodes' => $recoveryCodes,
        ], $layout);
    }

    /**
     * POST auth/disable2FA
     * Disables 2FA. Requires: current password + valid TOTP code.
     */
    public function disable2FA(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/setup2FA');
        }

        $this->requireAuth();
        $this->verifyCsrf();

        $userId   = (int) $_SESSION['user_id'];
        $password = trim($_POST['current_password'] ?? '');
        $totpCode = trim($_POST['totp_code']        ?? '');

        // 1. Verify current password
        $user = $this->user->findByEmail($_SESSION['user_email'] ?? '');
        if (!$user) {
            // findByEmail needs email — fetch by ID first
            $userRow = $this->user->findById($userId);
            $user    = $this->user->findByEmail($userRow['email'] ?? '');
        }

        if (!$user || !password_verify($password, $user['password_hash'] ?? '')) {
            $this->flash('error', 'Current password is incorrect.');
            $this->redirect('auth/setup2FA');
        }

        // 2. Verify TOTP
        $secret = $this->user->get2FASecret($userId);
        if (!$secret || !TOTP::verify($secret, $totpCode)) {
            $this->flash('error', 'Invalid authenticator code.');
            $this->redirect('auth/setup2FA');
        }

        // 3. Disable
        $this->user->disableTotp($userId);
        $this->flash('success', 'Two-factor authentication has been disabled.');
        $this->redirect('auth/setup2FA');
    }

    // Authenticated password update endpoints.

    /**
     * GET auth/changePassword
     * Shows the change-password form.
     * Requires: logged in.
     */
    public function changePassword(): void
    {
        $this->requireAuth();

        $userId  = (int) $_SESSION['user_id'];
        $user    = $this->user->findById($userId);
        $role    = $_SESSION['user_role'] ?? 'tenant';
        $layout  = ($role === 'landlord') ? 'landlord' : 'tenant';
        $has2FA  = (bool) ($user['totp_enabled'] ?? false);

        $this->view('auth/change_password', [
            'pageTitle' => 'Change Password | BoardTrack',
            'has2FA'    => $has2FA,
        ], $layout);
    }

    /**
     * POST auth/changePasswordPost
     *
     * Security requirements:
     *   1. Current password must be correct.
     *   2. If 2FA is enabled: valid TOTP code required.
     *   3. New password must be ≥ 8 chars and match confirmation.
     */
    public function changePasswordPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/changePassword');
        }

        $this->requireAuth();
        $this->verifyCsrf();

        $userId      = (int) $_SESSION['user_id'];
        $currentPw   = $_POST['current_password'] ?? '';
        $newPw       = $_POST['new_password']      ?? '';
        $confirmPw   = $_POST['confirm_password']  ?? '';
        $totpCode    = trim($_POST['totp_code']    ?? '');

        // Fetch full user row (includes password_hash + totp fields)
        $userRow = $this->user->findById($userId);
        $email   = $userRow['email'] ?? '';
        $fullUser = $this->user->findByEmail($email);

        // 1. Verify current password
        if (!$fullUser || !password_verify($currentPw, $fullUser['password_hash'] ?? '')) {
            $this->flash('error', 'Current password is incorrect.');
            $this->redirect('auth/changePassword');
        }

        // 2. If 2FA is enabled, require TOTP code
        if (!empty($fullUser['totp_enabled'])) {
            $secret = $this->user->get2FASecret($userId);
            if (!$secret || !TOTP::verify($secret, $totpCode)) {
                $this->flash('error', 'Invalid authenticator code.');
                $this->redirect('auth/changePassword');
            }
        }

        // 3. Validate new password
        if (strlen($newPw) < 8) {
            $this->flash('error', 'New password must be at least 8 characters.');
            $this->redirect('auth/changePassword');
        }

        if ($newPw !== $confirmPw) {
            $this->flash('error', 'Passwords do not match.');
            $this->redirect('auth/changePassword');
        }

        if ($newPw === $currentPw) {
            $this->flash('error', 'New password must be different from current password.');
            $this->redirect('auth/changePassword');
        }

        // 4. Update
        $hashed = password_hash($newPw, PASSWORD_BCRYPT);
        $this->user->updatePassword($userId, $hashed);

        $this->flash('success', 'Password changed successfully.');

        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/profile' : 'tenant/profile');
    }

    // Public registration endpoints.

    /** GET auth/register */
    public function register(): void
    {
        $this->guestOnly();
        $this->view('auth/register', ['pageTitle' => 'Register | BoardTrack'], 'main');
    }

    /** POST auth/registerPost */
    public function registerPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/register');
        }

        $this->verifyCsrf();

        $name            = trim($_POST['name']                 ?? '');
        $email           = trim($_POST['email']                ?? '');
        $password        = trim($_POST['password']             ?? '');
        $confirm         = trim($_POST['confirm_password']     ?? '');
        $gender          = trim($_POST['gender']               ?? '');
        $roomPreference  = trim($_POST['room_type_preference'] ?? '');
        $airConditionedPreferenceRaw = $_POST['air_conditioned_preference'] ?? null;
        $airConditionedPreference    = ($airConditionedPreferenceRaw === null || $airConditionedPreferenceRaw === '')
            ? 0
            : (int) $airConditionedPreferenceRaw;
        $guardianName    = trim($_POST['guardian_name']         ?? '');
        $guardianEmail   = trim($_POST['guardian_email']        ?? '');
        $guardianPurpose = trim($_POST['guardian_purpose']      ?? '');

        $formOld = [
            'name'             => $name,
            'email'            => $email,
            'guardian_name'    => $guardianName,
            'guardian_email'   => $guardianEmail,
            'guardian_purpose' => $guardianPurpose,
            'air_conditioned_preference' => $airConditionedPreference,
        ];

        $errors = [];
        if (empty($name))                               $errors[] = 'Full name is required.';
        if (strlen($name) < 3)                          $errors[] = 'Name must be at least 3 characters.';
        if (empty($email))                              $errors[] = 'Email is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
        if (empty($password))                           $errors[] = 'Password is required.';
        if (strlen($password) < 8)                      $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm)                     $errors[] = 'Passwords do not match.';
        if (empty($gender))                             $errors[] = 'Please select your gender.';
        if (!in_array($gender, ['male', 'female']))     $errors[] = 'Invalid gender selected.';
        if (empty($roomPreference))                     $errors[] = 'Please select a room type preference.';
        if (!in_array($airConditionedPreference, [0, 1], true)) $errors[] = 'Invalid air-conditioning preference selected.';
        if (empty($guardianName) || strlen($guardianName) < 2) {
            $errors[] = 'Guardian/emergency contact name is required (at least 2 characters).';
        }
        if (empty($guardianEmail) || !filter_var($guardianEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid guardian/emergency contact email is required.';
        }
        if (strcasecmp($guardianEmail, $email) === 0) {
            $errors[] = 'Guardian email must be different from your own email.';
        }
        if (empty($guardianPurpose) || strlen($guardianPurpose) < 10) {
            $errors[] = 'Please explain why we should contact this person (at least 10 characters).';
        }

        $idFilePath = null;
        if (isset($_FILES['government_id']) && $_FILES['government_id']['error'] === UPLOAD_ERR_OK) {
            $file    = $_FILES['government_id'];
            $allowed = ['image/jpeg', 'image/png'];
            $finfo   = new finfo(FILEINFO_MIME_TYPE);
            $mime    = $finfo->file($file['tmp_name']);
            if (!in_array($mime, $allowed))      $errors[] = 'ID must be a JPG or PNG image.';
            if ($file['size'] > UPLOAD_MAX_SIZE) $errors[] = 'ID file must be 2MB or less.';
            if (empty($errors)) {
                $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = bin2hex(random_bytes(16)) . '.' . $ext;
                $filepath = UPLOAD_IDS . '/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $idFilePath = $filename;
                } else {
                    $errors[] = 'Failed to save ID file. Please try again.';
                }
            }
        } else {
            $errors[] = 'A valid government ID image is required.';
        }

        if (!empty($errors)) {
            foreach ($errors as $e) {
                $this->flash('error', $e);
            }
            $_SESSION['form_old'] = $formOld;
            $this->redirect('auth/register');
        }

        if ($this->user->emailExists($email)) {
            $this->flash('error', 'That email address is already registered.');
            $_SESSION['form_old'] = $formOld;
            $this->redirect('auth/register');
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $userId = $this->user->createUser([
            'name'     => $name,
            'email'    => $email,
            'password' => $hashed,
            'role'     => 'tenant',
            'status'   => 'pending',
        ]);

        if (!$userId) {
            $this->flash('error', 'Registration failed. Please try again.');
            $this->redirect('auth/register');
        }

        $tenantModel = $this->model('Tenant');
        $profileId = $tenantModel->createProfile($userId, [
            'room_type_preference' => $roomPreference,
            'gender'               => $gender,
            'air_conditioned_preference' => $airConditionedPreference,
            'id_document_path'     => $idFilePath,
            'guardian_name'        => $guardianName,
            'guardian_email'       => $guardianEmail,
            'guardian_purpose'     => $guardianPurpose,
        ]);

        if (!$profileId) {
            $this->flash('error', 'Registration failed while creating your profile. Please try again.');
            $this->redirect('auth/register');
        }

        require_once ROOT_PATH . '/app/helpers/BoardTrackMail.php';
        
        // Create email verification token
        $verifyModel = $this->model('EmailVerification');
        $token = $verifyModel->createToken($userId);
        
        BoardTrackMail::verificationEmail($email, $name, $token);
        BoardTrackMail::registrationReceived($email, $name);
        BoardTrackMail::guardianRegistrationNotice($guardianEmail, $guardianName, $name, $guardianPurpose);

        $auditLog = $this->model('AuditLog');
        $auditLog->log(null, 'tenant_registered', 'user', $userId, null,
            ['email' => $email, 'guardian_email' => $guardianEmail], 'New tenant registration submitted');

        $this->flash('success', 'Account created! Please check your email to verify your address before signing in.');
        $this->redirect('auth/login');
    }

    // Email verification endpoints.

    public function verify(string $token = ''): void
    {
        if (empty($token)) {
            $this->flash('error', 'Invalid verification link.');
            $this->redirect('auth/login');
        }

        $verifyModel = $this->model('EmailVerification');
        $userId = $verifyModel->verifyToken($token);

        if (!$userId) {
            $this->flash('error', 'Verification link is invalid or has expired.');
            $this->redirect('auth/login');
        }

        $this->user->updateStatus($userId, 'pending');
        
        $user = $this->user->findById($userId);
        $this->flash('success', 'Email verified successfully! Your account is now pending landlord approval.');
        $this->redirect('auth/login');
    }

    // Password recovery endpoints.

    public function forgotPassword(): void
    {
        $this->guestOnly();
        $this->view('auth/forgot-password', ['pageTitle' => 'Forgot Password | BoardTrack'], 'main');
    }

    public function forgotPasswordPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/forgotPassword');
        }

        $this->verifyCsrf();

        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            $this->flash('error', 'Email is required.');
            $this->redirect('auth/forgotPassword');
        }

        $user = $this->user->findByEmail($email);
        if ($user) {
            $resetModel = $this->model('PasswordReset');
            $token = $resetModel->createToken((int)$user['id']);
            
            require_once ROOT_PATH . '/app/helpers/BoardTrackMail.php';
            BoardTrackMail::passwordReset($user['email'], $user['name'], $token);
        }

        // Always show success message for security
        $this->flash('success', 'If an account exists with that email, a password reset link has been sent.');
        $this->redirect('auth/login');
    }

    public function resetPassword(string $token = ''): void
    {
        if (empty($token)) {
            $this->flash('error', 'Invalid reset link.');
            $this->redirect('auth/login');
        }

        $resetModel = $this->model('PasswordReset');
        $userId = $resetModel->verifyToken($token);

        if (!$userId) {
            $this->flash('error', 'Reset link is invalid or has expired.');
            $this->redirect('auth/login');
        }

        $this->view('auth/reset-password', [
            'pageTitle' => 'Reset Password | BoardTrack',
            'token' => $token
        ], 'main');
    }

    public function resetPasswordPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/login');
        }

        $this->verifyCsrf();

        $token     = $_POST['token']            ?? '';
        $newPw     = $_POST['new_password']      ?? '';
        $confirmPw = $_POST['confirm_password']  ?? '';

        $resetModel = $this->model('PasswordReset');
        $userId = $resetModel->verifyToken($token);

        if (!$userId) {
            $this->flash('error', 'Reset session expired. Please start over.');
            $this->redirect('auth/forgotPassword');
        }

        if (strlen($newPw) < 8) {
            $this->flash('error', 'Password must be at least 8 characters.');
            $this->redirect('auth/resetPassword/' . $token);
        }

        if ($newPw !== $confirmPw) {
            $this->flash('error', 'Passwords do not match.');
            $this->redirect('auth/resetPassword/' . $token);
        }

        $hashed = password_hash($newPw, PASSWORD_BCRYPT);
        $this->user->updatePassword($userId, $hashed);
        $resetModel->markAsUsed($token);

        $this->flash('success', 'Password reset successfully. You can now log in.');
        $this->redirect('auth/login');
    }

    // Logout endpoint.

    /** GET auth/logout */
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        $this->redirect('auth/login');
    }

    // Legacy email OTP endpoints retained for backward compatibility.
    // These methods are intentionally preserved but no longer called.
    // The login flow now uses Google Authenticator (TOTP) exclusively.
    // Remove in Prompt 3 once 2FA is stable.

    /**
     * @deprecated  Use totpVerify() instead.
     *              Email OTP replaced by Google Authenticator.
     */
    public function otpVerify(): void
    {
        // Redirect pending sessions to the active TOTP flow.
        if (isset($_SESSION['2fa_pending_user'])) {
            $this->redirect('auth/totpVerify');
        }
        $this->redirect('auth/login');
    }

    /**
     * @deprecated  Use totpVerifyPost() instead.
     */
    public function otpVerifyPost(): void
    {
        $this->redirect('auth/login');
    }

    /**
     * @deprecated  Email OTP resend no longer used.
     */
    public function otpResend(): void
    {
        $this->redirect('auth/login');
    }

    /*
     * Legacy OTP session cleanup for historical compatibility.
     *
     * // Generate OTP and send via email
     * $otpCode = sprintf('%06d', random_int(0, 999999));
     * $_SESSION['pending_user'] = $user;
     * $_SESSION['otp_code']     = $otpCode;
     * $_SESSION['otp_expiry']   = time() + 300;
     * ... Mailer::send(...) ...
     * $this->redirect('auth/otpVerify');
     */

    // Private helpers.

    /**
     * Complete login: regenerate session, set session variables, redirect.
     * Called directly when 2FA is not enabled.
     */
    private function completeLogin(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['user_email'] = $user['email'];

        if ($user['role'] === 'landlord') {
            $this->redirect('landlord/dashboard');
        } else {
            $this->redirect('tenant/dashboard');
        }
    }

    /**
     * Finalize login after successful TOTP / recovery-code verification.
     * Clears 2FA pending session, regenerates session ID, sets auth session.
     */
    private function finalize2FALogin(array $user): void
    {
        unset($_SESSION['2fa_pending_user'], $_SESSION['2fa_pending_expires']);
        $this->completeLogin($user);
    }

    /**
     * Guard: ensure a valid 2FA-pending session exists.
     * Redirects to login if missing or expired.
     */
    private function guard2FAPending(): void
    {
        if (
            empty($_SESSION['2fa_pending_user']) ||
            empty($_SESSION['2fa_pending_expires']) ||
            time() > $_SESSION['2fa_pending_expires']
        ) {
            unset($_SESSION['2fa_pending_user'], $_SESSION['2fa_pending_expires']);
            $this->flash('error', 'Session expired. Please log in again.');
            $this->redirect('auth/login');
        }
    }
}