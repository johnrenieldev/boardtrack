<?php
/**
 * BoardTrack | Landlord Layout
 */
$role = 'landlord';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <title><?= htmlspecialchars($pageTitle ?? 'BoardTrack') ?></title>
  <!-- Local Font Awesome for offline support -->
  <link rel="stylesheet" href="<?= Router::asset('css/font-awesome.min.css') ?>">
  <!-- Google Fonts with system font fallbacks -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <style>
    /* System font fallbacks for offline mode */
    body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
    h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
  </style>
  <link rel="stylesheet" href="<?= Router::asset('css/output.css') ?>?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= Router::asset('css/dashboard.css') ?>?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= Router::asset('css/responsive-fixes.css') ?>?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= Router::asset('css/alerts.css') ?>?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= Router::asset('css/compatibility.css') ?>?v=<?= time() ?>">
  <!-- Local Chart.js for offline support -->
  <script src="<?= Router::asset('js/chart.min.js') ?>"></script>
  <script>
    // Global base URL for AJAX requests (fixes notification polling in subdirectories)
    window.BOARDTRACK_BASE_URL = '<?= rtrim(BASE_URL, '/') ?>/index.php';
  </script>
</head>
<body class="dashboard-body overflow-x-hidden bg-gray-50"
  data-mark-notif-read-url="<?= htmlspecialchars(Router::url($markNotificationReadUrl ?? ''), ENT_QUOTES) ?>">
<div class="dashboard-layout min-h-screen w-full max-w-full overflow-x-hidden">

  <?php require APP_PATH . '/views/components/sidebar.php'; ?>

  <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

  <div class="dashboard-main">
    <?php require APP_PATH . '/views/components/navbar.php'; ?>
    <?php require APP_PATH . '/views/components/alerts.php'; ?>
    <main class="dashboard-content w-full max-w-full overflow-x-hidden px-4 sm:px-6 py-4" role="main">
      <?php require $__viewFile; ?>
    </main>
  </div>

  <?php require APP_PATH . '/views/components/mobile-bottom-nav.php'; ?>

</div>

<!-- Local SweetAlert2 for offline support -->
<script src="<?= Router::asset('js/sweetalert2.all.min.js') ?>"></script>
<script src="<?= Router::asset('js/responsive-handler.js') ?>?v=<?= time() ?>"></script>
<script src="<?= Router::asset('js/confirmActions.js') ?>"></script>
<script src="<?= Router::asset('js/notifications.js') ?>?v=<?= time() ?>"></script>
<script src="<?= Router::asset('js/grid-scroll-hint.js') ?>?v=<?= time() ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var sidebar   = document.getElementById('sidebar');
  var toggle    = document.getElementById('sidebarToggle');
  var closeBtn  = document.getElementById('sidebarClose');
  var overlay   = document.getElementById('sidebarOverlay');

  function openSidebar() {
    sidebar.classList.add('open');
    overlay.classList.add('active');
    toggle.setAttribute('aria-expanded', 'true');
    overlay.setAttribute('aria-hidden', 'false');
  }

  function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('active');
    toggle.setAttribute('aria-expanded', 'false');
    overlay.setAttribute('aria-hidden', 'true');
  }

  if (toggle)   toggle.addEventListener('click', openSidebar);
  if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
  if (overlay)  overlay.addEventListener('click', closeSidebar);

  document.addEventListener('click', function(e) {
    if (window.innerWidth < 768 && sidebar && sidebar.classList.contains('open')) {
      if (!sidebar.contains(e.target) && toggle && !toggle.contains(e.target) && !overlay.contains(e.target)) {
        closeSidebar();
      }
    }
  });
});
</script>
<?php require APP_PATH . '/views/landlord/partials/payment_modals.php'; ?>
<script src="<?= Router::asset('js/alerts.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
