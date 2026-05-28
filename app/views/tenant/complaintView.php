<?php
/**
 * BoardTrack — Tenant: View Complaint
 */
?>
<div class="dash-page-header">
  <div class="flex items-center gap-4">
    <a href="<?= Router::url('tenant/complaints') ?>" id="backButton" class="btn btn-sm btn-outline">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    <div>
      <h1 class="dash-page-title">Complaint Details</h1>
      <p class="dash-page-sub">Track the status of your reported issue.</p>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var backButton = document.getElementById('backButton');
  if (backButton && sessionStorage.getItem('fromNotifications') === 'true') {
    backButton.href = '<?= Router::url('tenant/notifications') ?>';
    sessionStorage.removeItem('fromNotifications');
  }
});
</script>

<div class="dashboard-grid mt-6">
  <div class="grid-col-8 mx-auto">
    <div class="data-card">
      <div class="card-header flex justify-between items-center">
        <div class="flex items-center gap-3">
          <span class="badge badge-<?= match($complaint['category']) {
            'maintenance' => 'warning',
            'roommate_conflict' => 'danger',
            'billing' => 'info',
            'room_change' => 'primary',
            default => 'secondary'
          } ?>">
            <?= ucfirst(str_replace('_', ' ', $complaint['category'])) ?>
          </span>
          <?php if ($complaint['is_anonymous']): ?>
            <span class="badge badge-secondary"><i class="fa-solid fa-user-secret"></i> Anonymous</span>
          <?php endif; ?>
        </div>
        <span class="badge badge-<?= match($complaint['status']) {
          'resolved' => 'success',
          'pending' => 'danger',
          'in_progress' => 'warning',
          default => 'secondary'
        } ?> p-2 px-3">
          <?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?>
        </span>
      </div>
      <div class="card-body">
        <h2 class="text-2xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($complaint['title']) ?></h2>
        <div class="text-xs text-gray-400 mb-6">Submitted on <?= date('F j, Y, g:i a', strtotime($complaint['created_at'])) ?></div>

        <div class="p-5 bg-gray-50 rounded-xl border border-gray-100 text-gray-800 leading-relaxed mb-8">
          <?= nl2br(htmlspecialchars($complaint['description'])) ?>
        </div>

        <?php if ($complaint['landlord_response']): ?>
          <div class="response-section p-6 bg-blue-50 rounded-xl border border-blue-100 relative overflow-hidden mb-6">
            <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
              <i class="fa-solid fa-reply text-6xl text-blue-900"></i>
            </div>
            <div class="flex items-center gap-2 mb-3">
              <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">
                <i class="fa-solid fa-user-tie"></i>
              </div>
              <span class="font-bold text-blue-900">Landlord's Response</span>
            </div>
            <div class="text-blue-900 leading-relaxed">
              <?= nl2br(htmlspecialchars($complaint['landlord_response'])) ?>
            </div>
            <?php if ($complaint['resolved_at']): ?>
              <div class="mt-4 text-xs text-blue-400 font-medium border-t border-blue-100 pt-3">
                Resolved on <?= date('F j, Y, g:i a', strtotime($complaint['resolved_at'])) ?>
              </div>
            <?php endif; ?>
          </div>

          <?php if (!$complaint['tenant_response']): ?>
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-6">
              <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-comment-dots text-gray-600"></i>
                Your Reply
              </h3>
              <form action="<?= Router::url('tenant/respond-complaint') ?>" method="POST">
                <input type="hidden" name="complaint_id" value="<?= (int)$complaint['id'] ?>">
                <div class="mb-4">
                  <textarea name="response" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none" rows="4" placeholder="Write your response to the landlord..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                  <i class="fa-solid fa-paper-plane"></i> Send Response
                </button>
              </form>
            </div>
          <?php else: ?>
            <div class="response-section p-6 bg-green-50 rounded-xl border border-green-100 relative overflow-hidden">
              <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                <i class="fa-solid fa-reply text-6xl text-green-900"></i>
              </div>
              <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center text-xs">
                  <i class="fa-solid fa-user"></i>
                </div>
                <span class="font-bold text-green-900">Your Response</span>
              </div>
              <div class="text-green-900 leading-relaxed">
                <?= nl2br(htmlspecialchars($complaint['tenant_response'])) ?>
              </div>
              <?php if ($complaint['tenant_response_at']): ?>
                <div class="mt-4 text-xs text-green-400 font-medium border-t border-green-100 pt-3">
                  Responded on <?= date('F j, Y, g:i a', strtotime($complaint['tenant_response_at'])) ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-200">
            <i class="fa-solid fa-hourglass-half text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-400 font-medium">Awaiting landlord review and response.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
.grid-col-8 { grid-column: span 8; }
.mx-auto { margin-left: auto; margin-right: auto; }
</style>
