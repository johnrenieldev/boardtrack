<?php
/**
 * BoardTrack | PaymentController (legacy routes | landlord)
 */
class PaymentController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/payments' : 'tenant/bills');
    }

    public function payments(): void
    {
        $this->redirect(($_SESSION['user_role'] ?? '') === 'landlord' ? 'landlord/payments' : 'tenant/bills');
    }

    public function approvePayment(): void
    {
        $this->redirect('landlord/payments');
    }

    public function rejectPayment(): void
    {
        $this->redirect('landlord/payments');
    }
}
