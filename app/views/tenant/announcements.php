<?php
/**
 * BoardTrack — Tenant: Announcements
 * app/views/tenant/announcements.php
 * Layout: tenant.php
 */
$announcements = $announcements ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Announcements</h1>
      <p class="page-subtitle">Notices and updates from your landlord.</p>
    </div>
  </div>
</div>

<?php if (empty($announcements)): ?>
  <div class="card">
    <div class="empty-state">
      <i class="fa-solid fa-bullhorn"></i>
      <h3>No Announcements</h3>
      <p>There are no announcements at this time. Check back later.</p>
    </div>
  </div>
<?php else: ?>
  <div style="display:flex;flex-direction:column;gap:12px;">
    <?php foreach ($announcements as $announcement):
      $priority = $announcement['priority'] ?? 'normal';
    ?>
      <div class="card" style="padding:20px 24px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:10px;">
          <div style="display:flex;align-items:flex-start;gap:12px;flex:1;">
            <div class="notif-icon announcement" style="flex-shrink:0;margin-top:2px;">
              <?php if ($priority === 'high'): ?>
                <i class="fa-solid fa-circle-exclamation"></i>
              <?php else: ?>
                <i class="fa-solid fa-bullhorn"></i>
              <?php endif; ?>
            </div>
            <div style="flex:1;min-width:0;">
              <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <h3 style="font-weight:600;font-size:0.95rem;color:var(--gray-900);margin:0;"><?= htmlspecialchars($announcement['title']) ?></h3>
                <?php if ($priority === 'high'): ?>
                  <span class="badge badge-urgent">Urgent</span>
                <?php elseif ($priority === 'medium'): ?>
                  <span class="badge badge-high">Important</span>
                <?php endif; ?>
              </div>
              <div style="font-size:0.78rem;color:var(--gray-400);margin-top:3px;">
                Posted <?= date('M d, Y', strtotime($announcement['created_at'])) ?>
              </div>
            </div>
          </div>
        </div>

        <div style="font-size:0.85rem;color:var(--gray-600);line-height:1.7;margin-left:44px;">
          <?= nl2br(htmlspecialchars($announcement['content'])) ?>
        </div>

        <?php if (!empty($announcement['event_date'])): ?>
          <div style="display:inline-flex;align-items:center;gap:6px;margin-top:12px;margin-left:44px;padding:6px 12px;background:var(--primary-light);border-radius:var(--radius);font-size:0.82rem;color:var(--primary);font-weight:500;">
            <i class="fa-solid fa-calendar-day"></i>
            Event: <?= date('F j, Y', strtotime($announcement['event_date'])) ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
