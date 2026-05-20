<?php
/**
 * BoardTrack — 2FA Setup Success Page
 * app/views/auth/setup_2fa_success.php
 * Layout: landlord.php OR tenant.php
 *
 * Variables:
 *   $recoveryCodes  (array)  Plain-text recovery codes — shown exactly ONCE, then gone.
 *
 * SECURITY: These codes are generated fresh, displayed here, then cleared from session.
 *           They are NOT stored anywhere in recoverable plain text after this page loads.
 */
?>

<div class="dash-page-header">
  <div>
    <h1 class="dash-page-title">2FA Enabled Successfully</h1>
    <p class="dash-page-sub">Save your recovery codes in a safe place.</p>
  </div>
</div>

<div class="card" style="max-width: 620px; margin-top: 24px;">
  <div class="card-header" style="background: var(--green-50, #f0fdf4); border-bottom-color: var(--green-200, #bbf7d0);">
    <h3 style="color: var(--green-700, #15803d);">
      <i class="fa-solid fa-circle-check"></i>
      Two-factor authentication is now active
    </h3>
  </div>

  <div class="card-body">

    <div style="background: var(--amber-50, #fffbeb); border: 2px solid var(--amber-400, #fbbf24); border-radius: 8px; padding: 16px; margin-bottom: 24px;">
      <p style="font-weight: 700; color: var(--amber-800, #92400e); margin-bottom: 6px;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        Save these recovery codes NOW — they will not be shown again.
      </p>
      <p style="font-size: 0.85rem; color: var(--amber-700, #b45309);">
        If you lose access to your Google Authenticator app, you can use one of these codes to log in.
        Each code can only be used once. Store them in a password manager or printed in a secure location.
      </p>
    </div>

    <div id="recoveryCodes" style="background: var(--gray-900); border-radius: 8px; padding: 20px; margin-bottom: 20px;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <?php foreach ($recoveryCodes as $code): ?>
          <code style="font-family: monospace; font-size: 0.95rem; color: #4ade80; background: rgba(255,255,255,0.05); padding: 8px 12px; border-radius: 4px; display: block; letter-spacing: 0.05em;">
            <?= htmlspecialchars($code) ?>
          </code>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap;">
      <button type="button" onclick="copyCodes()"
        class="btn btn-secondary" id="copyBtn">
        <i class="fa-solid fa-copy"></i> Copy All Codes
      </button>
      <button type="button" onclick="printCodes()"
        class="btn btn-secondary">
        <i class="fa-solid fa-print"></i> Print
      </button>
    </div>

    <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--gray-200);">

    <p style="font-size: 0.85rem; color: var(--gray-500); margin-bottom: 20px;">
      I have saved my recovery codes in a safe place.
    </p>

    <?php
    $role = $_SESSION['user_role'] ?? 'tenant';
    $dashUrl = $role === 'landlord' ? 'landlord/dashboard' : 'tenant/dashboard';
    ?>
    <a href="<?= Router::url($dashUrl) ?>" class="btn btn-primary">
      <i class="fa-solid fa-house"></i> Go to Dashboard
    </a>
    <a href="<?= Router::url('auth/setup2FA') ?>" class="btn btn-secondary" style="margin-left: 10px;">
      View 2FA Settings
    </a>

  </div>
</div>

<script>
function copyCodes() {
  const codes = <?= json_encode($recoveryCodes) ?>;
  const text = codes.join('\n');
  navigator.clipboard.writeText(text).then(() => {
    const btn = document.getElementById('copyBtn');
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
    setTimeout(() => {
      btn.innerHTML = '<i class="fa-solid fa-copy"></i> Copy All Codes';
    }, 2000);
  }).catch(() => {
    alert('Copy failed — please select and copy the codes manually.');
  });
}

function printCodes() {
  const codes = <?= json_encode($recoveryCodes) ?>;
  const w = window.open('', '_blank');
  w.document.write(`
    <html><head><title>BoardTrack Recovery Codes</title>
    <style>body{font-family:monospace;padding:40px}h2{margin-bottom:20px}
    .code{background:#f0f0f0;padding:8px 12px;margin:6px 0;border-radius:4px;font-size:1.1rem}
    p{color:#555;font-size:0.9rem;margin-top:20px}</style></head>
    <body>
    <h2>BoardTrack — 2FA Recovery Codes</h2>
    ${codes.map(c => `<div class="code">${c}</div>`).join('')}
    <p>Printed: ${new Date().toLocaleString()}<br>
    Each code can only be used once. Keep this page in a secure location.</p>
    </body></html>
  `);
  w.document.close();
  w.print();
}
</script>