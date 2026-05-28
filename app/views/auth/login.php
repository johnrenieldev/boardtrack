<?php
/**
 * BoardTrack — Login Page
 * app/views/auth/login.php
 * Layout: main.php
 */
$errors   = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
$oldEmail = $_SESSION['form_old']['email'] ?? '';
unset($_SESSION['form_old']);
?>
<div class="min-h-screen flex">

  <!-- Left branding panel -->
  <div class="hidden lg:flex flex-col justify-between w-80 bg-gray-900 p-10">
    <div>
      <div class="font-heading font-bold text-2xl text-white mb-1">Board<span class="text-brand-500">Track</span></div>
      <div class="text-gray-400 text-sm">Boarding House Management</div>
    </div>
    <div>
      <h2 class="text-white font-bold text-xl leading-snug mb-3">
        One platform for landlords and tenants.
      </h2>
      <p class="text-gray-400 text-sm leading-relaxed">
        Manage tenants, billing, rooms, complaints, and announcements from a single secure dashboard.
      </p>
    </div>
  </div>

  <!-- Right form panel -->
  <div class="flex-1 flex items-center justify-center px-6 pt-10 pb-20 md:p-6 bg-gray-50">
    <div class="w-full max-w-sm">

      <a href="<?= Router::url('home/index') ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-8">
        <i class="fa-solid fa-arrow-left text-xs"></i> Back
      </a>

      <!-- Login Form -->
      <h1 class="font-heading font-bold text-2xl text-gray-900 mb-1">Sign in</h1>
      <p class="text-gray-500 text-sm mb-6">Enter your credentials to continue.</p>

      <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $f): ?>
          <?php if (empty($f['field'])): ?>
            <div class="alert-public <?= $f['type'] === 'error' ? 'error' : 'success' ?>">
              <i class="fa-solid <?= $f['type'] === 'error' ? 'fa-circle-xmark' : 'fa-circle-check' ?> flex-shrink-0"></i>
              <?= htmlspecialchars($f['message']) ?>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- Role toggle -->
      <div class="flex bg-gray-200 rounded-md p-1 mb-6">
        <button type="button" id="roleTenant"
          class="flex-1 py-1.5 text-sm font-semibold rounded transition-all bg-white text-gray-900 shadow-sm"
          onclick="setRole('tenant')">
          Tenant
        </button>
        <button type="button" id="roleLandlord"
          class="flex-1 py-1.5 text-sm font-medium rounded transition-all text-gray-500"
          onclick="setRole('landlord')">
          Landlord
        </button>
      </div>

      <form action="<?= Router::url('auth/loginPost') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
        <input type="hidden" name="role_hint" id="roleHint" value="tenant">

        <div class="mb-4">
          <label class="auth-label" for="email">Email address</label>
          <input class="auth-input" type="email" id="email" name="email"
                 value="<?= htmlspecialchars($oldEmail) ?>"
                 placeholder="you@example.com" autocomplete="email" required>
          <?php if (!empty($errors['email'])): ?>
            <div class="form-error"><?= htmlspecialchars($errors['email']['message']) ?></div>
          <?php endif; ?>
        </div>

        <div class="mb-6">
          <div class="flex items-center justify-between mb-1">
            <label class="auth-label" for="password">Password</label>
            <a href="<?= Router::url('auth/forgotPassword') ?>" class="text-xs font-semibold text-brand-600 hover:underline">Forgot?</a>
          </div>
          <div class="relative">
            <input class="auth-input" type="password" id="password" name="password"
                   placeholder="Enter your password" autocomplete="current-password" required
                   style="padding-right: 40px;">
            <button type="button" onclick="togglePw('password','eyeIcon')"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-eye text-sm" id="eyeIcon"></i>
            </button>
          </div>
          <?php if (!empty($errors['password'])): ?>
            <div class="form-error"><?= htmlspecialchars($errors['password']['message']) ?></div>
          <?php endif; ?>
        </div>

        <button type="submit" class="auth-btn">Sign in</button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-6">
        Don't have an account?
        <a href="<?= Router::url('auth/register') ?>" class="text-brand-600 font-semibold hover:underline">Register as tenant</a>
      </p>

    </div>
  </div>

</div>

<script>
function setRole(role) {
  document.getElementById('roleHint').value = role;
  var tenant   = document.getElementById('roleTenant');
  var landlord = document.getElementById('roleLandlord');
  if (role === 'tenant') {
    tenant.className   = 'flex-1 py-1.5 text-sm font-semibold rounded transition-all bg-white text-gray-900 shadow-sm';
    landlord.className = 'flex-1 py-1.5 text-sm font-medium rounded transition-all text-gray-500';
  } else {
    landlord.className = 'flex-1 py-1.5 text-sm font-semibold rounded transition-all bg-white text-gray-900 shadow-sm';
    tenant.className   = 'flex-1 py-1.5 text-sm font-medium rounded transition-all text-gray-500';
  }
}
function togglePw(id, iconId) {
  var inp  = document.getElementById(id);
  var icon = document.getElementById(iconId);
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    inp.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}
</script>