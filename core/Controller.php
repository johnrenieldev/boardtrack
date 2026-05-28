<?php

class Controller
{
    // Render view with optional layout wrapper.
    protected function view(string $view, array $data = [], ?string $layout = 'main'): void
    {
        if (in_array($layout, ['landlord', 'tenant'], true) && !empty($_SESSION['user_id'])) {
            $notifModel = $this->model('Notification');
            $userModel  = $this->model('User');
            $userId     = (int) $_SESSION['user_id'];
            $role       = $_SESSION['user_role'] ?? 'tenant';
            $prefix     = $role === 'landlord' ? 'landlord' : 'tenant';

            $user = $userModel->findById($userId);
            $hasUnread = $notifModel->hasUnread($userId);

            $data['hasUnreadNotifications']   = $hasUnread;
            $data['markNotificationReadUrl']  = "{$prefix}/markNotificationRead";
            $data['userStatus']               = $user['status'] ?? 'pending';
        }

        if (!empty($data)) extract($data, EXTR_SKIP);

        $viewFile = APP_PATH . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            $this->abort(500, "View not found: {$view}");
        }

        if ($layout === null) {
            require $viewFile;
            return;
        }

        $layoutFile = APP_PATH . '/views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            $this->abort(500, "Layout not found: {$layout}");
        }

        $__viewFile = $viewFile;
        require $layoutFile;
    }

    // Instantiate model class by conventional filename.
    protected function model(string $name): object
    {
        $file = APP_PATH . '/models/' . $name . '.php';
        if (!file_exists($file)) {
            $this->abort(500, "Model not found: {$name}");
        }
        require_once $file;
        if (!class_exists($name)) {
            $this->abort(500, "Model class {$name} not found");
        }
        return new $name();
    }

    // Redirect response and terminate execution.
    protected function redirect(string $path = ''): void
    {
        header('Location: ' . Router::url($path));
        exit;
    }

    // CSRF token utilities.
    protected function csrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                $this->abort(403, 'Invalid CSRF token. Please refresh the page and try again.');
            }
        }
    }

    // Authentication and authorization guards.
    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->flash('error', 'Please log in to continue.');
            $this->redirect('auth/login');
        }
    }

    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? '') !== $role) {
            $this->flash('error', 'You do not have permission to access this page.');
            $this->redirect('auth/login');
        }
    }

    protected function guestOnly(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect($this->roleDashboardPath());
        }
    }

    /** Redirect when session is invalid without forcing logout cleanup flow. */
    protected function invalidSession(string $message = 'Please log in again.'): void
    {
        $this->flash('error', $message);
        $this->redirect('auth/login');
    }

    protected function roleDashboardPath(): string
    {
        return ($_SESSION['user_role'] ?? '') === 'landlord' ? 'landlord/dashboard' : 'tenant/dashboard';
    }

    /**
     * Require logged-in tenant with a tenants row.
     * @return array Tenant profile joined with user fields
     */
    protected function requireTenantProfile(): array
    {
        $this->requireRole('tenant');
        $tenant = $this->model('Tenant')->findByUserId((int) $_SESSION['user_id']);
        if (!$tenant) {
            $this->invalidSession('Your tenant profile is incomplete. Please contact the landlord or register again.');
        }
        return $tenant;
    }

    /**
     * Require approved tenant with a room assignment.
     */
    protected function requireApprovedTenant(): array
    {
        $tenant = $this->requireTenantProfile();
        
        // Strictly check for approved status and room assignment
        if (($tenant['user_status'] ?? '') !== 'approved' || empty($tenant['room_id'])) {
            $this->flash('error', 'Your account is currently under review by the landlord. Access will be available once approved.');
            $this->redirect('tenant/dashboard');
        }
        
        return $tenant;
    }

    /**
     * Validate uploaded image (JPG/PNG, max size).
     * @return array{ok: bool, error?: string, ext?: string}
     */
    protected function validateImageUpload(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Please select a valid file to upload.'];
        }
        if (!in_array($file['type'] ?? '', UPLOAD_ALLOWED, true)) {
            return ['ok' => false, 'error' => 'Only JPG and PNG images are allowed.'];
        }
        if (($file['size'] ?? 0) > UPLOAD_MAX_SIZE) {
            return ['ok' => false, 'error' => 'File must be smaller than 2MB.'];
        }
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, UPLOAD_ALLOWED_EXT, true)) {
            $ext = ($file['type'] ?? '') === 'image/png' ? 'png' : 'jpg';
        }
        return ['ok' => true, 'ext' => $ext];
    }

    protected function storeUploadedImage(array $file, string $directory, string $prefix = ''): ?string
    {
        $check = $this->validateImageUpload($file);
        if (!$check['ok']) {
            return null;
        }
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $filename = ($prefix ?: 'file_') . bin2hex(random_bytes(16)) . '.' . $check['ext'];
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            return null;
        }
        return $filename;
    }

    // Queue flash message for next request.
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    // Return JSON response and terminate execution.
    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json')
            || strcasecmp($xhr, 'XMLHttpRequest') === 0;
    }

    // Render fallback error response.
    protected function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        $titles = [404 => 'Not Found', 403 => 'Forbidden', 500 => 'Server Error'];
        echo '<!DOCTYPE html><html><head><title>' . $code . '</title>
              <style>body{font-family:sans-serif;text-align:center;padding:80px;background:#f9fafb}
              h1{font-size:3rem;color:#2563eb}h2{color:#374151}p{color:#6b7280}</style>
              </head><body>
              <h1>' . $code . '</h1><h2>' . htmlspecialchars($titles[$code] ?? 'Error') . '</h2>
              <p>' . htmlspecialchars($message) . '</p>
              <a href="' . BASE_URL . '">← Back to home</a>
              </body></html>';
        exit;
    }
}