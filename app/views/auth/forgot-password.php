<?php
/**
 * BoardTrack — Forgot Password
 * app/views/auth/forgot-password.php
 * Layout: main.php
 */
$alerts = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
?>
<div class="min-h-screen flex items-center justify-center bg-gray-50 p-6">
  <div class="w-full max-w-sm">
    <div class="text-center mb-8">
      <div class="font-heading font-bold text-2xl text-gray-900 mb-1">Board<span class="text-brand-600">Track</span></div>
      <h1 class="font-heading font-bold text-xl text-gray-900 mt-4">Forgot Password</h1>
      <p class="text-gray-500 text-sm">Enter your email and we'll send you a reset link.</p>
    </div>

    <?php foreach ($alerts as $f): ?>
      <div class="alert-public <?= $f['type'] === 'error' ? 'error' : 'success' ?> mb-6">
        <i class="fa-solid <?= $f['type'] === 'error' ? 'fa-circle-xmark' : 'fa-circle-check' ?> flex-shrink-0"></i>
        <?= htmlspecialchars($f['message']) ?>
      </div>
    <?php endforeach; ?>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
      <form action="<?= Router::url('auth/forgotPasswordPost') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $this->csrf() ?>">
        
        <div class="mb-6">
          <label class="block text-sm font-semibold text-gray-700 mb-1" for="email">Email Address</label>
          <input class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all" 
                 type="email" id="email" name="email"
                 placeholder="you@example.com" autocomplete="email" required>
        </div>

        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:translate-y-0">
          Send Reset Link
        </button>
      </form>
    </div>

    <p class="text-center text-sm text-gray-500 mt-8">
      Remembered your password? 
      <a href="<?= Router::url('auth/login') ?>" class="text-brand-600 font-semibold hover:underline">Sign in</a>
    </p>
  </div>
</div>
