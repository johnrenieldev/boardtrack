/**
 * BoardTrack — SweetAlert2 confirmations for logout and destructive actions
 */
document.addEventListener('DOMContentLoaded', function () {
  function confirmDialog(opts) {
    return Swal.fire({
      title: opts.title || 'Confirm',
      text: opts.text || 'Are you sure you want to proceed?',
      icon: opts.icon || 'warning',
      showCancelButton: true,
      confirmButtonColor: opts.confirmColor || '#2563eb',
      cancelButtonColor: '#94a3b8',
      confirmButtonText: opts.confirmText || 'Yes, proceed',
      cancelButtonText: 'Cancel',
      reverseButtons: true,
      focusCancel: true,
    });
  }

  document.querySelectorAll('a.confirm-logout, a[data-confirm-logout]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      var href = link.getAttribute('href');
      if (!href) return;
      confirmDialog({
        title: 'Log out?',
        text: link.getAttribute('data-message') || 'Are you sure you want to log out of your account?',
        icon: 'warning',
        confirmColor: '#dc2626',
        confirmText: 'Yes, log out',
      }).then(function (r) {
        if (r.isConfirmed) window.location.href = href;
      });
    });
  });

  document.querySelectorAll('a.confirm-action').forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      var href = link.getAttribute('href');
      if (!href || href === '#') return;
      confirmDialog({
        title: link.getAttribute('data-action') || 'Confirm',
        text: link.getAttribute('data-message') || 'Are you sure?',
        confirmColor: link.getAttribute('data-color') || '#2563eb',
        confirmText: link.getAttribute('data-confirm-text') || 'Yes, proceed',
      }).then(function (r) {
        if (r.isConfirmed) window.location.href = href;
      });
    });
  });

  document.querySelectorAll('a[data-confirm]:not(.confirm-logout):not(.confirm-action)').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      var href = el.getAttribute('href') || el.getAttribute('data-href');
      confirmDialog({
        title: 'Confirm',
        text: el.getAttribute('data-confirm') || 'Are you sure?',
      }).then(function (r) {
        if (r.isConfirmed && href) window.location.href = href;
      });
    });
  });

  document.querySelectorAll('form[data-confirm], form.confirm-form').forEach(function (form) {
    var bypassConfirm = false;
    form.addEventListener('submit', function (e) {
      if (bypassConfirm) return;
      e.preventDefault();
      var isDanger = form.classList.contains('confirm-danger')
        || form.querySelector('.btn-danger[type="submit"]');
      confirmDialog({
        title: form.getAttribute('data-action') || 'Confirm',
        text: form.getAttribute('data-message') || form.getAttribute('data-confirm') || 'Are you sure you want to proceed?',
        confirmColor: form.getAttribute('data-color') || (isDanger ? '#dc2626' : '#2563eb'),
        confirmText: form.getAttribute('data-confirm-text') || 'Yes, proceed',
      }).then(function (r) {
        if (r.isConfirmed) {
          bypassConfirm = true;
          form.submit();
        }
      });
    });
  });
});
