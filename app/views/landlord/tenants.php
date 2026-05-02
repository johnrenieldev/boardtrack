<?php
/**
 * BoardTrack — Landlord: Tenants
 * app/views/landlord/tenants.php
 * Layout: landlord.php
 */
$tenants        = $tenants        ?? [];
$filters        = $filters        ?? [];
$availableRooms = $availableRooms ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Tenants</h1>
      <p class="page-subtitle">Manage tenant registrations, approvals, and room assignments.</p>
    </div>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-users" style="margin-right:4px;"></i> Total</div>
    <div class="stat-value"><?= count($tenants) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-clock" style="margin-right:4px;"></i> Pending</div>
    <div class="stat-value"><?= count(array_filter($tenants, fn($t) => $t['user_status'] === 'pending')) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-circle-check" style="margin-right:4px;"></i> Active</div>
    <div class="stat-value"><?= count(array_filter($tenants, fn($t) => $t['user_status'] === 'active')) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label"><i class="fa-solid fa-list-ol" style="margin-right:4px;"></i> Waiting List</div>
    <div class="stat-value"><?= count(array_filter($tenants, fn($t) => $t['user_status'] === 'waiting_list')) ?></div>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px;padding:14px 20px;">
  <form method="GET" action="<?= Router::url('landlord/tenants') ?>" class="filter-bar" style="margin-bottom:0;">
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="waiting_list" <?= ($filters['status'] ?? '') === 'waiting_list' ? 'selected' : '' ?>>Waiting List</option>
      <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
      <option value="moved_out" <?= ($filters['status'] ?? '') === 'moved_out' ? 'selected' : '' ?>>Moved Out</option>
    </select>
    <input type="text" name="search" class="form-input" placeholder="Search name or email..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= Router::url('landlord/tenants') ?>" class="btn btn-secondary btn-sm">Clear</a>
  </form>
</div>

<!-- Tenants Table -->
<div class="card">
  <?php if (empty($tenants)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-users"></i>
      <h3>No tenants found</h3>
      <p>No tenants match the current filters.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table">
        <thead>
          <tr>
            <th>Tenant</th>
            <th>Status</th>
            <th>Room Preference</th>
            <th>Room Assigned</th>
            <th>Registered</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tenants as $tenant): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:32px;height:32px;border-radius:var(--radius);background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:0.78rem;flex-shrink:0;">
                    <?= strtoupper(substr($tenant['name'], 0, 1)) ?>
                  </div>
                  <div>
                    <div class="td-name"><?= htmlspecialchars($tenant['name']) ?></div>
                    <div class="td-sub"><?= htmlspecialchars($tenant['email']) ?></div>
                  </div>
                </div>
              </td>
              <td>
                <?php
                  $sBadge = match($tenant['user_status']) {
                    'active'       => 'badge-active',
                    'pending'      => 'badge-pending',
                    'waiting_list' => 'badge-waiting',
                    'rejected'     => 'badge-rejected',
                    'moved_out'    => 'badge-normal',
                    default        => 'badge-normal'
                  };
                ?>
                <span class="badge <?= $sBadge ?>">
                  <?= ucfirst(str_replace('_', ' ', $tenant['user_status'])) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($tenant['room_type_preference'] ?? '—') ?></td>
              <td><?= !empty($tenant['room_number']) ? htmlspecialchars($tenant['room_number']) : '—' ?></td>
              <td style="font-size:0.82rem;color:var(--gray-500);"><?= date('M j, Y', strtotime($tenant['registered_at'])) ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:4px;">
                  <a href="<?= Router::url('landlord/view-tenant/' . $tenant['id']) ?>" class="btn btn-secondary btn-sm btn-icon" title="View Details">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                  <?php if ($tenant['user_status'] === 'pending'): ?>
                    <button type="button" class="btn btn-success btn-sm btn-icon" onclick="showApproveModal(<?= $tenant['id'] ?>, '<?= htmlspecialchars(addslashes($tenant['name'])) ?>')" title="Approve">
                      <i class="fa-solid fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="showRejectModal(<?= $tenant['id'] ?>)" title="Reject">
                      <i class="fa-solid fa-xmark"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Approve Modal -->
<div class="modal-overlay" id="approveModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Approve Tenant</span>
      <button class="modal-close" onclick="closeModal('approveModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/approve-tenant') ?>" method="POST">
      <input type="hidden" name="tenant_id" id="approveTenantId">
      <div class="modal-body">
        <p style="margin:0 0 16px;color:var(--gray-600);">You are about to approve <strong id="approveTenantName"></strong>.</p>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Assign Room (Optional)</label>
          <select name="room_id" class="form-select">
            <option value="">— Add to Waiting List —</option>
            <?php foreach ($availableRooms as $r): ?>
              <option value="<?= $r['id'] ?>">Room <?= htmlspecialchars($r['room_number']) ?> (<?= ucfirst($r['room_type']) ?>, <?= $r['max_occupants'] - ($r['actual_occupants'] ?? 0) ?> spots)</option>
            <?php endforeach; ?>
          </select>
          <span class="form-help">Leave empty to add tenant to waiting list instead.</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('approveModal')">Cancel</button>
        <button type="submit" class="btn btn-success">Approve Tenant</button>
      </div>
    </form>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal-overlay" id="rejectModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Reject Tenant</span>
      <button class="modal-close" onclick="closeModal('rejectModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/reject-tenant') ?>" method="POST">
      <input type="hidden" name="tenant_id" id="rejectTenantId">
      <div class="modal-body">
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Rejection Reason <span class="req">*</span></label>
          <textarea name="reason" class="form-textarea" rows="3" required placeholder="Explain why this application is being rejected..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('rejectModal')">Cancel</button>
        <button type="submit" class="btn btn-danger">Reject Tenant</button>
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
function showApproveModal(tenantId, tenantName) {
  document.getElementById('approveTenantId').value = tenantId;
  document.getElementById('approveTenantName').textContent = tenantName;
  openModal('approveModal');
}
function showRejectModal(tenantId) {
  document.getElementById('rejectTenantId').value = tenantId;
  openModal('rejectModal');
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
