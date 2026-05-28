<?php
/**
 * Tenant profile view.
 *
 * Renders profile data, contact information, and update form fields.
 */
?>
<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">My Profile</h1>
    <p class="dash-page-sub">Update your personal information and room preferences.</p>
  </div>
</div>

<?php
$alerts = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
foreach ($alerts as $f):
?>
  <div class="alert <?= $f['type'] ?>" style="margin-top: 16px;">
    <i class="fa-solid <?= $f['type'] === 'error' ? 'fa-circle-xmark' : 'fa-circle-check' ?>"></i>
    <?= htmlspecialchars($f['message']) ?>
  </div>
<?php endforeach; ?>

<!-- Profile info form -->
<form action="<?= Router::url('tenant/updateProfile') ?>" method="POST"
      class="confirm-form" data-action="Update Profile" data-message="Save changes to your profile?">
  <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
  <div class="card" style="max-width: 600px; margin-top: 24px;">
    <div class="card-header">
      <h3><i class="fa-solid fa-user-circle"></i> Profile Settings</h3>
    </div>
    <div class="card-body">
      <div class="form-group">
        <label>Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-input"
               value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Email Address <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-input"
               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Room Priority Preference</label>
        <select name="room_type_preference" class="form-select">
          <option value="single" <?= ($tenant['room_type_preference'] ?? '') === 'single' ? 'selected' : '' ?>>Single Room</option>
          <option value="shared" <?= ($tenant['room_type_preference'] ?? '') === 'shared' ? 'selected' : '' ?>>Shared Room</option>
        </select>
        <div class="form-help">Helps the landlord assign you efficiently if you are on the waiting list.</div>
      </div>
      <div class="form-group">
        <div style="display: flex; align-items: center; gap: 10px; padding: 8px 0;">
          <input type="checkbox" name="air_conditioned_preference" id="aircon" value="1" 
                 <?= !empty($tenant['air_conditioned_preference']) ? 'checked' : '' ?>
                 style="width: 18px; h-18px; cursor: pointer;">
          <label for="aircon" style="margin: 0; cursor: pointer; font-weight: 600;">I prefer an air-conditioned room</label>
        </div>
        <div class="form-help">This is a preference and depends on room availability.</div>
      </div>
      <div class="form-group">
        <label>Gender <span class="text-danger">*</span></label>
        <select name="gender" class="form-select" required>
          <option value="male" <?= ($tenant['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
          <option value="female" <?= ($tenant['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
        </select>
      </div>
    </div>
  </div>

  <div class="card" style="max-width: 600px; margin-top: 20px; background-color: var(--warning-light); border: 1px solid var(--warning-border); border-radius: var(--radius, 8px); padding: 24px; box-shadow: var(--shadow-sm);">
    <h3 style="display: flex; align-items: center; gap: 8px; margin-top: 0; margin-bottom: 8px; color: var(--warning); font-size: 1.1rem; font-weight: 700;">
      <i class="fa-solid fa-user-shield" style="color: var(--warning);"></i> Guardian / Parent / Emergency Contact
    </h3>
    <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin-bottom: 20px; line-height: 1.4;">
      Someone we can reach if you are in danger, and who may be notified about important account and payment updates.
    </p>

    <div class="form-group" style="margin-bottom: 16px;">
      <label style="display: block; font-weight: 600; font-size: 0.9rem; color: var(--color-text-primary); margin-bottom: 6px;">Contact Full Name <span style="color: var(--color-danger);">*</span></label>
      <input type="text" name="guardian_name" class="form-input" required
             value="<?= htmlspecialchars($tenant['guardian_name'] ?? '') ?>" placeholder="e.g. Maria dela Cruz (parent/guardian)">
    </div>

    <div class="form-group" style="margin-bottom: 16px;">
      <label style="display: block; font-weight: 600; font-size: 0.9rem; color: var(--color-text-primary); margin-bottom: 6px;">Contact Email <span style="color: var(--color-danger);">*</span></label>
      <input type="email" name="guardian_email" class="form-input" required
             value="<?= htmlspecialchars($tenant['guardian_email'] ?? '') ?>" placeholder="parent@example.com">
    </div>

    <div class="form-group" style="margin-bottom: 12px;">
      <label style="display: block; font-weight: 600; font-size: 0.9rem; color: var(--color-text-primary); margin-bottom: 6px;">Why should we contact this person? <span style="color: var(--color-danger);">*</span></label>
      <textarea name="guardian_purpose" class="form-input" required minlength="10" style="min-height: 80px; resize: vertical;" placeholder="e.g. My mother — contact in emergencies; notify her when my rent payment is confirmed."><?= htmlspecialchars($tenant['guardian_purpose'] ?? '') ?></textarea>
    </div>

    <p style="font-size: 0.8rem; color: var(--color-text-muted); margin: 0 0 16px 0; line-height: 1.4;">
      Minimum 10 characters. This is shown to the landlord and used for payment confirmation emails.
    </p>
  </div>

  <div style="max-width: 600px; text-align: right; margin-top: 20px;">
    <button type="submit" class="btn btn-primary">Save Changes</button>
  </div>
</form>

<!-- Security section: Password -->
<div class="card" style="max-width: 600px; margin-top: 20px;">
  <div class="card-header">
    <h3><i class="fa-solid fa-key"></i> Password</h3>
  </div>
  <div class="card-body">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0;">
      <div>
        <strong style="color: var(--color-text-primary);">Account Password</strong>
        <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin: 4px 0 0;">
          Update your login password. Your current password is required.
        </p>
      </div>
      <a href="<?= Router::url('tenant/changePassword') ?>" class="btn btn-secondary btn-sm">
        <i class="fa-solid fa-lock"></i> Change
      </a>
    </div>
  </div>
</div>

<!-- Security section: 2FA -->
<div class="card" style="max-width: 600px; margin-top: 20px;">
  <div class="card-header">
    <h3><i class="fa-solid fa-shield-halved"></i> Two-Factor Authentication</h3>
  </div>
  <div class="card-body">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0;">
      <div>
        <strong style="color: var(--color-text-primary);">Security Layer</strong>
        <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin: 4px 0 0;">
          <?php if (!empty($user['totp_enabled'])): ?>
            <span style="color: var(--color-success); font-weight: 600;">
              <i class="fa-solid fa-check-circle"></i> Enabled
            </span>
            — Google Authenticator is protecting your account.
          <?php else: ?>
            <span style="color: var(--warning-600, #d97706); font-weight: 600;">
              <i class="fa-solid fa-triangle-exclamation"></i> Not enabled
            </span>
            — Add extra security with Google Authenticator.
          <?php endif; ?>
        </p>
      </div>
      <a href="<?= Router::url('auth/setup2FA') ?>" class="btn btn-secondary btn-sm">
        <i class="fa-solid fa-gear"></i> Manage
      </a>
    </div>
  </div>
</div>

<!-- Rate BoardTrack (Optional) -->
<?php
  $hasSubmittedReview = $hasSubmittedReview ?? false;
  $userReview = $userReview ?? null;
  if (($userStatus ?? '') === 'approved'):
?>
<div class="card" style="max-width: 600px; margin-top: 20px;">
  <div class="card-header">
    <h3><i class="fa-solid fa-star"></i> Rate BoardTrack</h3>
  </div>
  <div class="card-body">
    <?php if ($hasSubmittedReview): ?>
      <div class="alert alert-success" style="margin-top: 0;">
        <i class="fa-solid fa-check-circle"></i>
        Thank you for your review! Your rating: <?= str_repeat('★', $userReview['rating'] ?? 5) ?><?= str_repeat('☆', 5 - ($userReview['rating'] ?? 5)) ?>
      </div>
      <div style="background: var(--gray-50); padding: 16px; border-radius: 8px; margin-top: 12px;">
        <p style="font-style: italic; color: var(--color-text-secondary); margin: 0;">
          "<?= htmlspecialchars($userReview['review_text'] ?? '') ?>"
        </p>
        <p style="font-size: 0.8rem; color: var(--color-text-muted); margin: 8px 0 0;">
          Submitted on <?= date('M d, Y', strtotime($userReview['created_at'] ?? 'now')) ?>
        </p>
      </div>
      <div style="margin-top: 16px;">
        <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('editReviewForm').classList.remove('hidden');">
          <i class="fa-solid fa-pen"></i> Edit Review
        </button>
      </div>

      <!-- Edit Review Form -->
      <div id="editReviewForm" class="hidden" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--gray-200);">
        <form action="<?= Router::url('tenant/update-review') ?>" method="POST" id="editReviewFormSubmit">
          <input type="hidden" name="testimonial_id" value="<?= $userReview['id'] ?? '' ?>">
          <div class="form-group">
            <label>Your Rating</label>
            <div class="flex gap-2" id="editStarRating" style="margin-bottom: 8px;">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <button type="button" class="edit-star-btn text-2xl <?= $i <= ($userReview['rating'] ?? 5) ? 'text-yellow-400' : 'text-gray-300' ?> hover:text-yellow-400 transition-colors" data-rating="<?= $i ?>">
                  <i class="fa-solid fa-star"></i>
                </button>
              <?php endfor; ?>
            </div>
            <input type="hidden" id="editRatingInput" name="rating" value="<?= $userReview['rating'] ?? 5 ?>">
          </div>

          <div class="form-group">
            <label>Your Review</label>
            <textarea 
              id="editReviewText" 
              name="review_text" 
              rows="4" 
              class="form-input"
              required
            ><?= htmlspecialchars($userReview['review_text'] ?? '') ?></textarea>
            <div class="form-help">Minimum 10 characters</div>
          </div>

          <div style="display: flex; gap: 8px; margin-top: 16px;">
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-save"></i> Save Changes
            </button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('editReviewForm').classList.add('hidden');">
              Cancel
            </button>
          </div>
        </form>
      </div>
    <?php else: ?>
      <div style="text-align: center; padding: 20px 0;">
        <p style="color: var(--color-text-secondary); margin-bottom: 20px;">
          Share your experience with us! Your feedback helps us improve our services.
        </p>
        <a href="<?= Router::url('tenant/review') ?>" class="btn btn-primary">
          <i class="fa-solid fa-star"></i> Submit a Review
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- Log Out Session -->
<div class="card border border-danger-200 bg-danger-50/20" style="max-width: 600px; margin-top: 20px;">
  <div style="padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
    <div>
      <strong class="text-danger-700 font-bold text-base flex items-center gap-2">
        <i class="fa-solid fa-right-from-bracket"></i> Terminate Session
      </strong>
      <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin: 4px 0 0;">
        Securely log out of your BoardTrack tenant account.
      </p>
    </div>
    <a href="<?= Router::url('auth/logout') ?>" class="btn confirm-logout bg-danger-600 hover:bg-danger-700 text-white font-bold text-sm px-4 rounded flex items-center gap-2 shadow-sm transition-all" style="min-height: 44px; display: inline-flex; align-items: center; text-decoration: none;">
      <i class="fa-solid fa-right-from-bracket"></i> Log Out
    </a>
  </div>
</div>