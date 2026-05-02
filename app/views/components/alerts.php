<?php
/**
 * BoardTrack — Flash Alerts Component
 * app/views/components/alerts.php
 */
$flash = $_SESSION['flash'] ?? [];
if (empty($flash)) return;
?>
<div class="alert-container" id="alertContainer">
<?php foreach ($flash as $i => $f):
  $icons = [
    'success' => 'fa-circle-check',
    'error'   => 'fa-circle-xmark',
    'warning' => 'fa-triangle-exclamation',
    'info'    => 'fa-circle-info',
  ];
  $icon = $icons[$f['type']] ?? 'fa-circle-info';
?>
  <div class="alert alert-<?= htmlspecialchars($f['type']) ?>" id="alert-<?= $i ?>">
    <i class="fa-solid <?= $icon ?> alert-icon"></i>
    <span class="alert-message"><?= htmlspecialchars($f['message']) ?></span>
    <button class="alert-close" onclick="this.closest('.alert').remove()">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>
<?php endforeach;
unset($_SESSION['flash']); ?>
</div>
<script>
  setTimeout(function() {
    var c = document.getElementById('alertContainer');
    if (c) { c.style.opacity = '0'; c.style.transition = 'opacity 0.4s'; setTimeout(function(){ c.remove(); }, 400); }
  }, 5000);
</script>