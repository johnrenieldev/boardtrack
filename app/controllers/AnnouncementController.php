<?php
/**
 * BoardTrack — AnnouncementController (Phase 1 Fixed)
 * Handles announcements for both roles.
 */
class AnnouncementController extends Controller
{
    private object $announcementModel;
    private object $notificationModel;
    private object $tenantModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->announcementModel = $this->model('Announcement');
        $this->notificationModel = $this->model('Notification');
        $this->tenantModel       = $this->model('Tenant');
    }

    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/announcements' : 'tenant/announcements');
    }

    // ── LANDLORD: List announcements ────────────────────────────
    public function announcements(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('tenant/announcements');
        }
        $announcements = $this->announcementModel->getAllWithAuthor();
        $this->view('landlord/announcements', [
            'pageTitle'     => 'Announcements — BoardTrack',
            'announcements' => $announcements,
        ], 'landlord');
    }

    // ── LANDLORD: Create announcement ──────────────────────────
    public function createAnnouncement(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('landlord/announcements');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/announcements');
        }
        $data = [
            'title'      => trim($_POST['title']   ?? ''),
            'content'    => trim($_POST['content'] ?? ''),
            'priority'   => $_POST['priority'] ?? 'normal',
            'event_date' => !empty($_POST['event_date']) ? $_POST['event_date'] : null,
            'is_active'  => 1,
            'created_by' => (int)$_SESSION['user_id'],
        ];
        if (empty($data['title']) || empty($data['content'])) {
            $this->flash('error', 'Title and content required.');
            $this->redirect('landlord/announcements');
        }
        $this->announcementModel->insert($data);
        // Notify active tenants
        $activeTenants = $this->tenantModel->getActiveTenants();
        foreach ($activeTenants as $tenant) {
            $this->notificationModel->createNotification(
                $tenant['user_id'], 'announcement', $data['title'],
                "New announcement: {$data['title']}", 'tenant/announcements'
            );
        }
        $this->flash('success', 'Announcement created and tenants notified.');
        $this->redirect('landlord/announcements');
    }

    // ── LANDLORD: Delete announcement ──────────────────────────
    public function deleteAnnouncement(int $id): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('landlord/announcements');
        }
        $this->announcementModel->delete($id);
        $this->flash('success', 'Announcement deleted.');
        $this->redirect('landlord/announcements');
    }

    // ── TENANT: List announcements ─────────────────────────────
    public function tenantAnnouncements(): void
    {
        $announcements = $this->announcementModel->getActive();
        $this->view('tenant/announcements', [
            'pageTitle'     => 'Announcements — BoardTrack',
            'announcements' => $announcements,
        ], 'tenant');
    }
}
?>

