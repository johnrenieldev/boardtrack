<?php
/**
 * BoardTrack — Notifications (landlord)
 * app/views/landlord/notifications.php
 *
 * Sections:
 *   "New"  → unread notifications  (blue cards, data-notif-read="0")
 *   "Seen" → read notifications    (white cards, data-notif-read="1")
 *
 * notifications.js moves cards New → Seen on click and syncs the red dot.
 * No "Mark All as Read". No numeric counters.
 */

/* ── Helper: render one notification card ──────────────────── */
function renderNotifCard(array $notif, string $deleteUrlBase, bool $isRead): string
{
    $id      = (int) $notif['id'];
    $link    = trim($notif['link_url'] ?? '');
    $href    = $link !== '' ? Router::url($link) : null;
    $readVal = $isRead ? '1' : '0';
    $classes = 'notif-item'
             . ($href   ? ' notif-link'  : '')
             . ($isRead ? ' notif-read'  : ' notif-unread');

    $icon = match ($notif['type'] ?? 'general') {
        'payment'      => 'fa-peso-sign',
        'complaint'    => 'fa-exclamation-circle',
        'room'         => 'fa-door-open',
        'announcement' => 'fa-bullhorn',
        'billing'      => 'fa-file-invoice',
        'review'       => 'fa-star',
        default        => 'fa-bell',
    };

    $deleteUrl = Router::url($deleteUrlBase . '/' . $id);
    $time      = date('M j, Y g:i A', strtotime($notif['created_at']));

    $tag   = $href ? 'a' : 'div';
    $attrs = ' class="' . htmlspecialchars($classes, ENT_QUOTES) . '"'
           . ' data-notif-id="' . $id . '"'
           . ' data-notif-read="' . $readVal . '"';

    if ($href) {
        $attrs .= ' href="' . htmlspecialchars($href, ENT_QUOTES) . '"';
    } else {
        $attrs .= ' role="button" tabindex="0"';
    }

    $dotHtml   = $isRead ? '' : '<span class="notif-dot" aria-hidden="true"></span>';
    $arrowHtml = $href
        ? '<div class="notif-arrow" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></div>'
        : '';

    return '<' . $tag . $attrs . '>'
         . '<div class="notif-icon-wrap" aria-hidden="true"><i class="fa-solid ' . $icon . '"></i></div>'
         . '<div class="notif-body">'
         . '<div class="notif-title">' . htmlspecialchars($notif['title'],   ENT_QUOTES) . '</div>'
         . '<div class="notif-msg">'   . htmlspecialchars($notif['message'], ENT_QUOTES) . '</div>'
         . '<div class="notif-time">'  . $time . '</div>'
         . '</div>'
         . $dotHtml
         . '<button type="button" class="notif-delete-btn"'
         . ' data-delete-notif-id="' . $id . '"'
         . ' data-delete-url="' . htmlspecialchars($deleteUrl, ENT_QUOTES) . '"'
         . ' title="Delete notification"'
         . ' aria-label="Delete notification"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>'
         . $arrowHtml
         . '</' . $tag . '>';
}

/* ── Data preparation ─────────────────────────────────────── */
$deleteUrlBase = 'notification/delete';

$unreadNotifs = [];
$readNotifs   = [];
foreach ($notifications ?? [] as $n) {
    if ((int) $n['is_read'] === 0) {
        $unreadNotifs[] = $n;
    } else {
        $readNotifs[] = $n;
    }
}
$hasAny = !empty($unreadNotifs) || !empty($readNotifs);
?>
<div class="page-header">
  <div class="page-header-row flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="page-title">Notifications</h1>
      <p class="page-subtitle">Stay up to date with your latest activity and alerts.</p>
    </div>
  </div>
</div>

<?php if (!$hasAny): ?>
  <div class="empty-state-card">
    <i class="fa-solid fa-bell-slash"></i>
    <h3>No Notifications</h3>
    <p>You're all caught up! New alerts will appear here.</p>
  </div>
<?php else: ?>
  <div class="data-card">
    <div class="notif-list-container" style="padding: 8px 0;">

      <?php if (!empty($unreadNotifs)): ?>
        <div class="notif-group" id="notif-section-new">
          <div class="notif-group-header">New</div>
          <?php foreach ($unreadNotifs as $notif): ?>
            <?= renderNotifCard($notif, $deleteUrlBase, false) ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($readNotifs)): ?>
        <div class="notif-group" id="notif-section-seen">
          <div class="notif-group-header">Seen</div>
          <?php foreach ($readNotifs as $notif): ?>
            <?= renderNotifCard($notif, $deleteUrlBase, true) ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
<?php endif; ?>

<style>
/* ── Notification cards ────────────────────────────────────── */
.notif-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  position: relative;
  transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
  text-decoration: none;
  color: inherit;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  transform: translateX(4px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.notif-link:hover {
  text-decoration: none;
  color: inherit;
}
.notif-unread {
  background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
  border-left: 4px solid #0ea5e9;
}
.notif-unread:hover {
  background: linear-gradient(135deg, #bae6fd 0%, #7dd3fc 100%);
}
.notif-read {
  background: #fff;
  border-left: 4px solid transparent;
}

/* ── Icon ──────────────────────────────────────────────────── */
.notif-icon-wrap {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* ── Body ──────────────────────────────────────────────────── */
.notif-body  { flex: 1; min-width: 0; }
.notif-title {
  font-weight: 700;
  font-size: 0.95rem;
  color: #1e293b;
  margin-bottom: 6px;
  line-height: 1.3;
}
.notif-msg {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.5;
  margin-bottom: 8px;
}
.notif-time {
  font-size: 0.75rem;
  color: #94a3b8;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* ── Per-card unread dot ───────────────────────────────────── */
.notif-dot {
  width: 10px;
  height: 10px;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border-radius: 50%;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.5);
  animation: notif-pulse 2s infinite;
}
@keyframes notif-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%       { opacity: 0.7; transform: scale(1.1); }
}

/* ── Chevron arrow ─────────────────────────────────────────── */
.notif-arrow {
  color: #cbd5e1;
  font-size: 0.875rem;
  transition: color 0.15s ease, transform 0.15s ease;
}
.notif-item:hover .notif-arrow {
  color: #3b82f6;
  transform: translateX(4px);
}

/* ── Delete button ─────────────────────────────────────────── */
.notif-delete-btn {
  background: transparent;
  border: none;
  color: #94a3b8;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.15s ease, color 0.15s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  flex-shrink: 0;
}
.notif-delete-btn:hover  { background: #fee2e2; color: #dc2626; }
.notif-delete-btn:active { transform: scale(0.95); }

/* ── Section groups ────────────────────────────────────────── */
.notif-group { margin-bottom: 12px; }
.notif-group-header {
  padding: 16px 24px 12px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 1px;
  background: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
}
</style>