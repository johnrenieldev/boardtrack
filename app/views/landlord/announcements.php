<?php
/**
 * BoardTrack — Landlord: Announcements
 * app/views/landlord/announcements.php
 * Layout: landlord.php
 */
$announcements = $announcements ?? [];
$statistics    = $statistics    ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Announcements</h1>
      <p class="page-subtitle">Post notices visible to all active tenants.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('createModal')">
      <i class="fa-solid fa-plus"></i> New Announcement
    </button>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-bullhorn" style="margin-right:4px;"></i> Total</div>
    <div class="stat-value"><?= $statistics['total'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-circle-check" style="margin-right:4px;"></i> Active</div>
    <div class="stat-value"><?= $statistics['active'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-eye-slash" style="margin-right:4px;"></i> Inactive</div>
    <div class="stat-value"><?= $statistics['expired'] ?? 0 ?></div>
  </div>
</div>

<!-- Announcements Table -->
<div class="card">
  <?php if (empty($announcements)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-bullhorn"></i>
      <h3>No Announcements Yet</h3>
      <p>Create your first announcement to notify all tenants.</p>
      <button type="button" class="btn btn-primary" style="margin-top:12px;" onclick="openModal('createModal')">
        <i class="fa-solid fa-plus"></i> Create Announcement
      </button>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Priority</th>
            <th>Posted</th>
            <th>Event Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($announcements as $a): ?>
            <tr>
              <td>
                <div class="td-name"><?= htmlspecialchars($a['title']) ?></div>
                <div class="td-sub">By <?= htmlspecialchars($a['author_name']) ?></div>
              </td>
              <td>
                <?php
                  $prBadge = match($a['priority']) {
                    'urgent' => 'badge-urgent',
                    'high'   => 'badge-high',
                    default  => 'badge-normal'
                  };
                ?>
                <span class="badge <?= $prBadge ?>">
                  <?= ucfirst($a['priority']) ?>
                </span>
              </td>
              <td style="font-size:0.82rem;color:var(--gray-500);"><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
              <td style="font-size:0.82rem;color:var(--gray-500);"><?= $a['event_date'] ? date('M j, Y', strtotime($a['event_date'])) : '—' ?></td>
              <td>
                <span class="badge <?= $a['is_active'] ? 'badge-active' : 'badge-normal' ?>">
                  <?= $a['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:4px;">
                  <a href="<?= Router::url('landlord/edit-announcement/' . $a['id']) ?>" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                    <i class="fa-solid fa-pen"></i>
                  </a>
                  <form action="<?= Router::url('landlord/toggle-announcement') ?>" method="POST" style="display:inline;">
                    <input type="hidden" name="announcement_id" value="<?= $a['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-icon <?= $a['is_active'] ? 'btn-secondary' : 'btn-success' ?>" title="<?= $a['is_active'] ? 'Deactivate' : 'Activate' ?>">
                      <i class="fa-solid <?= $a['is_active'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                    </button>
                  </form>
                  <form action="<?= Router::url('landlord/delete-announcement') ?>" method="POST" style="display:inline;" data-confirm="Are you sure you want to permanently delete this announcement?">
                    <input type="hidden" name="announcement_id" value="<?= $a['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Create Announcement Modal -->
<div class="modal-overlay" id="createModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">New Announcement</span>
      <button class="modal-close" onclick="closeModal('createModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/create-announcement') ?>" method="POST">
      <div class="modal-body">
        <input type="hidden" name="announcement_id" value="">
        <div class="form-group">
          <label class="form-label">Title <span class="req">*</span></label>
          <input type="text" name="title" class="form-input" required placeholder="e.g., Monthly Rent Reminder">
        </div>
        <div class="form-group">
          <label class="form-label">Content <span class="req">*</span></label>
          <textarea name="content" class="form-textarea" rows="5" required placeholder="Write your announcement here..."></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Priority</label>
            <select name="priority" class="form-select">
              <option value="normal" selected>Normal</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Event Date (Optional)</label>
            <input type="date" name="event_date" class="form-input">
          </div>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Expires At (Optional)</label>
          <input type="datetime-local" name="expires_at" class="form-input">
          <span class="form-help">Leave blank for no expiration.</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('createModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Post Announcement</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) {
  document.getElementById(id).style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
  document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay').forEach(function(m) { m.style.display = 'none'; });
    document.body.style.overflow = '';
  }
});
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.style.display = 'none';
    document.body.style.overflow = '';
  }
});
</script>
