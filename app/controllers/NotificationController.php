<?php
/**
 * BoardTrack — NotificationController (Phase 1 Fixed)
 */
class NotificationController extends Controller
{
    private object $notificationModel;
    private object $userModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->notificationModel = $this->model('Notification');
        $this->userModel         = $this->model('User');
    }

    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/dashboard' : 'tenant/notifications');
    }

    // ── TENANT/LANDLORD: List notifications ───────────────────
    public function notifications(): void
    {
        $user = $this->userModel->find((int)$_SESSION['user_id']);
        $notifications = $this->notificationModel->getForUser($user['id']);
        $unreadCount = $this->notificationModel->getUnreadCount($user['id']);
        $this->view('tenant/notifications', [
            'pageTitle'        => 'Notifications — BoardTrack',
            'notifications'    => $notifications,
            'unreadCount'      => $unreadCount,
        ], 'tenant');
    }

    // ── Mark single notification as read ─────────────────────
    public function markRead(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->notificationModel->markRead($id, (int)$_SESSION['user_id']);
            $this->json(['success' => true]);
        }
        $this->redirect('tenant/notifications');
    }

    // ── Mark all notifications as read ────────────────────────
    public function markAllRead(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->notificationModel->markAllRead((int)$_SESSION['user_id']);
            $this->json(['success' => true]);
        }
        $this->redirect('tenant/notifications');
    }
}
?>

