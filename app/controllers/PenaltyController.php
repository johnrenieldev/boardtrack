<?php
/**
 * BoardTrack | PenaltyController
 * Handles manual penalty processing and viewing
 */
class PenaltyController extends Controller
{
    private object $billModel;
    private object $tenantModel;
    private object $notificationModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireRole('landlord'); // Only landlords can access
        $this->billModel = $this->model('Bill');
        $this->tenantModel = $this->model('Tenant');
        $this->notificationModel = $this->model('Notification');
    }

    /**
     * View penalty dashboard
     */
    public function index(): void
    {
        $eligibleBills = $this->billModel->getBillsEligibleForPenalty();
        $billsWithPenalties = $this->billModel->getBillsWithPendingPenaltyNotifications();

        $this->view('landlord/penalties', [
            'pageTitle' => 'Overdue Penalties | BoardTrack',
            'eligibleBills' => $eligibleBills,
            'billsWithPenalties' => $billsWithPenalties,
        ], 'landlord');
    }

    /**
     * Manually process overdue penalties
     */
    public function processNow(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/penalties');
        }

        try {
            // Process penalties
            $results = $this->billModel->processOverduePenalties();

            // Send notifications
            $billsWithPenalties = $this->billModel->getBillsWithPendingPenaltyNotifications();
            $notificationsSent = 0;

            foreach ($billsWithPenalties as $bill) {
                try {
                    $penaltyDetails = $this->billModel->getPenaltyDetails((int) $bill['id']);
                    
                    // Create in-app notification
                    $this->notificationModel->insert([
                        'user_id' => $bill['tenant_id'],
                        'type' => 'penalty_applied',
                        'title' => 'Overdue Penalty Applied',
                        'message' => "A 10% penalty has been applied to your overdue bill: {$bill['bill_name']}. " .
                                    "Original: ₱" . number_format($penaltyDetails['original_amount'], 2) . ", " .
                                    "Penalty: ₱" . number_format($penaltyDetails['penalty_amount'], 2) . ", " .
                                    "Total Due: ₱" . number_format($penaltyDetails['current_amount'], 2),
                        'link' => '/tenant/bills',
                    ]);

                    // Send email notification
                    if (!empty($bill['email'])) {
                        $this->sendPenaltyEmail($bill, $penaltyDetails);
                    }

                    // Send guardian email if available
                    if (!empty($bill['guardian_email'])) {
                        $this->sendPenaltyEmail($bill, $penaltyDetails, true);
                    }

                    // Mark notification as sent
                    $this->billModel->markPenaltyNotificationSent((int) $bill['id']);
                    $notificationsSent++;

                } catch (Exception $e) {
                    error_log("Failed to send penalty notification for bill #{$bill['id']}: " . $e->getMessage());
                }
            }

            $_SESSION['success'] = "Penalties processed successfully! " .
                                  "Processed: {$results['processed']}, " .
                                  "Notifications sent: {$notificationsSent}, " .
                                  "Total penalty: ₱" . number_format($results['total_penalty'], 2);

        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to process penalties: " . $e->getMessage();
        }

        $this->redirect('landlord/penalties');
    }

    /**
     * View penalty details for a specific bill
     */
    public function viewBill(int $billId): void
    {
        $bill = $this->billModel->find($billId);
        if (!$bill) {
            $_SESSION['error'] = "Bill not found.";
            $this->redirect('landlord/penalties');
        }

        $penaltyDetails = $this->billModel->getPenaltyDetails($billId);

        $this->view('landlord/penalty_details', [
            'pageTitle' => 'Penalty Details | BoardTrack',
            'bill' => $bill,
            'penaltyDetails' => $penaltyDetails,
        ], 'landlord');
    }

    /**
     * Send penalty notification email
     */
    private function sendPenaltyEmail(array $bill, array $penaltyDetails, bool $isGuardian = false): bool
    {
        require_once __DIR__ . '/../helpers/Mailer.php';
        
        $email = $isGuardian ? $bill['guardian_email'] : $bill['email'];
        $name = $isGuardian ? ($bill['guardian_name'] ?? 'Guardian') : $bill['tenant_name'];
        
        $subject = "Overdue Penalty Applied - BoardTrack";
        
        $recipient = $isGuardian ? "Dear Guardian" : "Dear {$name}";
        $tenantInfo = $isGuardian ? " for {$bill['tenant_name']}" : "";
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc2626; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
                .bill-details { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #e5e7eb; }
                .amount-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
                .total-row { display: flex; justify-content: space-between; padding: 15px 0; font-size: 18px; font-weight: bold; color: #dc2626; }
                .warning { background: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
                .btn { display: inline-block; background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>⚠️ Overdue Penalty Applied</h1>
                </div>
                <div class='content'>
                    <p>{$recipient},</p>
                    <p>A 10% monthly penalty has been applied to an overdue bill{$tenantInfo}.</p>
                    
                    <div class='bill-details'>
                        <h3>{$bill['bill_name']}</h3>
                        <p><strong>Room:</strong> {$bill['room_number']}</p>
                        <p><strong>Original Due Date:</strong> " . date('F j, Y', strtotime($bill['due_date'])) . "</p>
                        <p><strong>Months Overdue:</strong> {$penaltyDetails['missed_cycles']}</p>
                        
                        <div class='amount-row'>
                            <span>Original Amount:</span>
                            <span>₱" . number_format($penaltyDetails['original_amount'], 2) . "</span>
                        </div>
                        <div class='amount-row'>
                            <span>Penalty (10% × {$penaltyDetails['missed_cycles']} month" . ($penaltyDetails['missed_cycles'] > 1 ? 's' : '') . "):</span>
                            <span>₱" . number_format($penaltyDetails['penalty_amount'], 2) . "</span>
                        </div>
                        <div class='total-row'>
                            <span>Total Amount Due:</span>
                            <span>₱" . number_format($penaltyDetails['current_amount'], 2) . "</span>
                        </div>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Important:</strong> Additional 10% penalties will be applied each month the bill remains unpaid (compounding). 
                        Please settle this bill as soon as possible to avoid further charges.
                    </div>
                    
                    <p><strong>Penalty Calculation (Compounding):</strong></p>
                    <p>Each month, the current amount increases by 10%.</p>
                    <p>Current Amount = Previous Amount × 1.10 per month overdue</p>
                    
                    <center>
                        <a href='" . Router::url('tenant/bills') . "' class='btn'>View Bill & Pay Now</a>
                    </center>
                    
                    <p>If you have any questions or concerns, please contact your landlord immediately.</p>
                    
                    <p>Best regards,<br>BoardTrack Management System</p>
                </div>
                <div class='footer'>
                    <p>This is an automated notification from BoardTrack.</p>
                    <p>Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        try {
            $mailer = new Mailer();
            return $mailer->send($email, $subject, $body);
        } catch (Exception $e) {
            error_log("Penalty email error: " . $e->getMessage());
            return false;
        }
    }
}
