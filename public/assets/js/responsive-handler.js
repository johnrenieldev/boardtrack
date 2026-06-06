/**
 * BoardTrack — Responsive Handler
 * Handles responsive behavior and WebView-specific fixes
 */

(function() {
  'use strict';

  // Detect if running in WebView
  const isWebView = (function() {
    const ua = navigator.userAgent.toLowerCase();
    const isAndroid = ua.indexOf('android') > -1;
    const isIOS = /iphone|ipod|ipad/.test(ua);
    const isWebViewAndroid = isAndroid && ua.indexOf('wv') > -1;
    const isWebViewIOS = isIOS && !ua.match(/safari/i);
    
    return isWebViewAndroid || isWebViewIOS;
  })();

  // Add WebView class to body
  if (isWebView) {
    document.documentElement.classList.add('is-webview');
    document.body.classList.add('is-webview');
  }

  // Viewport height fix for mobile browsers
  function setViewportHeight() {
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
  }

  // Call on load and resize
  setViewportHeight();
  window.addEventListener('resize', setViewportHeight);
  window.addEventListener('orientationchange', setViewportHeight);

  // Prevent zoom on double tap for iOS
  let lastTouchEnd = 0;
  document.addEventListener('touchend', function(event) {
    const now = Date.now();
    if (now - lastTouchEnd <= 300) {
      event.preventDefault();
    }
    lastTouchEnd = now;
  }, false);

  // Fix for iOS input zoom
  if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(function(input) {
      if (input.style.fontSize === '' || parseInt(input.style.fontSize) < 16) {
        input.style.fontSize = '16px';
      }
    });
  }

  // Handle scroll hint for horizontal scrolling containers
  function handleScrollHint() {
    const scrollContainers = document.querySelectorAll('.grid-5-cols, .stats-scroll-container');
    
    scrollContainers.forEach(function(container) {
      if (!container) return;
      
      function checkScroll() {
        const isScrolledToEnd = container.scrollLeft + container.clientWidth >= container.scrollWidth - 10;
        
        if (isScrolledToEnd) {
          container.classList.add('scrolled-end');
        } else {
          container.classList.remove('scrolled-end');
        }
      }
      
      container.addEventListener('scroll', checkScroll);
      checkScroll(); // Initial check
      
      // Recheck on window resize
      window.addEventListener('resize', checkScroll);
    });
  }

  // Initialize scroll hints when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', handleScrollHint);
  } else {
    handleScrollHint();
  }

  // Fix table responsiveness
  function fixTableResponsiveness() {
    const tables = document.querySelectorAll('.bt-table');
    
    tables.forEach(function(table) {
      // Ensure all td elements have data-label for mobile view
      const headers = table.querySelectorAll('thead th');
      const rows = table.querySelectorAll('tbody tr');
      
      rows.forEach(function(row) {
        const cells = row.querySelectorAll('td');
        cells.forEach(function(cell, index) {
          if (headers[index] && !cell.getAttribute('data-label')) {
            const label = headers[index].textContent.trim();
            cell.setAttribute('data-label', label);
          }
        });
      });
    });
  }

  // Call on load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fixTableResponsiveness);
  } else {
    fixTableResponsiveness();
  }

  // Handle orientation change
  window.addEventListener('orientationchange', function() {
    // Force reflow
    document.body.style.display = 'none';
    document.body.offsetHeight; // Trigger reflow
    document.body.style.display = '';
    
    // Recalculate viewport height
    setTimeout(setViewportHeight, 100);
  });

  // Note: overscroll prevention removed - it blocked scroll in Android WebView
  // because the page scroll is handled by .dashboard-main overflow-y:auto

  // Fix for Android WebView keyboard pushing content
  if (isWebView && /android/i.test(navigator.userAgent)) {
    const originalHeight = window.innerHeight;
    
    window.addEventListener('resize', function() {
      const currentHeight = window.innerHeight;
      
      // If height decreased significantly, keyboard is probably open
      if (currentHeight < originalHeight * 0.75) {
        document.body.classList.add('keyboard-open');
      } else {
        document.body.classList.remove('keyboard-open');
      }
    });
  }

  // Smooth scroll polyfill for older browsers
  if (!('scrollBehavior' in document.documentElement.style)) {
    const smoothScrollTo = function(element, target, duration) {
      const start = element.scrollTop;
      const change = target - start;
      const startTime = performance.now();
      
      const animateScroll = function(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        element.scrollTop = start + change * easeInOutQuad(progress);
        
        if (progress < 1) {
          requestAnimationFrame(animateScroll);
        }
      };
      
      requestAnimationFrame(animateScroll);
    };
    
    const easeInOutQuad = function(t) {
      return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
    };
    
    // Override scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
      anchor.addEventListener('click', function(e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          e.preventDefault();
          smoothScrollTo(window, target.offsetTop, 500);
        }
      });
    });
  }

  // Log device info for debugging (only in development)
  if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    console.log('Device Info:', {
      userAgent: navigator.userAgent,
      isWebView: isWebView,
      screenWidth: window.screen.width,
      screenHeight: window.screen.height,
      windowWidth: window.innerWidth,
      windowHeight: window.innerHeight,
      devicePixelRatio: window.devicePixelRatio,
      orientation: window.orientation || screen.orientation?.type
    });
  }

  // Expose utility functions globally
  window.BoardTrackResponsive = {
    isWebView: isWebView,
    setViewportHeight: setViewportHeight,
    fixTableResponsiveness: fixTableResponsiveness,
    handleScrollHint: handleScrollHint
  };

})();
