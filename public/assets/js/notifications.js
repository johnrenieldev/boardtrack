/**
 * BoardTrack — Notification System
 * public/assets/js/notifications.js
 *
 * Requirements:
 * - No numeric counters. Red dot indicates existence of unread notifications.
 * - Sections: "New" (unread) and "Seen" (read).
 * - Click unread -> Mark read (AJAX) -> Move to "Seen" -> Update dots.
 * - Click read -> Navigate normally.
 * - Delete -> Remove from DOM -> Update dots.
 * - Double-click and race condition guards included.
 */
(function () {
  'use strict';

  /**
   * Syncs global red dots (navbar/sidebar) based on current DOM state.
   * FIXED: Now properly hides red dot when all notifications are read.
   * Also updates the "New" section count badge.
   */
  function syncGlobalDots() {
    // Check if there are any unread notifications in the DOM
    var unreadElements = document.querySelectorAll('[data-notif-read="0"]');
    var hasUnread = unreadElements.length > 0;
    
    // Debug logging (remove after testing)
    console.log('[NotifSync] Unread count:', unreadElements.length, 'hasUnread:', hasUnread);
    
    // Update all red dots (navbar and sidebar)
    var allDots = document.querySelectorAll('.notif-red-dot, .notif-red-dot-sidebar');
    console.log('[NotifSync] Found', allDots.length, 'red dots to update');
    
    allDots.forEach(function (dot) {
      if (hasUnread) {
        dot.hidden = false;
        dot.removeAttribute('hidden');
        dot.style.display = '';
      } else {
        dot.hidden = true;
        dot.setAttribute('hidden', '');
        dot.style.display = 'none';
      }
    });
    
    // Update the "New" section count badge
    var countBadge = document.querySelector('.notif-count-badge');
    if (countBadge) {
      var unreadCount = unreadElements.length;
      countBadge.textContent = unreadCount;
      
      // Hide badge if count is 0
      if (unreadCount === 0) {
        countBadge.style.display = 'none';
      } else {
        countBadge.style.display = 'inline-flex';
      }
    }
  }

  /**
   * Moves a card from "New" section to "Seen" section.
   * Updates styling and removes unread indicators.
   */
  function moveCardToSeen(card) {
    var seenSection = document.getElementById('notif-section-seen');

    // Create "Seen" section if it doesn't exist
    if (!seenSection) {
      seenSection = document.createElement('div');
      seenSection.className = 'notif-group';
      seenSection.id = 'notif-section-seen';

      var header = document.createElement('div');
      header.className = 'notif-group-header';
      header.textContent = 'Seen';
      seenSection.appendChild(header);

      var newSection = document.getElementById('notif-section-new');
      if (newSection && newSection.parentNode) {
        newSection.parentNode.insertBefore(seenSection, newSection.nextSibling);
      } else {
        var container = document.querySelector('.notif-list-container');
        if (container) container.appendChild(seenSection);
      }
    }

    // Update card state
    card.setAttribute('data-notif-read', '1');
    card.classList.remove('notif-unread');
    card.classList.add('notif-read');

    // Remove unread dot from card
    var dot = card.querySelector('.notif-dot');
    if (dot) dot.remove();

    // Move to Seen section
    seenSection.appendChild(card);

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
      moveCardToSeen(card);
      syncGlobalDots();
    }

    // Failsafe timer (1500ms) - increased to allow AJAX to complete
    var failsafe = href ? setTimeout(function() {
      applyUpdate();
      navigate();
    }, 1500) : null;

    if (!markReadUrl) {
      if (failsafe) clearTimeout(failsafe);
      applyUpdate();
      navigate();
      return;
    }

    // AJAX Mark Read - wait for completion before navigating
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
    .then(function(response) {
      if (failsafe) clearTimeout(failsafe);
      // Wait a brief moment to ensure DB write completes
      return new Promise(function(resolve) {
        setTimeout(resolve, 100);
      });
    })
    .then(function() {
      applyUpdate();
      navigate();
    })
    .catch(function(err) {
      console.log('[NotifClick] AJAX failed, using optimistic update:', err);
      if (failsafe) clearTimeout(failsafe);
      applyUpdate(); // Optimistic update
      navigate();
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
    
    // Only sync red dots if we're on the notifications page
    // (where [data-notif-id] elements actually exist)
    var isNotificationsPage = document.querySelector('[data-notif-id]') !== null;
    
    if (isNotificationsPage) {
      // Force sync on page load to ensure red dots match actual DOM state
      syncGlobalDots();
      
      // Sync again after a short delay to catch any late-loading elements
      setTimeout(function() {
        syncGlobalDots();
      }, 100);
      
      // Sync again after 500ms to be extra sure
      setTimeout(function() {
        syncGlobalDots();
      }, 500);
    } else {
      // On non-notification pages, poll for unread count to sync red dots
      // This ensures red dots update when user reads notifications and navigates back
      pollUnreadCount();
      
      // Poll more frequently initially (in case user just navigated from notifications page)
      setTimeout(pollUnreadCount, 2000);  // Poll after 2 seconds
      setTimeout(pollUnreadCount, 5000);  // Poll after 5 seconds
      setTimeout(pollUnreadCount, 10000); // Poll after 10 seconds
      
      // Then poll every 30 seconds for ongoing updates
      setInterval(pollUnreadCount, 30000);
    }
  });
  
  /**
   * Polls the server for unread notification count and updates red dots.
   * Only used on non-notification pages.
   */
  function pollUnreadCount() {
    // Use server-provided base URL if available, otherwise construct from window.location
    var pollUrl = (window.BOARDTRACK_BASE_URL || '/index.php') + '?url=notification/unreadCount';
    
    fetch(pollUrl, {
      method: 'GET',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if (data && typeof data.has_unread !== 'undefined') {
        var allDots = document.querySelectorAll('.notif-red-dot, .notif-red-dot-sidebar');
        allDots.forEach(function(dot) {
          if (data.has_unread) {
            dot.hidden = false;
            dot.removeAttribute('hidden');
            dot.style.display = '';
          } else {
            dot.hidden = true;
            dot.setAttribute('hidden', '');
            dot.style.display = 'none';
          }
        });
      }
    })
    .catch(function(err) {
      // Silently fail - don't disrupt user experience
      console.log('[NotifPoll] Failed to poll unread count:', err);
    });
  }
  
  // Also sync when page becomes visible (handles tab switching)
  // But only if we're on the notifications page
  document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
      var isNotificationsPage = document.querySelector('[data-notif-id]') !== null;
      if (isNotificationsPage) {
        syncGlobalDots();
      } else {
        // Poll immediately when returning to a non-notification page
        pollUnreadCount();
      }
    }
  });

})();