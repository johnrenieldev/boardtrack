<?php
/**
 * BoardTrack — Reset Password
 * app/views/auth/reset-password.php
 * Layout: main.php
 */
$alerts = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
?>
<div class="min-h-screen flex items-center justify-center bg-gray-50 p-6">
  <div class="w-full max-w-sm">
    <div class="text-center mb-8">
      <div class="font-heading font-bold text-2xl text-gray-900 mb-1">Board<span class="text-brand-600">Track</span></div>
      <h1 class="font-heading font-bold text-xl text-gray-900 mt-4">Set New Password</h1>
      <p class="text-gray-500 text-sm">Choose a new secure password for your account.</p>
    </div>

    <?php foreach ($alerts as $f): ?>
      <div class="alert-public <?= $f['type'] === 'error' ? 'error' : 'success' ?> mb-6">
        <i class="fa-solid <?= $f['type'] === 'error' ? 'fa-circle-xmark' : 'fa-circle-check' ?> flex-shrink-0"></i>
        <?= htmlspecialchars($f['message']) ?>
      </div>
    <?php endforeach; ?>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
      <form action="<?= Router::url('auth/resetPasswordPost') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
        
        <div class="mb-4">
          <label class="block text-sm font-semibold text-gray-700 mb-1" for="new_password">New Password</label>
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

        <div class="mb-6">
          <label class="block text-sm font-semibold text-gray-700 mb-1" for="confirm_password">Confirm Password</label>
          <div class="relative">
            <input class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all pr-10" 
                   type="password" id="confirm_password" name="confirm_password"
                   placeholder="Repeat new password" autocomplete="new-password" required>
            <button type="button" onclick="togglePw('confirm_password', 'eyeC')"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-eye text-sm" id="eyeC"></i>
            </button>
          </div>
          <p id="matchMsg" class="text-[10px] font-bold uppercase tracking-wider mt-1"></p>
        </div>

        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:translate-y-0">
          Reset Password
        </button>
      </form>
    </div>

    <p class="text-center text-sm text-gray-500 mt-8">
      Changed your mind? 
      <a href="<?= Router::url('auth/login') ?>" class="text-brand-600 font-semibold hover:underline">Back to sign in</a>
    </p>
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
