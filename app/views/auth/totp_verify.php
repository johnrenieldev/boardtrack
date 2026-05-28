<?php
/**
 * BoardTrack — TOTP Verify Page
 * app/views/auth/totp_verify.php
 * Layout: main.php
 */
$errors = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);

$user = $_SESSION['2fa_pending_user'] ?? null;
if (!$user) {
    header('Location: ' . Router::url('auth/login'));
    exit;
}
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
        Two-Factor Authentication
      </h2>
      <p class="text-gray-400 text-sm leading-relaxed">
        Your account is protected by 2FA. Please enter the code from your authenticator app to continue.
      </p>
    </div>
  </div>

  <!-- Right form panel -->
  <div class="flex-1 flex items-center justify-center p-6 bg-gray-50">
    <div class="w-full max-w-sm">

      <a href="<?= Router::url('auth/login') ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-8">
        <i class="fa-solid fa-arrow-left text-xs"></i> Back to login
      </a>

      <h1 class="font-heading font-bold text-2xl text-gray-900 mb-1">2FA Verification</h1>
      <p class="text-gray-500 text-sm mb-6">Enter the code from Google Authenticator.</p>

      <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $f): ?>
          <div class="alert-public <?= $f['type'] === 'error' ? 'error' : 'success' ?>">
            <i class="fa-solid <?= $f['type'] === 'error' ? 'fa-circle-xmark' : 'fa-circle-check' ?> flex-shrink-0"></i>
            <?= htmlspecialchars($f['message']) ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <form action="<?= Router::url('auth/totpVerifyPost') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
        <div class="mb-6">
          <label class="auth-label" for="totp_code">Authenticator Code</label>
          <input class="auth-input font-mono text-center text-lg tracking-widest" type="text" id="totp_code" name="totp_code"
                 placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code" autofocus required>
        </div>
        <button type="submit" class="auth-btn">Verify Code</button>
      </form>

      <!-- Recovery Code Option -->
      <div class="mt-6 border-t border-gray-200 pt-6">
        <p class="text-center text-sm text-gray-500 mb-4">
          Can't access your authenticator app?
        </p>
        <form action="<?= Router::url('auth/totpRecovery') ?>" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
          <div class="mb-4">
            <label class="auth-label" for="recovery_code">Recovery Code</label>
            <input class="auth-input font-mono" type="text" id="recovery_code" name="recovery_code"
                   placeholder="XXXX-XXXX" required>
          </div>
          <button type="submit" class="auth-btn bg-gray-600 hover:bg-gray-700">Use Recovery Code</button>
        </form>
      </div>

    </div>
  </div>
</div>
