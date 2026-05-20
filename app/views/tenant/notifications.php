<?php
/**
 * BoardTrack — Notifications (tenant & landlord)
 */
$markAllUrl = $markAllUrl ?? 'tenant/notifications/mark-all-read';
?>
<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">Notifications</h1>
    <p class="dash-page-sub">Stay up to date with your latest activity and alerts.</p>
  </div>
  <?php if (!empty($notifications)): ?>
    <div class="dash-page-actions">
      <form action="<?= Router::url($markAllUrl) ?>" method="POST" class="js-mark-all-notifications">
        <button type="submit" class="btn btn-outline"><i class="fa-solid fa-check-double"></i> Mark All Read</button>
      </form>
    </div>
  <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
  <div class="empty-state-card">
    <i class="fa-solid fa-bell-slash"></i>
    <h3>No Notifications</h3>
    <p>You're all caught up! New alerts will appear here.</p>
  </div>
<?php else: ?>
  <div class="data-card">
    <div style="padding: 8px 0;">
      <?php foreach ($notifications as $notif): ?>
        <?php
          $link = trim($notif['link_url'] ?? '');
          $href = $link !== '' ? Router::url($link) : null;
        ?>
        <?php
          $isRead = !empty($notif['is_read']);
          $notifAttrs = ' data-notif-id="' . (int) $notif['id'] . '" data-notif-read="' . ($isRead ? '1' : '0') . '"';
        ?>
        <?php if ($href): ?>
        <a href="<?= htmlspecialchars($href) ?>" class="notif-item notif-link <?= $isRead ? '' : 'notif-unread' ?>"<?= $notifAttrs ?> style="text-decoration:none;color:inherit;">
        <?php else: ?>
        <div class="notif-item <?= $isRead ? '' : 'notif-unread' ?>"<?= $notifAttrs ?> role="button" tabindex="0" style="cursor:pointer;">
        <?php endif; ?>
          <div class="notif-icon-wrap">
            <i class="fa-solid <?= match($notif['type'] ?? 'general') {
              'payment'      => 'fa-peso-sign',
              'complaint'    => 'fa-exclamation-circle',
              'room'         => 'fa-door-open',
              'announcement' => 'fa-bullhorn',
              'billing'      => 'fa-file-invoice',
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
        <?php if ($href): ?></a><?php else: ?></div><?php endif; ?>
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
.notif-item:hover, .notif-link:hover { background: var(--gray-50); }
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
