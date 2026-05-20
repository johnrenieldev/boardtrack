<?php
/**
 * Verify payment approval prerequisites in DB
 * php database/test_payment_flow.php
 */
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/Model.php';
require_once APP_PATH . '/model/Payment.php';
require_once APP_PATH . '/model/Tenant.php';
require_once APP_PATH . '/model/User.php';

$db = Database::getInstance();
$paymentModel = new Payment();
$tenantModel = new Tenant();
$userModel = new User();

$pending = $paymentModel->getPending();
echo 'Pending payments: ' . count($pending) . "\n";

if (!empty($pending)) {
    $p = $pending[0];
    $tenant = $tenantModel->find((int) $p['tenant_id']);
    $user = $tenant ? $userModel->find((int) $tenant['user_id']) : null;
    echo "Sample payment #{$p['id']} tenant={$p['tenant_name']} bill={$p['bill_name']}\n";
    echo '  Tenant email: ' . ($user['email'] ?? 'N/A') . "\n";
    echo '  Guardian email: ' . ($tenant['guardian_email'] ?? 'NONE — old tenant without guardian') . "\n";
    echo '  Approve URL: ' . Router::url('landlord/approve-payment') . " (POST payment_id={$p['id']})\n";
} else {
    echo "No pending payments — create a bill and tenant payment to test approve flow.\n";
}

$pendingTenants = $db->query(
    "SELECT t.id, u.name, u.email, t.guardian_email
     FROM tenants t JOIN users u ON t.user_id = u.id
     WHERE u.status = 'pending' AND u.role = 'tenant' LIMIT 3"
)->fetchAll(PDO::FETCH_ASSOC);
echo "\nPending tenants for approval: " . count($pendingTenants) . "\n";
foreach ($pendingTenants as $t) {
    echo "  #{$t['id']} {$t['name']} guardian=" . ($t['guardian_email'] ?: 'none') . "\n";
}

require_once CORE_PATH . '/Router.php';
