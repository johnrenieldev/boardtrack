<?php
/**
 * Integration tests — php database/test_integration.php
 * Optional: TEST_SEND_MAIL=1 php database/test_integration.php
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
require_once APP_PATH . '/model/Notification.php';
require_once APP_PATH . '/helpers/BoardtrackMail.php';

echo "=== Integration Tests ===\n\n";

$db = Database::getInstance();
$errors = 0;

function assertTrue(bool $cond, string $msg): void
{
    global $errors;
    if ($cond) {
        echo "[PASS] {$msg}\n";
    } else {
        echo "[FAIL] {$msg}\n";
        $errors++;
    }
}

// --- Test 1: Tenant profile insert with guardian (rolled back) ---
$db->beginTransaction();
try {
    $userModel = new User();
    $testEmail = 'audit_' . bin2hex(random_bytes(4)) . '@boardtrack-test.local';
    $uid = $userModel->createUser([
        'name'     => 'Audit Test User',
        'email'    => $testEmail,
        'password' => password_hash('TestPass123!', PASSWORD_BCRYPT),
        'role'     => 'tenant',
        'status'   => 'pending',
    ]);
    assertTrue($uid > 0, 'Test user insert');

    $tenantModel = new Tenant();
    $tid = $tenantModel->createProfile((int) $uid, [
        'room_type_preference' => 'single',
        'id_document_path'     => 'test_id.png',
        'guardian_name'        => 'Audit Guardian',
        'guardian_email'       => 'guardian_' . bin2hex(random_bytes(4)) . '@boardtrack-test.local',
        'guardian_purpose'     => 'Emergency contact and payment notifications for audit test.',
    ]);
    assertTrue($tid > 0, 'createProfile with guardian fields');

    $row = $tenantModel->find((int) $tid);
    assertTrue(
        ($row['guardian_name'] ?? '') === 'Audit Guardian'
        && !empty($row['guardian_email'])
        && str_contains($row['guardian_purpose'] ?? '', 'payment'),
        'Guardian fields readable from tenants.find()'
    );

    $db->rollBack();
    echo "[INFO] Test user/tenant rolled back (no junk data)\n";
} catch (Throwable $e) {
    $db->rollBack();
    assertTrue(false, 'Guardian insert test: ' . $e->getMessage());
}

// --- Test 2: Notification mark-read + unread count ---
$db->beginTransaction();
try {
    $landlord = $db->query("SELECT id FROM users WHERE role = 'landlord' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    assertTrue(!empty($landlord), 'Landlord user exists for notification test');

    if ($landlord) {
        $notif = new Notification();
        $nid = $notif->createNotification(
            (int) $landlord['id'],
            'general',
            'Audit Notification',
            'Test message for mark-read',
            'landlord/dashboard'
        );
        assertTrue($nid > 0, 'createNotification');

        $before = $notif->getUnreadCount((int) $landlord['id']);
        $notif->markRead($nid, (int) $landlord['id']);
        $after = $notif->getUnreadCount((int) $landlord['id']);
        assertTrue($before >= $after && $after === $before - 1, 'markRead decreases unread count');
    }
    $db->rollBack();
} catch (Throwable $e) {
    $db->rollBack();
    assertTrue(false, 'Notification test: ' . $e->getMessage());
}

// --- Test 3: BoardtrackMail static send (optional live) ---
if (getenv('TEST_SEND_MAIL') === '1') {
    $to = getenv('TEST_MAIL_TO') ?: MAIL_USERNAME;
    $r1 = BoardtrackMail::registrationReceived($to, 'Integration Test');
    $r2 = BoardtrackMail::tenantPaymentApproved($to, 'Integration Test', 'Monthly Rent', 2500.0, 'GCash');
    assertTrue($r1, "SMTP registrationReceived to {$to}");
    assertTrue($r2, "SMTP tenantPaymentApproved to {$to}");
} else {
    echo "[SKIP] Live SMTP (set TEST_SEND_MAIL=1)\n";
}

// --- Test 4: HTTP smoke (register page has guardian fields) ---
$base = rtrim(BASE_URL, '/');
$registerUrl = $base . '/index.php?url=auth/register';
$ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
$html = @file_get_contents($registerUrl, false, $ctx);
if ($html !== false) {
    assertTrue(str_contains($html, 'guardian_name'), 'HTTP register page has guardian_name field');
    assertTrue(str_contains($html, 'guardian_purpose'), 'HTTP register page has guardian_purpose field');
} else {
    echo "[WARN] Could not fetch {$registerUrl} — is XAMPP/Apache running?\n";
}

echo "\n=== Integration result: " . ($errors === 0 ? 'ALL PASSED' : "{$errors} FAILED") . " ===\n";
exit($errors > 0 ? 1 : 0);
