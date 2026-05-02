<?php
/**
 * BoardTrack — Landlord: Waiting List
 * app/views/landlord/waitingList.php
 * Layout: landlord.php
 */
$queue          = $queue          ?? [];
$statistics     = $statistics     ?? [];
$availableRooms = $availableRooms ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Waiting List</h1>
      <p class="page-subtitle">Approved tenants awaiting room assignments (FIFO based on approval date).</p>
    </div>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-list-ol" style="margin-right:4px;"></i> Total Waiting</div>
    <div class="stat-value"><?= $statistics['total_waiting'] ?? count($queue) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-user" style="margin-right:4px;"></i> Single Preference</div>
    <div class="stat-value"><?= $statistics['single_preference'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-users" style="margin-right:4px;"></i> Shared Preference</div>
    <div class="stat-value"><?= $statistics['shared_preference'] ?? 0 ?></div>
  </div>
</div>

<!-- Waiting List Table -->
<div class="card">
  <?php if (empty($queue)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-list-ol"></i>
      <h3>No Tenants Waiting</h3>
      <p>The waiting list is currently empty. Approved tenants without a room will appear here.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Tenant</th>
            <th>Preference</th>
            <th>Approved On</th>
            <th>Wait Duration</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($queue as $index => $item): ?>
            <tr>
              <td>
                <span style="font-weight:700;font-size:0.95rem;color:var(--gray-900);"><?= $index + 1 ?></span>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:32px;height:32px;border-radius:var(--radius);background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:0.78rem;flex-shrink:0;">
                    <?= strtoupper(substr($item['tenant_name'], 0, 1)) ?>
                  </div>
                  <div>
                    <div class="td-name"><?= htmlspecialchars($item['tenant_name']) ?></div>
                    <div class="td-sub"><?= htmlspecialchars($item['tenant_email'] ?? $item['email'] ?? '') ?></div>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge <?= ($item['room_type_preference'] ?? '') === 'single' ? 'badge-single' : 'badge-shared' ?>">
                  <?= ucfirst(htmlspecialchars($item['room_type_preference'] ?? '—')) ?>
                </span>
              </td>
              <td style="font-size:0.82rem;color:var(--gray-500);">
                <?= date('M j, Y', strtotime($item['approved_at'] ?? $item['created_at'] ?? 'now')) ?>
              </td>
              <td style="font-size:0.82rem;color:var(--gray-500);">
                <?= (int)($item['wait_days'] ?? 0) ?> days
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:4px;">
                  <a href="<?= Router::url('landlord/view-tenant/' . $item['tenant_id']) ?>" class="btn btn-secondary btn-sm btn-icon" title="View Profile">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                  <button type="button" class="btn btn-primary btn-sm" onclick="openAssignModal(<?= (int)$item['id'] ?>, '<?= htmlspecialchars(addslashes($item['tenant_name'])) ?>', '<?= htmlspecialchars($item['room_type_preference'] ?? '') ?>')" title="Assign Room">
                    <i class="fa-solid fa-plus"></i> Assign
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Assign Room Modal -->
<div class="modal-overlay" id="assignModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Assign Room</span>
      <button class="modal-close" onclick="closeModal('assignModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/assign-from-waiting') ?>" method="POST">
      <input type="hidden" name="waiting_id" id="assignWaitingId">
      <div class="modal-body">
        <p style="margin:0 0 16px;color:var(--gray-600);">Assign <strong id="assignTenantName"></strong> to a <strong id="assignPreference"></strong> room.</p>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Select Available Room <span class="req">*</span></label>
          <select name="room_id" id="roomSelect" class="form-select" required>
            <option value="">— Select Room —</option>
            <?php foreach ($availableRooms as $r): ?>
              <option value="<?= $r['id'] ?>">
                Room <?= htmlspecialchars($r['room_number']) ?> (<?= ucfirst(htmlspecialchars($r['room_type'])) ?>, <?= (int)$r['max_occupants'] - (int)($r['actual_occupants'] ?? 0) ?> spots left)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('assignModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Assign Room</button>
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
function openAssignModal(waitingId, tenantName, preference) {
  document.getElementById('assignWaitingId').value = waitingId;
  document.getElementById('assignTenantName').textContent = tenantName;
  document.getElementById('assignPreference').textContent = preference;
  openModal('assignModal');
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
