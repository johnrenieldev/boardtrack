<?php
/**
 * BoardTrack — Tenant: Notifications
 */
?>
<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">Notifications</h1>
    <p class="dash-page-sub">Stay up to date with your latest activity and alerts.</p>
  </div>
  <?php if (!empty($notifications)): ?>
    <div class="dash-page-actions">
      <form action="<?= Router::url('tenant/mark-all-notifications-read') ?>" method="POST">
        <button type="submit" class="btn btn-outline"><i class="fa-solid fa-check-double"></i> Mark All Read</button>
      </form>
    </div>
  <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
  <div class="empty-state-card">
    <i class="fa-solid fa-bell-slash"></i>
    <h3>No Notifications</h3>
    <p>You're all caught up! Notifications from your landlord will appear here.</p>
  </div>
<?php else: ?>
  <div class="data-card">
    <div style="padding: 8px 0;">
      <?php foreach ($notifications as $notif): ?>
        <div class="notif-item <?= $notif['is_read'] ? '' : 'notif-unread' ?>">
          <div class="notif-icon-wrap">
            <i class="fa-solid <?= match($notif['type'] ?? 'general') {
              'payment'      => 'fa-peso-sign',
              'complaint'    => 'fa-exclamation-circle',
              'room'         => 'fa-door-open',
              'announcement' => 'fa-bullhorn',
              default        => 'fa-bell'
            } ?>"></i>
          </div>
          <div class="notif-body">
            <div class="notif-title"><?= htmlspecialchars($notif['title']) ?></div>
            <div class="notif-msg"><?= htmlspecialchars($notif['message']) ?></div>
            <div class="notif-time"><?= date('M j, Y g:i A', strtotime($notif['created_at'])) ?></div>
          </div>
          <?php if (!$notif['is_read']): ?>
            <span class="notif-dot"></span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<style>
.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 18px 24px;
  border-bottom: 1px solid var(--gray-100);
  position: relative;
  transition: background 0.15s;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: var(--gray-50); }
.notif-unread { background: #F5F3FF; }
.notif-icon-wrap {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  background: var(--primary-light);
  color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
  margin-top: 2px;
}
.notif-body { flex: 1; min-width: 0; }
.notif-title {
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--gray-800);
  margin-bottom: 4px;
}
.notif-msg {
  font-size: 0.83rem;
  color: var(--gray-500);
  line-height: 1.5;
}
.notif-time {
  font-size: 0.75rem;
  color: var(--gray-300);
  margin-top: 6px;
}
.notif-dot {
  width: 8px;
  height: 8px;
  background: var(--primary);
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 6px;
}
</style>
