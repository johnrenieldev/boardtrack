<?php
/**
 * BoardTrack — Landlord: View Complaint
 */
?>
<div class="dash-page-header">
  <div class="flex items-center gap-4">
    <a href="<?= Router::url('landlord/complaints') ?>" class="btn btn-sm btn-outline">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    <div>
      <h1 class="dash-page-title">Complaint Details</h1>
      <p class="dash-page-sub">Review and resolve tenant issues.</p>
    </div>
  </div>
</div>

<div class="dashboard-grid mt-6">
  <!-- Left Column: Complaint Details -->
  <div class="grid-col-8">
    <div class="data-card mb-6">
      <div class="card-header flex justify-between items-center">
        <h3><i class="fa-solid fa-circle-exclamation"></i> Complaint Information</h3>
        <span class="badge badge-<?= match($complaint['status']) {
          'resolved' => 'success',
          'pending' => 'danger',
          'in_progress' => 'warning',
          default => 'secondary'
        } ?>">
          <?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?>
        </span>
      </div>
      <div class="card-body">
        <div class="mb-6">
          <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Subject</label>
          <div class="text-xl font-bold text-gray-900"><?= htmlspecialchars($complaint['title']) ?></div>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
          <div>
            <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Category</label>
            <div class="flex items-center gap-2">
              <span class="badge badge-<?= match($complaint['category']) {
                'maintenance' => 'warning',
                'roommate_conflict' => 'danger',
                'billing' => 'info',
                'room_change' => 'primary',
                default => 'secondary'
              } ?>">
                <i class="fa-solid <?= match($complaint['category']) {
                  'maintenance' => 'fa-wrench',
                  'roommate_conflict' => 'fa-user-group',
                  'billing' => 'fa-file-invoice-dollar',
                  'room_change' => 'fa-door-open',
                  default => 'fa-circle'
                } ?>"></i>
                <?= ucfirst(str_replace('_', ' ', $complaint['category'])) ?>
              </span>
              <?php if ($complaint['is_anonymous']): ?>
                <span class="badge badge-secondary"><i class="fa-solid fa-user-secret"></i> Anonymous Submission</span>
              <?php endif; ?>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Submitted On</label>
            <div class="text-gray-700"><?= date('F j, Y, g:i a', strtotime($complaint['created_at'])) ?></div>
          </div>
        </div>

        <div class="mb-6">
          <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Description</label>
          <div class="p-4 bg-gray-50 rounded-lg border border-gray-100 text-gray-800 leading-relaxed">
            <?= nl2br(htmlspecialchars($complaint['description'])) ?>
          </div>
        </div>

        <?php if ($complaint['landlord_response']): ?>
          <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
            <label class="block text-xs font-bold uppercase text-blue-400 mb-1">Landlord Response</label>
            <div class="text-blue-900 leading-relaxed"><?= nl2br(htmlspecialchars($complaint['landlord_response'])) ?></div>
            <?php if ($complaint['resolved_at']): ?>
              <div class="mt-2 text-xs text-blue-400">Resolved on <?= date('M j, Y', strtotime($complaint['resolved_at'])) ?></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Right Column: Tenant Info & Actions -->
  <div class="grid-col-4">
    <div class="data-card mb-6">
      <div class="card-header">
        <h3><i class="fa-solid fa-user"></i> Submitting Tenant</h3>
      </div>
      <div class="card-body">
        <?php if ($complaint['is_anonymous']): ?>
          <div class="text-center py-4">
            <i class="fa-solid fa-user-secret text-4xl text-gray-300 mb-2"></i>
            <p class="font-bold text-gray-600">Anonymous Tenant</p>
            <p class="text-xs text-gray-400">Privacy protected for roommate conflict.</p>
          </div>
        <?php else: ?>
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl">
              <?= strtoupper(substr($complaint['display_name'] ?? 'A', 0, 1)) ?>
            </div>
            <div>
              <div class="font-bold text-gray-900"><?= htmlspecialchars($complaint['display_name'] ?? 'Anonymous') ?></div>
              <div class="text-xs text-gray-500">Room <?= htmlspecialchars($complaint['room_number'] ?? 'N/A') ?></div>
            </div>
          </div>
          <a href="<?= Router::url('landlord/view-tenant/' . $complaint['tenant_id']) ?>" class="btn btn-sm btn-outline btn-block">
            View Tenant Profile
          </a>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($complaint['status'] !== 'resolved' && $complaint['status'] !== 'closed'): ?>
      <div class="data-card">
        <div class="card-header">
          <h3><i class="fa-solid fa-reply"></i> Take Action</h3>
        </div>
        <div class="card-body">
          <form action="<?= Router::url('landlord/respond-complaint') ?>" method="POST">
            <input type="hidden" name="complaint_id" value="<?= $complaint['id'] ?>">
            
            <div class="form-group">
              <label>Update Status</label>
              <select name="status" class="form-select">
                <option value="in_progress" <?= $complaint['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="resolved">Resolved / Fixed</option>
                <option value="pending">Set Back to Pending</option>
              </select>
            </div>

            <div class="form-group">
              <label>Your Response <span class="required">*</span></label>
              <textarea name="response" class="form-textarea" rows="4" required placeholder="Write your response to the tenant..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
              Submit Response & Update
            </button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.grid-col-8 { grid-column: span 8; }
.grid-col-4 { grid-column: span 4; }
</style>
