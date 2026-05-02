<?php
/**
 * BoardTrack — Auth Controller
 * app/controllers/AuthController.php
 *
 * FIXED:
 *  - loginPost() now checks $user['password_hash'] (matches DB column)
 *  - session_regenerate_id() re-enabled
 *  - Flash messages use $this->flash() consistently
 *  - Rejected users cannot log in
 */

class AuthController extends Controller
{
    private object $user;

    public function __construct()
    {
        $this->user = $this->model('User');
    }

    // ── GET: Show login form ───────────────────────────────────
    public function login(): void
    {
        $this->guestOnly();
        $this->view('auth/login', ['pageTitle' => 'Sign In — BoardTrack'], 'main');
    }

    // ── GET: Show register form ────────────────────────────────
    public function register(): void
    {
        $this->guestOnly();
        $this->view('auth/register', ['pageTitle' => 'Register — BoardTrack'], 'main');
    }

    // ── POST: Handle login ─────────────────────────────────────
    public function loginPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/login');
        }

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $roleHint = $_POST['role_hint'] ?? 'tenant';

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Email and password are required.');
            $this->redirect('auth/login');
        }

        $user = $this->user->findByEmail($email);

        // FIX: DB column is password_hash — check it directly
        if (!$user || !password_verify($password, $user['password_hash'] ?? '')) {
            $this->flash('error', 'Incorrect email or password.');
            $this->redirect('auth/login');
        }

        if ($user['role'] !== $roleHint) {
            $this->flash('error', 'Account not found for the selected role.');
            $this->redirect('auth/login');
        }

        if ($user['status'] === 'rejected') {
            $this->flash('error', 'Your registration was rejected. Please contact the landlord for details.');
            $this->redirect('auth/login');
        }

        // FIX: re-enabled — prevents session fixation attacks
        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'landlord') {
            $this->redirect('landlord/dashboard');
        } else {
            $this->redirect('tenant/dashboard');
        }
    }

    // ── POST: Handle registration ──────────────────────────────
    public function registerPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/register');
        }

        $name           = trim($_POST['name']                ?? '');
        $email          = trim($_POST['email']               ?? '');
        $password       = trim($_POST['password']            ?? '');
        $confirm        = trim($_POST['confirm_password']    ?? '');
        $roomPreference = trim($_POST['room_type_preference'] ?? '');

        // Validation
        $errors = [];
        if (empty($name))                               $errors[] = 'Full name is required.';
        if (strlen($name) < 3)                          $errors[] = 'Name must be at least 3 characters.';
        if (empty($email))                              $errors[] = 'Email is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
        if (empty($password))                           $errors[] = 'Password is required.';
        if (strlen($password) < 8)                      $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm)                     $errors[] = 'Passwords do not match.';
        if (empty($roomPreference))                     $errors[] = 'Please select a room type preference.';

        // File upload
        $idFilePath = null;
        if (isset($_FILES['government_id']) && $_FILES['government_id']['error'] === UPLOAD_ERR_OK) {
            $file    = $_FILES['government_id'];
            $allowed = ['image/jpeg', 'image/png'];
            $finfo   = new finfo(FILEINFO_MIME_TYPE);
            $mime    = $finfo->file($file['tmp_name']);
            if (!in_array($mime, $allowed))      $errors[] = 'ID must be a JPG or PNG image.';
            if ($file['size'] > UPLOAD_MAX_SIZE) $errors[] = 'ID file must be 2MB or less.';
            if (empty($errors)) {
                $ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename   = bin2hex(random_bytes(16)) . '.' . $ext;
                $filepath   = UPLOAD_IDS . '/' . $filename;
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
            foreach ($errors as $e) $this->flash('error', $e);
            $_SESSION['form_old'] = ['name' => $name, 'email' => $email];
            $this->redirect('auth/register');
        }

        if ($this->user->emailExists($email)) {
            $this->flash('error', 'That email address is already registered.');
            $_SESSION['form_old'] = ['name' => $name, 'email' => $email];
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

        // Create tenant profile
        $tenantModel = $this->model('Tenant');
        $tenantModel->createProfile($userId, [
            'room_type_preference' => $roomPreference,
            'id_document_path'     => $idFilePath,
        ]);

        // Audit log
        $auditLog = $this->model('AuditLog');
        $auditLog->log(null, 'tenant_registered', 'user', $userId, null,
            ['email' => $email], 'New tenant registration submitted');

        // Auto-login and redirect to personality quiz
        session_regenerate_id(true);
        $_SESSION['user_id']   = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = 'tenant';

        $this->flash('success', 'Account created! Please complete the personality quiz to continue.');
        $this->redirect('tenant/personality');
    }

    // ── GET: Logout ────────────────────────────────────────────
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
}