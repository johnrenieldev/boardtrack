<?php
/**
 * BoardTrack | AnnouncementController
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
        $this->redirect($role === 'landlord' ? 'landlord/announcements' : 'tenant/notifications');
    }
    /**
     * LANDLORD: List announcements
     */
    public function announcements(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('tenant/notifications');
        }
        $announcements = $this->announcementModel->getAllWithAuthor();
        $this->view('landlord/announcements', [
            'pageTitle'     => 'Announcements | BoardTrack',
            'announcements' => $announcements,
        ], 'landlord');
    }
    /**
     * LANDLORD: Create announcement
     */
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
        // Notify active tenants with full announcement content
        $activeTenants = $this->tenantModel->getActiveTenants();
        foreach ($activeTenants as $tenant) {
            $this->notificationModel->createNotification(
                $tenant['user_id'], 'announcement', $data['title'],
                $data['content'], 'tenant/notifications'
            );
        }
        $this->flash('success', 'Announcement created and tenants notified.');
        $this->redirect('landlord/announcements');
    }
    /**
     * LANDLORD: Edit announcement
     */
    public function editAnnouncement(int $id): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('landlord/announcements');
        }
        $announcement = $this->announcementModel->find($id);
        if (!$announcement) {
            $this->flash('error', 'Announcement not found.');
            $this->redirect('landlord/announcements');
        }
        $announcements = $this->announcementModel->getAllWithAuthor();
        $this->view('landlord/announcements', [
            'pageTitle'     => 'Announcements | BoardTrack',
            'announcements' => $announcements,
            'editAnnouncement' => $announcement,
        ], 'landlord');
    }

    /**
     * LANDLORD: Update announcement
     */
    public function updateAnnouncement(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('landlord/announcements');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/announcements');
        }
        $id = (int)($_POST['announcement_id'] ?? 0);
        if (!$id) {
            $this->flash('error', 'Invalid announcement.');
            $this->redirect('landlord/announcements');
        }
        $data = [
            'title'      => trim($_POST['title']   ?? ''),
            'content'    => trim($_POST['content'] ?? ''),
            'priority'   => $_POST['priority'] ?? 'normal',
            'event_date' => !empty($_POST['event_date']) ? $_POST['event_date'] : null,
        ];
        if (empty($data['title']) || empty($data['content'])) {
            $this->flash('error', 'Title and content required.');
            $this->redirect('landlord/edit-announcement/' . $id);
        }
        $this->announcementModel->update($data, ['id' => $id]);
        $this->flash('success', 'Announcement updated.');
        $this->redirect('landlord/announcements');
    }

    /**
     * LANDLORD: Toggle announcement active status
     */
    public function toggleAnnouncement(): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('landlord/announcements');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/announcements');
        }
        $id = (int)($_POST['announcement_id'] ?? 0);
        if (!$id) {
            $this->flash('error', 'Invalid announcement.');
            $this->redirect('landlord/announcements');
        }
        $announcement = $this->announcementModel->find($id);
        if ($announcement) {
            $this->announcementModel->update(['is_active' => $announcement['is_active'] ? 0 : 1], ['id' => $id]);
            $this->flash('success', 'Announcement status updated.');
        }
        $this->redirect('landlord/announcements');
    }

    /**
     * LANDLORD: Delete announcement
     */
    public function deleteAnnouncement(int $id): void
    {
        if ($_SESSION['user_role'] !== 'landlord') {
            $this->redirect('landlord/announcements');
        }
        $this->announcementModel->delete($id);
        $this->flash('success', 'Announcement deleted.');
        $this->redirect('landlord/announcements');
    }
    /**
     * TENANT: Announcements — redirected to notifications
     */
    public function tenantAnnouncements(): void
    {
        $this->redirect('tenant/notifications');
    }
}
?>