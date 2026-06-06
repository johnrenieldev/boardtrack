<?php
/**
 * BoardTrack — Landlord: Complaints
 * app/views/landlord/complaints.php
 * Layout: landlord.php
 */
$complaints = $complaints ?? [];
$statistics = $statistics ?? [];
$filters    = $filters    ?? [];
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Complaints</h1>
      <p class="page-subtitle">Track and resolve tenant complaints.</p>
    </div>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <div class="stat-label">Total Complaints</div>
    </div>
    <div class="stat-value"><?= $statistics['total'] ?? 0 ?></div>
    <div class="stat-footer">Across <span>all categories</span></div>
  </div>

  <div class="stat-card <?= ($statistics['pending'] ?? 0) > 0 ? 'urgent' : '' ?>">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-clock"></i></div>
      <div class="stat-label">Pending</div>
    </div>
    <div class="stat-value"><?= $statistics['pending'] ?? 0 ?></div>
    <div class="stat-footer">Awaiting <span>initial review</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-spinner"></i></div>
      <div class="stat-label">In Progress</div>
    </div>
    <div class="stat-value"><?= $statistics['in_progress'] ?? 0 ?></div>
    <div class="stat-footer">Currently <span>being resolved</span></div>
  </div>

  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon-box"><i class="fa-solid fa-circle-check"></i></div>
      <div class="stat-label">Resolved</div>
    </div>
    <div class="stat-value"><?= $statistics['resolved'] ?? 0 ?></div>
    <div class="stat-footer">Successfully <span>closed</span></div>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px;padding:14px 20px;">
  <form method="GET" action="index.php" class="filter-bar" style="margin-bottom:0;">
    <input type="hidden" name="url" value="landlord/complaints">
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
      <option value="resolved" <?= ($filters['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
    </select>
    <select name="category" class="form-select">
      <option value="">All Categories</option>
      <option value="maintenance" <?= ($filters['category'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
      <option value="roommate_conflict" <?= ($filters['category'] ?? '') === 'roommate_conflict' ? 'selected' : '' ?>>Roommate Conflict</option>
      <option value="billing" <?= ($filters['category'] ?? '') === 'billing' ? 'selected' : '' ?>>Billing</option>
      <option value="room_change" <?= ($filters['category'] ?? '') === 'room_change' ? 'selected' : '' ?>>Room Change</option>
      <option value="other" <?= ($filters['category'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= Router::url('landlord/complaints') ?>" class="btn btn-secondary btn-sm">Clear</a>
  </form>
</div>

<!-- Complaints Table -->
<div class="card">
  <?php if (empty($complaints)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <h3>No complaints found</h3>
      <p>No complaints match the current filters.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table">
        <thead>
          <tr>
            <th>Complaint</th>
            <th data-col="category">Category</th>
            <th>Submitted By</th>
            <th data-col="date">Date</th>
            <th data-col="status">Status</th>
            <th data-col="actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($complaints as $complaint): ?>
            <tr>
              <td data-label="Complaint">
                <div class="td-name"><?= htmlspecialchars($complaint['title']) ?></div>
                <?php if ($complaint['is_anonymous']): ?>
                  <span class="badge badge-normal" style="margin-top:4px;"><i class="fa-solid fa-user-secret"></i> Anonymous</span>
                <?php endif; ?>
              </td>
              <td data-label="Category" data-col="category">
                <?php
                  $catBadge = match($complaint['category']) {
                    'maintenance'       => 'badge-high',
                    'roommate_conflict' => 'badge-urgent',
                    'billing'           => 'badge-pv',
                    'room_change'       => 'badge-waiting',
                    default             => 'badge-normal'
                  };
                ?>
                <span class="badge <?= $catBadge ?>">
                  <?= ucfirst(str_replace('_', ' ', $complaint['category'])) ?>
                </span>
              </td>
              <td data-label="Submitted By">
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:32px;height:32px;border-radius:var(--radius);background:var(--gray-100);color:var(--gray-500);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:0.78rem;flex-shrink:0;">
                    <?= strtoupper(substr($complaint['display_name'] ?? 'A', 0, 1)) ?>
                  </div>
                  <div>
                    <div class="td-name"><?= htmlspecialchars($complaint['display_name'] ?? 'Anonymous') ?></div>
                    <?php if (!$complaint['is_anonymous'] && !empty($complaint['room_number'])): ?>
                      <div class="td-sub">Room <?= htmlspecialchars($complaint['room_number']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td data-label="Date" data-col="date" style="font-size:0.82rem;color:var(--gray-500);"> 
                <?= date('M j, Y', strtotime($complaint['created_at'])) ?>
              </td>
              <td data-label="Status" data-col="status">
                <?php
                  $stBadge = match($complaint['status']) {
                    'pending'     => 'badge-open',
                    'in_progress' => 'badge-progress',
                    'resolved'    => 'badge-resolved',
                    default       => 'badge-normal'
                  };
                ?>
                <span class="badge <?= $stBadge ?>">
                  <?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?>
                </span>
              </td>
              <td data-label="Actions" data-col="actions">
                  <button type="button" class="btn btn-secondary btn-sm" onclick='showComplaintModal(<?= htmlspecialchars(json_encode($complaint)) ?>)'>
                    <i class="fa-solid fa-eye"></i> View
                  </button>
                  <button type="button" class="btn btn-secondary btn-sm" onclick="confirmDeleteComplaint(<?= (int)$complaint['id'] ?>, <?= htmlspecialchars(json_encode($complaint['title'])) ?>)" style="color:var(--color-danger);">
                    <i class="fa-solid fa-trash"></i> Delete
                  </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Complaint Detail Modal -->
<div class="modal-overlay" id="complaintModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title" id="modalTitle">Complaint Details</span>
      <button class="modal-close" onclick="closeModal('complaintModal')">&times;</button>
    </div>
    <div class="modal-body">
      <div class="detail-grid" style="margin-bottom:16px;">
        <div class="detail-item">
          <div class="detail-label">Category</div>
          <div class="detail-value"><span id="modalCategory" class="badge"></span></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">From</div>
          <div class="detail-value" id="modalTenant"></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Submitted</div>
          <div class="detail-value" id="modalDate"></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Status</div>
          <div class="detail-value"><span id="modalStatus" class="badge"></span></div>
        </div>
      </div>
      <div style="background:var(--gray-50);padding:16px;border-radius:var(--radius);margin-bottom:16px;">
        <div style="font-size:0.78rem;font-weight:600;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px;">Description</div>
        <p id="modalDescription" style="color:var(--gray-700);line-height:1.6;margin:0;"></p>
      </div>
      <div id="modalResponseSection" style="display:none;background:var(--primary-light);padding:16px;border-radius:var(--radius);">
        <div style="font-size:0.78rem;font-weight:600;color:var(--primary);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px;">Landlord Response</div>
        <p id="modalResponse" style="color:var(--gray-700);line-height:1.6;margin:0;"></p>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('complaintModal')">Close</button>
      <button type="button" class="btn btn-primary" id="updateStatusBtn" onclick="showUpdateStatusModal()">Update Status</button>
    </div>
  </div>
</div>

<!-- Update Status Modal -->
<div class="modal-overlay" id="updateStatusModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Update Complaint Status</span>
      <button class="modal-close" onclick="closeModal('updateStatusModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/respond-complaint') ?>" method="POST">
      <input type="hidden" name="complaint_id" id="updateComplaintId">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Status <span class="req">*</span></label>
          <select name="status" class="form-select" required>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="resolved">Resolved</option>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Response (Optional)</label>
          <textarea name="response" class="form-textarea" rows="3" placeholder="Enter your response to the tenant..."></textarea>
          <span class="form-help">This will be visible to the tenant.</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('updateStatusModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Status</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Complaint Form (hidden) -->
<form id="deleteComplaintForm" action="<?= Router::url('landlord/delete-complaint') ?>" method="POST" style="display:none;">
  <input type="hidden" name="complaint_id" id="deleteComplaintId">
</form>

<script>
var currentComplaint = null;

function openModal(id) {
  document.getElementById(id).style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
  document.body.style.overflow = '';
}

function getCategoryBadgeClass(category) {
  var classes = { maintenance: 'badge-high', roommate_conflict: 'badge-urgent', billing: 'badge-pv', room_change: 'badge-waiting', other: 'badge-normal' };
  return classes[category] || 'badge-normal';
}
function getStatusBadgeClass(status) {
  var classes = { resolved: 'badge-resolved', pending: 'badge-open', in_progress: 'badge-progress' };
  return classes[status] || 'badge-normal';
}

function showComplaintModal(complaint) {
  currentComplaint = complaint;
  document.getElementById('modalTitle').textContent = complaint.title;
  var catEl = document.getElementById('modalCategory');
  catEl.textContent = complaint.category.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
  catEl.className = 'badge ' + getCategoryBadgeClass(complaint.category);
  document.getElementById('modalTenant').textContent = (complaint.display_name || 'Anonymous') + (complaint.room_number ? ' (Room ' + complaint.room_number + ')' : '');
  document.getElementById('modalDate').textContent = new Date(complaint.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  var stEl = document.getElementById('modalStatus');
  stEl.textContent = complaint.status.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
  stEl.className = 'badge ' + getStatusBadgeClass(complaint.status);
  document.getElementById('modalDescription').textContent = complaint.description;
  var responseSection = document.getElementById('modalResponseSection');
  if (complaint.landlord_response) {
    responseSection.style.display = 'block';
    document.getElementById('modalResponse').textContent = complaint.landlord_response;
  } else {
    responseSection.style.display = 'none';
  }
  openModal('complaintModal');
}

function showUpdateStatusModal() {
  closeModal('complaintModal');
  document.getElementById('updateComplaintId').value = currentComplaint.id;
  openModal('updateStatusModal');
}

function confirmDeleteComplaint(id, title) {
  Swal.fire({
    title: 'Delete complaint?',
    text: 'Permanently delete "' + title + '"? This cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#94a3b8',
    confirmButtonText: 'Yes, delete',
    cancelButtonText: 'Cancel',
  }).then(function (r) {
    if (r.isConfirmed) {
      document.getElementById('deleteComplaintId').value = id;
      document.getElementById('deleteComplaintForm').submit();
    }
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
</script>

<style>
/* Center column headers and data */
.bt-table th {
  text-align: center !important;
}

.bt-table td {
  text-align: center !important;
}

/* Keep first column (Complaint) left-aligned */
.bt-table th:first-child,
.bt-table td:first-child {
  text-align: left !important;
}

/* Mobile responsive styles for complaints table */
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
  
  /* Complaint - full width with grey background */
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
  
  /* Category badge - wrap tightly */
  .bt-table td[data-label="Category"] .badge {
    display: inline-flex !important;
    width: auto !important;
    max-width: fit-content !important;
  }
  
  /* Submitted By - align left */
  .bt-table td[data-label="Submitted By"] > div {
    justify-content: flex-start !important;
  }
  
  /* Status badge - wrap tightly */
  .bt-table td[data-label="Status"] .badge {
    display: inline-flex !important;
    width: auto !important;
    max-width: fit-content !important;
  }
  
  /* Actions - center the buttons */
  .bt-table td[data-col="actions"] {
    justify-content: center !important;
    text-align: center !important;
  }
  
  .bt-table td[data-col="actions"]::before {
    display: none !important;
  }
  
  .bt-table td[data-col="actions"] {
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
    padding: 10px 14px !important;
  }
  
  /* Make action buttons full width on mobile */
  .bt-table td[data-col="actions"] > button {
    width: 100% !important;
    margin: 0 !important;
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
