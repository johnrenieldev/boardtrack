<?php
/**
 * BoardTrack — Change Password
 * app/views/auth/change_password.php
 * Layout: landlord.php OR tenant.php
 *
 * Variables:
 *   $has2FA  (bool)  Whether the current user has 2FA enabled
 *
 * Security flow:
 *   1. Current password required.
 *   2. If 2FA is enabled: Google Authenticator code also required.
 *   3. New password + confirmation required (≥ 8 chars).
 */

$alerts = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
?>

<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">Change Password</h1>
    <p class="dash-page-sub">
      <?php if ($has2FA): ?>
        Your current password and authenticator code are required.
      <?php else: ?>
        Enter your current password and choose a new one.
      <?php endif; ?>
    </p>
  </div>
</div>

<?php foreach ($alerts as $f): ?>
  <div class="alert <?= $f['type'] ?>" style="margin-top: 16px;">
    <i class="fa-solid <?= $f['type'] === 'error' ? 'fa-circle-xmark' : 'fa-circle-check' ?>"></i>
    <?= htmlspecialchars($f['message']) ?>
  </div>
<?php endforeach; ?>

<div class="card" style="max-width: 520px; margin-top: 24px;">
  <div class="card-header">
    <h3><i class="fa-solid fa-key"></i> Change Password</h3>
  </div>

  <form action="<?= Router::url('auth/changePasswordPost') ?>" method="POST">
    <div class="card-body">

      <!-- Step 1: Current Password -->
      <div style="margin-bottom: 8px;">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--gray-400); letter-spacing: 0.05em;">
          Step 1 — Verify identity
        </span>
      </div>

      <div class="form-group">
        <label>Current Password <span class="text-danger">*</span></label>
        <div style="position: relative;">
          <input type="password" name="current_password" id="currentPw" class="form-input"
                 placeholder="••••••••" autocomplete="current-password" required
                 style="padding-right: 40px;">
          <button type="button" onclick="togglePw('currentPw', 'eyeC')"
            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--gray-400);cursor:pointer;">
            <i class="fa-solid fa-eye text-sm" id="eyeC"></i>
          </button>
        </div>
      </div>

      <?php if ($has2FA): ?>
        <div class="form-group">
          <label>Authenticator Code <span class="text-danger">*</span></label>
          <input type="text" name="totp_code" class="form-input font-mono"
                 placeholder="000000" maxlength="6" inputmode="numeric"
                 autocomplete="one-time-code" required
                 style="letter-spacing: 0.3em; font-size: 1.1rem; text-align: center;">
          <div class="form-help">Open Google Authenticator and enter the current 6-digit code.</div>
        </div>
      <?php endif; ?>

      <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--gray-200);">

      <!-- Step 2: New Password -->
      <div style="margin-bottom: 8px;">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--gray-400); letter-spacing: 0.05em;">
          Step 2 — Set new password
        </span>
      </div>

      <div class="form-group">
        <label>New Password <span class="text-danger">*</span></label>
        <div style="position: relative;">
          <input type="password" name="new_password" id="newPw" class="form-input"
                 placeholder="Min. 8 characters" autocomplete="new-password" required
                 minlength="8" oninput="checkStrength(this.value)"
                 style="padding-right: 40px;">
          <button type="button" onclick="togglePw('newPw', 'eyeN')"
            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--gray-400);cursor:pointer;">
            <i class="fa-solid fa-eye text-sm" id="eyeN"></i>
          </button>
        </div>
        <!-- Strength meter -->
        <div style="margin-top: 6px; height: 4px; background: var(--gray-200); border-radius: 2px;">
          <div id="strengthBar" style="height: 100%; border-radius: 2px; width: 0; transition: width 0.3s, background 0.3s;"></div>
        </div>
        <div id="strengthLabel" style="font-size: 0.75rem; color: var(--gray-400); margin-top: 3px;"></div>
      </div>

      <div class="form-group">
        <label>Confirm New Password <span class="text-danger">*</span></label>
        <div style="position: relative;">
          <input type="password" name="confirm_password" id="confirmPw" class="form-input"
                 placeholder="Repeat new password" autocomplete="new-password" required
                 style="padding-right: 40px;">
          <button type="button" onclick="togglePw('confirmPw', 'eyeCo')"
            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--gray-400);cursor:pointer;">
            <i class="fa-solid fa-eye text-sm" id="eyeCo"></i>
          </button>
        </div>
        <div id="matchMsg" style="font-size: 0.75rem; margin-top: 3px;"></div>
      </div>

    </div>

    <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center;">
      <?php
      $role = $_SESSION['user_role'] ?? 'tenant';
      $backUrl = $role === 'landlord' ? 'landlord/profile' : 'tenant/profile';
      ?>
      <a href="<?= Router::url($backUrl) ?>" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-lock"></i> Update Password
      </button>
    </div>
  </form>
</div>

<script>
function togglePw(inputId, iconId) {
  var inp = document.getElementById(inputId);
  var icon = document.getElementById(iconId);
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    inp.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}

function checkStrength(val) {
  var bar = document.getElementById('strengthBar');
  var lbl = document.getElementById('strengthLabel');
  var score = 0;
  if (val.length >= 8)  score++;
  if (val.length >= 12) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  var colors = ['#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
  var labels = ['Very weak','Weak','Fair','Strong','Very strong'];
  var pct    = (score / 5) * 100;
  bar.style.width  = pct + '%';
  bar.style.background = colors[score - 1] || '#e5e7eb';
  lbl.textContent  = val.length ? labels[score - 1] || '' : '';
}

// Match check
document.getElementById('confirmPw').addEventListener('input', function () {
  var msg = document.getElementById('matchMsg');
  var nv  = document.getElementById('newPw').value;
  if (!this.value) { msg.textContent = ''; return; }
  if (this.value === nv) {
    msg.style.color = '#16a34a';
    msg.textContent = '✓ Passwords match';
  } else {
    msg.style.color = '#ef4444';
    msg.textContent = '✗ Passwords do not match';
  }
});
</script>