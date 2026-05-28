<?php
/**
 * BoardTrack — Landlord: View Complaint
 */
?>
<div class="complaint-page-header">
  <div class="flex items-center gap-4 mb-2">
    <a href="<?= Router::url('landlord/complaints') ?>" id="backButton" class="btn-back">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
      <h1 class="complaint-title">Complaint Details</h1>
      <p class="complaint-subtitle">Review and resolve tenant issues.</p>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var backButton = document.getElementById('backButton');
  if (backButton && sessionStorage.getItem('fromNotifications') === 'true') {
    backButton.href = '<?= Router::url('landlord/notifications') ?>';
    sessionStorage.removeItem('fromNotifications');
  }
});
</script>

<div class="complaint-grid">
  <!-- Left Column: Complaint Details -->
  <div class="complaint-main space-y-6">
    <!-- Status Card -->
    <div class="card overflow-hidden bg-brand-600 shadow-lg shadow-brand-500/10 border-none">
      <div class="p-5 flex items-center gap-5">
        <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-xl text-white border border-white/20">
          <i class="fa-solid <?= match($complaint['status']) {
            'resolved' => 'fa-circle-check',
            'pending' => 'fa-clock',
            'in_progress' => 'fa-spinner',
            default => 'fa-circle-info'
          } ?>"></i>
        </div>
        <div class="flex-1">
          <div class="text-[0.6rem] font-black text-white/70 uppercase tracking-widest mb-0.5">Current Status</div>
          <div class="text-xl font-black text-white flex items-center gap-2">
            <?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?>
            <span class="w-1.5 h-1.5 rounded-full <?= match($complaint['status']) {
              'resolved' => 'bg-success-400',
              'in_progress' => 'bg-warning-400',
              'pending' => 'bg-amber-400',
              default => 'bg-gray-400'
            } ?> shadow-[0_0_6px_rgba(255,255,255,0.4)]"></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Complaint Information Card -->
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
        <h3 class="font-black text-gray-900 flex items-center gap-2 uppercase tracking-widest text-xs">
          <i class="fa-solid fa-circle-exclamation text-brand-600"></i> Complaint Information
        </h3>
        <div class="category-badge px-3 py-1 rounded-lg text-[0.65rem] font-black uppercase tracking-widest border <?= match($complaint['category']) {
          'maintenance' => 'bg-blue-50 text-blue-600 border-blue-200',
          'roommate_conflict' => 'bg-danger-50 text-danger-600 border-danger-200',
          'billing' => 'bg-amber-50 text-amber-600 border-amber-200',
          'room_change' => 'bg-success-50 text-success-600 border-success-200',
          default => 'bg-gray-50 text-gray-600 border-gray-200'
        } ?>">
          <?= ucfirst(str_replace('_', ' ', $complaint['category'])) ?>
        </div>
      </div>
      <div class="p-8 space-y-8">
        <!-- Subject -->
        <div>
          <label class="block text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-2">Subject</label>
          <div class="text-2xl font-black text-gray-900 leading-tight"><?= htmlspecialchars($complaint['title']) ?></div>
        </div>

        <!-- Meta Info -->
        <div class="grid grid-cols-2 gap-8 py-6 border-y border-gray-50">
          <div>
            <label class="block text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1">Submitted On</label>
            <div class="text-sm font-black text-gray-700"><?= date('F j, Y', strtotime($complaint['created_at'])) ?></div>
            <div class="text-[0.65rem] text-gray-400 font-bold"><?= date('g:i A', strtotime($complaint['created_at'])) ?></div>
          </div>
          <div>
            <label class="block text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-1">Privacy Level</label>
            <?php if ($complaint['is_anonymous']): ?>
              <div class="text-xs font-black text-amber-600 flex items-center gap-1.5 uppercase tracking-tight">
                <i class="fa-solid fa-user-secret"></i> Anonymous Submission
              </div>
            <?php else: ?>
              <div class="text-xs font-black text-success-600 flex items-center gap-1.5 uppercase tracking-tight">
                <i class="fa-solid fa-user-check"></i> Verified Identity
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-3">Description</label>
          <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100 text-sm font-medium text-gray-700 leading-relaxed italic">
            "<?= nl2br(htmlspecialchars($complaint['description'])) ?>"
          </div>
        </div>

        <!-- Responses -->
        <div class="space-y-4">
          <?php if ($complaint['landlord_response']): ?>
            <div class="p-5 bg-brand-50 rounded-2xl border border-brand-100 relative overflow-hidden">
              <i class="fa-solid fa-shield-halved absolute -right-4 -bottom-4 text-brand-100 text-6xl opacity-30"></i>
              <div class="relative">
                <div class="flex items-center justify-between mb-3">
                  <div class="text-[0.65rem] font-black text-brand-400 uppercase tracking-widest">Landlord Response</div>
                  <?php if ($complaint['resolved_at']): ?>
                    <span class="text-[0.6rem] font-black text-brand-400 uppercase">Resolved: <?= date('M j, Y', strtotime($complaint['resolved_at'])) ?></span>
                  <?php endif; ?>
                </div>
                <div class="text-sm font-bold text-brand-900 leading-relaxed">
                  <?= nl2br(htmlspecialchars($complaint['landlord_response'])) ?>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($complaint['tenant_response']): ?>
            <div class="p-5 bg-success-50 rounded-2xl border border-success-100 relative overflow-hidden">
              <i class="fa-solid fa-reply absolute -right-4 -bottom-4 text-success-100 text-6xl opacity-30"></i>
              <div class="relative">
                <div class="flex items-center justify-between mb-3">
                  <div class="text-[0.65rem] font-black text-success-400 uppercase tracking-widest">Tenant Follow-up</div>
                  <span class="text-[0.6rem] font-black text-success-400 uppercase"><?= date('M j, Y', strtotime($complaint['tenant_response_at'])) ?></span>
                </div>
                <div class="text-sm font-bold text-success-900 leading-relaxed">
                  <?= nl2br(htmlspecialchars($complaint['tenant_response'])) ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Column: Tenant Info & Actions -->
  <div class="complaint-sidebar space-y-6">
    <!-- Tenant Card -->
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
        <h3 class="text-[0.65rem] font-black text-gray-400 uppercase tracking-widest">Submitting Tenant</h3>
      </div>
      <div class="p-6">
        <?php if ($complaint['is_anonymous']): ?>
          <div class="flex flex-col items-center text-center p-4">
            <div class="w-16 h-16 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-4 border-4 border-white shadow-sm">
              <i class="fa-solid fa-user-secret"></i>
            </div>
            <div class="font-black text-gray-900 uppercase tracking-tight mb-1">Identity Hidden</div>
            <p class="text-[0.65rem] text-gray-400 font-bold leading-tight px-4">Privacy protected due to sensitive nature of complaint.</p>
          </div>
        <?php else: ?>
          <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-2xl bg-brand-100 text-brand-600 flex items-center justify-center text-xl font-black border-2 border-white shadow-sm">
              <?= strtoupper(substr($complaint['display_name'] ?? 'A', 0, 1)) ?>
            </div>
            <div>
              <div class="font-black text-gray-900 leading-tight"><?= htmlspecialchars($complaint['display_name'] ?? 'Anonymous') ?></div>
              <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Room <?= htmlspecialchars($complaint['room_number'] ?? 'N/A') ?></div>
            </div>
          </div>
          <a href="<?= Router::url('landlord/view-tenant/' . $complaint['tenant_id']) ?>" class="btn btn-secondary w-full py-2.5 font-black text-[0.65rem] uppercase tracking-widest rounded-xl shadow-xs transition-all hover:bg-gray-100">
            <i class="fa-solid fa-user-circle mr-2"></i> View Profile
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Action Card -->
    <?php if ($complaint['status'] !== 'resolved' && $complaint['status'] !== 'closed'): ?>
      <div class="card overflow-hidden border-brand-100 shadow-lg shadow-brand-500/5">
        <div class="px-6 py-4 border-b border-brand-50 bg-brand-50/30 flex items-center justify-between">
          <h3 class="font-black text-brand-900 flex items-center gap-2 uppercase tracking-widest text-xs">
            <i class="fa-solid fa-reply-all"></i> Resolution Center
          </h3>
          <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
        </div>
        <div class="p-6">
          <form action="<?= Router::url('landlord/respond-complaint') ?>" method="POST" class="space-y-5">
            <input type="hidden" name="complaint_id" value="<?= $complaint['id'] ?>">
            
            <div>
              <label class="block text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-2">Update Progress</label>
              <select name="status" class="form-select font-black text-xs uppercase tracking-tight rounded-xl border-gray-200 focus:border-brand-500 transition-all">
                <option value="in_progress" <?= $complaint['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="resolved">Mark as Resolved</option>
                <option value="pending">Move to Pending</option>
              </select>
            </div>

            <div>
              <label class="block text-[0.65rem] font-black text-gray-400 uppercase tracking-widest mb-2">Official Response <span class="text-danger">*</span></label>
              <textarea name="response" class="form-textarea text-sm font-medium rounded-2xl border-gray-200 focus:border-brand-500 transition-all min-h-[120px]" required placeholder="Detail the steps taken to address this issue..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-full py-4 flex items-center justify-center gap-3 text-sm font-black uppercase tracking-widest shadow-lg shadow-brand-500/20 rounded-2xl transition-all hover:-translate-y-1 active:translate-y-0">
              <i class="fa-solid fa-paper-plane text-lg"></i>
              Post Response
            </button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
/* Clean up existing custom styles and replace with standard classes where possible */
.complaint-page-header { margin-bottom: 24px; }
.btn-back { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 12px; background: white; border: 1px solid #e2e8f0; color: #64748b; transition: all 0.2s ease; }
.btn-back:hover { background: #f8fafc; color: #0f172a; transform: translateX(-4px); }
.complaint-grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; }
@media (max-width: 1024px) { .complaint-grid { grid-template-columns: 1fr; } }
</style>
