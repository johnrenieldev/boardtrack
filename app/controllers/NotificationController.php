<?php
/**
 * BoardTrack — NotificationController
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
        $this->redirect($role === 'landlord' ? 'landlord/notifications' : 'tenant/notifications');
    }

    public function notifications(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        if ($role === 'landlord') {
            $this->redirect('landlord/notifications');
            return;
        }
        $this->redirect('tenant/notifications');
    }

    public function markRead(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int) $_SESSION['user_id'];
            if ($id > 0) {
                $this->notificationModel->markRead($id, $userId);
            }
            $this->json([
                'success'      => true,
                'unread_count' => $this->notificationModel->getUnreadCount($userId),
            ]);
        }
        $this->redirectNotifications();
    }

    public function markAllRead(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int) $_SESSION['user_id'];
            $this->notificationModel->markAllRead($userId);
            $this->json(['success' => true, 'unread_count' => 0]);
        }
        $this->redirectNotifications();
    }

    private function redirectNotifications(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/notifications' : 'tenant/notifications');
    }
}
