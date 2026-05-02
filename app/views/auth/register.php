<?php
/**
 * BoardTrack — Register Page
 * app/views/auth/register.php
 * Layout: main.php
 */
$errors  = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
$old     = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);
?>
<div class="min-h-screen flex">

  <!-- Left panel -->
  <div class="hidden lg:flex flex-col justify-between w-80 bg-gray-900 p-10">
    <div>
      <div class="font-heading font-bold text-2xl text-white mb-1">Board<span class="text-brand-500">Track</span></div>
      <div class="text-gray-400 text-sm">Boarding House Management</div>
    </div>
    <div>
      <h2 class="text-white font-bold text-xl leading-snug mb-4">Registration Steps</h2>
      <?php
        $steps = ['Fill in your details','Upload government ID','Complete personality quiz','Wait for landlord approval','Get room assigned'];
        foreach ($steps as $i => $s):
      ?>
      <div class="flex items-start gap-3 mb-3">
        <div class="w-5 h-5 rounded-full bg-brand-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5"><?= $i+1 ?></div>
        <span class="text-gray-300 text-sm"><?= $s ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-gray-600 text-xs">For Academic Use Only</div>
  </div>

  <!-- Form panel -->
  <div class="flex-1 flex items-start justify-center p-6 bg-gray-50 overflow-y-auto py-10">
    <div class="w-full max-w-md">

      <a href="<?= Router::url('home/index') ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-6">
        <i class="fa-solid fa-arrow-left text-xs"></i> Back to home
      </a>

      <h1 class="font-heading font-bold text-2xl text-gray-900 mb-1">Create tenant account</h1>
      <p class="text-gray-500 text-sm mb-6">For new boarding house applicants only.</p>

      <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $f): ?>
          <div class="alert-public <?= $f['type'] === 'error' ? 'error' : 'success' ?>">
            <i class="fa-solid <?= $f['type'] === 'error' ? 'fa-circle-xmark' : 'fa-circle-check' ?> flex-shrink-0"></i>
            <?= htmlspecialchars($f['message']) ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <form action="<?= Router::url('auth/registerPost') ?>" method="POST" enctype="multipart/form-data" novalidate>

        <!-- Name -->
        <div class="mb-4">
          <label class="auth-label" for="name">Full Name <span class="text-red-500">*</span></label>
          <input class="auth-input" type="text" id="name" name="name"
                 value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                 placeholder="e.g. Juan dela Cruz" required minlength="3">
        </div>

        <!-- Email -->
        <div class="mb-4">
          <label class="auth-label" for="email">Email Address <span class="text-red-500">*</span></label>
          <input class="auth-input" type="email" id="email" name="email"
                 value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                 placeholder="you@example.com" required>
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label class="auth-label" for="password">Password <span class="text-red-500">*</span></label>
          <div class="relative">
            <input class="auth-input" type="password" id="password" name="password"
                   placeholder="Minimum 8 characters" required minlength="8"
                   style="padding-right:40px;" oninput="checkStrength(this.value)">
            <button type="button" onclick="togglePw('password','ei1')"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-eye text-sm" id="ei1"></i>
            </button>
          </div>
          <div class="mt-1.5 h-1 bg-gray-200 rounded" id="strengthBar"></div>
          <p class="text-xs text-gray-400 mt-1" id="strengthLabel"></p>
        </div>

        <!-- Confirm password -->
        <div class="mb-4">
          <label class="auth-label" for="confirm_password">Confirm Password <span class="text-red-500">*</span></label>
          <div class="relative">
            <input class="auth-input" type="password" id="confirm_password" name="confirm_password"
                   placeholder="Re-enter your password" required
                   style="padding-right:40px;" oninput="checkMatch()">
            <button type="button" onclick="togglePw('confirm_password','ei2')"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-eye text-sm" id="ei2"></i>
            </button>
          </div>
          <p class="text-xs mt-1" id="matchMsg"></p>
        </div>

        <!-- Room preference -->
        <div class="mb-4">
          <label class="auth-label">Room Type Preference <span class="text-red-500">*</span></label>
          <div class="grid grid-cols-2 gap-3">
            <label class="flex items-center gap-3 border border-gray-300 rounded-md p-3 cursor-pointer hover:border-brand-500 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 transition-colors">
              <input type="radio" name="room_type_preference" value="single" required class="accent-brand-600">
              <div>
                <div class="text-sm font-semibold text-gray-900">Single Room</div>
                <div class="text-xs text-gray-400">Private, solo occupancy</div>
              </div>
            </label>
            <label class="flex items-center gap-3 border border-gray-300 rounded-md p-3 cursor-pointer hover:border-brand-500 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 transition-colors">
              <input type="radio" name="room_type_preference" value="shared" class="accent-brand-600">
              <div>
                <div class="text-sm font-semibold text-gray-900">Shared Room</div>
                <div class="text-xs text-gray-400">With compatible roommates</div>
              </div>
            </label>
          </div>
        </div>

        <!-- Government ID -->
        <div class="mb-6">
          <label class="auth-label">Government ID <span class="text-red-500">*</span></label>
          <div class="border-2 border-dashed border-gray-300 rounded-md p-6 text-center hover:border-brand-500 transition-colors cursor-pointer" onclick="document.getElementById('gov_id').click()">
            <i class="fa-solid fa-cloud-arrow-up text-2xl text-gray-400 mb-2 block"></i>
            <p class="text-sm font-medium text-gray-700" id="fileLabel">Click to upload ID</p>
            <p class="text-xs text-gray-400 mt-1">JPG or PNG, max 2MB</p>
            <input type="file" id="gov_id" name="government_id" accept=".jpg,.jpeg,.png"
                   class="hidden" onchange="updateFileLabel(this)" required>
          </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-6 flex gap-2 text-sm text-blue-700">
          <i class="fa-solid fa-circle-info flex-shrink-0 mt-0.5"></i>
          <span>After registering, you must complete a personality questionnaire before landlord review.</span>
        </div>

        <button type="submit" class="auth-btn">Create Account</button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-5">
        Already have an account?
        <a href="<?= Router::url('auth/login') ?>" class="text-brand-600 font-semibold hover:underline">Sign in</a>
      </p>

    </div>
  </div>
</div>

<script>
function togglePw(id, iconId) {
  var inp = document.getElementById(id);
  var icon = document.getElementById(iconId);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  icon.className = inp.type === 'text' ? 'fa-solid fa-eye-slash text-sm' : 'fa-solid fa-eye text-sm';
}
function checkStrength(v) {
  var bar   = document.getElementById('strengthBar');
  var label = document.getElementById('strengthLabel');
  var score = 0;
  if (v.length >= 8) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  var colors = ['','#ef4444','#f59e0b','#22c55e','#22c55e'];
  var labels = ['','Weak','Fair','Good','Strong'];
  bar.style.width = (score * 25) + '%';
  bar.style.background = colors[score] || '#e5e7eb';
  label.textContent = v.length ? labels[score] || 'Very weak' : '';
  label.style.color = colors[score] || '#6b7280';
}
function checkMatch() {
  var pw   = document.getElementById('password').value;
  var cpw  = document.getElementById('confirm_password').value;
  var msg  = document.getElementById('matchMsg');
  if (!cpw) { msg.textContent = ''; return; }
  if (pw === cpw) { msg.textContent = 'Passwords match'; msg.style.color = '#16a34a'; }
  else            { msg.textContent = 'Passwords do not match'; msg.style.color = '#dc2626'; }
}
function updateFileLabel(input) {
  var label = document.getElementById('fileLabel');
  label.textContent = input.files[0] ? input.files[0].name : 'Click to upload ID';
}
</script>