<?php
/**
 * BoardTrack — Tenant Profile
 * app/views/tenant/profile.php
 *
 * UPDATED (Prompt 2):
 *  - Password change removed from this form (moved to auth/changePassword)
 *  - 2FA management link added
 *  - Profile update (name, email, room preference) unchanged
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
    </div>
  </div>

  <div class="card" style="max-width: 600px; margin-top: 20px; background-color: #fffdf5; border: 1px solid #fde68a; border-radius: var(--radius, 8px); padding: 24px; box-shadow: var(--shadow-sm);">
    <h3 style="display: flex; align-items: center; gap: 8px; margin-top: 0; margin-bottom: 8px; color: #78350f; font-size: 1.1rem; font-weight: 700;">
      <i class="fa-solid fa-user-shield" style="color: #d97706;"></i> Guardian / Parent / Emergency Contact
    </h3>
    <p style="font-size: 0.85rem; color: #92400e; margin-bottom: 20px; line-height: 1.4;">
      Someone we can reach if you are in danger, and who may be notified about important account and payment updates.
    </p>

    <div class="form-group" style="margin-bottom: 16px;">
      <label style="display: block; font-weight: 600; font-size: 0.9rem; color: #78350f; margin-bottom: 6px;">Contact Full Name <span style="color: #dc2626;">*</span></label>
      <input type="text" name="guardian_name" class="form-input" required
             value="<?= htmlspecialchars($tenant['guardian_name'] ?? '') ?>" placeholder="e.g. Maria dela Cruz (parent/guardian)">
    </div>

    <div class="form-group" style="margin-bottom: 16px;">
      <label style="display: block; font-weight: 600; font-size: 0.9rem; color: #78350f; margin-bottom: 6px;">Contact Email <span style="color: #dc2626;">*</span></label>
      <input type="email" name="guardian_email" class="form-input" required
             value="<?= htmlspecialchars($tenant['guardian_email'] ?? '') ?>" placeholder="parent@example.com">
    </div>

    <div class="form-group" style="margin-bottom: 12px;">
      <label style="display: block; font-weight: 600; font-size: 0.9rem; color: #78350f; margin-bottom: 6px;">Why should we contact this person? <span style="color: #dc2626;">*</span></label>
      <textarea name="guardian_purpose" class="form-input" required minlength="10" style="min-height: 80px; resize: vertical;" placeholder="e.g. My mother — contact in emergencies; notify her when my rent payment is confirmed."><?= htmlspecialchars($tenant['guardian_purpose'] ?? '') ?></textarea>
    </div>

    <p style="font-size: 0.8rem; color: #b45309; margin: 0 0 16px 0; line-height: 1.4;">
      Minimum 10 characters. This is shown to the landlord and used for payment confirmation emails.
    </p>
  </div>

  <div style="max-width: 600px; text-align: right; margin-top: 20px;">
    <button type="submit" class="btn btn-primary">Save Changes</button>
  </div>
</form>

<!-- Security section -->
<div class="card" style="max-width: 600px; margin-top: 20px;">
  <div class="card-header">
    <h3><i class="fa-solid fa-shield-halved"></i> Security</h3>
  </div>
  <div class="card-body" style="display: flex; flex-direction: column; gap: 16px;">

    <!-- Change Password -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 0; border-bottom: 1px solid var(--gray-200);">
      <div>
        <strong style="color: var(--gray-800);">Password</strong>
        <p style="font-size: 0.85rem; color: var(--gray-500); margin: 0;">
          Change your login password. Current password required.
        </p>
      </div>
      <a href="<?= Router::url('auth/changePassword') ?>" class="btn btn-secondary">
        <i class="fa-solid fa-key"></i> Change
      </a>
    </div>

    <!-- 2FA -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 0;">
      <div>
        <strong style="color: var(--gray-800);">Two-Factor Authentication</strong>
        <p style="font-size: 0.85rem; color: var(--gray-500); margin: 0;">
          <?php if (!empty($user['totp_enabled'])): ?>
            <span style="color: var(--green-600);"><i class="fa-solid fa-check-circle"></i> Enabled</span>
            — Google Authenticator is protecting your account.
          <?php else: ?>
            <span style="color: var(--amber-600);"><i class="fa-solid fa-exclamation-triangle"></i> Not enabled</span>
            — Add extra security with Google Authenticator.
          <?php endif; ?>
        </p>
      </div>
      <a href="<?= Router::url('auth/setup2FA') ?>" class="btn btn-secondary">
        <i class="fa-solid fa-shield-halved"></i> Manage
      </a>
    </div>

  </div>
</div>