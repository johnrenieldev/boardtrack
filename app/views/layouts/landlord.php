<?php
/**
 * BoardTrack — Landlord Layout
 */
$role = 'landlord';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'BoardTrack') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= Router::asset('css/dashboard.css') ?>">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="dashboard-body"
  data-mark-notif-read-url="<?= htmlspecialchars($markNotificationReadUrl ?? '', ENT_QUOTES) ?>"
  data-mark-all-notif-url="<?= htmlspecialchars($markAllNotificationsUrl ?? '', ENT_QUOTES) ?>"
  data-unread-notif-count="<?= (int) ($unreadNotificationCount ?? 0) ?>">
<div class="dashboard-layout">

  <?php require APP_PATH . '/views/components/sidebar.php'; ?>

  <div class="dashboard-main">
    <?php require APP_PATH . '/views/components/navbar.php'; ?>
    <?php require APP_PATH . '/views/components/alerts.php'; ?>
    <main class="dashboard-content">
      <?php require $__viewFile; ?>
    </main>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= Router::asset('js/confirm-actions.js') ?>"></script>
<script src="<?= Router::asset('js/notifications.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var sidebar   = document.getElementById('sidebar');
  var toggle    = document.getElementById('sidebarToggle');
  var closeBtn  = document.getElementById('sidebarClose');
  if (toggle)   toggle.addEventListener('click',   function() { sidebar.classList.add('open'); });
  if (closeBtn) closeBtn.addEventListener('click', function() { sidebar.classList.remove('open'); });
  document.addEventListener('click', function(e) {
    if (window.innerWidth < 768 && sidebar && sidebar.classList.contains('open')) {
      if (!sidebar.contains(e.target) && toggle && !toggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    }
  });
});
</script>
<?php require APP_PATH . '/views/landlord/partials/payment_modals.php'; ?>
</body>
</html>
