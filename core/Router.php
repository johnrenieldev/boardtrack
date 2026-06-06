<?php

class Router
{
    /**
     * Generate a full URL for a controller/method path.
     *
     * @param string $path  e.g. 'auth/login', 'tenant/bills'
     * @return string       Full URL
     */
    public static function url(string $path = ''): string
    {
        $base = rtrim(BASE_URL, '/');
        $path = ltrim($path, '/');
        $query = '';

        if (str_contains($path, '?')) {
            [$path, $query] = explode('?', $path, 2);
            $path = trim($path, '/');
        }

        if (empty($path)) {
            return $query !== '' ? $base . '/index.php?' . $query : $base . '/';
        }

        // Build as query string format: index.php?url=controller/method
        // .htaccess rewrites clean URLs to this format internally
        return $base . '/index.php?url=' . $path . ($query !== '' ? '&' . $query : '');
    }

    /**
     * Generate a URL to a public asset.
     *
     * @param string $path  Relative to public/assets/, e.g. 'css/landing.css'
     * @return string
     */
    public static function asset(string $path): string
    {
        $base = rtrim(BASE_URL, '/');
        $path = ltrim($path, '/');
        return $base . '/assets/' . $path;
    }

    /**
     * Generate a URL for an uploaded file.
     *
     * @param string $subdir  'ids' | 'payments' | 'gcash'
     * @param string $filename
     * @return string
     */
    public static function upload(string $subdir, string $filename): string
    {
        $base = rtrim(BASE_URL, '/');
        return $base . '/uploads/' . $subdir . '/' . $filename;
    }

    /**
     * Redirect helper — outputs a Location header.
     * Can be used from anywhere, not just controllers.
     *
     * @param string $path
     */
    public static function redirect(string $path): void
    {
        header('Location: ' . self::url($path));
        exit;
    }

    /**
     * Returns the current active URL segment (for active nav link detection).
     *
     * @return string  e.g. 'tenant/dashboard'
     */
    public static function current(): string
    {
        return $_GET['url'] ?? '';
    }

    /**
     * Check if the current URL starts with the given path.
     * Useful for adding 'active' class to nav links.
     *
     * @param string $path
     * @return bool
     */
    public static function isActive(string $path): bool
    {
        return str_starts_with(self::current(), ltrim($path, '/'));
    }
}
