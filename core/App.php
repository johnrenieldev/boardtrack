<?php
/**
 * BoardTrack — Application Router
 * core/App.php
 *
 * Parses the URL, loads the correct controller,
 * and calls the correct method with optional parameters.
 *
 * URL format: index.php?url=controller/method/param
 * Example:    index.php?url=auth/login
 *             index.php?url=tenant/bills/12
 *
 * If no URL is given, defaults to HomeController::index().
 */

class App
{
    protected string $controllerName = DEFAULT_CONTROLLER;
    protected string $method         = DEFAULT_METHOD;
    protected array  $params         = [];

    /** @var Controller */
    protected object $controller;

    public function __construct()
    {
        $this->parseUrl();
        $this->loadController();
        $this->dispatch();
    }

    // URL PARSING

    /**
     * Reads ?url= query string, sanitizes, and splits into parts.
     * Populates $controllerName, $method, and $params.
     */
    private function parseUrl(): void
    {
        if (!isset($_GET['url'])) {
            return;
        }

        // Sanitize: only allow letters, digits, hyphens, slashes
        $url = filter_var(
            trim($_GET['url'], '/'),
            FILTER_SANITIZE_URL
        );

        $segments = explode('/', $url);

        // Segment 0 → controller name (PascalCase it)
        if (!empty($segments[0])) {
            $this->controllerName = $this->toPascalCase($segments[0]);
        }

        // Segment 1 → method name (camelCase it)
        if (!empty($segments[1])) {
            $this->method = $this->toCamelCase($segments[1]);
        }

        // Segment 2+ → parameters
        if (!empty($segments[2])) {
            $this->params = array_slice($segments, 2);
        }
    }

    // CONTROLLER LOADING

    /**
     * Resolves the controller file path, requires it,
     * and instantiates the controller class.
     *
     * Falls back to HomeController if the target is not found.
     */
    private function loadController(): void
    {
        $file = APP_PATH . '/controllers/' . $this->controllerName . 'Controller.php';

        if (file_exists($file)) {
            require_once $file;
            $class = $this->controllerName . 'Controller';

            if (class_exists($class)) {
                $this->controller = new $class();
                return;
            }
        }

        // Controller not found — log it and load 404
        $this->logMissing('Controller', $this->controllerName);
        $this->load404();
    }

    // DISPATCHING

    /**
     * Calls the requested method on the loaded controller,
     * passing any URL parameters as arguments.
     */
    private function dispatch(): void
    {
        if (!method_exists($this->controller, $this->method)) {
            $this->logMissing('Method', $this->method);
            $this->load404();
            return;
        }

        call_user_func_array(
            [$this->controller, $this->method],
            $this->params
        );
    }

    // HELPERS

    /**
     * Converts a URL segment like "auth-login" or "authlogin"
     * to "Auth" (PascalCase) for controller class names.
     */
    private function toPascalCase(string $segment): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $segment)));
    }

    /**
     * Converts a URL segment like "show-bills" to "showBills" (camelCase)
     * for method names.
     */
    private function toCamelCase(string $segment): string
    {
        $pascal = $this->toPascalCase($segment);
        return lcfirst($pascal);
    }

    /**
     * Loads the HomeController and calls its notFound() method,
     * or echoes a raw fallback if that also fails.
     */
    private function load404(): void
    {
        $file = APP_PATH . '/controllers/HomeController.php';

        if (file_exists($file)) {
            require_once $file;
            if (class_exists('HomeController') && method_exists('HomeController', 'notFound')) {
                $this->controller = new HomeController();
                $this->controller->notFound();
                exit;
            }
        }

        http_response_code(404);
        echo '<h1 style="font-family:sans-serif;text-align:center;margin-top:80px;">
                404 — Page Not Found
              </h1>';
        exit;
    }

    /**
     * Appends a warning to the system log.
     */
    private function logMissing(string $type, string $name): void
    {
        $logFile = LOG_PATH . '/system.log';
        $entry   = '[' . date('Y-m-d H:i:s') . "] ROUTER: {$type} '{$name}' not found"
                 . ' | URL: ' . ($_GET['url'] ?? 'none')
                 . ' | IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown')
                 . PHP_EOL;

        if (is_dir(LOG_PATH) && is_writable(LOG_PATH)) {
            file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
        }
    }
}