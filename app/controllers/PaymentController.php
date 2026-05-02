<?php
/**
 * BoardTrack — PaymentController (Phase 1 Fixed)
 */
class PaymentController extends Controller
{
    private object $paymentModel;
    private object $billModel;
    private object $tenantModel;
    private object $notificationModel;
    private object $auditLogModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->paymentModel   = $this->model('Payment');
        $this->billModel      = $this->model('Bill');
        $this->tenantModel    = $this->model('Tenant');
        $this->notificationModel = $this->model('Notification');
        $this->auditLogModel  = $this->model('AuditLog');
    }

    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/payments' : 'tenant/bills');
    }

    // ── LANDLORD: List payments ────────────────────────────────
    public function payments(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('tenant/bills');
        }
        $filters = ['status' => $_GET['status'] ?? null];
        $payments = $this->paymentModel->getAllWithDetails($filters);
        $this->view('landlord/payments', [
            'pageTitle' => 'Payments — BoardTrack',
            'payments'  => $payments,
            'filters'   => $filters,
        ], 'landlord');
    }

    // ── LANDLORD: Approve payment ─────────────────────────────
    public function approvePayment(int $id): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('landlord/payments');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/payments');
        }
        $payment = $this->paymentModel->find($id);
        if (!$payment) {
            $this->flash('error', 'Payment not found.');
            $this->redirect('landlord/payments');
        }
        $this->paymentModel->update([
            'status' => 'approved',
            'reviewed_by' => (int)$_SESSION['user_id'],
            'reviewed_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
        $this->billModel->update([
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s')
        ], ['id' => $payment['bill_id']]);
        $tenant = $this->tenantModel->find($payment['tenant_id']);
        if ($tenant) {
            $this->notificationModel->createNotification(
                $tenant['user_id'], 'billing', 'Payment Approved',
                'Your payment has been verified.', 'tenant/bills'
            );
        }
        $this->auditLogModel->log($_SESSION['user_id'], 'payment_approved', 'payment', $id, ['status'=>'pending'], ['status'=>'approved'], 'Payment approved');
        $this->flash('success', 'Payment approved.');
        $this->redirect('landlord/payments');
    }

    // ── LANDLORD: Reject payment ──────────────────────────────
    public function rejectPayment(int $id): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('landlord/payments');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/payments');
        }
        $reason = trim($_POST['reason'] ?? '');
        if (empty($reason)) {
            $this->flash('error', 'Reason required.');
            $this->redirect('landlord/payments');
        }
        $payment = $this->paymentModel->find($id);
        if (!$payment) {
            $this->flash('error', 'Payment not found.');
            $this->redirect('landlord/payments');
        }
        $this->paymentModel->update([
            'status' => 'rejected',
            'reviewed_by' => (int)$_SESSION['user_id'],
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_notes' => $reason
        ], ['id' => $id]);
        $this->billModel->update(['status' => 'unpaid'], ['id' => $payment['bill_id']]);
        $tenant = $this->tenantModel->find($payment['tenant_id']);
        if ($tenant) {
            $this->notificationModel->createNotification(
                $tenant['user_id'], 'billing', 'Payment Rejected',
                "Reason: {$reason}", 'tenant/bills'
            );
        }
        $this->auditLogModel->log($_SESSION['user_id'], 'payment_rejected', 'payment', $id, ['status'=>'pending'], ['status'=>'rejected'], $reason);
        $this->flash('success', 'Payment rejected.');
        $this->redirect('landlord/payments');
    }
}
?>

