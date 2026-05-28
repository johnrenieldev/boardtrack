<?php
/**
 * BoardTrack | NotificationController
 * app/controllers/NotificationController.php
 *
 * Handles:
 *  - index / notifications  → redirect to role-specific page
 *  - markRead               → AJAX: mark a single notification read (fallback route)
 *  - delete                 → AJAX: delete a notification
 *
 * Note: The primary mark-read AJAX endpoint is handled by
 *       LandlordController::markNotificationRead() and
 *       TenantController::markNotificationRead(), which are called
 *       directly by notifications.js via data-mark-notif-read-url.
 *
 * "Mark All as Read" has been removed.
 */
class NotificationController extends Controller
{
    private object $notificationModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->notificationModel = $this->model('Notification');
    }

    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/notifications' : 'tenant/notifications');
    }

    public function notifications(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/notifications' : 'tenant/notifications');
    }

    /**
     * POST notification/markRead/{id}
     * Fallback mark-read endpoint (role-specific controllers are preferred).
     * Validates ownership via WHERE user_id = :uid in the model.
     */
    public function markRead(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectNotifications();
            return;
        }

        $userId = (int) $_SESSION['user_id'];

        if ($id > 0) {
            $this->notificationModel->markRead($id, $userId);
        }

        $this->json(['success' => true]);
    }

    /**
     * POST notification/delete/{id}
     * Deletes a notification belonging to the authenticated user.
     * Ownership enforced by model: WHERE id = :id AND user_id = :uid.
     */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectNotifications();
            return;
        }

        $userId = (int) $_SESSION['user_id'];

        if ($id > 0 && $this->notificationModel->deleteForUser($id, $userId)) {
            $this->json(['success' => true]);
            return;
        }

        $this->json(['success' => false], 400);
    }

    private function redirectNotifications(): void
    {
        $role = $_SESSION['user_role'] ?? 'tenant';
        $this->redirect($role === 'landlord' ? 'landlord/notifications' : 'tenant/notifications');
    }
}