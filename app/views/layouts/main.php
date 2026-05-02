<?php
/**
 * BoardTrack — Public Layout (landing, auth)
 * app/views/layouts/main.php
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($metaDesc ?? 'BoardTrack — Boarding House Management System') ?>">
  <title><?= htmlspecialchars($pageTitle ?? 'BoardTrack') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter','system-ui','sans-serif'], heading: ['Poppins','sans-serif'] },
          colors: { brand: { 50:'#eff6ff',100:'#dbeafe',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',900:'#1e3a5f' } }
        }
      }
    }
  </script>
  <style>
    body { font-family: 'Inter', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
    h1,h2,h3 { font-family: 'Poppins', system-ui, sans-serif; }
    /* Auth form styles */
    .auth-input {
      width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px;
      font-size: 0.875rem; color: #111827; background: #fff; transition: border-color 0.15s, box-shadow 0.15s;
      outline: none; font-family: inherit;
    }
    .auth-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .auth-input::placeholder { color: #9ca3af; }
    .auth-label { display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 5px; }
    .auth-btn {
      width: 100%; padding: 10px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px;
      font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s; font-family: inherit;
    }
    .auth-btn:hover { background: #1d4ed8; }
    .alert-public {
      padding: 11px 14px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 16px;
      display: flex; align-items: flex-start; gap: 9px; border: 1px solid transparent;
    }
    .alert-public.error   { background: #fef2f2; border-color: #fecaca; color: #7f1d1d; }
    .alert-public.success { background: #f0fdf4; border-color: #bbf7d0; color: #14532d; }
    .alert-public.warning { background: #fffbeb; border-color: #fde68a; color: #78350f; }
  </style>
</head>
<body class="bg-gray-50 text-gray-900">

<?php
// Render public flash messages
$flash = $_SESSION['flash'] ?? [];
if (!empty($flash)):
  foreach ($flash as $f):
    $type = $f['type'] === 'error' ? 'error' : ($f['type'] === 'success' ? 'success' : 'warning');
    $icons = ['error'=>'fa-circle-xmark','success'=>'fa-circle-check','warning'=>'fa-triangle-exclamation'];
?>
  <div class="fixed top-4 right-4 z-50 max-w-sm w-full">
    <div class="alert-public <?= $type ?>">
      <i class="fa-solid <?= $icons[$type] ?> mt-0.5 flex-shrink-0"></i>
      <span><?= htmlspecialchars($f['message']) ?></span>
    </div>
  </div>
<?php
  endforeach;
  unset($_SESSION['flash']);
endif;
?>

<?php require $__viewFile; ?>

<script>
// Simple flash auto-dismiss
setTimeout(function() {
  document.querySelectorAll('.alert-public').forEach(function(el) {
    el.parentElement && el.parentElement.remove ? el.parentElement.remove() : el.remove();
  });
}, 5000);
</script>
</body>
</html>