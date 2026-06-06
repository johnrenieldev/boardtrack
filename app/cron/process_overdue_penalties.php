<?php
/**
 * BoardTrack — Overdue Penalty Processor
 * app/cron/process_overdue_penalties.php
 * 
 * This script should be run monthly via cron job or task scheduler
 * Recommended: Run on the 1st day of each month at 00:00
 * 
 * CRON SETUP (Linux/Hostinger):
 * 0 0 1 * * /usr/bin/php /path/to/boardtrack/app/cron/process_overdue_penalties.php
 * 
 * WINDOWS TASK SCHEDULER:
 * Program: C:\xampp\php\php.exe
 * Arguments: C:\xampp\htdocs\boardtrack\app\cron\process_overdue_penalties.php
 * Trigger: Monthly, 1st day, 12:00 AM
 */

// Prevent direct browser access
if (php_sapi_name() !== 'cli' && !isset($_GET['manual_run'])) {
    die('This script can only be run from command line or with manual_run parameter.');
}

// Load application core
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../core/Router.php';
require_once __DIR__ . '/../models/Bill.php';
require_once __DIR__ . '/../models/Tenant.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../helpers/BoardTrackMail.php';
require_once __DIR__ . '/../helpers/EmailTemplates.php';
require_once __DIR__ . '/../helpers/Mailer.php';

// Initialize models
$billModel = new Bill();
$tenantModel = new Tenant();
$notificationModel = new Notification();

// Log start
$logFile = __DIR__ . '/../../logs/penalty_processor.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function logMessage(string $message): void
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
    echo "[{$timestamp}] {$message}\n";
}

logMessage("=== OVERDUE PENALTY PROCESSOR STARTED ===");

try {
    // Step 1: Process overdue bills and apply penalties
    logMessage("Processing overdue bills...");
    $results = $billModel->processOverduePenalties();
    
    logMessage("Eligible bills: {$results['eligible']}");
    logMessage("Processed: {$results['processed']}");
    logMessage("Failed: {$results['failed']}");
    logMessage("Total penalty applied: ₱" . number_format($results['total_penalty'], 2));

    // Step 2: Send notifications for bills with penalties
    logMessage("Sending penalty notifications...");
    $billsWithPenalties = $billModel->getBillsWithPendingPenaltyNotifications();
    $notificationsSent = 0;
    $notificationsFailed = 0;

    foreach ($billsWithPenalties as $bill) {
        try {
            $penaltyDetails = $billModel->getPenaltyDetails((int) $bill['id']);
            
            // Send in-app notification to tenant
            $notificationModel->insert([
                'user_id' => $bill['tenant_id'],
                'type' => 'billing',
                'title' => 'Overdue Penalty Applied',
                'message' => "A 10% compounding penalty has been applied to your overdue bill: {$bill['bill_name']}. " .
                            "Original: ₱" . number_format($penaltyDetails['original_amount'], 2) . ", " .
                            "Penalty: ₱" . number_format($penaltyDetails['penalty_amount'], 2) . ", " .
                            "New Total: ₱" . number_format($penaltyDetails['current_amount'], 2) . ". " .
                            "Please pay immediately to avoid further charges.",
                'link' => 'tenant/bills',
            ]);

            // Send email to tenant using BoardTrackMail
            if (!empty($bill['email'])) {
                $emailSent = BoardTrackMail::tenantOverduePenalty(
                    $bill['email'],
                    $bill['tenant_name'],
                    $bill['bill_name'],
                    $penaltyDetails['original_amount'],
                    $penaltyDetails['penalty_amount'],
                    $penaltyDetails['current_amount'],
                    $penaltyDetails['penalty_count'],
                    $bill['due_date']
                );
                
                if ($emailSent) {
                    logMessage("Email sent to tenant: {$bill['tenant_name']} ({$bill['email']})");
                } else {
                    logMessage("Failed to send email to tenant: {$bill['tenant_name']} ({$bill['email']})");
                }
            }

            // Send email to guardian if available using BoardTrackMail
            if (!empty($bill['guardian_email'])) {
                $guardianEmailSent = BoardTrackMail::guardianOverduePenalty(
                    $bill['guardian_email'],
                    $bill['guardian_name'] ?? 'Guardian',
                    $bill['tenant_name'],
                    $bill['bill_name'],
                    $penaltyDetails['original_amount'],
                    $penaltyDetails['penalty_amount'],
                    $penaltyDetails['current_amount'],
                    $penaltyDetails['penalty_count'],
                    $bill['due_date']
                );
                
                if ($guardianEmailSent) {
                    logMessage("Email sent to guardian: {$bill['guardian_name']} ({$bill['guardian_email']})");
                } else {
                    logMessage("Failed to send email to guardian: {$bill['guardian_name']} ({$bill['guardian_email']})");
                }
            }

            // Mark notification as sent
            $billModel->markPenaltyNotificationSent((int) $bill['id']);
            $notificationsSent++;

        } catch (Exception $e) {
            logMessage("Failed to send notification for bill #{$bill['id']}: " . $e->getMessage());
            $notificationsFailed++;
        }
    }

    logMessage("Notifications sent: {$notificationsSent}");
    logMessage("Notifications failed: {$notificationsFailed}");

} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
}

logMessage("=== OVERDUE PENALTY PROCESSOR COMPLETED ===\n");
