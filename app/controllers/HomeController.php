<?php
/**
 * BoardTrack — Home Controller
 * app/controllers/HomeController.php
 *
 * Handles the public-facing landing page and 404.
 */

class HomeController extends Controller
{
    /**
     * GET  /  or  /?url=home/index
     * Renders the landing page.
     */
    public function index(): void
    {
        // If already logged in, redirect to the right dashboard
        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['user_role'] ?? '';
            if ($role === 'landlord') {
                $this->redirect('landlord/dashboard');
            } else {
                $this->redirect('tenant/dashboard');
            }
        }

        $this->view('home/landing', [
            'pageTitle' => 'BoardTrack — Boarding House Management System',
            'metaDesc'  => 'BoardTrack helps landlords manage tenants, billing, and complaints while keeping tenants informed about their room, payments, and announcements.',
            'bodyClass' => 'page-landing',
        ], 'main');
    }

    /**
     * Renders the 404 Not Found page.
     * Called by App.php when a controller or method is missing.
     */
    public function notFound(): void
    {
        http_response_code(404);
        $this->view('home/landing', [
            'pageTitle' => '404 — Page Not Found | BoardTrack',
            'bodyClass' => 'page-landing',
        ], 'main');
    }
}