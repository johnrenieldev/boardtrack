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
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
  <div class="stat-card">
    <div class="stat-label">Total</div>
    <div class="stat-value"><?= $totalComplaints ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Pending</div>
    <div class="stat-value"><?= $pendingCount ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">In Progress</div>
    <div class="stat-value"><?= $inProgressCount ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Resolved</div>
    <div class="stat-value"><?= $resolvedCount ?></div>
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
            <th>Category</th>
            <th>Status</th>
            <th>Submitted</th>
            <th>Actions</th>
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
          <tr>
            <td>
              <div class="td-name">
                <?= htmlspecialchars($complaint['title']) ?>
                <?php if (!empty($complaint['is_anonymous'])): ?>
                  <span class="badge badge-normal" style="margin-left:4px;font-size:0.65rem;"><i class="fa-solid fa-user-secret"></i> Anon</span>
                <?php endif; ?>
              </div>
            </td>
            <td>
              <span class="badge <?= $categoryBadge[$complaint['category']] ?? 'badge-normal' ?>">
                <?= ucfirst(str_replace('_', ' ', $complaint['category'])) ?>
              </span>
            </td>
            <td>
              <span class="badge <?= $statusBadge[$complaint['status']] ?? 'badge-normal' ?>">
                <?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?>
              </span>
            </td>
            <td style="color:var(--gray-400);font-size:0.82rem;"><?= date('M d, Y', strtotime($complaint['created_at'])) ?></td>
            <td>
              <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="openDetailModal(<?= $idx ?>)">
                  <i class="fa-solid fa-eye"></i> View
                </button>
                <?php if ($complaint['status'] === 'pending'): ?>
                  <button type="button" class="btn btn-secondary btn-sm" onclick="openEditModal(<?= $idx ?>)" style="color:var(--primary);">
                    <i class="fa-solid fa-pen"></i> Edit
                  </button>
                  <button type="button" class="btn btn-secondary btn-sm" onclick="confirmDelete(<?= (int)$complaint['id'] ?>, <?= htmlspecialchars(json_encode($complaint['title'])) ?>)" style="color:#ef4444;">
                    <i class="fa-solid fa-trash"></i> Delete
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
        <div id="detailDescription" style="font-size:0.85rem;color:var(--gray-700);line-height:1.6;padding:12px;background:var(--gray-50);border-radius:var(--radius);"></div>
      </div>
      <div id="detailResponseSection" style="display:none;">
        <div style="border-top:1px solid var(--gray-200);padding-top:14px;">
          <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
            <i class="fa-solid fa-reply" style="color:var(--gray-500);font-size:0.8rem;"></i>
            <span style="font-weight:600;font-size:0.85rem;color:var(--gray-700);">Landlord's Response</span>
          </div>
          <div id="detailResponse" style="font-size:0.85rem;color:var(--gray-700);line-height:1.6;padding:12px;background:var(--gray-50);border-radius:var(--radius);"></div>
          <div id="detailResponseDate" style="font-size:0.75rem;color:var(--gray-400);margin-top:6px;"></div>
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
          <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:var(--gray-50);border-radius:var(--radius);border:1px solid var(--gray-200);cursor:pointer;">
            <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1" style="margin-top:2px;">
            <div>
              <div style="font-size:0.85rem;font-weight:500;color:var(--gray-900);">Submit Anonymously</div>
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
  if (confirm('Delete complaint "' + title + '"?\nThis action cannot be undone.')) {
    document.getElementById('deleteComplaintId').value = id;
    document.getElementById('deleteComplaintForm').submit();
  }
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
</script>
