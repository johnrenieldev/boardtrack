<?php
/**
 * BoardTrack feature audit — run: php database/test_features_audit.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/config/config.php';
require_once $root . '/config/database.php';
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/Model.php';
require_once APP_PATH . '/model/Tenant.php';
require_once APP_PATH . '/model/User.php';
require_once APP_PATH . '/model/Notification.php';
require_once APP_PATH . '/model/Payment.php';
require_once APP_PATH . '/model/Bill.php';
require_once APP_PATH . '/helpers/EmailTemplates.php';
require_once APP_PATH . '/helpers/BoardtrackMail.php';

$passed = 0;
$failed = 0;
$warnings = 0;

function ok(string $msg): void
{
    global $passed;
    $passed++;
    echo "[PASS] {$msg}\n";
}

function fail(string $msg): void
{
    global $failed;
    $failed++;
    echo "[FAIL] {$msg}\n";
}

function warn(string $msg): void
{
    global $warnings;
    $warnings++;
    echo "[WARN] {$msg}\n";
}

echo "=== BoardTrack Feature Audit ===\n\n";

// 1. Database columns
$db = Database::getInstance();
$requiredCols = ['guardian_name', 'guardian_email', 'guardian_purpose', 'room_type_preference', 'id_document_path'];
$stmt = $db->prepare(
    "SELECT COLUMN_NAME FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = 'tenants'"
);
$stmt->execute([':schema' => DB_NAME]);
$cols = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'COLUMN_NAME');

foreach ($requiredCols as $col) {
    if (in_array($col, $cols, true)) {
        ok("DB column tenants.{$col} exists");
    } else {
        fail("DB column tenants.{$col} MISSING — run php database/migrate_guardian_fields.php");
    }
}

// 2. Email templates render
try {
    $html = EmailTemplates::registrationReceived('Test Tenant');
    if (strlen($html) > 200 && str_contains($html, 'Test Tenant')) {
        ok('EmailTemplates::registrationReceived renders');
    } else {
        fail('EmailTemplates::registrationReceived output invalid');
    }
    $html2 = EmailTemplates::guardianPaymentApproved('Tenant', 'Rent', 1500.0, 'Emergency contact');
    if (str_contains($html2, '₱1,500.00') || str_contains($html2, '1500')) {
        ok('EmailTemplates::guardianPaymentApproved renders');
    } else {
        fail('EmailTemplates::guardianPaymentApproved output invalid');
    }
} catch (Throwable $e) {
    fail('EmailTemplates threw: ' . $e->getMessage());
}

// 3. BoardtrackMail enabled check
if (defined('MAIL_ENABLED') && MAIL_ENABLED) {
    ok('MAIL_ENABLED is true');
} else {
    warn('MAIL_ENABLED is false — emails will be skipped in production flow');
}

if (defined('MAIL_HOST') && MAIL_USERNAME && MAIL_PASSWORD) {
    ok('SMTP credentials configured in config/mail.php');
} else {
    warn('SMTP credentials incomplete');
}

// 4. Sample tenant guardian data (latest tenant)
$tenantModel = new Tenant();
$stmt = $db->query(
    "SELECT t.id, t.guardian_name, t.guardian_email, t.guardian_purpose, u.email AS user_email
     FROM tenants t JOIN users u ON t.user_id = u.id
     ORDER BY t.id DESC LIMIT 5"
);
$recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($recent)) {
    warn('No tenants in database — registration flow not yet tested with real data');
} else {
    $withGuardian = 0;
    foreach ($recent as $row) {
        if (!empty($row['guardian_email']) && !empty($row['guardian_name'])) {
            $withGuardian++;
        }
    }
    if ($withGuardian > 0) {
        ok("{$withGuardian} recent tenant(s) have guardian name + email saved");
    } else {
        warn('Recent tenants lack guardian fields — register a new account to verify');
    }
}

// 5. Controller methods exist
$checks = [
    [APP_PATH . '/controllers/AuthController.php', 'registerPost'],
    [APP_PATH . '/controllers/LandlordController.php', 'approveTenant'],
    [APP_PATH . '/controllers/LandlordController.php', 'approvePayment'],
    [APP_PATH . '/controllers/LandlordController.php', 'markNotificationRead'],
    [APP_PATH . '/controllers/TenantController.php', 'markNotificationRead'],
    [APP_PATH . '/helpers/BoardtrackMail.php', 'tenantPaymentApproved'],
];
foreach ($checks as [$file, $needle]) {
    $content = file_get_contents($file);
    if ($content !== false && str_contains($content, "function {$needle}") || str_contains($content, "static function {$needle}")) {
        ok("{$needle} present in " . basename($file));
    } else {
        fail("{$needle} missing in " . basename($file));
    }
}

// 6. View files
$views = [
    'app/views/auth/register.php' => 'guardian_name',
    'app/views/landlord/partials/payment_modals.php' => 'approvePaymentModal',
    'app/views/tenant/notifications.php' => 'data-notif-id',
    'public/assets/js/notifications.js' => 'markReadRequest',
];
foreach ($views as $path => $needle) {
    $full = $root . '/' . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (is_file($full) && str_contains((string) file_get_contents($full), $needle)) {
        ok("View/asset {$path} contains {$needle}");
    } else {
        fail("View/asset {$path} missing {$needle}");
    }
}

// 7. Layout injects notification URLs
$layout = file_get_contents(APP_PATH . '/views/layouts/tenant.php');
if ($layout && str_contains($layout, 'notifications.js') && str_contains($layout, 'data-mark-notif-read-url')) {
    ok('Tenant layout loads notifications.js + data attributes');
} else {
    fail('Tenant layout missing notification wiring');
}

// 8. Dry-run mail (optional — set TEST_SEND_MAIL=1 to actually send)
if (getenv('TEST_SEND_MAIL') === '1' && BoardtrackMail::isEnabled()) {
    $testTo = getenv('TEST_MAIL_TO') ?: MAIL_USERNAME;
    $sent = BoardtrackMail::registrationReceived($testTo, 'Audit Test User');
    if ($sent) {
        ok("Live mail sent to {$testTo}");
    } else {
        fail("Live mail failed to {$testTo} — check logs");
    }
} else {
    warn('Skipping live SMTP send (set TEST_SEND_MAIL=1 TEST_MAIL_TO=you@email.com to test)');
}

echo "\n=== Summary: {$passed} passed, {$failed} failed, {$warnings} warnings ===\n";
exit($failed > 0 ? 1 : 0);
