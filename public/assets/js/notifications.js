/**
 * BoardTrack — Notification System
 * public/assets/js/notifications.js
 *
 * Requirements:
 * - No numeric counters. Red dot indicates existence of unread notifications.
 * - Sections: "New" (unread) and "Today" (read).
 * - Click unread -> Mark read (AJAX) -> Move to "Today" -> Update dots.
 * - Click read -> Navigate normally.
 * - Delete -> Remove from DOM -> Update dots.
 * - Double-click and race condition guards included.
 */
(function () {
  'use strict';

  /**
   * Syncs global red dots (navbar/sidebar) based on current DOM state.
   */
  function syncGlobalDots() {
    // Only sync from DOM if we are on a page that actually lists notifications
    var listContainer = document.querySelector('.notif-list-container');
    if (!listContainer) return;

    var hasUnread = document.querySelector('[data-notif-read="0"]') !== null;
    document.querySelectorAll('.notif-red-dot').forEach(function (dot) {
      dot.hidden = !hasUnread;
    });
  }

  /**
   * Moves a card from "New" section to "Today" section.
   * Updates styling and removes unread indicators.
   */
  function moveCardToToday(card) {
    var todaySection = document.getElementById('notif-section-today');

    // Create "Today" section if it doesn't exist
    if (!todaySection) {
      todaySection = document.createElement('div');
      todaySection.className = 'notif-group';
      todaySection.id = 'notif-section-today';

      var header = document.createElement('div');
      header.className = 'notif-group-header';
      header.textContent = 'Today';
      todaySection.appendChild(header);

      var newSection = document.getElementById('notif-section-new');
      if (newSection && newSection.parentNode) {
        newSection.parentNode.insertBefore(todaySection, newSection.nextSibling);
      } else {
        var container = document.querySelector('.notif-list-container');
        if (container) container.appendChild(todaySection);
      }
    }

    // Update card state
    card.setAttribute('data-notif-read', '1');
    card.classList.remove('notif-unread');
    card.classList.add('notif-read');

    // Remove unread dot from card
    var dot = card.querySelector('.notif-dot');
    if (dot) dot.remove();

    // Move to Today section
    todaySection.appendChild(card);

    // Remove "New" section if empty
    var newSection = document.getElementById('notif-section-new');
    if (newSection && newSection.querySelectorAll('[data-notif-id]').length === 0) {
      newSection.remove();
    }
  }

  /**
   * Handles notification click events.
   */
  function handleNotificationClick(e) {
    var card = e.target.closest('[data-notif-id]');
    if (!card) return;

    // Delegate delete button
    if (e.target.closest('.notif-delete-btn')) {
      e.preventDefault();
      e.stopPropagation();
      handleDelete(e.target.closest('.notif-delete-btn'));
      return;
    }

    // Already read: navigate normally
    if (card.getAttribute('data-notif-read') === '1') return;

    // Double-click guard
    if (card.getAttribute('data-processing') === '1') {
      e.preventDefault();
      return;
    }
    card.setAttribute('data-processing', '1');

    var id          = card.getAttribute('data-notif-id');
    var href        = card.getAttribute('href');
    var markReadUrl = (document.body.getAttribute('data-mark-notif-read-url') || '').trim();

    // Prevent navigation until AJAX resolves or failsafe fires
    e.preventDefault();

    var navigated = false;
    function navigate() {
      if (navigated) return;
      navigated = true;
      if (href) window.location.href = href;
    }

    function applyUpdate() {
      moveCardToToday(card);
      syncGlobalDots();
      navigate();
    }

    // Failsafe timer (900ms)
    var failsafe = href ? setTimeout(applyUpdate, 900) : null;

    if (!markReadUrl) {
      if (failsafe) clearTimeout(failsafe);
      applyUpdate();
      return;
    }

    // AJAX Mark Read
    var params = new URLSearchParams();
    params.append('notification_id', id);

    fetch(markReadUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: params.toString()
    })
    .then(function() {
      if (failsafe) clearTimeout(failsafe);
      applyUpdate();
    })
    .catch(function() {
      if (failsafe) clearTimeout(failsafe);
      applyUpdate(); // Optimistic update
    });
  }

  /**
   * Handles notification deletion.
   */
  function handleDelete(btn) {
    var card      = btn.closest('[data-notif-id]');
    var notifId   = btn.getAttribute('data-delete-notif-id');
    var deleteUrl = btn.getAttribute('data-delete-url');

    if (!card || !notifId || !deleteUrl) return;

    if (btn.getAttribute('data-processing') === '1') return;
    btn.setAttribute('data-processing', '1');

    if (!confirm('Delete this notification?')) {
      btn.removeAttribute('data-processing');
      return;
    }

    fetch(deleteUrl, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if (data && data.success) {
        card.style.opacity = '0';
        card.style.transform = 'translateX(20px)';
        card.style.transition = 'all 0.2s ease';

        setTimeout(function() {
          var section = card.closest('.notif-group');
          card.remove();

          if (section && section.querySelectorAll('[data-notif-id]').length === 0) {
            section.remove();
          }

          // Show empty state if no notifications remain
          if (document.querySelectorAll('[data-notif-id]').length === 0) {
            var container = document.querySelector('.notif-list-container');
            if (container) {
              container.innerHTML = 
                '<div class="empty-state-card">' +
                '<i class="fa-solid fa-bell-slash"></i>' +
                '<h3>No Notifications</h3>' +
                '<p>You\'re all caught up! New alerts will appear here.</p>' +
                '</div>';
            }
          }
          syncGlobalDots();
        }, 200);
      } else {
        btn.removeAttribute('data-processing');
      }
    })
    .catch(function() {
      btn.removeAttribute('data-processing');
      alert('Failed to delete notification.');
    });
  }

  // Initialize
  document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', handleNotificationClick);
    syncGlobalDots();
  });

})();