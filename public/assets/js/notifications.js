/**
 * BoardTrack — notification mark-read (AJAX) and navbar badge updates
 */
(function () {
  function readBodyData() {
    var body = document.body;
    return {
      markReadUrl: body.getAttribute('data-mark-notif-read-url') || '',
      markAllUrl: body.getAttribute('data-mark-all-notif-url') || '',
      unreadCount: parseInt(body.getAttribute('data-unread-notif-count') || '0', 10) || 0,
    };
  }

  function badgeLabel(count) {
    return count > 99 ? '99+' : String(count);
  }

  function updateUnreadBadge(count) {
    var badge = document.getElementById('notifUnreadBadge');
    var bell = document.getElementById('notifBellBtn');
    var sidebarBadge = document.getElementById('sidebarNotifBadge');

    count = Math.max(0, parseInt(count, 10) || 0);
    document.body.setAttribute('data-unread-notif-count', String(count));

    if (bell) {
      bell.setAttribute(
        'title',
        count > 0
          ? count + ' unread notification' + (count === 1 ? '' : 's')
          : 'Notifications'
      );
    }

    if (badge) {
      if (count > 0) {
        badge.textContent = badgeLabel(count);
        badge.hidden = false;
      } else {
        badge.hidden = true;
      }
    }

    if (sidebarBadge) {
      if (count > 0) {
        sidebarBadge.textContent = badgeLabel(count);
        sidebarBadge.hidden = false;
      } else {
        sidebarBadge.hidden = true;
      }
    }
  }

  function markReadRequest(notificationId) {
    var cfg = readBodyData();
    if (!cfg.markReadUrl || !notificationId) {
      return Promise.resolve(null);
    }

    var body = new URLSearchParams();
    body.append('notification_id', String(notificationId));

    return fetch(cfg.markReadUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
      body: body.toString(),
      credentials: 'same-origin',
    })
      .then(function (res) {
        return res.ok ? res.json() : null;
      })
      .catch(function () {
        return null;
      });
  }

  function markItemReadInDom(el) {
    if (!el || el.getAttribute('data-notif-read') === '1') return;
    el.setAttribute('data-notif-read', '1');
    el.classList.remove('notif-unread');
    var dot = el.querySelector('.notif-dot');
    if (dot) dot.remove();
  }

  function onNotificationClick(e) {
    var el = e.target.closest('[data-notif-id]');
    if (!el || el.getAttribute('data-notif-read') === '1') return;

    var id = el.getAttribute('data-notif-id');
    var href = el.getAttribute('href');

    if (href) {
      var fd = new FormData();
      fd.append('notification_id', id);
      if (navigator.sendBeacon) {
        navigator.sendBeacon(readBodyData().markReadUrl, fd);
      } else {
        markReadRequest(id);
      }
      markItemReadInDom(el);
      var cfg = readBodyData();
      updateUnreadBadge(Math.max(0, cfg.unreadCount - 1));
      return;
    }

    e.preventDefault();
    markReadRequest(id).then(function (data) {
      markItemReadInDom(el);
      if (data && typeof data.unread_count === 'number') {
        updateUnreadBadge(data.unread_count);
      } else {
        var cfg = readBodyData();
        updateUnreadBadge(Math.max(0, cfg.unreadCount - 1));
      }
    });
  }

  function onMarkAllSubmit(e) {
    var form = e.target.closest('.js-mark-all-notifications');
    if (!form) return;

    e.preventDefault();
    var cfg = readBodyData();
    if (!cfg.markAllUrl) return;

    fetch(cfg.markAllUrl, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
      credentials: 'same-origin',
    })
      .then(function (res) {
        return res.ok ? res.json() : null;
      })
      .then(function () {
        document.querySelectorAll('[data-notif-id]').forEach(markItemReadInDom);
        updateUnreadBadge(0);
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var cfg = readBodyData();
    updateUnreadBadge(cfg.unreadCount);

    document.addEventListener('click', onNotificationClick);
    document.addEventListener('submit', onMarkAllSubmit);
  });
})();
