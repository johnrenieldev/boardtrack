<?php
/**
 * BoardTrack — Tenant Pending View
 * app/views/tenant/pending.php
 * Layout: tenant.php
 *
 * Shown to tenants whose account is pending, on waiting list, or active without room.
 */
$user   = $user   ?? [];
$tenant = $tenant ?? [];
$status = $user['status'] ?? 'pending';
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1 class="page-title">Account Status</h1>
      <p class="page-subtitle">Your current registration status</p>
    </div>
  </div>
</div>

<div style="max-width:640px;">

  <?php if ($status === 'pending' || $status === 'unverified'): ?>
    <!-- PENDING REVIEW -->
    <div class="card" style="text-align:center;padding:40px 32px;">
      <div style="width:64px;height:64px;border-radius:50%;background:var(--warning-light);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
        <i class="fa-solid fa-hourglass-half" style="font-size:1.5rem;color:var(--warning);"></i>
      </div>
      <h2 style="font-family:var(--font-heading);font-size:1.15rem;font-weight:700;color:var(--gray-900);margin:0 0 8px;">Your Application is Under Review</h2>
      <p style="font-size:0.88rem;color:var(--gray-500);margin:0 0 24px;max-width:440px;margin-left:auto;margin-right:auto;line-height:1.6;">
        The landlord is reviewing your registration. You will be notified once a decision has been made.
      </p>
      <?php if (!empty($user['created_at'])): ?>
        <p style="font-size:0.78rem;color:var(--gray-400);margin:0 0 24px;">
          You submitted your application on <?= date('M d, Y', strtotime($user['created_at'])) ?>
        </p>
      <?php endif; ?>

      <!-- Progress Steps -->
      <div style="text-align:left;max-width:360px;margin:0 auto;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
          <i class="fa-solid fa-circle-check" style="color:var(--success);font-size:0.85rem;width:18px;text-align:center;"></i>
          <span style="font-size:0.85rem;color:var(--gray-700);">Account registered</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
          <?php if ($tenant['personality_completed'] ?? false): ?>
            <i class="fa-solid fa-circle-check" style="color:var(--success);font-size:0.85rem;width:18px;text-align:center;"></i>
            <span style="font-size:0.85rem;color:var(--gray-700);">Personality quiz completed</span>
          <?php else: ?>
            <i class="fa-solid fa-circle-exclamation" style="color:var(--warning);font-size:0.85rem;width:18px;text-align:center;"></i>
            <span style="font-size:0.85rem;color:var(--gray-700);">Personality quiz <a href="<?= Router::url('tenant/personality') ?>" style="color:var(--primary);font-weight:600;">not completed</a></span>
          <?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
          <i class="fa-regular fa-circle" style="color:var(--gray-300);font-size:0.85rem;width:18px;text-align:center;"></i>
          <span style="font-size:0.85rem;color:var(--gray-400);">Landlord review pending</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
          <i class="fa-regular fa-circle" style="color:var(--gray-300);font-size:0.85rem;width:18px;text-align:center;"></i>
          <span style="font-size:0.85rem;color:var(--gray-400);">Room assignment</span>
        </div>
      </div>

      <?php if (!($tenant['personality_completed'] ?? false)): ?>
        <div style="margin-top:24px;">
          <a href="<?= Router::url('tenant/personality') ?>" class="btn btn-primary">
            <i class="fa-solid fa-clipboard-list"></i> Complete Personality Quiz
          </a>
        </div>
      <?php endif; ?>
    </div>

  <?php elseif ($status === 'waiting_list'): ?>
    <!-- WAITING LIST -->
    <div class="card" style="text-align:center;padding:40px 32px;">
      <div style="width:64px;height:64px;border-radius:50%;background:var(--info-light);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
        <i class="fa-solid fa-list-ol" style="font-size:1.5rem;color:var(--info);"></i>
      </div>
      <h2 style="font-family:var(--font-heading);font-size:1.15rem;font-weight:700;color:var(--gray-900);margin:0 0 8px;">You're on the Waiting List</h2>
      <p style="font-size:0.88rem;color:var(--gray-500);margin:0 0 16px;max-width:440px;margin-left:auto;margin-right:auto;line-height:1.6;">
        You've been approved! However, your preferred room type is currently full. You'll be notified when a room becomes available.
      </p>
      <?php if (!empty($tenant['approved_at'] ?? $user['updated_at'] ?? '')): ?>
        <p style="font-size:0.78rem;color:var(--gray-400);margin:0;">
          Approved on <?= date('M d, Y', strtotime($tenant['approved_at'] ?? $user['updated_at'])) ?>
        </p>
      <?php endif; ?>
    </div>

  <?php elseif ($status === 'active' && empty($tenant['room_id'])): ?>
    <!-- ACTIVE BUT NO ROOM -->
    <div class="card" style="text-align:center;padding:40px 32px;">
      <div style="width:64px;height:64px;border-radius:50%;background:var(--success-light);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
        <i class="fa-solid fa-door-open" style="font-size:1.5rem;color:var(--success);"></i>
      </div>
      <h2 style="font-family:var(--font-heading);font-size:1.15rem;font-weight:700;color:var(--gray-900);margin:0 0 8px;">Room Assignment Pending</h2>
      <p style="font-size:0.88rem;color:var(--gray-500);margin:0;max-width:440px;margin-left:auto;margin-right:auto;line-height:1.6;">
        Your account is active. The landlord will assign you to a room shortly.
      </p>
    </div>

  <?php else: ?>
    <!-- FALLBACK -->
    <div class="card" style="text-align:center;padding:40px 32px;">
      <div style="width:64px;height:64px;border-radius:50%;background:var(--gray-100);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
        <i class="fa-solid fa-circle-info" style="font-size:1.5rem;color:var(--gray-400);"></i>
      </div>
      <h2 style="font-family:var(--font-heading);font-size:1.15rem;font-weight:700;color:var(--gray-900);margin:0 0 8px;">Account Status</h2>
      <p style="font-size:0.88rem;color:var(--gray-500);margin:0;">
        Current status: <span class="badge badge-normal"><?= ucfirst(htmlspecialchars($status)) ?></span>
      </p>
    </div>
  <?php endif; ?>

  <!-- What Happens Next -->
  <div class="card" style="margin-top:16px;">
    <div class="card-title" style="margin-bottom:12px;">What happens next?</div>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <div style="display:flex;gap:10px;font-size:0.85rem;color:var(--gray-600);">
        <span style="font-weight:700;color:var(--primary);flex-shrink:0;">1.</span>
        The landlord will review your registration details and uploaded ID.
      </div>
      <div style="display:flex;gap:10px;font-size:0.85rem;color:var(--gray-600);">
        <span style="font-weight:700;color:var(--primary);flex-shrink:0;">2.</span>
        Your personality quiz results will be used for compatible roommate matching.
      </div>
      <div style="display:flex;gap:10px;font-size:0.85rem;color:var(--gray-600);">
        <span style="font-weight:700;color:var(--primary);flex-shrink:0;">3.</span>
        You'll receive a notification when your application is approved or rejected.
      </div>
      <div style="display:flex;gap:10px;font-size:0.85rem;color:var(--gray-600);">
        <span style="font-weight:700;color:var(--primary);flex-shrink:0;">4.</span>
        If approved, you'll be assigned to a room or placed on the waiting list.
      </div>
    </div>
  </div>

</div>
