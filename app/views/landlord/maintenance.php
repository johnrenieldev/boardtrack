<?php
/**
 * BoardTrack — Landlord: Maintenance Requests
 * app/views/landlord/maintenance.php
 * Layout: landlord.php
 */
$requests = $requests ?? [];
$stats    = $stats ?? [];
$filters  = $filters ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Maintenance Requests</h1>
      <p class="page-subtitle">Track and manage room maintenance requests.</p>
    </div>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-wrench"></i></div>
      <div class="stat-label">Total Requests</div>
    </div>
    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
    <div class="stat-footer">Across <span>all categories</span></div>
  </div>

  <div class="stat-card <?= ($stats['pending'] ?? 0) > 0 ? 'urgent' : '' ?>">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-clock"></i></div>
      <div class="stat-label">Pending</div>
    </div>
    <div class="stat-value"><?= $stats['pending'] ?? 0 ?></div>
    <div class="stat-footer">Awaiting <span>initial review</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-spinner"></i></div>
      <div class="stat-label">In Progress</div>
    </div>
    <div class="stat-value"><?= $stats['in_progress'] ?? 0 ?></div>
    <div class="stat-footer">Currently <span>being fixed</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-circle-check"></i></div>
      <div class="stat-label">Completed</div>
    </div>
    <div class="stat-value"><?= $stats['completed'] ?? 0 ?></div>
    <div class="stat-footer">Successfully <span>resolved</span></div>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px;padding:14px 20px;">
  <form method="GET" action="<?= Router::url('landlord/maintenance') ?>" class="filter-bar" style="margin-bottom:0;">
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
      <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
      <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
    </select>
    <select name="priority" class="form-select">
      <option value="">All Priorities</option>
      <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
      <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
      <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
      <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
    </select>
    <select name="category" class="form-select">
      <option value="">All Categories</option>
      <option value="plumbing" <?= ($filters['category'] ?? '') === 'plumbing' ? 'selected' : '' ?>>Plumbing</option>
      <option value="electrical" <?= ($filters['category'] ?? '') === 'electrical' ? 'selected' : '' ?>>Electrical</option>
      <option value="carpentry" <?= ($filters['category'] ?? '') === 'carpentry' ? 'selected' : '' ?>>Carpentry</option>
      <option value="painting" <?= ($filters['category'] ?? '') === 'painting' ? 'selected' : '' ?>>Painting</option>
      <option value="cleaning" <?= ($filters['category'] ?? '') === 'cleaning' ? 'selected' : '' ?>>Cleaning</option>
      <option value="appliance" <?= ($filters['category'] ?? '') === 'appliance' ? 'selected' : '' ?>>Appliance</option>
      <option value="other" <?= ($filters['category'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= Router::url('landlord/maintenance') ?>" class="btn btn-secondary btn-sm">Clear</a>
  </form>
</div>

<!-- Maintenance Requests Table -->
<div class="card">
  <?php if (empty($requests)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-wrench"></i>
      <h3>No maintenance requests found</h3>
      <p>No maintenance requests match the current filters.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table">
        <thead>
          <tr>
            <th>Request</th>
            <th>Category</th>
            <th>Tenant</th>
            <th>Priority</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($requests as $req): ?>
            <tr>
              <td data-label="Request">
                <div class="td-name"><?= htmlspecialchars($req['title']) ?></div>
                <?php if (!empty($req['room_number'])): ?>
                  <div class="td-sub">Room <?= htmlspecialchars($req['room_number']) ?></div>
                <?php endif; ?>
              </td>
              <td data-label="Category">
                <?php
                  $catBadge = match($req['category']) {
                    'plumbing'   => 'badge-pv',
                    'electrical' => 'badge-waiting',
                    'carpentry'  => 'badge-normal',
                    'painting'   => 'badge-active',
                    'cleaning'   => 'badge-pending',
                    'appliance'  => 'badge-high',
                    default      => 'badge-normal'
                  };
                ?>
                <span class="badge <?= $catBadge ?>">
                  <?= ucfirst($req['category']) ?>
                </span>
              </td>
              <td data-label="Tenant">
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:32px;height:32px;border-radius:var(--radius);background:var(--gray-100);color:var(--gray-500);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:0.78rem;flex-shrink:0;">
                    <?= strtoupper(substr($req['tenant_name'] ?? 'A', 0, 1)) ?>
                  </div>
                  <div>
                    <div class="td-name"><?= htmlspecialchars($req['tenant_name']) ?></div>
                    <?php if (!empty($req['room_number'])): ?>
                      <div class="td-sub">Room <?= htmlspecialchars($req['room_number']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td data-label="Priority">
                <?php
                  $priorityBadge = match($req['priority']) {
                    'low'    => 'badge-normal',
                    'medium' => 'badge-waiting',
                    'high'   => 'badge-pending',
                    'urgent' => 'badge-overdue'
                  };
                ?>
                <span class="badge <?= $priorityBadge ?>">
                  <?= ucfirst($req['priority']) ?>
                </span>
              </td>
              <td data-label="Date" style="font-size:0.82rem;color:var(--gray-505);"><?= date('M j, Y', strtotime($req['requested_at'])) ?></td>
              <td data-label="Status">
                <?php
                  $statusBadge = match($req['status']) {
                    'pending'     => 'badge-pending',
                    'in_progress' => 'badge-waiting',
                    'completed'   => 'badge-paid',
                    'rejected'    => 'badge-rejected',
                    'cancelled'   => 'badge-normal'
                  };
                ?>
                <span class="badge <?= $statusBadge ?>">
                  <?= ucfirst(str_replace('_', ' ', $req['status'])) ?>
                </span>
              </td>
              <td data-label="Actions">
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                  <button type="button" class="btn btn-secondary btn-sm" onclick='showMaintenanceModal(<?= htmlspecialchars(json_encode($req)) ?>)'>
                    <i class="fa-solid fa-eye"></i> View
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

<!-- Maintenance Detail Modal -->
<div class="modal-overlay" id="maintenanceModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title" id="modalTitle">Maintenance Request Details</span>
      <button class="modal-close" onclick="closeModal('maintenanceModal')">&times;</button>
    </div>
    <div class="modal-body">
      <div class="detail-grid" style="margin-bottom:16px;">
        <div class="detail-item">
          <div class="detail-label">Category</div>
          <div class="detail-value"><span id="modalCategory" class="badge"></span></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Priority</div>
          <div class="detail-value"><span id="modalPriority" class="badge"></span></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Tenant</div>
          <div class="detail-value" id="modalTenant"></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Requested</div>
          <div class="detail-value" id="modalDate"></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Status</div>
          <div class="detail-value"><span id="modalStatus" class="badge"></span></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Assigned To</div>
          <div class="detail-value" id="modalAssigned"></div>
        </div>
      </div>
      <div style="background:var(--gray-50);padding:16px;border-radius:var(--radius);margin-bottom:16px;">
        <div style="font-size:0.78rem;font-weight:600;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px;">Description</div>
        <p id="modalDescription" style="color:var(--gray-700);line-height:1.6;margin:0;"></p>
      </div>
      <div id="modalCostSection" style="display:none;background:var(--primary-light);padding:16px;border-radius:var(--radius);">
        <div style="font-size:0.78rem;font-weight:600;color:var(--primary);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px;">Cost Information</div>
        <div class="detail-grid">
          <div class="detail-item">
            <div class="detail-label">Estimated Cost</div>
            <div class="detail-value" id="modalEstimatedCost"></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Actual Cost</div>
            <div class="detail-value" id="modalActualCost"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('maintenanceModal')">Close</button>
      <button type="button" class="btn btn-primary" id="updateStatusBtn" onclick="showUpdateStatusModal()">Update Status</button>
    </div>
  </div>
</div>

<!-- Update Status Modal -->
<div class="modal-overlay" id="updateStatusModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Update Maintenance Status</span>
      <button class="modal-close" onclick="closeModal('updateStatusModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/update-maintenance') ?>" method="POST">
      <input type="hidden" name="id" id="updateMaintenanceId">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Status <span class="req">*</span></label>
          <select name="status" class="form-select" required id="statusSelect" onchange="toggleCostFields()">
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Assigned To</label>
          <input type="text" name="assigned_to" class="form-input" placeholder="Name of person assigned">
        </div>
        <div class="form-group">
          <label class="form-label">Scheduled Date</label>
          <input type="date" name="scheduled_at" class="form-input">
        </div>
        <div class="form-group">
          <label class="form-label">Estimated Cost (₱)</label>
          <input type="number" name="estimated_cost" class="form-input" step="0.01" placeholder="0.00">
        </div>
        <div class="form-group" id="actualCostGroup" style="display:none;">
          <label class="form-label">Actual Cost (₱) <span class="req">*</span></label>
          <input type="number" name="actual_cost" class="form-input" step="0.01" placeholder="0.00">
          <span class="form-help">Required when marking as completed. A bill will be created for the tenant.</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('updateStatusModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Status</button>
      </div>
    </form>
  </div>
</div>

<script>
var currentMaintenance = null;

function openModal(id) {
  document.getElementById(id).style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
  document.body.style.overflow = '';
}

function getCategoryBadgeClass(category) {
  var classes = { plumbing: 'badge-pv', electrical: 'badge-waiting', carpentry: 'badge-normal', painting: 'badge-active', cleaning: 'badge-pending', appliance: 'badge-high', other: 'badge-normal' };
  return classes[category] || 'badge-normal';
}
function getPriorityBadgeClass(priority) {
  var classes = { low: 'badge-normal', medium: 'badge-waiting', high: 'badge-pending', urgent: 'badge-overdue' };
  return classes[priority] || 'badge-normal';
}
function getStatusBadgeClass(status) {
  var classes = { pending: 'badge-pending', in_progress: 'badge-waiting', completed: 'badge-paid', rejected: 'badge-rejected', cancelled: 'badge-normal' };
  return classes[status] || 'badge-normal';
}

function showMaintenanceModal(req) {
  currentMaintenance = req;
  document.getElementById('modalTitle').textContent = req.title;
  var catEl = document.getElementById('modalCategory');
  catEl.textContent = req.category.charAt(0).toUpperCase() + req.category.slice(1);
  catEl.className = 'badge ' + getCategoryBadgeClass(req.category);
  var priEl = document.getElementById('modalPriority');
  priEl.textContent = req.priority.charAt(0).toUpperCase() + req.priority.slice(1);
  priEl.className = 'badge ' + getPriorityBadgeClass(req.priority);
  document.getElementById('modalTenant').textContent = req.tenant_name + (req.room_number ? ' (Room ' + req.room_number + ')' : '');
  document.getElementById('modalDate').textContent = new Date(req.requested_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  var stEl = document.getElementById('modalStatus');
  stEl.textContent = req.status.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
  stEl.className = 'badge ' + getStatusBadgeClass(req.status);
  document.getElementById('modalAssigned').textContent = req.assigned_to || 'Not assigned';
  document.getElementById('modalDescription').textContent = req.description;
  
  var costSection = document.getElementById('modalCostSection');
  if (req.estimated_cost || req.actual_cost) {
    costSection.style.display = 'block';
    document.getElementById('modalEstimatedCost').textContent = req.estimated_cost ? '₱' + parseFloat(req.estimated_cost).toFixed(2) : 'Not set';
    document.getElementById('modalActualCost').textContent = req.actual_cost ? '₱' + parseFloat(req.actual_cost).toFixed(2) : 'Not set';
  } else {
    costSection.style.display = 'none';
  }
  
  openModal('maintenanceModal');
}

function showUpdateStatusModal() {
  closeModal('maintenanceModal');
  document.getElementById('updateMaintenanceId').value = currentMaintenance.id;
  document.getElementById('statusSelect').value = currentMaintenance.status;
  toggleCostFields();
  openModal('updateStatusModal');
}

function toggleCostFields() {
  var status = document.getElementById('statusSelect').value;
  var actualCostGroup = document.getElementById('actualCostGroup');
  if (status === 'completed') {
    actualCostGroup.style.display = 'block';
  } else {
    actualCostGroup.style.display = 'none';
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
</script>
