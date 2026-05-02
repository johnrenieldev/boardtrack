<?php

class Controller
{
    // ── View loading ───────────────────────────────────────────
    protected function view(string $view, array $data = [], ?string $layout = 'main'): void
    {
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

    // ── Model loading ──────────────────────────────────────────
    // FIX: folder is /app/model/ (not /models/)
    protected function model(string $name): object
    {
        $file = APP_PATH . '/model/' . $name . '.php';
        if (!file_exists($file)) {
            $this->abort(500, "Model not found: {$name}");
        }
        require_once $file;
        if (!class_exists($name)) {
            $this->abort(500, "Model class {$name} not found");
        }
        return new $name();
    }

    // ── Redirect ───────────────────────────────────────────────
    protected function redirect(string $path = ''): void
    {
        header('Location: ' . rtrim(BASE_URL, '/') . '/' . ltrim($path, '/'));
        exit;
    }

    // ── Auth guards ────────────────────────────────────────────
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
            $role = $_SESSION['user_role'] ?? 'tenant';
            $this->redirect($role === 'landlord' ? 'landlord/dashboard' : 'tenant/dashboard');
        }
    }

    // ── Flash messages ─────────────────────────────────────────
    // FIX: always stores as array with type+message keys
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    // ── JSON response ──────────────────────────────────────────
    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // ── Error page ─────────────────────────────────────────────
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