<?php
/**
 * BoardTrack | Top Navbar Component
 * app/views/components/navbar.php
 */
$role        = $role ?? ($_SESSION['user_role'] ?? 'tenant');
$userName    = $_SESSION['user_name'] ?? 'User';
$userInitial = strtoupper(substr($userName, 0, 1));
$pageTitle   = $pageTitle ?? 'BoardTrack';
$hasUnread   = (bool) ($hasUnreadNotifications ?? false);
?>
<div class="topbar-wrap">
  <header class="topbar w-full max-w-full overflow-x-hidden" role="banner">
    <div class="topbar-left">
      <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle sidebar menu" aria-expanded="false" aria-controls="sidebar">
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
      </button>
    </div>

    <div class="topbar-right">
      <?php
        $notifUrl = $role === 'landlord' ? 'landlord/notifications' : 'tenant/notifications';
      ?>
      <a href="<?= Router::url($notifUrl) ?>" class="topbar-icon-btn topbar-notif-btn" id="notifBellBtn"
         aria-label="Notifications">
        <i class="fa-solid fa-bell" aria-hidden="true"></i>
        <span class="notif-red-dot"<?= $hasUnread ? '' : ' hidden' ?> aria-label="Unread notifications"></span>
      </a>
      <?php $profileUrl = $role === 'landlord' ? 'landlord/profile' : 'tenant/profile'; ?>
      <a href="<?= Router::url($profileUrl) ?>" class="topbar-user-btn" style="text-decoration:none;" aria-label="View profile">
        <div class="topbar-avatar <?= $role === 'landlord' ? 'avatar-landlord' : '' ?>" aria-hidden="true" style="background: var(--color-brand);">
          <?= htmlspecialchars($userInitial) ?>
        </div>
      </a>
    </div>
  </header>
</div>

<style>
.topbar-notif-btn {
  position: relative;
}
.notif-red-dot {
  position: absolute;
  top: 5px;
  right: 5px;
  width: 9px;
  height: 9px;
  background: #ef4444;
  border-radius: 50%;
  border: 2px solid #fff;
  display: block;
  pointer-events: none;
  flex-shrink: 0;
}
.notif-red-dot[hidden] {
  display: none !important;
}
</style>