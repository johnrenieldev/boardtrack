/**
 * BoardTrack Modern Alert System
 * Unified notification and confirmation dialogs with formal UI/UX
 */

const BoardTrackAlerts = {
  /**
   * Show a toast notification
   * @param {string} message - The message to display
   * @param {string} type - Type: 'success', 'error', 'warning', 'info'
   * @param {number} duration - Duration in ms (default: 5000)
   */
  toast(message, type = 'info', duration = 5000) {
    const container = this.getOrCreateContainer();
    const alert = this.createAlert(message, type, true);
    container.appendChild(alert);
    
    // Trigger animation
    setTimeout(() => alert.classList.add('show'), 10);
    
    // Auto remove
    if (duration > 0) {
      setTimeout(() => this.removeAlert(alert), duration);
    }
    
    return alert;
  },
  
  /**
   * Show a confirmation dialog
   * @param {Object} options - Configuration object
   * @returns {Promise<boolean>} - Resolves to true if confirmed, false if cancelled
   */
  confirm(options = {}) {
    const defaults = {
      title: 'Confirm Action',
      message: 'Are you sure you want to proceed?',
      confirmText: 'Confirm',
      cancelText: 'Cancel',
      type: 'warning', // 'warning', 'danger', 'info'
      icon: 'fa-circle-question'
    };
    
    const config = { ...defaults, ...options };
    
    return new Promise((resolve) => {
      const modal = this.createConfirmModal(config, resolve);
      document.body.appendChild(modal);
      setTimeout(() => modal.classList.add('show'), 10);
    });
  },
  
  /**
   * Show an alert dialog (informational, single button)
   * @param {Object} options - Configuration object
   */
  alert(options = {}) {
    const defaults = {
      title: 'Notice',
      message: '',
      buttonText: 'OK',
      type: 'info',
      icon: 'fa-circle-info'
    };
    
    const config = { ...defaults, ...options };
    
    return new Promise((resolve) => {
      const modal = this.createAlertModal(config, resolve);
      document.body.appendChild(modal);
      setTimeout(() => modal.classList.add('show'), 10);
    });
  },
  
  /**
   * Create alert element
   */
  createAlert(message, type, dismissible = true) {
    const alert = document.createElement('div');
    alert.className = `bt-alert bt-alert-${type}`;
    
    const icons = {
      success: 'fa-circle-check',
      error: 'fa-circle-xmark',
      warning: 'fa-triangle-exclamation',
      info: 'fa-circle-info'
    };
    
    alert.innerHTML = `
      <div class="bt-alert-icon">
        <i class="fa-solid ${icons[type] || icons.info}"></i>
      </div>
      <div class="bt-alert-content">
        <div class="bt-alert-message">${message}</div>
      </div>
      ${dismissible ? `
        <button class="bt-alert-close" onclick="BoardTrackAlerts.removeAlert(this.closest('.bt-alert'))">
          <i class="fa-solid fa-xmark"></i>
        </button>
      ` : ''}
    `;
    
    return alert;
  },
  
  /**
   * Create confirmation modal
   */
  createConfirmModal(config, resolve) {
    const overlay = document.createElement('div');
    overlay.className = 'bt-modal-overlay';
    
    const typeColors = {
      warning: 'warning',
      danger: 'danger',
      info: 'brand'
    };
    
    const color = typeColors[config.type] || 'brand';
    
    overlay.innerHTML = `
      <div class="bt-modal bt-modal-confirm">
        <div class="bt-modal-header bt-modal-header-${color}">
          <div class="bt-modal-icon">
            <i class="fa-solid ${config.icon}"></i>
          </div>
          <h3 class="bt-modal-title">${config.title}</h3>
        </div>
        <div class="bt-modal-body">
          <p class="bt-modal-message">${config.message}</p>
        </div>
        <div class="bt-modal-footer">
          <button class="bt-btn bt-btn-secondary bt-modal-cancel">
            ${config.cancelText}
          </button>
          <button class="bt-btn bt-btn-${color} bt-modal-confirm-btn">
            ${config.confirmText}
          </button>
        </div>
      </div>
    `;
    
    // Event listeners
    overlay.querySelector('.bt-modal-cancel').addEventListener('click', () => {
      this.closeModal(overlay);
      resolve(false);
    });
    
    overlay.querySelector('.bt-modal-confirm-btn').addEventListener('click', () => {
      this.closeModal(overlay);
      resolve(true);
    });
    
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        this.closeModal(overlay);
        resolve(false);
      }
    });
    
    // ESC key
    const escHandler = (e) => {
      if (e.key === 'Escape') {
        this.closeModal(overlay);
        resolve(false);
        document.removeEventListener('keydown', escHandler);
      }
    };
    document.addEventListener('keydown', escHandler);
    
    return overlay;
  },
  
  /**
   * Create alert modal (single button)
   */
  createAlertModal(config, resolve) {
    const overlay = document.createElement('div');
    overlay.className = 'bt-modal-overlay';
    
    const typeColors = {
      success: 'success',
      error: 'danger',
      warning: 'warning',
      info: 'brand'
    };
    
    const color = typeColors[config.type] || 'brand';
    
    overlay.innerHTML = `
      <div class="bt-modal bt-modal-alert">
        <div class="bt-modal-header bt-modal-header-${color}">
          <div class="bt-modal-icon">
            <i class="fa-solid ${config.icon}"></i>
          </div>
          <h3 class="bt-modal-title">${config.title}</h3>
        </div>
        <div class="bt-modal-body">
          <p class="bt-modal-message">${config.message}</p>
        </div>
        <div class="bt-modal-footer">
          <button class="bt-btn bt-btn-${color} bt-modal-ok-btn">
            ${config.buttonText}
          </button>
        </div>
      </div>
    `;
    
    // Event listeners
    overlay.querySelector('.bt-modal-ok-btn').addEventListener('click', () => {
      this.closeModal(overlay);
      resolve(true);
    });
    
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        this.closeModal(overlay);
        resolve(true);
      }
    });
    
    // ESC key
    const escHandler = (e) => {
      if (e.key === 'Escape') {
        this.closeModal(overlay);
        resolve(true);
        document.removeEventListener('keydown', escHandler);
      }
    };
    document.addEventListener('keydown', escHandler);
    
    return overlay;
  },
  
  /**
   * Get or create alert container
   */
  getOrCreateContainer() {
    let container = document.getElementById('bt-alert-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'bt-alert-container';
      container.className = 'bt-alert-container';
      document.body.appendChild(container);
    }
    return container;
  },
  
  /**
   * Remove alert
   */
  removeAlert(alert) {
    if (!alert) return;
    alert.classList.remove('show');
    alert.classList.add('hide');
    setTimeout(() => alert.remove(), 300);
  },
  
  /**
   * Close modal
   */
  closeModal(overlay) {
    overlay.classList.remove('show');
    overlay.classList.add('hide');
    setTimeout(() => overlay.remove(), 300);
  }
};

// Make it globally available
window.BoardTrackAlerts = BoardTrackAlerts;

// Shorthand aliases
window.btToast = (message, type, duration) => BoardTrackAlerts.toast(message, type, duration);
window.btConfirm = (options) => BoardTrackAlerts.confirm(options);
window.btAlert = (options) => BoardTrackAlerts.alert(options);

// Enhanced confirm handler for forms
window.btConfirmForm = function(event, options) {
  event.preventDefault();
  event.stopPropagation();
  
  const form = event.target.closest('form') || event.currentTarget;
  
  BoardTrackAlerts.confirm(options).then(confirmed => {
    if (confirmed && form) {
      // Remove the onsubmit handler temporarily to avoid loop
      const originalOnSubmit = form.onsubmit;
      form.onsubmit = null;
      form.submit();
      // Restore it after submission
      setTimeout(() => { form.onsubmit = originalOnSubmit; }, 100);
    }
  });
  
  return false;
};

// Override native confirm - but keep it synchronous for inline usage
const nativeConfirm = window.confirm;
window.confirm = function(message) {
  // For now, keep native confirm for backward compatibility
  // Users should migrate to btConfirm for async behavior
  return nativeConfirm(message);
};

// Override native alert
const nativeAlert = window.alert;
window.alert = function(message) {
  if (typeof message === 'string') {
    BoardTrackAlerts.alert({
      title: 'Notice',
      message: message,
      buttonText: 'OK',
      type: 'info'
    });
    return;
  }
  return nativeAlert(message);
};
