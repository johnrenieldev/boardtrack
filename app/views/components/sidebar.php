<?php
/**
 * BoardTrack — Sidebar Navigation Component
 * app/views/components/sidebar.php
 */
$role        = $role ?? ($_SESSION['user_role'] ?? 'tenant');
$userName    = $_SESSION['user_name'] ?? 'User';
$userInitial = strtoupper(substr($userName, 0, 1));
$currentUrl  = $_GET['url'] ?? '';
$sidebarUnread = (int) ($unreadNotificationCount ?? 0);
?>
<aside class="sidebar" id="sidebar">

  <div class="sidebar-header">
    <a href="<?= Router::url('home/index') ?>" class="sidebar-logo">
      Board<span>Track</span>
    </a>
    <button class="sidebar-close" id="sidebarClose">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <div class="sidebar-user">
    <div class="sidebar-avatar <?= $role === 'landlord' ? 'avatar-landlord' : '' ?>">
      <?= htmlspecialchars($userInitial) ?>
    </div>
    <div>
      <div class="sidebar-user-name"><?= htmlspecialchars($userName) ?></div>
      <div class="sidebar-user-role"><?= $role === 'landlord' ? 'Administrator' : 'Tenant' ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">

    <?php if ($role === 'landlord'): ?>

      <div class="nav-section-label">Management</div>
      <a href="<?= Router::url('landlord/dashboard') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/dashboard') ? 'active' : '' ?>">
        <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
      </a>
      <?php
        $pendingNavActive = ($currentUrl === 'landlord/tenants' && ($_GET['status'] ?? '') === 'pending');
        $tenantsNavActive = str_starts_with($currentUrl, 'landlord/tenant') && !$pendingNavActive;
      ?>

      <a href="<?= Router::url('landlord/tenants') ?>"
         class="nav-item <?= $tenantsNavActive ? 'active' : '' ?>">
        <i class="fa-solid fa-users"></i><span>Tenants</span>
      </a>
      <a href="<?= Router::url('landlord/rooms') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/room') ? 'active' : '' ?>">
        <i class="fa-solid fa-door-open"></i><span>Rooms</span>
      </a>

      <div class="nav-section-label">Finance</div>
      <a href="<?= Router::url('landlord/bills') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/bill') ? 'active' : '' ?>">
        <i class="fa-solid fa-file-invoice"></i><span>Billing</span>
      </a>
      <a href="<?= Router::url('landlord/payments') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/payment') ? 'active' : '' ?>">
        <i class="fa-solid fa-credit-card"></i><span>Payments</span>
      </a>

      <div class="nav-section-label">Communication</div>
      <a href="<?= Router::url('landlord/complaints') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/complaint') ? 'active' : '' ?>">
        <i class="fa-solid fa-triangle-exclamation"></i><span>Complaints</span>
      </a>
      <a href="<?= Router::url('landlord/announcements') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/announcement') ? 'active' : '' ?>">
        <i class="fa-solid fa-bullhorn"></i><span>Announcements</span>
      </a>
      <a href="<?= Router::url('landlord/notifications') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/notification') ? 'active' : '' ?>">
        <i class="fa-solid fa-bell"></i><span>Notifications</span>
        <?php if ($sidebarUnread > 0): ?>
          <span class="nav-badge" id="sidebarNotifBadge"><?= $sidebarUnread > 99 ? '99+' : $sidebarUnread ?></span>
        <?php endif; ?>
      </a>

      <div class="nav-section-label">Account</div>
      <a href="<?= Router::url('landlord/profile') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'landlord/profile') ? 'active' : '' ?>">
        <i class="fa-solid fa-user"></i><span>Profile</span>
      </a>

    <?php else: ?>

      <a href="<?= Router::url('tenant/dashboard') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'tenant/dashboard') ? 'active' : '' ?>">
        <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
      </a>
      <a href="<?= Router::url('tenant/bills') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'tenant/bill') ? 'active' : '' ?>">
        <i class="fa-solid fa-file-invoice"></i><span>My Bills</span>
      </a>
      <a href="<?= Router::url('tenant/complaints') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'tenant/complaint') ? 'active' : '' ?>">
        <i class="fa-solid fa-triangle-exclamation"></i><span>Complaints</span>
      </a>
      <a href="<?= Router::url('tenant/notifications') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'tenant/notification') ? 'active' : '' ?>">
        <i class="fa-solid fa-bell"></i><span>Notifications</span>
        <?php if ($sidebarUnread > 0): ?>
          <span class="nav-badge" id="sidebarNotifBadge"><?= $sidebarUnread > 99 ? '99+' : $sidebarUnread ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= Router::url('tenant/profile') ?>"
         class="nav-item <?= str_starts_with($currentUrl, 'tenant/profile') ? 'active' : '' ?>">
        <i class="fa-solid fa-user"></i><span>My Profile</span>
      </a>

    <?php endif; ?>

  </nav>

  <div class="sidebar-footer">
    <a href="<?= Router::url('auth/logout') ?>"
       class="nav-item nav-logout confirm-logout"
       data-message="Are you sure you want to log out? You will need to sign in again to continue.">
      <i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span>
    </a>
  </div>

</aside>