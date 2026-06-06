<?php
/**
 * BoardTrack | Home Controller
 * app/controllers/HomeController.php
 *
 * Handles the public-facing landing page and 404.
 */

class HomeController extends Controller
{
    private object $testimonialModel;

    public function __construct()
    {
        $this->testimonialModel = $this->model('Testimonial');
    }

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

        // Fetch approved testimonials for the landing page
        $testimonials = $this->testimonialModel->getApprovedTestimonials(6);

        $this->view('home/landing', [
            'pageTitle' => 'BoardTrack | Boarding House Management System',
            'metaDesc'  => 'BoardTrack helps landlords manage tenants, billing, and complaints while keeping tenants informed about their room, payments, and announcements.',
            'bodyClass' => 'page-landing',
            'testimonials' => $testimonials,
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
            'pageTitle' => '404 | Page Not Found | BoardTrack',
            'bodyClass' => 'page-landing',
        ], 'main');
    }

    /**
     * POST /home/contact
     * Handles contact form submissions from the landing page.
     */
    public function contact(): void
    {
        header('Content-Type: application/json');

        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = $_POST['subject'] ?? '';
        $message = trim($_POST['message'] ?? '');

        // Validate required fields
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            return;
        }

        // Validate subject options
        $validSubjects = ['technical', 'partnership', 'general'];
        if (!in_array($subject, $validSubjects)) {
            echo json_encode(['success' => false, 'message' => 'Invalid subject']);
            return;
        }

        $subjectLabels = [
            'technical' => 'Technical Support',
            'partnership' => 'Partnership Inquiry',
            'general' => 'General Question'
        ];

        $supportEmail = defined('MAIL_FROM') && !empty(MAIL_FROM) ? MAIL_FROM : 'support@bsit2a.com';
        $supportName  = APP_NAME . ' Support';

        require_once ROOT_PATH . '/app/helpers/BoardTrackMail.php';

        try {
            $emailSent = BoardTrackMail::contactUs(
                $supportEmail,
                $supportName,
                $name,
                $email,
                $subjectLabels[$subject],
                $message
            );

            if ($emailSent) {
                echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
            } else {
                error_log('[BoardTrack Contact Form] contactUs returned false. enabled=' . (BoardTrackMail::isEnabled() ? 'true' : 'false')
                    . ' host=' . (defined('MAIL_HOST') ? MAIL_HOST : 'n/a')
                    . ' port=' . (defined('MAIL_PORT') ? MAIL_PORT : 'n/a'));
                echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again later.']);
            }
        } catch (Throwable $t) {
            error_log('[BoardTrack Contact Form] Exception: ' . $t->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again later.']);
        }

    }
}
