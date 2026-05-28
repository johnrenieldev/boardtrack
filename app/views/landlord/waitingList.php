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
  <div class="stat-card <?= ($statistics['total_waiting'] ?? count($queue)) > 0 ? 'urgent' : '' ?>">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-list-ol"></i></div>
      <div class="stat-label">Total Waiting</div>
    </div>
    <div class="stat-value"><?= $statistics['total_waiting'] ?? count($queue) ?></div>
    <div class="stat-footer">Awaiting <span>room assignment</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-user"></i></div>
      <div class="stat-label">Single Preference</div>
    </div>
    <div class="stat-value"><?= $statistics['single_preference'] ?? 0 ?></div>
    <div class="stat-footer">Preferred <span>private rooms</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-users"></i></div>
      <div class="stat-label">Shared Preference</div>
    </div>
    <div class="stat-value"><?= $statistics['shared_preference'] ?? 0 ?></div>
    <div class="stat-footer">Preferred <span>shared rooms</span></div>
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
              <td data-label="#">
                <span style="font-weight:700;font-size:0.95rem;color:var(--color-text-primary);"><?= $index + 1 ?></span>
              </td>
              <td data-label="Tenant">
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:32px;height:32px;border-radius:var(--radius);background:var(--primary);color:var(--color-surface);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:0.78rem;flex-shrink:0;">
                    <?= strtoupper(substr($item['tenant_name'], 0, 1)) ?>
                  </div>
                  <div>
                    <div class="td-name"><?= htmlspecialchars($item['tenant_name']) ?></div>
                    <div class="td-sub"><?= htmlspecialchars($item['tenant_email'] ?? $item['email'] ?? '') ?></div>
                  </div>
                </div>
              </td>
              <td data-label="Preference">
                <span class="badge <?= ($item['room_type_preference'] ?? '') === 'single' ? 'badge-single' : 'badge-shared' ?>">
                  <?= ucfirst(htmlspecialchars($item['room_type_preference'] ?? '—')) ?>
                </span>
              </td>
              <td data-label="Approved" style="font-size:0.82rem;color:var(--color-text-secondary);">
                <?= date('M j, Y', strtotime($item['approved_at'] ?? $item['created_at'] ?? 'now')) ?>
              </td>
              <td data-label="Wait Time" style="font-size:0.82rem;color:var(--color-text-secondary);">
                <?= (int)($item['wait_days'] ?? 0) ?> days
              </td>
              <td data-label="Actions">
                <div style="display:flex;align-items:center;gap:4px;">
                  <a href="<?= Router::url('landlord/view-tenant/' . $item['tenant_id']) ?>" class="btn btn-secondary btn-sm btn-icon" title="View Profile">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                  <button type="button" class="btn btn-primary btn-sm" onclick="openAssignModal(<?= (int)$item['id'] ?>, '<?= htmlspecialchars(addslashes($item['tenant_name'])) ?>', '<?= htmlspecialchars($item['room_type_preference'] ?? '') ?>', <?= (int)($item['air_conditioned_preference'] ?? 0) ?>)" title="Assign Room">
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
        <p style="margin:0 0 16px;color:var(--color-text-secondary);">Assign <strong id="assignTenantName"></strong> to a <strong id="assignPreference"></strong> room.</p>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label" style="display:block;margin-bottom:6px;">Air-conditioning Filter</label>
          <select id="assignRoomAirconFilter" class="form-select" style="margin-bottom:12px;">
            <option value="match" selected>Match tenant preference</option>
            <option value="any">Any room</option>
            <option value="1">Air-conditioned only</option>
            <option value="0">Non-air-conditioned only</option>
          </select>

          <label class="form-label">Select Available Room <span class="req">*</span></label>
          <select name="room_id" id="roomSelect" class="form-select" required>
            <option value="">— Select Room —</option>
            <?php foreach ($availableRooms as $r): ?>
              <option
                value="<?= $r['id'] ?>"
                data-aircon="<?= !empty($r['air_conditioned']) ? '1' : '0' ?>"
              >
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
function openAssignModal(waitingId, tenantName, preference, tenantAirconPreference) {
  document.getElementById('assignWaitingId').value = waitingId;
  document.getElementById('assignTenantName').textContent = tenantName;
  document.getElementById('assignPreference').textContent = preference;
  var filterEl = document.getElementById('assignRoomAirconFilter');
  if (filterEl) {
    filterEl.dataset.tenantAirconPref = (tenantAirconPreference ? '1' : '0');
    filterEl.value = 'match';
    applyAssignRoomAirconFilter();
  }
  openModal('assignModal');
}
function applyAssignRoomAirconFilter() {
  var filterEl = document.getElementById('assignRoomAirconFilter');
  var selectEl = document.getElementById('roomSelect');
  if (!filterEl || !selectEl) return;

  var filterValue = filterEl.value; // match | any | 1 | 0
  var tenantPref = (filterEl.dataset.tenantAirconPref === '1') ? 1 : 0;

  for (var i = 0; i < selectEl.options.length; i++) {
    var opt = selectEl.options[i];
    if (!opt.value) continue; // keep placeholder

    var optAircon = (opt.getAttribute('data-aircon') === '1') ? 1 : 0;
    var show = true;

    if (filterValue === 'any') {
      show = true;
    } else if (filterValue === 'match') {
      show = (optAircon === tenantPref);
    } else if (filterValue === '1') {
      show = (optAircon === 1);
    } else if (filterValue === '0') {
      show = (optAircon === 0);
    }

    opt.hidden = !show;
  }

  // If currently selected option is hidden, clear it.
  if (selectEl.value) {
    var selectedOpt = selectEl.selectedOptions && selectEl.selectedOptions[0] ? selectEl.selectedOptions[0] : null;
    if (selectedOpt && selectedOpt.hidden) {
      selectEl.value = '';
    }
  }
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

document.addEventListener('change', function(e) {
  if (e.target && e.target.id === 'assignRoomAirconFilter') {
    applyAssignRoomAirconFilter();
  }
});
</script>
