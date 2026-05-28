<?php
/**
 * BoardTrack | Dashboard Controller
 * app/controllers/DashboardController.php
 *
 * Central hub after login.
 * Routes tenant and landlord to their correct dashboard view.
 * All methods are protected | no guest access.
 */

class DashboardController extends Controller
{
    /**
     * GET /?url=dashboard/index
     *
     * Entry point after login.
     * Checks role and loads the correct dashboard view.
     */
    public function index(): void
    {
        // Guard — boots unauthenticated users back to login
        $this->requireAuth();

        $role = $_SESSION['user_role'] ?? '';

        // Route to the correct role-specific dashboard
        if ($role === 'landlord') {
            $this->redirect('landlord/dashboard');
        } elseif ($role === 'tenant') {
            $this->redirect('tenant/dashboard');
        } else {
            // Invalid or missing role — force logout
            $this->invalidSession('Invalid session. Please log in again.');
        }
    }
}