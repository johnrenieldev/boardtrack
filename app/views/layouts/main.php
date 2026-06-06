<?php
/**
 * BoardTrack | Public Layout (landing, auth)
 * app/views/layouts/main.php
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($metaDesc ?? 'BoardTrack | Boarding House Management System') ?>">
  <title><?= htmlspecialchars($pageTitle ?? 'BoardTrack') ?></title>
  <!-- Local Font Awesome for offline support -->
  <link rel="stylesheet" href="<?= Router::asset('css/font-awesome.min.css') ?>">
  <!-- Google Fonts with system font fallbacks -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= Router::asset('css/output.css') ?>">
  <style>
    html, body { width: 100%; overflow-x: hidden; overflow-x: clip; }
    /* System font fallbacks for offline mode */
    body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; -webkit-font-smoothing: antialiased; }
    h1,h2,h3 { font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
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
    .alert-public.info    { background: #eff6ff; border-color: #bfdbfe; color: #1e3a5f; }
    
    /* Landing page animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .animate-fade-in-up {
      animation: fadeInUp 0.6s ease-out forwards;
    }
    
    .animate-fade-in {
      animation: fadeIn 0.8s ease-out forwards;
    }
    
    /* Staggered animation delays */
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
    .delay-400 { animation-delay: 0.4s; }
    .delay-500 { animation-delay: 0.5s; }
    
    /* Smooth scroll behavior */
    html {
      scroll-behavior: smooth;
    }
    
    /* Mobile optimizations */
    @media (max-width: 640px) {
      .text-4xl { font-size: 2rem; }
      .text-5xl { font-size: 2.25rem; }
      .text-6xl { font-size: 2.5rem; }
    }
    
    @media (max-width: 375px) {
      .text-4xl { font-size: 1.75rem; }
      .text-5xl { font-size: 2rem; }
      .text-6xl { font-size: 2.25rem; }
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-900">

<?php
// Render public flash messages (top-right toast)
$flash = $_SESSION['flash'] ?? [];
if (!empty($flash)):
  foreach ($flash as $f):
    $rawType = $f['type'] ?? 'error';
    // Map all known types; anything else falls back to 'warning'
    $type = in_array($rawType, ['error','success','warning','info']) ? $rawType : 'warning';
    $icons = [
      'error'   => 'fa-circle-xmark',
      'success' => 'fa-circle-check',
      'warning' => 'fa-triangle-exclamation',
      'info'    => 'fa-circle-info',
    ];
    $icon = $icons[$type] ?? 'fa-circle-info';
?>
  <div class="flash-toast fixed top-4 right-4 z-50 max-w-sm w-full">
    <div class="alert-public <?= $type ?>">
      <i class="fa-solid <?= $icon ?> mt-0.5 flex-shrink-0"></i>
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
// Flash auto-dismiss — only removes the .flash-toast wrapper, never touches page content
setTimeout(function() {
  document.querySelectorAll('.flash-toast').forEach(function(el) {
    el.remove();
  });
}, 5000);
</script>
</body>
</html>
