<?php
/**
 * BoardTrack — Landlord: Tenants
 * app/views/landlord/tenants.php
 * Layout: landlord.php
 */
$tenants        = $tenants        ?? [];
$filters        = $filters        ?? [];
$availableRooms = $availableRooms ?? [];
$tenantStats    = $tenantStats    ?? [];
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
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-users"></i></div>
      <div class="stat-label">Total Tenants</div>
    </div>
    <div class="stat-value"><?= $tenantStats['total'] ?? 0 ?></div>
    <div class="stat-footer">Across <span>all statuses</span></div>
  </div>
  
  <div class="stat-card <?= ($tenantStats['pending'] ?? 0) > 0 ? 'urgent' : '' ?>">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-clock"></i></div>
      <div class="stat-label">Pending</div>
    </div>
    <div class="stat-value"><?= $tenantStats['pending'] ?? 0 ?></div>
    <div class="stat-footer">Requires <span>review</span></div>
  </div>
  
  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-circle-check"></i></div>
      <div class="stat-label">Active</div>
    </div>
    <div class="stat-value"><?= $tenantStats['active'] ?? 0 ?></div>
    <div class="stat-footer">Currently <span>in rooms</span></div>
  </div>
  
  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-list-ol"></i></div>
      <div class="stat-label">Waiting List</div>
    </div>
    <div class="stat-value"><?= $tenantStats['waiting_list'] ?? 0 ?></div>
    <div class="stat-footer">Awaiting <span>assignment</span></div>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px;padding:14px 20px;">
  <form method="GET" action="index.php" class="filter-bar" style="margin-bottom:0;">
    <input type="hidden" name="url" value="landlord/tenants">
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>All Pending</option>
      <option value="ready_for_review" <?= ($filters['status'] ?? '') === 'ready_for_review' ? 'selected' : '' ?>>Ready for Review</option>
      <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
      <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active (In Room)</option>
      <option value="waiting_list" <?= ($filters['status'] ?? '') === 'waiting_list' ? 'selected' : '' ?>>Waiting List</option>
      <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
      <option value="moved_out" <?= ($filters['status'] ?? '') === 'moved_out' ? 'selected' : '' ?>>Moved Out</option>
    </select>
    <select name="compatibility" class="form-select">
      <option value="">All Compatibility</option>
      <option value="excellent" <?= ($filters['compatibility'] ?? '') === 'excellent' ? 'selected' : '' ?>>Best Match (90%+)</option>
      <option value="good" <?= ($filters['compatibility'] ?? '') === 'good' ? 'selected' : '' ?>>Good Match (75%+)</option>
      <option value="moderate" <?= ($filters['compatibility'] ?? '') === 'moderate' ? 'selected' : '' ?>>Moderate Match (50%+)</option>
      <option value="poor" <?= ($filters['compatibility'] ?? '') === 'poor' ? 'selected' : '' ?>>Poor Match (<50%)</option>
    </select>
    <select name="gender" class="form-select">
      <option value="">All Genders</option>
      <option value="male" <?= ($filters['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
      <option value="female" <?= ($filters['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
      <option value="prefer_not_to_say" <?= ($filters['gender'] ?? '') === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option>
    </select>
    <input type="text" name="search" class="form-input" placeholder="Search name..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
    <a href="<?= Router::url('landlord/tenants') ?>" class="btn btn-ghost btn-sm">Clear</a>
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
            <th>Gender</th>
            <th data-col="status">Status</th>
            <th>Room Preference</th>
            <th>Room Assigned</th>
            <th>Registered</th>
            <th data-col="actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tenants as $tenant): ?>
            <tr>
              <td data-label="Tenant">
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:32px;height:32px;border-radius:var(--radius);background:var(--primary);color:var(--color-surface);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:0.78rem;flex-shrink:0;">
                    <?= strtoupper(substr($tenant['name'], 0, 1)) ?>
                  </div>
                  <div>
                    <div class="td-name"><?= htmlspecialchars($tenant['name']) ?></div>
                    <div class="td-sub"><?= htmlspecialchars($tenant['email']) ?></div>
                  </div>
                </div>
              </td>
              <td data-label="Gender">
                <div class="flex-center">
                  <span class="text-xs font-bold text-gray-500 flex items-center gap-1.5">
                    <?php
                      $genderIcon = match($tenant['gender'] ?? '') {
                        'male' => 'fa-mars text-blue-500',
                        'female' => 'fa-venus text-pink-500',
                        'prefer_not_to_say' => 'fa-genderless text-gray-500',
                        default => 'fa-question text-gray-400'
                      };
                      $genderLabel = match($tenant['gender'] ?? '') {
                        'male' => 'Male',
                        'female' => 'Female',
                        'prefer_not_to_say' => 'Prefer not to say',
                        default => '—'
                      };
                    ?>
                    <i class="fa-solid <?= $genderIcon ?>"></i>
                    <?= $genderLabel ?>
                  </span>
                </div>
              </td>
              <td data-label="Status" data-col="status">
                <div class="flex-center">
                  <?php
                    $activeQuestions = (int)($tenant['active_questions'] ?? 0);
                    $answeredQuestions = (int)($tenant['answered_questions'] ?? 0);
                    $hasCompleteQuiz = $activeQuestions > 0 && $answeredQuestions >= $activeQuestions;
                    $isReady = ($tenant['user_status'] === 'pending' && $tenant['personality_completed'] && !empty($tenant['id_document_path']) && $hasCompleteQuiz);
                    $sBadge = match($tenant['user_status']) {
                      'approved'     => !empty($tenant['room_id']) ? 'badge-active' : 'badge-waiting',
                      'pending'      => $isReady ? 'badge-info' : 'badge-pending',
                      'rejected'     => 'badge-rejected',
                      'moved_out'    => 'badge-normal',
                      default        => 'badge-normal'
                    };
                    $sLabel = match($tenant['user_status']) {
                      'approved'     => !empty($tenant['room_id']) ? 'Active' : 'Waiting List',
                      'pending'      => $isReady ? 'Ready for Review' : 'Incomplete Profile',
                      default        => ucfirst(str_replace('_', ' ', $tenant['user_status']))
                    };
                  ?>
                  <span class="badge <?= $sBadge ?>">
                    <?= $sLabel ?>
                  </span>
                </div>
              </td>
              <td data-label="Room Pref">
                <div class="flex-center">
                  <?= ucfirst(htmlspecialchars($tenant['room_type_preference'] ?? '—')) ?>
                </div>
              </td>
              <td data-label="Room">
                <div class="flex-center">
                  <?php if (!empty($tenant['room_number'])): ?>
                    <div style="font-weight:700;color:var(--color-text-primary);">Room <?= htmlspecialchars($tenant['room_number']) ?></div>
                  <?php elseif (!$tenant['personality_completed']): ?>
                    <span class="text-[0.65rem] text-warning-600 font-black uppercase tracking-widest">Incomplete Profile</span>
                  <?php else: ?>
                    <span class="text-gray-400">—</span>
                  <?php endif; ?>
                </div>
              </td>
              <td data-label="Registered" style="font-size:0.82rem;color:var(--color-text-secondary);"><?= date('M j, Y', strtotime($tenant['registered_at'])) ?></td>
              <td data-label="Actions" data-col="actions">
                <div class="flex-center">
                  <a href="<?= Router::url('landlord/view-tenant/' . $tenant['id']) ?>" class="btn btn-secondary btn-sm btn-icon" title="View Details">
                    <i class="fa-solid fa-eye text-xs"></i>
                  </a>
                  <?php if ($tenant['user_status'] === 'pending'): ?>
                    <?php if ($isReady): ?>
                      <button type="button" class="btn btn-success btn-sm btn-icon" onclick="showApproveModal(<?= $tenant['id'] ?>, '<?= htmlspecialchars(addslashes($tenant['name'])) ?>', <?= (int)($tenant['air_conditioned_preference'] ?? 0) ?>)" title="Approve">
                        <i class="fa-solid fa-check text-xs"></i>
                      </button>
                    <?php else: ?>
                      <button type="button" class="btn btn-secondary btn-sm btn-icon cursor-not-allowed opacity-50" title="Profile Incomplete" onclick="Swal.fire('Incomplete Profile', 'This tenant still needs a complete quiz and ID document before approval.', 'warning')">
                        <i class="fa-solid fa-check text-xs"></i>
                      </button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="showRejectModal(<?= $tenant['id'] ?>)" title="Reject">
                      <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                  <?php endif; ?>
                  <?php if ($tenant['user_status'] === 'approved' && !empty($tenant['room_id'])): ?>
                    <button type="button" class="btn btn-warning btn-sm btn-icon" onclick="showMoveOutModal(<?= $tenant['id'] ?>, '<?= htmlspecialchars(addslashes($tenant['name'])) ?>')" title="Mark as Moved Out">
                      <i class="fa-solid fa-person-walking-arrow-right text-xs"></i>
                    </button>
                  <?php endif; ?>
                  <?php if ($tenant['user_status'] === 'moved_out'): ?>
                    <button type="button" class="btn btn-success btn-sm btn-icon" onclick="showUndoMoveOutModal(<?= $tenant['id'] ?>, '<?= htmlspecialchars(addslashes($tenant['name'])) ?>')" title="Undo Move Out">
                      <i class="fa-solid fa-rotate-left text-xs"></i>
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
    <form action="<?= Router::url('landlord/approve-tenant') ?>" method="POST"
          data-confirm="Approve this tenant and assign the selected room?"
          data-action="Approve tenant">
      <input type="hidden" name="tenant_id" id="approveTenantId">
      <div class="modal-body">
        <p style="margin:0 0 16px;color:var(--color-text-secondary);">You are about to approve <strong id="approveTenantName"></strong>.</p>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Assign Room (Optional)</label>
          <div style="margin-bottom:10px;">
            <label class="form-label" style="display:block;margin-bottom:6px;">Air-conditioning Filter</label>
            <select id="approveRoomAirconFilter" class="form-select">
              <option value="match" selected>Match tenant preference</option>
              <option value="any">Any room</option>
              <option value="1">Air-conditioned only</option>
              <option value="0">Non-air-conditioned only</option>
            </select>
          </div>
          <select name="room_id" id="approveRoomSelect" class="form-select" onchange="previewCompatibility(this.value, 'approveCompPreview')">
            <option value="">— Add to Waiting List —</option>
            <?php foreach ($availableRooms as $r): ?>
              <option
                value="<?= $r['id'] ?>"
                data-aircon="<?= !empty($r['air_conditioned']) ? '1' : '0' ?>"
              >
                Room <?= htmlspecialchars($r['room_number']) ?> (<?= ucfirst($r['room_type']) ?>, <?= $r['max_occupants'] - ($r['actual_occupants'] ?? 0) ?> spots)
              </option>
            <?php endforeach; ?>
          </select>
          <div id="approveCompPreview" class="mt-3" style="display:none;"></div>
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
    <form action="<?= Router::url('landlord/reject-tenant') ?>" method="POST"
          data-confirm="Reject this tenant application? This cannot be undone."
          data-action="Reject tenant" data-color="#dc2626" data-confirm-text="Yes, reject">
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

<!-- Move Out Modal -->
<div class="modal-overlay" id="moveOutModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Mark Tenant as Moved Out</span>
      <button class="modal-close" onclick="closeModal('moveOutModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/move-out-tenant') ?>" method="POST">
      <input type="hidden" name="tenant_id" id="moveOutTenantId">
      <div class="modal-body">
        <p style="margin:0 0 16px;color:var(--color-text-secondary);">
          You are about to mark <strong id="moveOutTenantName"></strong> as moved out.
        </p>
        <?php if (!empty($_SESSION['move_out_warnings'])): ?>
          <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:var(--radius);padding:12px;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
              <i class="fa-solid fa-triangle-exclamation" style="color:#d97706;"></i>
              <strong style="color:#92400e;">Warning</strong>
            </div>
            <ul style="margin:0;padding-left:20px;color:#92400e;font-size:0.88rem;">
              <?php foreach ($_SESSION['move_out_warnings'] as $warning): ?>
                <li><?= htmlspecialchars($warning) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        <div class="form-group" style="margin-bottom:0;">
          <label style="display:flex;align-items:center;gap:8px;font-size:0.88rem;color:var(--color-text-secondary);">
            <input type="checkbox" name="force_move_out" value="1" style="width:16px;height:16px;">
            Force move out despite warnings
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('moveOutModal')">Cancel</button>
        <button type="submit" class="btn btn-warning">Mark as Moved Out</button>
      </div>
    </form>
  </div>
</div>

<!-- Undo Move Out Modal -->
<div class="modal-overlay" id="undoMoveOutModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Undo Move Out</span>
      <button class="modal-close" onclick="closeModal('undoMoveOutModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/undo-move-out') ?>" method="POST"
          data-confirm="Revert the move-out status for this tenant? They will be marked as active again."
          data-action="Undo move out">
      <input type="hidden" name="tenant_id" id="undoMoveOutTenantId">
      <div class="modal-body">
        <p style="margin:0;color:var(--color-text-secondary);">
          You are about to revert the move-out status for <strong id="undoMoveOutTenantName"></strong>.
          They will be marked as active again and will need to be assigned a room.
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('undoMoveOutModal')">Cancel</button>
        <button type="submit" class="btn btn-success">Undo Move Out</button>
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
function showApproveModal(tenantId, tenantName, tenantAirconPreference) {
  document.getElementById('approveTenantId').value = tenantId;
  document.getElementById('approveTenantName').textContent = tenantName;
  var filterEl = document.getElementById('approveRoomAirconFilter');
  if (filterEl) {
    filterEl.dataset.tenantAirconPref = (tenantAirconPreference ? '1' : '0');
    filterEl.value = 'match';
    applyApproveRoomAirconFilter();
  }
  openModal('approveModal');
}
function applyApproveRoomAirconFilter() {
  var filterEl = document.getElementById('approveRoomAirconFilter');
  var selectEl = document.getElementById('approveRoomSelect');
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

  // If the currently selected option is hidden, clear selection.
  if (selectEl.value) {
    var selectedOpt = selectEl.selectedOptions && selectEl.selectedOptions[0] ? selectEl.selectedOptions[0] : null;
    if (selectedOpt && selectedOpt.hidden) {
      selectEl.value = '';
    }
  }
}
function showRejectModal(tenantId) {
  document.getElementById('rejectTenantId').value = tenantId;
  openModal('rejectModal');
}
function showMoveOutModal(tenantId, tenantName) {
  document.getElementById('moveOutTenantId').value = tenantId;
  document.getElementById('moveOutTenantName').textContent = tenantName;
  openModal('moveOutModal');
}
function showUndoMoveOutModal(tenantId, tenantName) {
  document.getElementById('undoMoveOutTenantId').value = tenantId;
  document.getElementById('undoMoveOutTenantName').textContent = tenantName;
  openModal('undoMoveOutModal');
}

function previewCompatibility(roomId, containerId) {
  const container = document.getElementById(containerId);
  const tenantId = document.getElementById('approveTenantId').value;
  
  if (!roomId || !tenantId) {
    container.style.display = 'none';
    return;
  }

  container.innerHTML = '<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin text-brand-500"></i><p class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-widest mt-2">Calculating Compatibility...</p></div>';
  container.style.display = 'block';

  fetch('<?= Router::url("landlord/compatibility-preview") ?>&tenant_id=' + tenantId + '&room_id=' + roomId)
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        container.innerHTML = '<div class="p-3 bg-danger-50 text-danger-600 rounded-lg text-xs font-bold">' + data.error + '</div>';
        return;
      }

      const statusOnly = ['Empty Room', 'Incomplete Profile', 'Incomplete Data', 'Incomplete Roommate Data'].includes(data.status);
      const scoreLabel = statusOnly ? (data.status === 'Empty Room' ? 'Open room' : 'Pending data') : Math.round(data.score) + '%';
      const meterHtml = statusOnly ? `
        <div class="text-[0.7rem] font-bold text-gray-500">${scoreLabel}</div>
      ` : `
        <div class="flex items-center gap-3">
          <div class="text-xl font-black text-gray-900">${scoreLabel}</div>
          <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-${data.color === 'blue' ? 'brand' : (data.color === 'green' ? 'success' : (data.color === 'orange' ? 'warning' : 'danger'))}-500" style="width: ${data.score}%"></div>
          </div>
        </div>
      `;

      let reasonsHtml = '';
      if (data.explanation && data.explanation.length > 0) {
        reasonsHtml = `
          <div class="mt-3 pt-3 border-t border-gray-100">
            <div class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-widest mb-2">Why this match?</div>
            <div class="space-y-1">
              ${data.explanation.slice(0, 3).map(reason => `
                <div class="flex items-center gap-2 text-[0.7rem] font-bold text-gray-700">
                  <i class="fa-solid fa-check text-success-500"></i>
                  ${reason}
                </div>
              `).join('')}
            </div>
          </div>
        `;
      }

      container.innerHTML = `
        <div class="comp-preview-box">
          <div class="flex items-center justify-between mb-2">
            <div class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-widest">Compatibility Preview</div>
            <span class="comp-badge comp-badge-${data.color}">${data.status}</span>
          </div>
          ${meterHtml}
          ${reasonsHtml}
          ${!statusOnly && data.score < 50 ? `
            <div class="mt-3 p-2 bg-danger-50 border border-danger-100 rounded text-[0.65rem] text-danger-600 font-bold flex items-start gap-2">
              <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
              <span>Low compatibility detected. This match may increase roommate conflict.</span>
            </div>
          ` : ''}
        </div>
      `;
    })
    .catch(err => {
      container.innerHTML = '<div class="p-3 bg-danger-50 text-danger-600 rounded-lg text-xs font-bold">Failed to load preview</div>';
    });
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
  if (e.target && e.target.id === 'approveRoomAirconFilter') {
    applyApproveRoomAirconFilter();
  }
});
<?php
// Auto-open move out modal if there are warnings from the controller
if (!empty($_SESSION['move_out_warnings']) && !empty($_SESSION['move_out_tenant_id'])):
  $tenantId = $_SESSION['move_out_tenant_id'];
  $tenant = $this->model('Tenant')->find($tenantId);
  if ($tenant):
?>
  setTimeout(function() {
    showMoveOutModal(<?= $tenantId ?>, '<?= htmlspecialchars(addslashes($tenant['name'] ?? '')) ?>');
  }, 500);
<?php
  endif;
  unset($_SESSION['move_out_warnings']);
  unset($_SESSION['move_out_tenant_id']);
endif;
?>
</script>

<style>
/* Optimize table for compact and formal layout - no horizontal scroll */
.bt-table th,
.bt-table td {
  padding: 8px 10px !important;
  vertical-align: middle !important;
  font-size: 0.8rem !important;
}

.bt-table th {
  font-size: 0.75rem !important;
  font-weight: 600 !important;
}

/* Column widths for better alignment - reduced to fit without scroll */
.bt-table th:nth-child(1),
.bt-table td:nth-child(1) {
  width: 20%;
  min-width: 160px;
  text-align: left;
}

.bt-table th:nth-child(2),
.bt-table td:nth-child(2) {
  width: 10%;
  min-width: 85px;
  text-align: center;
}

.bt-table th:nth-child(3),
.bt-table td:nth-child(3) {
  width: 13%;
  min-width: 110px;
  text-align: center;
}

.bt-table th:nth-child(4),
.bt-table td:nth-child(4) {
  width: 12%;
  min-width: 95px;
  text-align: center;
}

.bt-table th:nth-child(5),
.bt-table td:nth-child(5) {
  width: 13%;
  min-width: 100px;
  text-align: center;
}

.bt-table th:nth-child(6),
.bt-table td:nth-child(6) {
  width: 11%;
  min-width: 90px;
  text-align: center;
}

.bt-table th:nth-child(7),
.bt-table td:nth-child(7) {
  width: 13%;
  min-width: 110px;
  text-align: center;
}

/* Center all column headers except Tenant */
.bt-table th:not(:first-child) {
  text-align: center;
}

/* Center column headers */
.bt-table th[data-col="status"],
.bt-table th[data-col="actions"] {
  text-align: center;
}

/* Make tenant names and emails more compact */
.bt-table .td-name {
  font-size: 0.8rem !important;
  font-weight: 600 !important;
  line-height: 1.3 !important;
}

.bt-table .td-sub {
  font-size: 0.7rem !important;
  color: var(--gray-500) !important;
  margin-top: 2px !important;
}

/* Make avatar smaller */
.bt-table td:first-child > div > div:first-child {
  width: 26px !important;
  height: 26px !important;
  font-size: 0.65rem !important;
}

/* Make badges more compact */
.bt-table .badge {
  font-size: 0.65rem !important;
  padding: 3px 8px !important;
  white-space: nowrap !important;
}

/* Make gender icons and text smaller and ensure proper centering */
.bt-table td[data-label="Gender"] span {
  font-size: 0.7rem !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 4px !important;
}

.bt-table td[data-label="Gender"] .flex-center {
  justify-content: center !important;
  display: flex !important;
}

/* Center content in specific columns */
.flex-center {
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Force center alignment for data cells in centered columns */
.bt-table td:nth-child(2),
.bt-table td:nth-child(3),
.bt-table td:nth-child(4),
.bt-table td:nth-child(5),
.bt-table td:nth-child(6),
.bt-table td:nth-child(7) {
  text-align: center !important;
}

/* Ensure flex-center divs are centered and take full width */
.bt-table td .flex-center {
  width: 100%;
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
}

/* Override any conflicting flex classes */
.bt-table td .flex-center > * {
  margin: 0 auto;
}

/* Make action buttons more compact */
.bt-table td[data-col="actions"] .btn {
  padding: 4px 6px !important;
}

.bt-table td[data-col="actions"] .btn i {
  font-size: 0.65rem !important;
}

.bt-table td[data-col="actions"] > div {
  gap: 3px !important;
}

/* Reduce registered date font size */
.bt-table td[data-label="Registered"] {
  font-size: 0.7rem !important;
  color: var(--gray-500) !important;
}

/* Make room preference and room assigned text smaller */
.bt-table td[data-label="Room Pref"],
.bt-table td[data-label="Room"] {
  font-size: 0.75rem !important;
}
</style>
