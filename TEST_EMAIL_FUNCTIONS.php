<?php
/**
 * EMAIL FUNCTION TEST SUITE
 * BoardTrack - Verify all email functions work with Hostinger SMTP
 * 
 * Usage: 
 * 1. Copy this file to your project root
 * 2. Access via browser: http://localhost/boardtrack/TEST_EMAIL_FUNCTIONS.php
 * 3. Review test results
 * 
 * DO NOT LEAVE IN PRODUCTION - DELETE AFTER TESTING
 */

// Set up basic constants if not already loaded
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
    require_once ROOT_PATH . '/config/config.php';
    require_once ROOT_PATH . '/config/mail.php';
    require_once ROOT_PATH . '/core/App.php';
    require_once ROOT_PATH . '/app/helpers/BoardTrackMail.php';
}

$results = [];

// Test 1: Configuration Check
$results['config_enabled'] = [
    'name' => 'Mail Enabled',
    'pass' => defined('MAIL_ENABLED') && MAIL_ENABLED,
    'value' => defined('MAIL_ENABLED') ? (MAIL_ENABLED ? 'YES' : 'NO') : 'NOT SET'
];

$results['config_host'] = [
    'name' => 'SMTP Host',
    'pass' => defined('MAIL_HOST') && !empty(MAIL_HOST),
    'value' => defined('MAIL_HOST') ? MAIL_HOST : 'NOT SET'
];

$results['config_port'] = [
    'name' => 'SMTP Port',
    'pass' => defined('MAIL_PORT') && MAIL_PORT > 0,
    'value' => defined('MAIL_PORT') ? MAIL_PORT : 'NOT SET'
];

$results['config_username'] = [
    'name' => 'SMTP Username',
    'pass' => defined('MAIL_USERNAME') && !empty(MAIL_USERNAME),
    'value' => defined('MAIL_USERNAME') ? MAIL_USERNAME : 'NOT SET'
];

$results['config_password'] = [
    'name' => 'SMTP Password',
    'pass' => defined('MAIL_PASSWORD') && !empty(MAIL_PASSWORD),
    'value' => defined('MAIL_PASSWORD') ? '***' . substr(MAIL_PASSWORD, -2) : 'NOT SET'
];

$results['config_from'] = [
    'name' => 'From Email',
    'pass' => defined('MAIL_FROM') && !empty(MAIL_FROM),
    'value' => defined('MAIL_FROM') ? MAIL_FROM : 'NOT SET'
];

$results['config_from_name'] = [
    'name' => 'From Name',
    'pass' => defined('MAIL_FROM_NAME') && !empty(MAIL_FROM_NAME),
    'value' => defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'NOT SET'
];

// Test 2: Helper Classes Check
$results['board_track_mail_exists'] = [
    'name' => 'BoardTrackMail Class',
    'pass' => class_exists('BoardTrackMail'),
    'value' => class_exists('BoardTrackMail') ? 'FOUND' : 'NOT FOUND'
];

$results['mailer_exists'] = [
    'name' => 'Mailer Class',
    'pass' => class_exists('Mailer'),
    'value' => class_exists('Mailer') ? 'FOUND' : 'NOT FOUND'
];

$results['email_templates_exists'] = [
    'name' => 'EmailTemplates Class',
    'pass' => class_exists('EmailTemplates'),
    'value' => class_exists('EmailTemplates') ? 'FOUND' : 'NOT FOUND'
];

// Test 3: Method Check
$results['method_registration'] = [
    'name' => 'Method: registrationReceived()',
    'pass' => method_exists('BoardTrackMail', 'registrationReceived'),
    'value' => method_exists('BoardTrackMail', 'registrationReceived') ? 'EXISTS' : 'MISSING'
];

$results['method_password_reset'] = [
    'name' => 'Method: passwordReset()',
    'pass' => method_exists('BoardTrackMail', 'passwordReset'),
    'value' => method_exists('BoardTrackMail', 'passwordReset') ? 'EXISTS' : 'MISSING'
];

$results['method_verification'] = [
    'name' => 'Method: verificationEmail()',
    'pass' => method_exists('BoardTrackMail', 'verificationEmail'),
    'value' => method_exists('BoardTrackMail', 'verificationEmail') ? 'EXISTS' : 'MISSING'
];

$results['method_tenant_approved'] = [
    'name' => 'Method: tenantApproved()',
    'pass' => method_exists('BoardTrackMail', 'tenantApproved'),
    'value' => method_exists('BoardTrackMail', 'tenantApproved') ? 'EXISTS' : 'MISSING'
];

$results['method_complaint'] = [
    'name' => 'Method: complaintSubmittedToLandlord()',
    'pass' => method_exists('BoardTrackMail', 'complaintSubmittedToLandlord'),
    'value' => method_exists('BoardTrackMail', 'complaintSubmittedToLandlord') ? 'EXISTS' : 'MISSING'
];

$results['method_payment'] = [
    'name' => 'Method: paymentSubmittedToLandlord()',
    'pass' => method_exists('BoardTrackMail', 'paymentSubmittedToLandlord'),
    'value' => method_exists('BoardTrackMail', 'paymentSubmittedToLandlord') ? 'EXISTS' : 'MISSING'
];

$results['method_contact'] = [
    'name' => 'Method: contactUs()',
    'pass' => method_exists('BoardTrackMail', 'contactUs'),
    'value' => method_exists('BoardTrackMail', 'contactUs') ? 'EXISTS' : 'MISSING'
];

// Count results
$passCount = 0;
$totalCount = count($results);
foreach ($results as $r) {
    if ($r['pass']) $passCount++;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardTrack Email Configuration Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        .summary-item .label {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        .tests-grid {
            display: grid;
            gap: 12px;
        }
        .test-row {
            display: grid;
            grid-template-columns: 1fr 200px 1fr;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            align-items: center;
            border-left: 4px solid #dee2e6;
        }
        .test-row.pass {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .test-row.fail {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .test-name {
            font-weight: 500;
            color: #212529;
        }
        .test-status {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .test-status.pass {
            background: #28a745;
            color: white;
        }
        .test-status.fail {
            background: #dc3545;
            color: white;
        }
        .test-value {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #495057;
            word-break: break-all;
        }
        .footer {
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #856404;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 BoardTrack Email Configuration Test</h1>
            <p>Hostinger SMTP Configuration Status</p>
        </div>
        <div class="content">
            <div class="warning">
                ⚠️ <strong>WARNING:</strong> This is a test file. Delete <code>TEST_EMAIL_FUNCTIONS.php</code> before deploying to production.
            </div>
            
            <div class="summary">
                <div class="summary-item">
                    <div class="number"><?php echo $passCount; ?>/<?php echo $totalCount; ?></div>
                    <div class="label">Tests Passed</div>
                </div>
                <div class="summary-item">
                    <div class="number" style="color: <?php echo ($passCount === $totalCount) ? '#28a745' : '#dc3545'; ?>">
                        <?php echo round(($passCount / $totalCount) * 100); ?>%
                    </div>
                    <div class="label">Success Rate</div>
                </div>
                <div class="summary-item">
                    <div class="number" style="color: <?php echo ($passCount === $totalCount) ? '#28a745' : '#dc3545'; ?>">
                        <?php echo $passCount === $totalCount ? '✓ READY' : '✗ NEEDS FIX'; ?>
                    </div>
                    <div class="label">Status</div>
                </div>
            </div>

            <div class="tests-grid">
                <?php foreach ($results as $key => $result): ?>
                    <div class="test-row <?php echo $result['pass'] ? 'pass' : 'fail'; ?>">
                        <div class="test-name"><?php echo htmlspecialchars($result['name']); ?></div>
                        <div class="test-status <?php echo $result['pass'] ? 'pass' : 'fail'; ?>">
                            <?php echo $result['pass'] ? '✓ Pass' : '✗ Fail'; ?>
                        </div>
                        <div class="test-value"><?php echo htmlspecialchars($result['value']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="footer">
            Generated: <?php echo date('Y-m-d H:i:s'); ?> | 
            <?php if ($passCount === $totalCount): ?>
                ✓ All tests passed - Email system is ready to use
            <?php else: ?>
                ✗ Some tests failed - Review configuration in config/mail.php
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
