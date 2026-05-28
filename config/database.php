<?php
/**
 * BoardTrack — Database Connection
 * config/database.php
 *
 * Returns a singleton PDO instance.
 * Used by all Model classes via the base Model.
 */

class Database
{
    private static ?PDO $instance = null;

    /**
     * Private constructor — no direct instantiation.
     */
    private function __construct() {}

    /**
     * Returns the singleton PDO connection.
     * Creates it on the first call.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }
        return self::$instance;
    }

    /**
     * Builds and returns a new PDO connection.
     */
    private static function createConnection(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            return $pdo;
        } catch (PDOException $e) {
            // Log the real error; show a generic message to users
            self::logError($e->getMessage());

            // Force display error for debugging
            die('<pre style="color:red;padding:20px;">
<strong>Database Connection Failed</strong>
' . htmlspecialchars($e->getMessage()) . '

<strong>Check:</strong>
  1. XAMPP MySQL is running
  2. DB_NAME "' . DB_NAME . '" exists in phpMyAdmin
  3. DB_USER / DB_PASS in config/config.php are correct
</pre>');
        }
    }

    /**
     * Writes connection errors to the log file.
     */
    private static function logError(string $message): void
    {
        $logFile = LOG_PATH . '/system.log';
        $entry   = '[' . date('Y-m-d H:i:s') . '] DB ERROR: ' . $message . PHP_EOL;

        if (is_dir(LOG_PATH) && is_writable(LOG_PATH)) {
            file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Prevent cloning the singleton.
     */
    private function __clone() {}
}