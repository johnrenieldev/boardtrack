<?php
/**
 * BoardTrack — Landlord Profile
 */
$user = $user ?? [];
$gcashQrUrl = !empty($user['gcash_qr_path']) ? Router::upload('gcash', $user['gcash_qr_path']) : null;
?>
<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">My Profile</h1>
    <p class="dash-page-sub">Account details, security, and GCash QR for tenant payments.</p>
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

<div class="card" style="max-width: 600px; margin-top: 24px;">
  <div class="card-header">
    <h3><i class="fa-solid fa-user-circle"></i> General Information</h3>
  </div>
  <form action="<?= Router::url('landlord/updateProfile') ?>" method="POST"
        class="confirm-form" data-action="Update profile" data-message="Save changes to your profile?">
    <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
    <div style="padding: 20px 24px;">
      <div class="form-group">
        <label class="form-label">Full Name <span class="req">*</span></label>
        <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label">Email Address <span class="req">*</span></label>
        <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
      </div>
    </div>
    <div style="padding: 12px 24px 20px; text-align: right; border-top: 1px solid var(--gray-100);">
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</div>

<div class="card" style="max-width: 600px; margin-top: 20px;">
  <div class="card-header">
    <h3><i class="fa-solid fa-qrcode"></i> GCash QR Code</h3>
  </div>
  <div style="padding: 20px 24px;">
    <p style="font-size: 0.88rem; color: var(--color-text-secondary); margin: 0 0 16px;">
      Upload your GCash receive-money QR. Tenants who choose <strong>GCash</strong> will see this code when paying bills so they can scan and transfer, then upload their receipt for your review.
    </p>

    <?php if (!empty($landlord['gcash_qr'])): ?>
      <div style="text-align: center; margin-bottom: 16px;">
        <img src="<?= Router::upload('gcash_qr', $landlord['gcash_qr']) ?>" alt="GCash QR Code" style="max-width: 200px; display: inline-block; border: 1px solid var(--color-border); border-radius: var(--radius);">
      </div>
    <?php else: ?>
      <div style="text-align: center; padding: 24px; background: var(--color-canvas); border-radius: var(--radius); border: 1px dashed var(--color-border); margin-bottom: 16px;">
        <i class="fa-solid fa-qrcode" style="font-size: 2rem; color: var(--color-text-muted);"></i>
        <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin: 8px 0 0;">No QR uploaded yet</p>
      </div>
    <?php endif; ?>

    <form action="<?= Router::url('landlord/upload-gcash-qr') ?>" method="POST" enctype="multipart/form-data" class="confirm-form"
          data-action="Upload QR" data-message="Upload or replace your GCash QR code?">
      <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
      <div class="form-group">
        <label class="form-label"><?= $gcashQrUrl ? 'Replace QR image' : 'Upload QR image' ?> (JPG/PNG, max 2MB)</label>
        <input type="file" name="gcash_qr" class="form-input" accept="image/jpeg,image/png,.jpg,.jpeg,.png" <?= $gcashQrUrl ? '' : 'required' ?>>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload"></i> <?= $gcashQrUrl ? 'Update QR Code' : 'Upload QR Code' ?></button>
    </form>

    <?php if ($gcashQrUrl): ?>
      <form action="<?= Router::url('landlord/remove-gcash-qr') ?>" method="POST" style="margin-top: 12px;"
            data-confirm="Remove your GCash QR? Tenants will not be able to scan it until you upload a new one."
            data-action="Remove QR" data-color="#dc2626" data-confirm-text="Yes, remove">
        <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
        <button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger);">
          <i class="fa-solid fa-trash"></i> Remove QR Code
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

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
      <a href="<?= Router::url('auth/changePassword') ?>" class="btn btn-secondary btn-sm">
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

<!-- Log Out Session -->
<div class="card border border-danger-200 bg-danger-50/20" style="max-width: 600px; margin-top: 20px;">
  <div style="padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
    <div>
      <strong class="text-danger-700 font-bold text-base flex items-center gap-2">
        <i class="fa-solid fa-right-from-bracket"></i> Terminate Session
      </strong>
      <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin: 4px 0 0;">
        Securely log out of your BoardTrack landlord account.
      </p>
    </div>
    <a href="<?= Router::url('auth/logout') ?>" class="btn confirm-logout bg-danger-600 hover:bg-danger-700 text-white font-bold text-sm px-4 rounded flex items-center gap-2 shadow-sm transition-all" style="min-height: 44px; display: inline-flex; align-items: center; text-decoration: none;">
      <i class="fa-solid fa-right-from-bracket"></i> Log Out
    </a>
  </div>
</div>

