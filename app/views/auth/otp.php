<?php
/**
 * BoardTrack — OTP Verification Page
 * app/views/auth/otp.php
 * Layout: main.php
 *
 * Variables from AuthController::otpVerify():
 *   $maskedEmail  (string)  e.g. "jr*********@gmail.com"
 *   $expiresIn    (int)     seconds remaining before OTP expires
 */

$alerts = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
?>

<div class="min-h-screen flex">

  <!-- ── Left branding panel (matches login.php) ── -->
  <div class="hidden lg:flex flex-col justify-between w-80 bg-gray-900 p-10">
    <div>
      <div class="font-heading font-bold text-2xl text-white mb-1">
        Board<span class="text-brand-500">Track</span>
      </div>
      <div class="text-gray-400 text-sm">Boarding House Management</div>
    </div>
    <div>
      <h2 class="text-white font-bold text-xl leading-snug mb-3">
        Two-step verification.
      </h2>
      <p class="text-gray-400 text-sm leading-relaxed">
        We emailed a 6-digit code to confirm it's really you.
        Enter it below to complete sign-in.
      </p>
    </div>
  </div>

  <!-- ── Right OTP panel ── -->
  <div class="flex-1 flex items-center justify-center p-6 bg-gray-50">
    <div class="w-full max-w-sm">

      <!-- Back link -->
      <a href="<?= Router::url('auth/login') ?>"
         class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-8">
        <i class="fa-solid fa-arrow-left text-xs"></i> Back to login
      </a>

      <!-- Icon -->
      <div class="flex justify-center mb-5">
        <div class="w-14 h-14 rounded-full flex items-center justify-center"
             style="background:#eff6ff;">
          <i class="fa-solid fa-envelope-open-text text-2xl" style="color:#2563eb;"></i>
        </div>
      </div>

      <!-- Heading -->
      <h1 class="font-heading font-bold text-2xl text-gray-900 mb-1 text-center">
        Check your email
      </h1>
      <p class="text-gray-500 text-sm text-center mb-1">
        We sent a 6-digit code to
      </p>
      <p class="font-semibold text-gray-800 text-sm text-center mb-6">
        <?= htmlspecialchars($maskedEmail ?? '***@***.***') ?>
      </p>

      <!-- Flash alerts (errors / success / warnings) -->
      <?php if (!empty($alerts)): ?>
        <?php foreach ($alerts as $a): ?>
          <?php
            $type  = $a['type'] ?? 'error';
            $icons = [
              'error'   => 'fa-circle-xmark',
              'success' => 'fa-circle-check',
              'warning' => 'fa-triangle-exclamation',
            ];
            $icon = $icons[$type] ?? 'fa-circle-xmark';
          ?>
          <div class="alert-public <?= htmlspecialchars($type) ?>">
            <i class="fa-solid <?= $icon ?> flex-shrink-0"></i>
            <?= htmlspecialchars($a['message']) ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- ── OTP Form ── -->
      <form action="<?= Router::url('auth/otpVerifyPost') ?>" method="POST">

        <div class="mb-4">
          <label class="auth-label" for="otp">Enter 6-digit code</label>
          <input
            class="auth-input"
            style="text-align:center;font-size:1.5rem;font-weight:700;letter-spacing:0.5em;"
            type="text"
            id="otp"
            name="otp"
            maxlength="6"
            minlength="6"
            pattern="\d{6}"
            placeholder="· · · · · ·"
            autocomplete="one-time-code"
            inputmode="numeric"
            autofocus
            required
          >
          <p class="text-xs text-gray-400 mt-1.5 text-center">
            Digits only &mdash; no spaces or dashes.
          </p>
        </div>

        <!-- Countdown timer -->
        <div id="timerWrap"
             class="flex items-center justify-center gap-1.5 text-sm mb-5">
          <i class="fa-regular fa-clock text-gray-400"></i>
          <span class="text-gray-500">Code expires in</span>
          <span id="countdown" class="font-semibold" style="color:#2563eb;">
            <?= gmdate('i:s', max(0, (int)($expiresIn ?? 300))) ?>
          </span>
        </div>

        <!-- Shown when timer hits 0 -->
        <div id="expiredMsg"
             class="hidden alert-public error mb-4">
          <i class="fa-solid fa-circle-xmark flex-shrink-0"></i>
          Code expired.&nbsp;
          <a href="<?= Router::url('auth/login') ?>"
             class="underline font-semibold">Log in again</a>
        </div>

        <button type="submit" class="auth-btn" id="submitBtn">
          <i class="fa-solid fa-shield-check mr-1"></i>
          Verify Code
        </button>

      </form>

      <!-- ── Resend Form ── -->
      <div class="flex items-center justify-center mt-5">
        <span class="text-sm text-gray-500 mr-1">Didn't receive it?</span>
        <form action="<?= Router::url('auth/otpResend') ?>" method="POST" style="display:inline;">
          <button
            type="submit"
            id="resendBtn"
            class="text-sm font-semibold hover:underline"
            style="color:#2563eb; background:none; border:none; cursor:pointer; padding:0;"
            disabled>
            Resend code
            <span id="resendTimerLabel" style="color:#6b7280;font-weight:400;">
              (wait <span id="resendCount">30</span>s)
            </span>
          </button>
        </form>
      </div>

    </div>
  </div>

</div>

<script>

//  OTP Expiry Countdown Timer

(function () {
  var secs      = <?= (int) max(0, (int)($expiresIn ?? 300)) ?>;
  var display   = document.getElementById('countdown');
  var timerWrap = document.getElementById('timerWrap');
  var expiredMsg = document.getElementById('expiredMsg');
  var submitBtn  = document.getElementById('submitBtn');

  if (secs <= 0) { showExpired(); return; }

  var tick = setInterval(function () {
    secs--;
    if (secs <= 0) { clearInterval(tick); showExpired(); return; }

    var m = Math.floor(secs / 60);
    var s = secs % 60;
    display.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;

    // Turn red in the last 60 seconds
    if (secs <= 60) {
      display.style.color = '#ef4444';
    }
  }, 1000);

  function showExpired() {
    timerWrap.style.display = 'none';
    expiredMsg.classList.remove('hidden');
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.5';
    submitBtn.style.cursor  = 'not-allowed';
  }
})();

//  Resend Cooldown (30s wait before enabling resend button)

(function () {
  var btn        = document.getElementById('resendBtn');
  var countEl    = document.getElementById('resendCount');
  var labelEl    = document.getElementById('resendTimerLabel');
  var cooldown   = 30;

  var tick = setInterval(function () {
    cooldown--;
    countEl.textContent = cooldown;

    if (cooldown <= 0) {
      clearInterval(tick);
      btn.disabled        = false;
      labelEl.textContent = '';
      btn.style.cursor    = 'pointer';
    }
  }, 1000);
})();

//  OTP Input — digits only, auto-strip non-numeric characters

document.getElementById('otp').addEventListener('input', function () {
  this.value = this.value.replace(/\D/g, '').slice(0, 6);
});
</script>