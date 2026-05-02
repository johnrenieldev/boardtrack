<?php
/**
 * BoardTrack — Tenant Layout
 * app/views/layouts/tenant.php
 */
$role = 'tenant';
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
</head>
<body class="dashboard-body">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
  var sidebar  = document.getElementById('sidebar');
  var toggle   = document.getElementById('sidebarToggle');
  var closeBtn = document.getElementById('sidebarClose');
  if (toggle)   toggle.addEventListener('click',   function() { sidebar.classList.add('open'); });
  if (closeBtn) closeBtn.addEventListener('click', function() { sidebar.classList.remove('open'); });
  document.addEventListener('click', function(e) {
    if (window.innerWidth < 768 && sidebar && sidebar.classList.contains('open')) {
      if (!sidebar.contains(e.target) && toggle && !toggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    }
  });

  document.querySelectorAll('a[data-confirm], button[data-confirm]:not([type="submit"])').forEach(function(el) {
    el.addEventListener('click', function(e) {
      e.preventDefault();
      var href = el.getAttribute('href') || el.getAttribute('data-href');
      var msg  = el.getAttribute('data-confirm') || 'Are you sure?';
      Swal.fire({ title: 'Confirm', text: msg, icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#2563eb', cancelButtonColor: '#9ca3af', confirmButtonText: 'Yes'
      }).then(function(r) { if (r.isConfirmed && href) window.location.href = href; });
    });
  });

  // SweetAlert confirm forms
  document.querySelectorAll('form[data-confirm]').forEach(function(form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var msg = form.getAttribute('data-confirm');
      Swal.fire({
        title: 'Confirm', text: msg,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#2563eb', cancelButtonColor: '#9ca3af',
        confirmButtonText: 'Yes, proceed'
      }).then(function(r) { if (r.isConfirmed) form.submit(); });
    });
  });
});
</script>
</body>
</html>