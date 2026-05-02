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
    <span class="topbar-title"><?= htmlspecialchars($pageTitle) ?></span>
  </div>

  <div class="topbar-right">
    <?php if ($role === 'landlord'): ?>
      <a href="<?= Router::url('landlord/dashboard') ?>" class="topbar-icon-btn" title="Dashboard">
        <i class="fa-solid fa-house"></i>
      </a>
    <?php else: ?>
      <a href="<?= Router::url('tenant/notifications') ?>" class="topbar-icon-btn" title="Notifications">
        <i class="fa-solid fa-bell"></i>
      </a>
    <?php endif; ?>
    <button class="topbar-user-btn" type="button">
      <div class="topbar-avatar <?= $role === 'landlord' ? 'avatar-landlord' : '' ?>">
        <?= htmlspecialchars($userInitial) ?>
      </div>
      <span class="topbar-user-name"><?= htmlspecialchars($userName) ?></span>
      <i class="fa-solid fa-chevron-down" style="font-size:0.65rem;color:#9ca3af;"></i>
    </button>
  </div>
</header>