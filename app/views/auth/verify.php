<div class="auth-page">
  <div class="auth-card" style="text-align: center;">
    
    <?php if (!empty($verified) && $verified === true): ?>
      <!-- Success State -->
      <div class="verify-icon success">
        <i class="fa-solid fa-check"></i>
      </div>
      <h1 style="font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;">
        Email Verified Successfully
      </h1>
      <p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 1.5rem;">
        Your email has been verified. You can now proceed to complete your personality questionnaire.
      </p>
      <a href="<?= Router::url('tenant/personality') ?>" class="btn btn-primary" style="width: 100%; padding: 0.75rem;">
        <i class="fa-solid fa-arrow-right"></i>
        Continue to Questionnaire
      </a>

    <?php elseif (!empty($error)): ?>
      <!-- Error State -->
      <div class="verify-icon error">
        <i class="fa-solid fa-xmark"></i>
      </div>
      <h1 style="font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;">
        Verification Failed
      </h1>
      <p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 1.5rem;">
        <?= htmlspecialchars($message ?? 'The verification link is invalid or has expired.') ?>
      </p>
      <form action="<?= Router::url('auth/resendVerification') ?>" method="POST" style="margin-bottom: 1rem;">
        <?php if (!empty($email)): ?>
          <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <?php endif; ?>
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem;">
          <i class="fa-solid fa-envelope"></i>
          Resend Verification Email
        </button>
      </form>
      <a href="<?= Router::url('auth/login') ?>" class="form-link">
        Back to Login
      </a>

    <?php else: ?>
      <!-- Pending/Default State -->
      <div class="verify-icon" style="background: #eff6ff; color: #2563eb;">
        <i class="fa-solid fa-envelope-open"></i>
      </div>
      <h1 style="font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;">
        Verify Your Email
      </h1>
      <p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 1.5rem;">
        We've sent a verification link to your email address. Please check your inbox and click the link to verify your account.
      </p>
      <div class="info-box" style="text-align: left; margin-bottom: 1.5rem;">
        <i class="fa-solid fa-circle-info"></i>
        <p>The verification link will expire in 24 hours. If you don't see the email, check your spam folder.</p>
      </div>
      <a href="<?= Router::url('auth/login') ?>" class="btn btn-outline" style="width: 100%; padding: 0.75rem;">
        Back to Login
      </a>

    <?php endif; ?>

  </div>
</div>
