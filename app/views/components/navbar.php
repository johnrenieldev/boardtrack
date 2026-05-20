<?php
/**
 * BoardTrack — Top Navbar Component
 * app/views/components/navbar.php
 */
$role        = $role ?? ($_SESSION['user_role'] ?? 'tenant');
$userName    = $_SESSION['user_name'] ?? 'User';
$userInitial = strtoupper(substr($userName, 0, 1));
$pageTitle   = $pageTitle ?? 'BoardTrack';
?>
<header class="topbar">
  <div class="topbar-left">
    <button class="topbar-toggle" id="sidebarToggle">
      <i class="fa-solid fa-bars"></i>
    </button>
  </div>

  <div class="topbar-right">
    <?php
      $notifUrl = $role === 'landlord' ? 'landlord/notifications' : 'tenant/notifications';
    ?>
    <?php $unreadNotifs = (int) ($unreadNotificationCount ?? 0); ?>
    <a href="<?= Router::url($notifUrl) ?>" class="topbar-icon-btn topbar-icon-btn--badge" id="notifBellBtn"
       title="<?= $unreadNotifs > 0 ? $unreadNotifs . ' unread notification' . ($unreadNotifs === 1 ? '' : 's') : 'Notifications' ?>">
      <i class="fa-solid fa-bell"></i>
      <span class="notif-badge" id="notifUnreadBadge" <?= $unreadNotifs <= 0 ? 'hidden' : '' ?>><?= $unreadNotifs > 99 ? '99+' : $unreadNotifs ?></span>
    </a>
    <?php $profileUrl = $role === 'landlord' ? 'landlord/profile' : 'tenant/profile'; ?>
    <a href="<?= Router::url($profileUrl) ?>" class="topbar-user-btn" style="text-decoration:none;">
      <div class="topbar-avatar <?= $role === 'landlord' ? 'avatar-landlord' : '' ?>">
        <?= htmlspecialchars($userInitial) ?>
      </div>
      <span class="topbar-user-name"><?= htmlspecialchars($userName) ?></span>
    </a>
  </div>
</header>