<?php
/**
 * BoardTrack — Tenant Change Password
 * app/views/tenant/change-password.php
 * Layout: tenant.php
 */
$alerts = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
$has2FA = $has2FA ?? false;
?>
<div class="page-header mb-6">
  <h1 class="page-title text-2xl font-bold text-gray-900">Change Password</h1>
  <p class="page-subtitle text-gray-500">Update your account security credentials.</p>
</div>

<?php foreach ($alerts as $f): ?>
  <div class="alert alert-<?= $f['type'] ?> mb-6">
    <i class="fa-solid <?= $f['type'] === 'error' ? 'fa-circle-xmark' : 'fa-circle-check' ?>"></i>
    <?= htmlspecialchars($f['message']) ?>
  </div>
<?php endforeach; ?>

<div class="max-w-2xl">
  <div class="card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="card-header px-6 py-4 border-b border-gray-100 bg-gray-50/50">
      <h3 class="font-bold text-gray-800 flex items-center gap-2">
        <i class="fa-solid fa-shield-halved text-brand-600"></i> Password Security
      </h3>
    </div>

    <form action="<?= Router::url('tenant/changePasswordPost') ?>" method="POST">
      <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
      
      <div class="card-body p-6">
        <!-- Section 1: Verify Current -->
        <div class="mb-8">
          <div class="flex items-center gap-2 mb-4">
            <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-[10px] font-black">1</span>
            <h4 class="text-xs font-black uppercase tracking-widest text-gray-400">Current Security</h4>
          </div>
          
          <div class="grid gap-6">
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-1" for="current_password">Current Password <span class="text-danger">*</span></label>
              <div class="relative">
                <input class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all pr-10" 
                       type="password" id="current_password" name="current_password"
                       placeholder="••••••••" autocomplete="current-password" required>
                <button type="button" onclick="togglePw('current_password', 'eyeC')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <i class="fa-solid fa-eye text-sm" id="eyeC"></i>
                </button>
              </div>
            </div>

            <?php if ($has2FA): ?>
              <div class="form-group">
                <label class="block text-sm font-semibold text-gray-700 mb-1" for="totp_code">Authenticator Code <span class="text-danger">*</span></label>
                <input class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all font-mono text-center tracking-[0.3em] text-lg" 
                       type="text" id="totp_code" name="totp_code"
                       placeholder="000000" maxlength="6" inputmode="numeric" required>
                <p class="text-[10px] text-gray-400 mt-1 font-medium">Enter the 6-digit code from your authenticator app.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <hr class="border-gray-100 mb-8">

        <!-- Section 2: Set New -->
        <div>
          <div class="flex items-center gap-2 mb-4">
            <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-[10px] font-black">2</span>
            <h4 class="text-xs font-black uppercase tracking-widest text-gray-400">New Password</h4>
          </div>

          <div class="grid gap-6">
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-1" for="new_password">New Password <span class="text-danger">*</span></label>
              <div class="relative">
                <input class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all pr-10" 
                       type="password" id="new_password" name="new_password"
                       placeholder="Min. 8 characters" autocomplete="new-password" required minlength="8"
                       oninput="checkStrength(this.value)">
                <button type="button" onclick="togglePw('new_password', 'eyeN')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <i class="fa-solid fa-eye text-sm" id="eyeN"></i>
                </button>
              </div>
              <div class="mt-2 h-1 w-full bg-gray-100 rounded-full overflow-hidden">
                <div id="strengthBar" class="h-full transition-all duration-300" style="width: 0;"></div>
              </div>
              <p id="strengthLabel" class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mt-1"></p>
            </div>

            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-1" for="confirm_password">Confirm New Password <span class="text-danger">*</span></label>
              <div class="relative">
                <input class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all pr-10" 
                       type="password" id="confirm_password" name="confirm_password"
                       placeholder="Repeat new password" autocomplete="new-password" required>
                <button type="button" onclick="togglePw('confirm_password', 'eyeCo')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <i class="fa-solid fa-eye text-sm" id="eyeCo"></i>
                </button>
              </div>
              <p id="matchMsg" class="text-[10px] font-bold uppercase tracking-wider mt-1"></p>
            </div>
          </div>
        </div>
      </div>

      <div class="card-footer px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
        <a href="<?= Router::url('tenant/profile') ?>" class="text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">Cancel</a>
        <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
          <i class="fa-solid fa-lock text-xs"></i> Update Password
        </button>
      </div>
    </form>
  </div>
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
  if (!val) { bar.style.width = '0'; lbl.textContent = ''; return; }
  
  if (val.length >= 8)  score++;
  if (val.length >= 12) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  
  var colors = ['#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
  var labels = ['Very weak','Weak','Fair','Strong','Very strong'];
  
  bar.style.width = (score / 5 * 100) + '%';
  bar.style.backgroundColor = colors[score - 1];
  lbl.textContent = labels[score - 1];
  lbl.style.color = colors[score - 1];
}

document.getElementById('confirm_password').addEventListener('input', function () {
  var msg = document.getElementById('matchMsg');
  var nv  = document.getElementById('new_password').value;
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
