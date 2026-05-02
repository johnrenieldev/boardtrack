<?php
/**
 * BoardTrack — Tenant Profile
 */
?>
<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">My Profile</h1>
    <p class="dash-page-sub">Update your personal information and room preferences.</p>
  </div>
</div>

<div class="card" style="max-width: 600px; margin-top: 24px;">
  <div class="card-header">
    <h3><i class="fa-solid fa-user-circle"></i> Profile Settings</h3>
  </div>
  <form action="<?= Router::url('tenant/update-profile') ?>" method="POST" class="confirm-form" data-action="Update Profile" data-message="Save changes to your profile?">
    <div class="card-body">
      <div class="form-group">
        <label>Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Email Address <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Room Priority Preference</label>
        <select name="room_type_preference" class="form-select">
            <option value="single" <?= ($tenant['room_type_preference'] ?? '') === 'single' ? 'selected' : '' ?>>Single Room</option>
            <option value="shared" <?= ($tenant['room_type_preference'] ?? '') === 'shared' ? 'selected' : '' ?>>Shared Room</option>
        </select>
        <div class="form-help">If you are on the waitlist, this preference helps the landlord assign you efficiently.</div>
      </div>
      <hr style="margin: 24px 0; border: 0; border-top: 1px solid var(--gray-200);">
      <h4 style="margin-bottom: 16px; color: var(--gray-700); font-family: var(--font-heading);">Change Password</h4>
      <p style="font-size: 0.85rem; color: var(--gray-500); margin-bottom: 16px;">Leave the password field blank if you do not wish to change it.</p>
      <div class="form-group">
        <label>New Password</label>
        <input type="password" name="new_password" class="form-input" placeholder="••••••••">
      </div>
    </div>
    <div class="card-footer" style="text-align: right;">
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</div>
