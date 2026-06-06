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
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-bullhorn"></i></div>
      <div class="stat-label">Total Posts</div>
    </div>
    <div class="stat-value"><?= $statistics['total'] ?? 0 ?></div>
    <div class="stat-footer">Across <span>all time</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-circle-check"></i></div>
      <div class="stat-label">Active</div>
    </div>
    <div class="stat-value"><?= $statistics['active'] ?? 0 ?></div>
    <div class="stat-footer">Currently <span>visible</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-eye-slash"></i></div>
      <div class="stat-label">Inactive</div>
    </div>
    <div class="stat-value"><?= $statistics['expired'] ?? 0 ?></div>
    <div class="stat-footer">Archived <span>notices</span></div>
  </div>
</div>

<!-- Announcements Table -->
<div class="card">
  <?php if (empty($announcements)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-bullhorn"></i>
      <h3>No Announcements Yet</h3>
      <p>Create your first announcement to notify all tenants.</p>
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
              <td data-label="Title">
                <div class="td-name"><?= htmlspecialchars($a['title']) ?></div>
                <div class="td-sub">By <?= htmlspecialchars($a['author_name']) ?></div>
              </td>
              <td data-label="Priority">
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
              <td data-label="Posted" style="font-size:0.82rem;color:var(--gray-500);"><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
              <td data-label="Event Date" style="font-size:0.82rem;color:var(--gray-500);"><?= $a['event_date'] ? date('M j, Y', strtotime($a['event_date'])) : '—' ?></td>
              <td data-label="Status">
                <span class="badge <?= $a['is_active'] ? 'badge-active' : 'badge-normal' ?>">
                  <?= $a['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td data-label="Actions">
                <div style="display:flex;align-items:center;gap:4px;">
                  <button type="button" class="btn btn-secondary btn-sm btn-icon" title="Edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($a)) ?>)">
                    <i class="fa-solid fa-pen"></i>
                  </button>
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

<!-- Edit Announcement Modal -->
<div class="modal-overlay" id="editModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">Edit Announcement</span>
      <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/update-announcement') ?>" method="POST">
      <div class="modal-body">
        <input type="hidden" name="announcement_id" id="edit_announcement_id" value="">
        <div class="form-group">
          <label class="form-label">Title <span class="req">*</span></label>
          <input type="text" name="title" id="edit_title" class="form-input" required placeholder="e.g., Monthly Rent Reminder">
        </div>
        <div class="form-group">
          <label class="form-label">Content <span class="req">*</span></label>
          <textarea name="content" id="edit_content" class="form-textarea" rows="5" required placeholder="Write your announcement here..."></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Priority</label>
            <select name="priority" id="edit_priority" class="form-select">
              <option value="normal">Normal</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Event Date (Optional)</label>
            <input type="date" name="event_date" id="edit_event_date" class="form-input">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Announcement</button>
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
function openEditModal(announcement) {
  document.getElementById('edit_announcement_id').value = announcement.id;
  document.getElementById('edit_title').value = announcement.title;
  document.getElementById('edit_content').value = announcement.content;
  document.getElementById('edit_priority').value = announcement.priority;
  document.getElementById('edit_event_date').value = announcement.event_date || '';
  openModal('editModal');
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

<style>
/* Center column headers and data */
.bt-table th {
  text-align: center !important;
}

.bt-table td {
  text-align: center !important;
}

/* Keep first column (Title) left-aligned */
.bt-table th:first-child,
.bt-table td:first-child {
  text-align: left !important;
}

/* Mobile responsive styles for announcements table */
@media (max-width: 767px) {
  .card {
    max-height: calc(100vh - 280px) !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    -webkit-overflow-scrolling: touch;
  }
  
  .table-wrap {
    overflow-x: visible !important;
    overflow-y: visible !important;
    width: 100% !important;
    max-width: 100% !important;
  }
  
  .bt-table {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    table-layout: auto !important;
  }
  
  .bt-table tbody tr {
    width: 100% !important;
    max-width: 100% !important;
    margin-bottom: 16px !important;
  }
  
  .bt-table tbody tr:last-child {
    margin-bottom: 20px !important;
  }
  
  .bt-table th,
  .bt-table td {
    width: auto !important;
    min-width: 0 !important;
    max-width: 100% !important;
  }
  
  /* Title - full width with grey background */
  .bt-table td:first-child {
    padding: 14px 16px !important;
    background: var(--gray-100) !important;
    text-align: left !important;
  }
  
  /* Other fields - label on left, value on right */
  .bt-table td[data-label]:not(:first-child)::before {
    width: 110px !important;
    min-width: 110px !important;
    flex-shrink: 0 !important;
    padding-right: 0 !important;
  }
  
  .bt-table td[data-label]:not(:first-child) {
    display: flex !important;
    justify-content: flex-start !important;
    align-items: center !important;
    text-align: left !important;
    gap: 12px !important;
    padding: 10px 14px !important;
  }
  
  /* Priority badge - wrap tightly */
  .bt-table td[data-label="Priority"] .badge {
    display: inline-flex !important;
    width: auto !important;
    max-width: fit-content !important;
  }
  
  /* Status badge - wrap tightly */
  .bt-table td[data-label="Status"] .badge {
    display: inline-flex !important;
    width: auto !important;
    max-width: fit-content !important;
  }
  
  /* Actions - center the buttons */
  .bt-table td[data-label="Actions"] {
    justify-content: center !important;
    text-align: center !important;
  }
  
  .bt-table td[data-label="Actions"]::before {
    display: none !important;
  }
  
  .bt-table td[data-label="Actions"] > div {
    width: 100% !important;
    justify-content: center !important;
    gap: 8px !important;
  }
  
  /* Make action buttons equal width on mobile */
  .bt-table td[data-label="Actions"] > div > button,
  .bt-table td[data-label="Actions"] > div > form {
    flex: 1 1 0 !important;
    min-width: 0 !important;
  }
  
  .bt-table td[data-label="Actions"] > div > button,
  .bt-table td[data-label="Actions"] > div > form > button {
    width: 100% !important;
    margin: 0 !important;
  }
  
  .bt-table td[data-label="Actions"] > div > form {
    display: flex !important;
    width: 100% !important;
  }
  
  /* Prevent text wrapping issues */
  .bt-table,
  .bt-table th,
  .bt-table td,
  .bt-table th *,
  .bt-table td * {
    word-break: normal !important;
    overflow-wrap: normal !important;
    word-wrap: normal !important;
    white-space: normal !important;
  }
  
  .bt-table td[data-label]::before {
    white-space: nowrap !important;
    word-break: keep-all !important;
  }
  
  /* Custom scrollbar for card */
  .card::-webkit-scrollbar {
    width: 6px;
  }
  
  .card::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
  }
  
  .card::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
  }
  
  .card::-webkit-scrollbar-thumb:hover {
    background: #555;
  }
}
</style>
