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
    <p style="font-size: 0.88rem; color: var(--gray-500); margin: 0 0 16px;">
      Upload your GCash receive-money QR. Tenants who choose <strong>GCash</strong> will see this code when paying bills so they can scan and transfer, then upload their receipt for your review.
    </p>

    <?php if ($gcashQrUrl): ?>
      <div style="text-align: center; margin-bottom: 20px; padding: 16px; background: var(--gray-50); border-radius: var(--radius); border: 1px solid var(--gray-200);">
        <img src="<?= htmlspecialchars($gcashQrUrl) ?>" alt="GCash QR Code" style="max-width: 220px; width: 100%; border-radius: 8px;">
        <p style="font-size: 0.78rem; color: var(--success); margin: 12px 0 0;"><i class="fa-solid fa-circle-check"></i> Visible to tenants on GCash payments</p>
      </div>
    <?php else: ?>
      <div style="text-align: center; padding: 24px; background: var(--gray-50); border-radius: var(--radius); border: 1px dashed var(--gray-300); margin-bottom: 16px;">
        <i class="fa-solid fa-qrcode" style="font-size: 2rem; color: var(--gray-300);"></i>
        <p style="font-size: 0.85rem; color: var(--gray-500); margin: 8px 0 0;">No QR uploaded yet</p>
      </div>
    <?php endif; ?>

    <form action="<?= Router::url('landlord/upload-gcash-qr') ?>" method="POST" enctype="multipart/form-data" class="confirm-form"
          data-action="Upload QR" data-message="Upload or replace your GCash QR code?">
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
        <button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger);">
          <i class="fa-solid fa-trash"></i> Remove QR Code
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="card" style="max-width: 600px; margin-top: 20px;">
  <div class="card-header">
    <h3><i class="fa-solid fa-shield-halved"></i> Security</h3>
  </div>
  <div style="padding: 0 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--gray-100);">
      <div>
        <strong style="color: var(--gray-800);">Password</strong>
        <p style="font-size: 0.85rem; color: var(--gray-500); margin: 4px 0 0;">Change your login password.</p>
      </div>
      <a href="<?= Router::url('auth/changePassword') ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-key"></i> Change</a>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 0;">
      <div>
        <strong style="color: var(--gray-800);">Two-Factor Authentication</strong>
        <p style="font-size: 0.85rem; color: var(--gray-500); margin: 4px 0 0;">
          <?php if (!empty($user['totp_enabled'])): ?>
            <span style="color: var(--success);"><i class="fa-solid fa-check-circle"></i> Enabled</span>
          <?php else: ?>
            <span style="color: var(--warning);"><i class="fa-solid fa-triangle-exclamation"></i> Not enabled</span>
          <?php endif; ?>
        </p>
      </div>
      <a href="<?= Router::url('auth/setup2FA') ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-shield-halved"></i> Manage</a>
    </div>
  </div>
</div>
