<?php
/**
 * BoardTrack | Sidebar Navigation Component
 * app/views/components/sidebar.php
 */
$role        = $role ?? ($_SESSION['user_role'] ?? 'tenant');
$userName    = $_SESSION['user_name'] ?? 'User';
$userInitial = strtoupper(substr($userName, 0, 1));
$currentUrl  = $_GET['url'] ?? '';
$hasUnread   = (bool) ($hasUnreadNotifications ?? false);
?>
<aside class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">

  <div class="sidebar-header">
    <a href="<?= Router::url('home/index') ?>" class="sidebar-logo" aria-label="BoardTrack Home">
      Board<span>Track</span>
    </a>
    <button class="sidebar-close" id="sidebarClose" aria-label="Close sidebar">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <div class="sidebar-user">
    <div class="sidebar-avatar <?= $role === 'landlord' ? 'avatar-landlord' : '' ?>" aria-hidden="true" style="background: var(--color-brand);">
      <?= htmlspecialchars($userInitial) ?>
    </div>
    <div>
      <div class="sidebar-user-name"><?= htmlspecialchars($userName) ?></div>
      <div class="sidebar-user-role"><?= $role === 'landlord' ? 'Administrator' : 'Tenant' ?></div>
    </div>
  </div>

  <nav class="sidebar-nav" aria-label="Navigation menu">

    <?php if ($role === 'landlord'): ?>

      <div class="nav-section-label">Management</div>
      <a href="<?= Router::url('landlord/dashboard') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/dashboard') ? 'active' : '' ?>"
         aria-current="<?= str_starts_with($currentUrl, 'landlord/dashboard') ? 'page' : 'false' ?>">
        <i class="fa-solid fa-gauge" aria-hidden="true"></i><span>Dashboard</span>
      </a>
      <?php
        $pendingNavActive = ($currentUrl === 'landlord/tenants' && ($_GET['status'] ?? '') === 'pending');
        $tenantsNavActive = str_starts_with($currentUrl, 'landlord/tenant') && !$pendingNavActive;
      ?>

      <a href="<?= Router::url('landlord/tenants') ?>"
         class="nav-item <?= $tenantsNavActive ? 'active' : '' ?>"
         aria-current="<?= $tenantsNavActive ? 'page' : 'false' ?>">
        <i class="fa-solid fa-users" aria-hidden="true"></i><span>Tenants</span>
      </a>
      <a href="<?= Router::url('landlord/rooms') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/room') ? 'active' : '' ?>"
         aria-current="<?= str_starts_with($currentUrl, 'landlord/room') ? 'page' : 'false' ?>">
        <i class="fa-solid fa-door-open" aria-hidden="true"></i><span>Rooms</span>
      </a>

      <div class="nav-section-label">Finance</div>
      <a href="<?= Router::url('landlord/bills') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/bill') ? 'active' : '' ?>"
         aria-current="<?= str_starts_with($currentUrl, 'landlord/bill') ? 'page' : 'false' ?>">
        <i class="fa-solid fa-file-invoice" aria-hidden="true"></i><span>Billing</span>
      </a>
      <a href="<?= Router::url('landlord/payments') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/payment') ? 'active' : '' ?>"
         aria-current="<?= str_starts_with($currentUrl, 'landlord/payment') ? 'page' : 'false' ?>">
        <i class="fa-solid fa-credit-card" aria-hidden="true"></i><span>Payments</span>
      </a>

      <div class="nav-section-label">Communication</div>
      <a href="<?= Router::url('landlord/complaints') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/complaint') ? 'active' : '' ?>"
         aria-current="<?= str_starts_with($currentUrl, 'landlord/complaint') ? 'page' : 'false' ?>">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><span>Complaints</span>
      </a>
      <a href="<?= Router::url('landlord/announcements') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/announcement') ? 'active' : '' ?>"
         aria-current="<?= str_starts_with($currentUrl, 'landlord/announcement') ? 'page' : 'false' ?>">
        <i class="fa-solid fa-bullhorn" aria-hidden="true"></i><span>Announcements</span>
      </a>
      <a href="<?= Router::url('landlord/notifications') ?>"
         class="nav-item nav-item-notif <?= str_starts_with($currentUrl, 'landlord/notification') ? 'active' : '' ?>"
         aria-current="<?= str_starts_with($currentUrl, 'landlord/notification') ? 'page' : 'false' ?>">
        <span class="nav-item-icon-wrap">
          <i class="fa-solid fa-bell" aria-hidden="true"></i>
          <span class="notif-red-dot notif-red-dot-sidebar"<?= $hasUnread ? '' : ' hidden' ?> aria-label="Unread notifications"></span>
        </span>
        <span>Notifications</span>
      </a>

      <div class="nav-section-label">Account</div>
      <a href="<?= Router::url('landlord/profile') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/profile') ? 'active' : '' ?>"
         aria-current="<?= str_starts_with($currentUrl, 'landlord/profile') ? 'page' : 'false' ?>">
        <i class="fa-solid fa-user" aria-hidden="true"></i><span>Profile</span>
      </a>

    <?php else: ?>

      <a href="<?= Router::url('tenant/dashboard') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'tenant/dashboard') ? 'active' : '' ?>"
         aria-current="<?= str_starts_with($currentUrl, 'tenant/dashboard') ? 'page' : 'false' ?>">
        <i class="fa-solid fa-gauge" aria-hidden="true"></i><span>Dashboard</span>
      </a>

      <?php if (($userStatus ?? '') === 'approved'): ?>
        <a href="<?= Router::url('tenant/bills') ?>"
           class="nav-item <?= str_starts_with($currentUrl, 'tenant/bill') ? 'active' : '' ?>"
           aria-current="<?= str_starts_with($currentUrl, 'tenant/bill') ? 'page' : 'false' ?>">
          <i class="fa-solid fa-file-invoice" aria-hidden="true"></i><span>My Bills</span>
        </a>
        <a href="<?= Router::url('tenant/complaints') ?>"
           class="nav-item <?= str_starts_with($currentUrl, 'tenant/complaint') ? 'active' : '' ?>"
           aria-current="<?= str_starts_with($currentUrl, 'tenant/complaint') ? 'page' : 'false' ?>">
          <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><span>Complaints</span>
        </a>
        <a href="<?= Router::url('tenant/notifications') ?>"
           class="nav-item nav-item-notif <?= str_starts_with($currentUrl, 'tenant/notification') ? 'active' : '' ?>"
           aria-current="<?= str_starts_with($currentUrl, 'tenant/notification') ? 'page' : 'false' ?>">
          <span class="nav-item-icon-wrap">
            <i class="fa-solid fa-bell" aria-hidden="true"></i>
            <span class="notif-red-dot notif-red-dot-sidebar"<?= $hasUnread ? '' : ' hidden' ?> aria-label="Unread notifications"></span>
          </span>
          <span>Notifications</span>
        </a>
      <?php endif; ?>

      <a href="<?= Router::url('tenant/profile') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'tenant/profile') ? 'active' : '' ?>"
         aria-current="<?= str_starts_with($currentUrl, 'tenant/profile') ? 'page' : 'false' ?>">
        <i class="fa-solid fa-user" aria-hidden="true"></i><span>My Profile</span>
      </a>

    <?php endif; ?>

  </nav>

  <div class="sidebar-footer">
    <a href="<?= Router::url('auth/logout') ?>"
       class="nav-item nav-logout confirm-logout"
       data-message="Are you sure you want to log out? You will need to sign in again to continue."
       aria-label="Log out">
      <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i><span>Log Out</span>
    </a>
  </div>

</aside>

<style>
.nav-item-notif {
  position: relative;
}
.nav-item-icon-wrap {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.notif-red-dot-sidebar {
  position: absolute;
  top: -3px;
  right: -4px;
  width: 8px;
  height: 8px;
  background: #ef4444;
  border-radius: 50%;
  border: 2px solid #fff;
  display: block;
  pointer-events: none;
}
</style>