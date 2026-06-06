/**
 * BoardTrack — Grid Scroll Hint
 * public/assets/js/grid-scroll-hint.js
 *
 * Manages scroll hint indicator for horizontally scrollable grids.
 * Shows a gradient fade on the right side when content is scrollable,
 * and hides it when user scrolls to the end.
 */
(function () {
  'use strict';

  /**
   * Initialize scroll hint for a grid element
   */
  function initScrollHint(grid) {
    if (!grid) return;

    // Check if content is scrollable
    function updateScrollHint() {
      const isScrollable = grid.scrollWidth > grid.clientWidth;
      const isScrolledToEnd = grid.scrollLeft + grid.clientWidth >= grid.scrollWidth - 5; // 5px threshold

      if (!isScrollable || isScrolledToEnd) {
        grid.classList.add('scrolled-end');
      } else {
        grid.classList.remove('scrolled-end');
      }
    }

    // Update on scroll
    grid.addEventListener('scroll', updateScrollHint);

    // Update on window resize
    window.addEventListener('resize', updateScrollHint);

    // Initial check
    updateScrollHint();

    // Re-check after a short delay (in case content loads dynamically)
    setTimeout(updateScrollHint, 100);
    setTimeout(updateScrollHint, 500);
  }

  /**
   * Initialize all grid-5-cols elements on the page
   */
  function initAllGrids() {
    const grids = document.querySelectorAll('.grid-5-cols');
    grids.forEach(function(grid) {
      initScrollHint(grid);
    });
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAllGrids);
  } else {
    initAllGrids();
  }

  // Re-initialize when page becomes visible (handles tab switching)
  document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
      setTimeout(initAllGrids, 100);
    }
  });

})();
