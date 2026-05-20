<?php
/**
 * BoardTrack — Landlord: View Tenant
 */
$tenant              = $tenant              ?? [];
$personalityAnswers  = $personalityAnswers  ?? [];
$bills               = $bills               ?? [];
$complaints          = $complaints          ?? [];
$isSuspicious        = $isSuspicious        ?? false;
$compatibilityScores = $compatibilityScores ?? [];
$availableRooms      = $availableRooms      ?? [];
?>
<div class="dash-page-header">
  <div class="flex items-center gap-4">
    <a href="<?= Router::url('landlord/tenants') ?>" class="btn btn-sm btn-outline">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    <div>
      <h1 class="dash-page-title"><?= htmlspecialchars($tenant['name']) ?></h1>
      <p class="dash-page-sub">Tenant Profile & Activity</p>
    </div>
  </div>
  <div class="dash-page-actions">
    <span class="badge badge-<?= match($tenant['user_status']) {
      'active' => 'success',
      'pending' => 'warning',
      'waiting_list' => 'info',
      'rejected' => 'danger',
      'moved_out' => 'secondary',
      default => 'secondary'
    } ?> p-2 px-4 text-sm">
      <?= ucfirst(str_replace('_', ' ', $tenant['user_status'])) ?>
    </span>
  </div>
</div>

<div class="dashboard-grid mt-6">
  <!-- Left Column: Profile & Personality -->
  <div class="grid-col-8">
    <div class="data-card mb-6">
      <div class="card-header">
        <h3><i class="fa-solid fa-user"></i> Basic Information</h3>
      </div>
      <div class="card-body">
        <div class="profile-details-grid">
          <div class="detail-group">
            <label>Full Name</label>
            <div class="detail-value"><?= htmlspecialchars($tenant['name']) ?></div>
          </div>
          <div class="detail-group">
            <label>Email Address</label>
            <div class="detail-value"><?= htmlspecialchars($tenant['email']) ?></div>
          </div>
          <div class="detail-group">
            <label>Room Preference</label>
            <div class="detail-value"><?= ucfirst($tenant['room_type_preference'] ?? 'Not specified') ?></div>
          </div>
          <div class="detail-group">
            <label>Registered On</label>
            <div class="detail-value"><?= date('F j, Y', strtotime($tenant['registered_at'])) ?></div>
          </div>
        </div>

        <?php if (!empty($tenant['guardian_name']) || !empty($tenant['guardian_email'])): ?>
        <div class="mt-6 pt-6 border-t border-gray-100">
          <h4 class="text-sm font-bold text-gray-800 mb-3"><i class="fa-solid fa-user-shield text-amber-600"></i> Guardian / Emergency Contact</h4>
          <div class="profile-details-grid">
            <div class="detail-group">
              <label>Contact Name</label>
              <div class="detail-value"><?= htmlspecialchars($tenant['guardian_name'] ?? '—') ?></div>
            </div>
            <div class="detail-group">
              <label>Contact Email</label>
              <div class="detail-value">
                <?php if (!empty($tenant['guardian_email'])): ?>
                  <a href="mailto:<?= htmlspecialchars($tenant['guardian_email']) ?>"><?= htmlspecialchars($tenant['guardian_email']) ?></a>
                <?php else: ?>—<?php endif; ?>
              </div>
            </div>
            <div class="detail-group" style="grid-column:1/-1;">
              <label>Why we contact this person</label>
              <div class="detail-value" style="white-space:pre-wrap;"><?= htmlspecialchars($tenant['guardian_purpose'] ?? '—') ?></div>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php $idDocPath = $tenant['id_document_path'] ?? $tenant['id_file_path'] ?? null; ?>
        <?php if ($idDocPath): ?>
          <div class="mt-6">
            <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Government ID</label>
            <a href="<?= Router::upload('ids', $idDocPath) ?>" target="_blank" class="id-preview-box">
              <img src="<?= Router::upload('ids', $idDocPath) ?>" alt="ID Proof" class="max-w-xs rounded border">
              <div class="mt-2 text-sm text-blue-600 font-medium">
                <i class="fa-solid fa-up-right-from-square"></i> View Full Size
              </div>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Personality Results -->
    <div class="data-card">
      <div class="card-header flex justify-between items-center">
        <h3><i class="fa-solid fa-brain"></i> Personality Questionnaire</h3>
        <?php if ($isSuspicious): ?>
          <span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> Suspicious Pattern</span>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php if (empty($personalityAnswers)): ?>
          <p class="text-muted">Questionnaire not yet completed.</p>
        <?php else: ?>
          <div class="personality-list">
            <?php foreach ($personalityAnswers as $answer): ?>
              <div class="personality-item mb-4 pb-4 border-b">
                <div class="font-medium text-gray-800 mb-1"><?= htmlspecialchars($answer['question_text']) ?></div>
                <div class="flex items-center gap-3">
                  <div class="text-sm text-blue-600 font-bold"><?= htmlspecialchars($answer['answer_text']) ?></div>
                  <div class="text-xs text-gray-400">(Score: <?= $answer['weight'] ?>)</div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Right Column: Room & Actions -->
  <div class="grid-col-4">
    <!-- Room Assignment -->
    <div class="data-card mb-6">
      <div class="card-header">
        <h3><i class="fa-solid fa-door-open"></i> Room Assignment</h3>
      </div>
      <div class="card-body">
        <?php if ($tenant['room_id'] ?? null): ?>
          <div class="room-box p-4 bg-blue-50 rounded-lg border border-blue-100 mb-4">
            <div class="text-xs font-bold text-blue-400 uppercase">Current Room</div>
            <div class="text-2xl font-bold text-blue-900"><?= htmlspecialchars($tenant['room_number'] ?? '—') ?></div>
            <div class="text-sm text-blue-600">Floor <?= $tenant['floor'] ?? '—' ?> · <?= ucfirst($tenant['room_type'] ?? 'N/A') ?></div>
          </div>
          <button type="button" class="btn btn-outline btn-block" onclick="showMoveOutModal()">
            <i class="fa-solid fa-right-from-bracket"></i> Mark as Moved Out
          </button>
        <?php else: ?>
          <div class="room-box p-4 bg-gray-50 rounded-lg border border-gray-100 mb-4 text-center">
            <i class="fa-solid fa-door-closed text-gray-300 text-3xl mb-2"></i>
            <p class="text-sm text-gray-500">No room assigned</p>
          </div>
          <button type="button" class="btn btn-primary btn-block" onclick="showAssignModal()">
            <i class="fa-solid fa-plus"></i> Assign Room
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Compatibility (If room assigned) -->
    <?php if (($tenant['room_id'] ?? null) && !empty($compatibilityScores)): ?>
      <div class="data-card mb-6">
        <div class="card-header">
          <h3><i class="fa-solid fa-people-arrows"></i> Roommate Compatibility</h3>
        </div>
        <div class="card-body">
          <?php foreach ($compatibilityScores as $score): ?>
            <div class="flex items-center justify-between mb-3">
              <div class="text-sm font-medium"><?= htmlspecialchars($score['roommate_name'] ?? 'Unknown') ?></div>
              <div class="text-sm font-bold <?= ($score['score'] ?? 0) >= 80 ? 'text-success' : (($score['score'] ?? 0) >= 60 ? 'text-warning' : 'text-danger') ?>">
                <?= $score['score'] ?? 0 ?>%
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Rejection (If pending) -->
    <?php if ($tenant['user_status'] === 'pending'): ?>
      <div class="data-card">
        <div class="card-header">
          <h3><i class="fa-solid fa-gears"></i> Review Actions</h3>
        </div>
        <div class="card-body">
          <button type="button" class="btn btn-success btn-block mb-2" onclick="showApproveModal()">
            <i class="fa-solid fa-check"></i> Approve & Assign
          </button>
          <button type="button" class="btn btn-danger btn-block" onclick="showRejectModal()">
            <i class="fa-solid fa-xmark"></i> Reject Application
          </button>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Approve Modal -->
<div class="modal-overlay" id="approveModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Approve Tenant</span>
      <button class="modal-close" onclick="closeModal('approveModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/approve-tenant') ?>" method="POST">
      <input type="hidden" name="tenant_id" value="<?= $tenant['id'] ?? '' ?>">
      <div class="modal-body">
        <p style="margin:0 0 16px;color:var(--gray-600);">You are about to approve <strong><?= htmlspecialchars($tenant['name'] ?? '') ?></strong>.</p>
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
    <form action="<?= Router::url('landlord/reject-tenant') ?>" method="POST"
          data-confirm="Reject this tenant application? This cannot be undone."
          data-action="Reject tenant" data-color="#dc2626" data-confirm-text="Yes, reject">
      <input type="hidden" name="tenant_id" value="<?= $tenant['id'] ?? '' ?>">
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

<!-- Assign Room Modal -->
<div class="modal-overlay" id="assignRoomModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Assign Room</span>
      <button class="modal-close" onclick="closeModal('assignRoomModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/approve-tenant') ?>" method="POST">
      <input type="hidden" name="tenant_id" value="<?= $tenant['id'] ?? '' ?>">
      <div class="modal-body">
        <p style="margin:0 0 16px;color:var(--gray-600);">Assign a room to <strong><?= htmlspecialchars($tenant['name'] ?? '') ?></strong>.</p>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Select Room <span class="req">*</span></label>
          <select name="room_id" class="form-select" required>
            <option value="">— Select a Room —</option>
            <?php foreach ($availableRooms as $r): ?>
              <option value="<?= $r['id'] ?>">Room <?= htmlspecialchars($r['room_number']) ?> (<?= ucfirst($r['room_type']) ?>, <?= $r['max_occupants'] - ($r['actual_occupants'] ?? 0) ?> spots)</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('assignRoomModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Assign Room</button>
      </div>
    </form>
  </div>
</div>

<!-- Move Out Modal -->
<div class="modal-overlay" id="moveOutModal" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Mark as Moved Out</span>
      <button class="modal-close" onclick="closeModal('moveOutModal')">&times;</button>
    </div>
    <form action="<?= Router::url('landlord/move-out-tenant') ?>" method="POST"
          data-confirm="Mark this tenant as moved out and remove them from their room?"
          data-action="Confirm move out" data-color="#dc2626" data-confirm-text="Yes, move out">
      <input type="hidden" name="tenant_id" value="<?= $tenant['id'] ?? '' ?>">
      <div class="modal-body">
        <p style="margin:0 0 16px;color:var(--gray-600);">Are you sure you want to mark <strong><?= htmlspecialchars($tenant['name'] ?? '') ?></strong> as moved out? This will remove them from Room <?= htmlspecialchars($tenant['room_number'] ?? '') ?>.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('moveOutModal')">Cancel</button>
        <button type="submit" class="btn btn-danger">Confirm Move Out</button>
      </div>
    </form>
  </div>
</div>

<!-- Modals Script -->
<script>
function openModal(id) {
  document.getElementById(id).style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
  document.body.style.overflow = '';
}
function showApproveModal() { openModal('approveModal'); }
function showRejectModal() { openModal('rejectModal'); }
function showAssignModal() { openModal('assignRoomModal'); }
function showMoveOutModal() { openModal('moveOutModal'); }
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
.profile-details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.detail-group label { display: block; font-size: 0.75rem; font-weight: 700; color: #9CA3AF; text-transform: uppercase; margin-bottom: 4px; }
.detail-value { font-size: 1rem; color: #1F2937; font-weight: 500; }
.id-preview-box { display: block; transition: opacity 0.2s; }
.id-preview-box:hover { opacity: 0.8; }
.grid-col-8 { grid-column: span 8; }
.grid-col-4 { grid-column: span 4; }
</style>
