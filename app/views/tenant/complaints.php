<?php
/**
 * BoardTrack — Tenant: My Complaints
 * app/views/tenant/complaints.php
 * Layout: tenant.php
 */
$complaints = $complaints ?? [];

$totalComplaints   = count($complaints);
$pendingCount      = count(array_filter($complaints, fn($c) => $c['status'] === 'pending'));
$inProgressCount   = count(array_filter($complaints, fn($c) => $c['status'] === 'in_progress'));
$resolvedCount     = count(array_filter($complaints, fn($c) => $c['status'] === 'resolved'));
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">My Complaints</h1>
      <p class="page-subtitle">Submit and track complaints about your room or boarding house.</p>
    </div>
    <div>
      <button type="button" class="btn btn-primary" onclick="openModal('createModal')">
        <i class="fa-solid fa-plus"></i> New Complaint
      </button>
    </div>
  </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-folder-open text-brand-500"></i> Total
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $totalComplaints ?></div>
  </div>
  
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-clock text-warning-500"></i> Pending
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $pendingCount ?></div>
  </div>
  
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-spinner text-brand-500"></i> In Progress
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $inProgressCount ?></div>
  </div>
  
  <div class="card p-4">
    <div class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
      <i class="fa-solid fa-circle-check text-success-500"></i> Resolved
    </div>
    <div class="text-2xl font-black text-gray-900 leading-none"><?= $resolvedCount ?></div>
  </div>
</div>

<!-- Filter Bar -->
<div class="card mb-4 p-4">
  <div class="filter-bar" style="margin-bottom:0;">
    <select class="form-select" id="statusFilter">
      <option value="all">All Statuses</option>
      <option value="pending">Pending</option>
      <option value="in_progress">In Progress</option>
      <option value="resolved">Resolved</option>
    </select>
    <button type="button" class="btn btn-primary btn-sm" onclick="applyFilter()">
      Filter
    </button>
    <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilter()">
      <i class="fa-solid fa-times"></i> Clear
    </button>
  </div>
</div>

<!-- Complaints List -->
<div class="card">
  <?php if (empty($complaints)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-circle-check"></i>
      <h3>No Complaints</h3>
      <p>You haven't submitted any complaints yet.</p>
      <div style="margin-top:12px;">
        <button type="button" class="btn btn-primary" onclick="openModal('createModal')">
          <i class="fa-solid fa-plus"></i> Submit a Complaint
        </button>
      </div>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="bt-table">
        <thead>
          <tr>
            <th>Title</th>
            <th data-col="category">Category</th>
            <th data-col="status">Status</th>
            <th>Submitted</th>
            <th data-col="actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $categoryBadge = [
            'maintenance'       => 'badge-unpaid',
            'roommate_conflict' => 'badge-overdue',
            'billing'           => 'badge-pv',
            'room_change'       => 'badge-single',
            'other'             => 'badge-normal',
          ];
          $statusBadge = [
            'pending'     => 'badge-pending',
            'in_progress' => 'badge-progress',
            'resolved'    => 'badge-resolved',
          ];
          foreach ($complaints as $idx => $complaint):
          ?>
          <tr data-status="<?= htmlspecialchars($complaint['status']) ?>">
            <td data-label="Title">
              <div class="td-name">
                <?= htmlspecialchars($complaint['title']) ?>
                <?php if (!empty($complaint['is_anonymous'])): ?>
                  <span class="badge badge-normal" style="margin-left:4px;font-size:0.65rem;"><i class="fa-solid fa-user-secret"></i> Anon</span>
                <?php endif; ?>
              </div>
            </td>
            <td data-label="Category" data-col="category">
              <span class="badge <?= $categoryBadge[$complaint['category']] ?? 'badge-normal' ?>">
                <?= ucfirst(str_replace('_', ' ', $complaint['category'])) ?>
              </span>
            </td>
            <td data-label="Status" data-col="status">
              <span class="badge <?= $statusBadge[$complaint['status']] ?? 'badge-normal' ?>">
                <?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?>
              </span>
            </td>
            <td data-label="Submitted" style="color:var(--color-text-muted);font-size:0.82rem;"> 
              <?= date('M d, Y', strtotime($complaint['created_at'])) ?>
            </td>
            <td data-label="Actions" data-col="actions">
              <div style="display:flex;gap:4px;justify-content:end;">
                <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="openDetailModal(<?= $idx ?>)" title="View Details">
                  <i class="fa-solid fa-eye text-xs"></i>
                </button>
                <?php if ($complaint['status'] === 'pending'): ?>
                  <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="openEditModal(<?= $idx ?>)" style="color:var(--primary);" title="Edit">
                    <i class="fa-solid fa-pen text-xs"></i>
                  </button>
                  <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="confirmDelete(<?= (int)$complaint['id'] ?>, <?= htmlspecialchars(json_encode($complaint['title'])) ?>)" style="color:var(--color-danger);" title="Delete">
                    <i class="fa-solid fa-trash text-xs"></i>
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

<!-- Complaint Detail Modal -->
<div class="modal-overlay" id="detailModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <div class="modal-title" id="detailTitle">Complaint Details</div>
      <button class="modal-close" onclick="closeModal('detailModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div class="detail-grid" style="margin-bottom:16px;">
        <div class="detail-item">
          <div class="detail-label">Category</div>
          <div class="detail-value" id="detailCategory"></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Status</div>
          <div class="detail-value" id="detailStatus"></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Submitted</div>
          <div class="detail-value" id="detailDate"></div>
        </div>
      </div>
      <div style="margin-bottom:16px;">
        <div class="detail-label" style="margin-bottom:6px;">Description</div>
        <div id="detailDescription" style="font-size:0.85rem;color:var(--color-text-primary);line-height:1.6;padding:12px;background:var(--color-canvas);border-radius:var(--radius);"></div>
      </div>
      <div id="detailResponseSection" style="display:none;">
        <div style="border-top:1px solid var(--color-border);padding-top:14px;">
          <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
            <i class="fa-solid fa-reply" style="color:var(--color-text-secondary);font-size:0.8rem;"></i>
            <span style="font-weight:600;font-size:0.85rem;color:var(--color-text-primary);">Landlord's Response</span>
          </div>
          <div id="detailResponse" style="font-size:0.85rem;color:var(--color-text-primary);line-height:1.6;padding:12px;background:var(--color-canvas);border-radius:var(--radius);"></div>
          <div id="detailResponseDate" style="font-size:0.75rem;color:var(--color-text-muted);margin-top:6px;"></div>
        </div>
      </div>
      <div id="detailTenantResponseSection" style="display:none;">
        <div style="border-top:1px solid var(--color-border);padding-top:14px;">
          <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
            <i class="fa-solid fa-comment-dots" style="color:var(--color-text-secondary);font-size:0.8rem;"></i>
            <span style="font-weight:600;font-size:0.85rem;color:var(--color-text-primary);">Your Response</span>
          </div>
          <div id="detailTenantResponse" style="font-size:0.85rem;color:var(--color-text-primary);line-height:1.6;padding:12px;background:var(--color-canvas);border-radius:var(--radius);"></div>
          <div id="detailTenantResponseDate" style="font-size:0.75rem;color:var(--color-text-muted);margin-top:6px;"></div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('detailModal')">Close</button>
    </div>
  </div>
</div>

<!-- Create Complaint Modal -->
<div class="modal-overlay" id="createModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <div class="modal-title">Submit a Complaint</div>
      <button class="modal-close" onclick="closeModal('createModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="<?= Router::url('tenant/save-complaint') ?>" method="POST">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Category <span class="req">*</span></label>
          <select name="category" id="category" class="form-select" required>
            <option value="">Select a category...</option>
            <option value="maintenance">Maintenance Issue</option>
            <option value="roommate_conflict">Roommate Conflict</option>
            <option value="billing">Billing Concern</option>
            <option value="room_change">Room Change Request</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="form-group" id="anonymousSection" style="display:none;">
          <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:var(--color-canvas);border-radius:var(--radius);border:1px solid var(--color-border);cursor:pointer;">
            <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1" style="margin-top:2px;">
            <div>
              <div style="font-size:0.85rem;font-weight:500;color:var(--color-text-primary);">Submit Anonymously</div>
              <div class="form-help" style="margin-top:2px;">Your identity will be hidden from the complaint view.</div>
            </div>
          </label>
        </div>
        <div class="form-group">
          <label class="form-label">Title <span class="req">*</span></label>
          <input type="text" name="title" class="form-input" required placeholder="Brief summary of your complaint...">
        </div>
        <div class="form-group">
          <label class="form-label">Description <span class="req">*</span></label>
          <textarea name="description" class="form-textarea" rows="5" required placeholder="Provide detailed information about your complaint..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('createModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Submit Complaint</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Complaint Modal -->
<div class="modal-overlay" id="editModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <div class="modal-title">Edit Complaint</div>
      <button class="modal-close" onclick="closeModal('editModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="<?= Router::url('tenant/update-complaint') ?>" method="POST">
      <input type="hidden" name="complaint_id" id="editComplaintId">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Category <span class="req">*</span></label>
          <select name="category" id="editCategory" class="form-select" required>
            <option value="maintenance">Maintenance Issue</option>
            <option value="roommate_conflict">Roommate Conflict</option>
            <option value="billing">Billing Concern</option>
            <option value="room_change">Room Change Request</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="form-group" id="editAnonymousSection" style="display:none;">
          <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:var(--gray-50);border-radius:var(--radius);border:1px solid var(--gray-200);cursor:pointer;">
            <input type="checkbox" name="is_anonymous" id="editIsAnonymous" value="1" style="margin-top:2px;">
            <div>
              <div style="font-size:0.85rem;font-weight:500;color:var(--gray-900);">Submit Anonymously</div>
              <div class="form-help" style="margin-top:2px;">Your identity will be hidden from the complaint view.</div>
            </div>
          </label>
        </div>
        <div class="form-group">
          <label class="form-label">Title <span class="req">*</span></label>
          <input type="text" name="title" id="editTitle" class="form-input" required>
        </div>
        <div class="form-group">
          <label class="form-label">Description <span class="req">*</span></label>
          <textarea name="description" id="editDescription" class="form-textarea" rows="5" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Complaint Form (hidden) -->
<form id="deleteComplaintForm" action="<?= Router::url('tenant/delete-complaint') ?>" method="POST" style="display:none;">
  <input type="hidden" name="complaint_id" id="deleteComplaintId">
</form>

<script>
var complaintsData = <?= json_encode(array_values($complaints)) ?>;

function openModal(id) {
  document.getElementById(id).style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
  document.body.style.overflow = '';
}

function openDetailModal(idx) {
  var c = complaintsData[idx];
  if (!c) return;
  document.getElementById('detailTitle').textContent = c.title;
  document.getElementById('detailCategory').textContent = c.category.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
  document.getElementById('detailStatus').textContent = c.status.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
  document.getElementById('detailDate').textContent = c.created_at;
  document.getElementById('detailDescription').innerHTML = (c.description || '').replace(/\n/g, '<br>');
  var respSection = document.getElementById('detailResponseSection');
  if (c.landlord_response) {
    respSection.style.display = 'block';
    document.getElementById('detailResponse').innerHTML = c.landlord_response.replace(/\n/g, '<br>');
    document.getElementById('detailResponseDate').textContent = c.resolved_at ? 'Responded on ' + c.resolved_at : '';
  } else {
    respSection.style.display = 'none';
  }
  var tenantRespSection = document.getElementById('detailTenantResponseSection');
  if (c.tenant_response) {
    tenantRespSection.style.display = 'block';
    document.getElementById('detailTenantResponse').innerHTML = c.tenant_response.replace(/\n/g, '<br>');
    document.getElementById('detailTenantResponseDate').textContent = c.tenant_response_at ? 'Responded on ' + c.tenant_response_at : '';
  } else {
    tenantRespSection.style.display = 'none';
  }
  openModal('detailModal');
}

function openEditModal(idx) {
  var c = complaintsData[idx];
  if (!c || c.status !== 'pending') return;
  document.getElementById('editComplaintId').value = c.id;
  document.getElementById('editCategory').value = c.category;
  document.getElementById('editTitle').value = c.title;
  document.getElementById('editDescription').value = c.description;
  document.getElementById('editIsAnonymous').checked = c.is_anonymous == 1;
  var anonSec = document.getElementById('editAnonymousSection');
  anonSec.style.display = c.category === 'roommate_conflict' ? 'block' : 'none';
  openModal('editModal');
}

function confirmDelete(id, title) {
  btConfirm({
    title: 'Delete Complaint',
    message: `Delete complaint "${title}"?\nThis action cannot be undone.`,
    confirmText: 'Yes, Delete',
    cancelText: 'Cancel',
    type: 'danger',
    icon: 'fa-trash-can'
  }).then(confirmed => {
    if (confirmed) {
      document.getElementById('deleteComplaintId').value = id;
      document.getElementById('deleteComplaintForm').submit();
    }
  });
}

// Anonymous toggle — create form
document.getElementById('category').addEventListener('change', function() {
  document.getElementById('anonymousSection').style.display = this.value === 'roommate_conflict' ? 'block' : 'none';
  if (this.value !== 'roommate_conflict') document.getElementById('is_anonymous').checked = false;
});

// Anonymous toggle — edit form
document.getElementById('editCategory').addEventListener('change', function() {
  document.getElementById('editAnonymousSection').style.display = this.value === 'roommate_conflict' ? 'block' : 'none';
  if (this.value !== 'roommate_conflict') document.getElementById('editIsAnonymous').checked = false;
});

document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.style.display = 'none';
    document.body.style.overflow = '';
  }
});
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay').forEach(function(m) { m.style.display = 'none'; });
    document.body.style.overflow = '';
  }
});

function applyFilter() {
  var status = document.getElementById('statusFilter').value;
  filterComplaints(status);
}

function clearFilter() {
  document.getElementById('statusFilter').value = 'all';
  filterComplaints('all');
}

function filterComplaints(status) {
  var rows = document.querySelectorAll('.bt-table tbody tr');
  var visibleCount = 0;
  rows.forEach(function(row) {
    if (status === 'all' || row.getAttribute('data-status') === status) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });
  
  // Show empty state if no results
  var emptyState = document.getElementById('filterEmptyState');
  if (visibleCount === 0 && !emptyState) {
    var tbody = document.querySelector('.bt-table tbody');
    if (tbody) {
      var emptyRow = document.createElement('tr');
      emptyRow.id = 'filterEmptyState';
      emptyRow.innerHTML = '<td colspan="5" style="text-align:center;padding:40px;color:var(--gray-500);"><i class="fa-solid fa-filter" style="font-size:2rem;margin-bottom:12px;display:block;opacity:0.3;"></i>No complaints found with this filter</td>';
      tbody.appendChild(emptyRow);
    }
  } else if (visibleCount > 0 && emptyState) {
    emptyState.remove();
  }
}
</script>

<style>
/* Fix table spacing and alignment */
.bt-table th,
.bt-table td {
  padding: 12px 16px !important;
  vertical-align: middle !important;
}

/* Column widths for better alignment and equal spacing */
.bt-table th:nth-child(1),
.bt-table td:nth-child(1) {
  width: 30%;
  min-width: 180px;
}

.bt-table th:nth-child(2),
.bt-table td:nth-child(2) {
  width: 18%;
  min-width: 130px;
  text-align: center;
}

.bt-table th:nth-child(3),
.bt-table td:nth-child(3) {
  width: 16%;
  min-width: 120px;
  text-align: center;
}

.bt-table th:nth-child(4),
.bt-table td:nth-child(4) {
  width: 18%;
  min-width: 130px;
  text-align: center;
}

.bt-table th:nth-child(5),
.bt-table td:nth-child(5) {
  width: 18%;
  min-width: 130px;
  text-align: center;
}

/* Center column headers */
.bt-table th[data-col="category"],
.bt-table th[data-col="status"],
.bt-table th[data-col="actions"] {
  text-align: center;
}

/* Center badges in their columns */
.bt-table td[data-col="category"],
.bt-table td[data-col="status"] {
  text-align: center;
}

/* Center action buttons */
.bt-table td[data-col="actions"] > div {
  justify-content: center !important;
}

/* Make badges more compact */
.bt-table .badge {
  font-size: 0.75rem;
  padding: 4px 10px;
}

/* Status badge colors */
.badge-pending {
  background: var(--warning-light);
  color: var(--warning);
}

.badge-progress {
  background: var(--brand-light);
  color: var(--brand);
}

.badge-resolved {
  background: var(--success-light);
  color: var(--success);
}

/* Mobile card fixes */
@media (max-width: 767px) {
  /* Create a scrollable container for cards on mobile */
  .card {
    max-height: calc(100vh - 280px) !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    -webkit-overflow-scrolling: touch;
  }
  
  /* Force table to be full width and prevent horizontal scroll */
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
  
  /* Add padding to last card so it's visible in scroll container */
  .bt-table tbody tr:last-child {
    margin-bottom: 20px !important;
  }
  
  /* Remove all column width constraints on mobile */
  .bt-table th,
  .bt-table td {
    width: auto !important;
    min-width: 0 !important;
    max-width: 100% !important;
  }
  
  /* Fix mobile card layout - reduce label width and gap */
  .bt-table td[data-label]:not(:first-child)::before {
    width: 85px !important;
    min-width: 85px !important;
    flex-shrink: 0 !important;
    padding-right: 0 !important;
  }
  
  .bt-table td[data-label]:not(:first-child) {
    display: flex !important;
    justify-content: flex-start !important;
    align-items: center !important;
    text-align: left !important;
    gap: 12px !important;
  }
  
  /* Override global word-break that causes vertical text */
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
  
  /* Allow wrapping only for long titles */
  .bt-table td:first-child .td-name {
    word-break: break-word !important;
    overflow-wrap: break-word !important;
  }
  
  /* Ensure proper text rendering */
  .bt-table td[data-label]::before {
    white-space: nowrap !important;
    word-break: keep-all !important;
  }
  
  /* Make mobile cards more compact */
  .bt-table td {
    padding: 10px 14px !important;
  }
  
  .bt-table td:first-child {
    padding: 14px 16px !important;
  }
  
  /* Center action buttons on mobile */
  .bt-table td[data-col="actions"] {
    justify-content: center !important;
    text-align: center !important;
  }
  
  .bt-table td[data-col="actions"]::before {
    display: none !important;
  }
  
  .bt-table td[data-col="actions"] > div {
    width: 100% !important;
    justify-content: center !important;
  }
  
  /* Style the scrollbar for better UX */
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
