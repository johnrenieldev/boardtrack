<?php
/**
 * BoardTrack — 2FA Setup / Management Page
 * app/views/auth/setup_2fa.php
 *
 * @var Controller  $this
 * @var array       $user          Current user row
 * @var bool        $has2FA        Whether 2FA is currently enabled
 * @var string|null $pendingSecret Base32 secret (pending verification)
 * @var string|null $qrUrl         QR code image URL
 */

$alerts = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
?>

<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">Two-Factor Authentication</h1>
    <p class="dash-page-sub">Secure your account with Google Authenticator.</p>
  </div>
</div>

<?php foreach ($alerts as $f): ?>
  <div class="alert alert-<?= $f['type'] ?>" style="margin-top: 16px;">
    <i class="fa-solid <?= $f['type'] === 'error' ? 'fa-circle-xmark' : 'fa-circle-check' ?>"></i>
    <?= htmlspecialchars($f['message']) ?>
  </div>
<?php endforeach; ?>

<div class="card" style="max-width: 620px; margin-top: 24px;">
  <div class="card-header">
    <h3>
      <i class="fa-solid fa-shield-halved"></i>
      Authenticator App
      <?php if ($has2FA): ?>
        <span class="badge badge-success" style="margin-left: 8px; font-size: 0.75rem;">Active</span>
      <?php else: ?>
        <span class="badge badge-warning" style="margin-left: 8px; font-size: 0.75rem;">Not enabled</span>
      <?php endif; ?>
    </h3>
  </div>

  <div class="card-body">

    <?php if ($has2FA): ?>
      <!-- ── 2FA IS ENABLED ─────────────────────────────────────── -->
      <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
        <div style="width: 48px; height: 48px; background: var(--green-100); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
          <i class="fa-solid fa-check text-success" style="font-size: 1.25rem;"></i>
        </div>
        <div>
          <p style="font-weight: 600; color: var(--gray-800);">Two-factor authentication is enabled.</p>
          <p style="font-size: 0.85rem; color: var(--gray-500);">
            Your account is protected. You will be asked for a code on every login.
          </p>
        </div>
      </div>

      <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--gray-200);">

      <h4 style="margin-bottom: 12px; color: var(--gray-700);">Disable Two-Factor Authentication</h4>
      <p style="font-size: 0.85rem; color: var(--gray-500); margin-bottom: 16px;">
        To disable 2FA you must verify your current password <strong>and</strong> your authenticator code.
      </p>

      <button type="button" onclick="toggleDisablePanel()"
        class="btn btn-secondary" style="margin-bottom: 16px; color: var(--color-danger); border-color: var(--color-danger-border);">
        <i class="fa-solid fa-lock-open"></i> Disable 2FA
      </button>

      <div id="disablePanel" style="display: none;">
        <form action="<?= Router::url('auth/disable2FA') ?>" method="POST" style="background: var(--gray-50); padding: 20px; border-radius: 8px; border: 1px solid var(--gray-200);">
          <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
          <div class="form-group">
            <label>Current Password <span class="text-danger">*</span></label>
            <input type="password" name="current_password" class="form-input" placeholder="••••••••" required autocomplete="current-password">
          </div>
          <div class="form-group">
            <label>Authenticator Code <span class="text-danger">*</span></label>
            <input type="text" name="totp_code" class="form-input font-mono" placeholder="000000"
                   maxlength="6" inputmode="numeric" autocomplete="one-time-code" required>
          </div>
          <button type="submit" class="btn btn-danger"
            onclick="return confirm('Are you sure you want to disable two-factor authentication?')">
            Confirm — Disable 2FA
          </button>
        </form>
      </div>

    <?php elseif ($pendingSecret && $qrUrl): ?>
      <!-- ── SETUP IN PROGRESS: QR shown ───────────────────────── -->
      <p style="color: var(--gray-600); margin-bottom: 20px;">
        <strong>Step 1:</strong> Open <strong>Google Authenticator</strong> on your phone and tap the <strong>+</strong> button → <em>Scan a QR code</em>.
      </p>

      <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 20px;">
        <!-- QR image is fetched by the browser from Google Charts. The secret is embedded
             inside the otpauth:// URI within the QR — this is by design and is how all
             TOTP apps receive the secret. It is NOT echoed as plain text in the HTML. -->
        <div style="position: relative; width: 200px; height: 200px; background: var(--gray-50); border: 4px solid var(--gray-200); border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
          <img src="<?= htmlspecialchars($qrUrl) ?>"
               alt="Google Authenticator QR Code"
               style="width: 100%; height: 100%; object-fit: contain;"
               onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
          <div style="display: none; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 20px; color: var(--gray-500);">
            <i class="fa-solid fa-triangle-exclamation text-warning" style="font-size: 2rem; margin-bottom: 8px;"></i>
            <p style="font-size: 0.75rem;">QR Code failed to load. Please use the manual entry code below.</p>
          </div>
        </div>
        <p style="font-size: 0.8rem; color: var(--gray-400); margin-top: 8px;">Scan with Google Authenticator</p>
        
        <div style="margin-top: 16px; text-align: center;">
          <p style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 4px;">Can't scan? Enter this code manually:</p>
          <code style="background: var(--gray-100); padding: 4px 12px; border-radius: 4px; font-weight: 700; color: var(--brand-600); letter-spacing: 0.1em; font-size: 1rem; cursor: pointer;"
                onclick="copySecret('<?= $pendingSecret ?>')" title="Click to copy">
            <?= htmlspecialchars(implode(' ', str_split((string)$pendingSecret, 4))) ?>
          </code>
        </div>
      </div>

      <div style="text-align: center; margin-bottom: 20px;">
        <form action="<?= Router::url('auth/setup2FAInit') ?>" method="POST" onsubmit="return confirm('This will invalidate your current QR code and generate a new one. Continue?')">
          <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
          <button type="submit" class="btn btn-outline btn-sm" style="font-size: 0.75rem; color: var(--gray-500); min-height: 32px; padding: 4px 12px;">
            <i class="fa-solid fa-rotate"></i> Regenerate QR Code
          </button>
        </form>
      </div>

      <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--gray-200);">

      <p style="color: var(--gray-600); margin-bottom: 16px;">
        <strong>Step 2:</strong> Enter the 6-digit code shown in the app to confirm setup.
      </p>

      <form action="<?= Router::url('auth/setup2FAConfirm') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
        <div class="form-group">
          <label>Verification Code <span class="text-danger">*</span></label>
          <input type="text" name="totp_code" class="form-input font-mono"
                 placeholder="000000" maxlength="6" inputmode="numeric"
                 autocomplete="one-time-code" autofocus required
                 style="letter-spacing: 0.3em; font-size: 1.2rem; text-align: center;">
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-check"></i> Confirm &amp; Enable 2FA
        </button>
      </form>

    <?php else: ?>
      <!-- ── 2FA NOT ENABLED, NOT STARTED ──────────────────────── -->
      <p style="color: var(--gray-600); margin-bottom: 20px;">
        Two-factor authentication adds a second layer of security. After entering your password,
        you will also be asked for a code from the Google Authenticator app on your phone.
      </p>

      <div style="background: var(--blue-50, #eff6ff); border: 1px solid var(--blue-200, #bfdbfe); border-radius: 8px; padding: 16px; margin-bottom: 24px;">
        <p style="font-size: 0.85rem; color: var(--blue-700, #1d4ed8); margin: 0;">
          <i class="fa-solid fa-circle-info"></i>
          <strong>Before you start:</strong> Install <strong>Google Authenticator</strong> on your phone
          (<a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" rel="noopener" style="color: inherit;">Android</a> /
           <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" rel="noopener" style="color: inherit;">iOS</a>).
        </p>
      </div>

      <form action="<?= Router::url('auth/setup2FAInit') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-shield-halved"></i> Enable Two-Factor Authentication
        </button>
      </form>

    <?php endif; ?>

  </div><!-- /card-body -->
</div><!-- /card -->

<script>
function toggleDisablePanel() {
  const panel = document.getElementById('disablePanel');
  if (panel.style.display === 'none') {
    panel.style.display = 'block';
  } else {
    panel.style.display = 'none';
  }
}

function copySecret(secret) {
  navigator.clipboard.writeText(secret).then(() => {
    alert('Secret copied to clipboard!');
  }).catch(() => {
    alert('Failed to copy secret. Please copy it manually.');
  });
}
</script>