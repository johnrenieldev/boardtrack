<?php
/**
 * BoardTrack | Mobile Bottom Navigation Component
 * app/views/components/mobile-bottom-nav.php
 */
$role       = $role ?? ($_SESSION['user_role'] ?? 'tenant');
$currentUrl = $_GET['url'] ?? '';
$hasUnread  = (bool) ($hasUnreadNotifications ?? false);
?>
<nav class="mobile-bottom-nav" role="navigation" aria-label="Mobile navigation">
  <?php if ($role === 'landlord'): ?>
    <a href="<?= Router::url('landlord/dashboard') ?>"
       class="bn-item <?= str_starts_with($currentUrl, 'landlord/dashboard') ? 'active' : '' ?>"
       aria-current="<?= str_starts_with($currentUrl, 'landlord/dashboard') ? 'page' : 'false' ?>"
       aria-label="Home">
      <i class="fa-solid fa-gauge" aria-hidden="true"></i>
      <span>Home</span>
    </a>
    <a href="<?= Router::url('landlord/tenants') ?>"
       class="bn-item <?= str_starts_with($currentUrl, 'landlord/tenant') ? 'active' : '' ?>"
       aria-current="<?= str_starts_with($currentUrl, 'landlord/tenant') ? 'page' : 'false' ?>"
       aria-label="Tenants">
      <i class="fa-solid fa-users" aria-hidden="true"></i>
      <span>Tenants</span>
    </a>
    <a href="<?= Router::url('landlord/bills') ?>"
       class="bn-item <?= str_starts_with($currentUrl, 'landlord/bill') ? 'active' : '' ?>"
       aria-current="<?= str_starts_with($currentUrl, 'landlord/bill') ? 'page' : 'false' ?>"
       aria-label="Bills">
      <i class="fa-solid fa-file-invoice" aria-hidden="true"></i>
      <span>Bills</span>
    </a>
    <a href="<?= Router::url('landlord/profile') ?>"
       class="bn-item <?= str_starts_with($currentUrl, 'landlord/profile') ? 'active' : '' ?>"
       aria-current="<?= str_starts_with($currentUrl, 'landlord/profile') ? 'page' : 'false' ?>"
       aria-label="Profile">
      <i class="fa-solid fa-user" aria-hidden="true"></i>
      <span>Profile</span>
    </a>
  <?php else: ?>
    <a href="<?= Router::url('tenant/dashboard') ?>"
       class="bn-item <?= str_starts_with($currentUrl, 'tenant/dashboard') ? 'active' : '' ?>"
       aria-current="<?= str_starts_with($currentUrl, 'tenant/dashboard') ? 'page' : 'false' ?>"
       aria-label="Home">
      <i class="fa-solid fa-gauge" aria-hidden="true"></i>
      <span>Home</span>
    </a>

    <?php if (($userStatus ?? '') === 'approved'): ?>
      <a href="<?= Router::url('tenant/bills') ?>"
         class="bn-item <?= str_starts_with($currentUrl, 'tenant/bill') ? 'active' : '' ?>"
         aria-current="<?= str_starts_with($currentUrl, 'tenant/bill') ? 'page' : 'false' ?>"
         aria-label="Bills">
        <i class="fa-solid fa-file-invoice" aria-hidden="true"></i>
        <span>Bills</span>
      </a>
      <a href="<?= Router::url('tenant/complaints') ?>"
         class="bn-item <?= str_starts_with($currentUrl, 'tenant/complaint') ? 'active' : '' ?>"
         aria-current="<?= str_starts_with($currentUrl, 'tenant/complaint') ? 'page' : 'false' ?>"
         aria-label="Help">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        <span>Help</span>
      </a>
    <?php endif; ?>

    <a href="<?= Router::url('tenant/profile') ?>"
       class="bn-item <?= str_starts_with($currentUrl, 'tenant/profile') ? 'active' : '' ?>"
       aria-current="<?= str_starts_with($currentUrl, 'tenant/profile') ? 'page' : 'false' ?>"
       aria-label="Profile">
      <i class="fa-solid fa-user" aria-hidden="true"></i>
      <span>Profile</span>
    </a>
  <?php endif; ?>
</nav>

<style>
.bn-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  color: var(--color-text-muted);
  text-decoration: none;
  font-size: 0.65rem;
  font-weight: 600;
  transition: all 0.2s ease;
  flex: 1;
  padding: 8px 0;
}
.bn-item i {
  font-size: 1.1rem;
}
.bn-item.active {
  color: var(--color-brand);
}
.bn-item.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  width: 20px;
  height: 3px;
  background: var(--color-brand);
  border-radius: 10px 10px 0 0;
}
</style>