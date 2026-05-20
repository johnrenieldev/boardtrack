<?php
/**
 * BoardTrack — Landlord: Audit Log
 * app/views/landlord/auditlog.php
 * Layout: landlord.php
 */
$logs       = $logs       ?? [];
$filters    = $filters    ?? [];
$pagination = $pagination ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Audit Log</h1>
      <p class="page-subtitle">Read-only record of all critical system actions.</p>
    </div>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px;padding:14px 20px;">
  <form method="GET" action="<?= Router::url('landlord/auditLog') ?>" class="filter-bar" style="margin-bottom:0;">
    <input type="hidden" name="url" value="landlord/auditLog">
    <select name="action" class="form-select">
      <option value="">All Actions</option>
      <option value="login" <?= ($filters['action'] ?? '') === 'login' ? 'selected' : '' ?>>Login</option>
      <option value="logout" <?= ($filters['action'] ?? '') === 'logout' ? 'selected' : '' ?>>Logout</option>
      <option value="create" <?= ($filters['action'] ?? '') === 'create' ? 'selected' : '' ?>>Create</option>
      <option value="update" <?= ($filters['action'] ?? '') === 'update' ? 'selected' : '' ?>>Update</option>
      <option value="delete" <?= ($filters['action'] ?? '') === 'delete' ? 'selected' : '' ?>>Delete</option>
      <option value="approve" <?= ($filters['action'] ?? '') === 'approve' ? 'selected' : '' ?>>Approve</option>
      <option value="reject" <?= ($filters['action'] ?? '') === 'reject' ? 'selected' : '' ?>>Reject</option>
    </select>
    <select name="entity" class="form-select">
      <option value="">All Entities</option>
      <option value="tenant" <?= ($filters['entity'] ?? '') === 'tenant' ? 'selected' : '' ?>>Tenant</option>
      <option value="room" <?= ($filters['entity'] ?? '') === 'room' ? 'selected' : '' ?>>Room</option>
      <option value="bill" <?= ($filters['entity'] ?? '') === 'bill' ? 'selected' : '' ?>>Bill</option>
      <option value="payment" <?= ($filters['entity'] ?? '') === 'payment' ? 'selected' : '' ?>>Payment</option>
      <option value="complaint" <?= ($filters['entity'] ?? '') === 'complaint' ? 'selected' : '' ?>>Complaint</option>
    </select>
    <input type="date" name="date_from" class="form-input" placeholder="From" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" style="min-width:140px;">
    <input type="date" name="date_to" class="form-input" placeholder="To" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" style="min-width:140px;">
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= Router::url('landlord/auditLog') ?>" class="btn btn-secondary btn-sm">Clear</a>
  </form>
</div>

<!-- Audit Log Table -->
<div class="card">
  <?php if (empty($logs)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-scroll"></i>
      <h3>No audit logs found</h3>
      <p>No log entries match the current filters.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table">
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>User</th>
            <th>Action</th>
            <th>Entity</th>
            <th>Description</th>
            <th>IP Address</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logs as $log): ?>
            <tr>
              <td>
                <div class="td-name" style="font-size:0.82rem;"><?= date('g:i A', strtotime($log['created_at'])) ?></div>
                <div class="td-sub"><?= date('M j, Y', strtotime($log['created_at'])) ?></div>
              </td>
              <td>
                <div class="td-name"><?= htmlspecialchars($log['user_name'] ?? 'System') ?></div>
                <div class="td-sub"><?= ucfirst($log['user_role'] ?? 'N/A') ?></div>
              </td>
              <td>
                <?php
                  $actionBadge = match($log['action']) {
                    'create'  => 'badge-active',
                    'update'  => 'badge-pv',
                    'delete'  => 'badge-rejected',
                    'approve' => 'badge-active',
                    'reject'  => 'badge-rejected',
                    'login'   => 'badge-waiting',
                    'logout'  => 'badge-normal',
                    default   => 'badge-normal'
                  };
                  $actionIcon = match($log['action']) {
                    'create'  => 'fa-plus',
                    'update'  => 'fa-pen',
                    'delete'  => 'fa-trash',
                    'approve' => 'fa-check',
                    'reject'  => 'fa-xmark',
                    'login'   => 'fa-right-to-bracket',
                    'logout'  => 'fa-right-from-bracket',
                    default   => 'fa-circle'
                  };
                ?>
                <span class="badge <?= $actionBadge ?>">
                  <i class="fa-solid <?= $actionIcon ?>"></i> <?= ucfirst($log['action']) ?>
                </span>
              </td>
              <td>
                <span style="background:var(--gray-100);padding:3px 8px;border-radius:4px;font-size:0.78rem;font-weight:500;color:var(--gray-600);">
                  <?= ucfirst($log['entity_type']) ?><?php if ($log['entity_id']): ?> #<?= $log['entity_id'] ?><?php endif; ?>
                </span>
              </td>
              <td>
                <?php
                  // Description is stored inside new_values JSON as _description
                  $desc = '';
                  if (!empty($log['new_values'])) {
                      $nv = json_decode($log['new_values'], true);
                      $desc = $nv['_description'] ?? '';
                  }
                  if (empty($desc)) {
                      $desc = ucfirst(str_replace('_', ' ', $log['action']));
                  }
                ?>
                <div style="color:var(--gray-600);line-height:1.5;margin-bottom:4px;font-size:0.82rem;"><?= htmlspecialchars($desc) ?></div>
                <?php if (!empty($log['old_values']) || !empty($log['new_values'])): ?>
                  <button type="button" class="btn btn-secondary btn-sm" onclick='showDetailsModal(<?= htmlspecialchars(json_encode($log)) ?>)'>
                    <i class="fa-solid fa-code"></i> View Changes
                  </button>
                <?php endif; ?>
              </td>
              <td>
                <code style="background:var(--gray-100);padding:3px 6px;border-radius:4px;font-size:0.78rem;color:var(--gray-500);"><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></code>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Pagination -->
<?php if (!empty($pagination) && ($pagination['total_pages'] ?? 1) > 1): ?>
  <div class="pagination">
    <?php if ($pagination['current_page'] > 1): ?>
      <a href="<?= Router::url('landlord/auditLog') ?>&page=<?= $pagination['current_page'] - 1 ?>" class="page-btn">&laquo; Previous</a>
    <?php endif; ?>
    <span style="color:var(--gray-500);font-size:0.82rem;">Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?></span>
    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
      <a href="<?= Router::url('landlord/auditLog') ?>&page=<?= $pagination['current_page'] + 1 ?>" class="page-btn">Next &raquo;</a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- Details Modal -->
<div class="modal-overlay" id="detailsModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">Audit Log Details</span>
      <button class="modal-close" onclick="closeModal('detailsModal')">&times;</button>
    </div>
    <div class="modal-body">
      <div style="margin-bottom:16px;">
        <div style="font-size:0.78rem;font-weight:600;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px;">Old Values</div>
        <pre id="oldValues" style="background:var(--gray-800);color:#e2e8f0;padding:14px;border-radius:var(--radius);font-size:0.82rem;overflow-x:auto;max-height:200px;overflow-y:auto;margin:0;"></pre>
      </div>
      <div>
        <div style="font-size:0.78rem;font-weight:600;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px;">New Values</div>
        <pre id="newValues" style="background:var(--gray-800);color:#e2e8f0;padding:14px;border-radius:var(--radius);font-size:0.82rem;overflow-x:auto;max-height:200px;overflow-y:auto;margin:0;"></pre>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('detailsModal')">Close</button>
    </div>
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
function showDetailsModal(log) {
  try {
    var oldVal = log.old_values ? JSON.stringify(JSON.parse(log.old_values), null, 2) : 'No old values';
  } catch(e) {
    var oldVal = log.old_values || 'No old values';
  }
  try {
    var newVal = log.new_values ? JSON.stringify(JSON.parse(log.new_values), null, 2) : 'No new values';
  } catch(e) {
    var newVal = log.new_values || 'No new values';
  }
  document.getElementById('oldValues').textContent = oldVal;
  document.getElementById('newValues').textContent = newVal;
  openModal('detailsModal');
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
