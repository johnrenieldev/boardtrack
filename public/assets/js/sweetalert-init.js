document.addEventListener('DOMContentLoaded', function() {
    // Intercept all links with the .confirm-action or .confirm-logout class
    const confirmLinks = document.querySelectorAll('.confirm-logout, .confirm-action');
    
    confirmLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            if(!href || href === '#') return;

            const isLogout = this.classList.contains('confirm-logout');
            const isDanger = this.classList.contains('text-danger') || isLogout;
            
            const actionText = this.getAttribute('data-action') || (isLogout ? 'Log out' : 'Proceed');
            const messageText = this.getAttribute('data-message') || (isLogout ? 'Are you sure you want to log out of your account?' : 'Are you sure you want to do this?');
            const confirmColor = this.getAttribute('data-color') || (isDanger ? '#EE5D50' : '#4318FF');

            Swal.fire({
                title: actionText,
                text: messageText,
                icon: isDanger ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#94A3B8',
                confirmButtonText: 'Yes, ' + actionText.toLowerCase(),
                cancelButtonText: 'Cancel',
                background: '#ffffff',
                borderRadius: '16px',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });

    // Forms with .confirm-form
    const confirmForms = document.querySelectorAll('form.confirm-form');
    confirmForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const actionText = this.getAttribute('data-action') || 'Proceed';
            const messageText = this.getAttribute('data-message') || 'Are you sure you want to continually proceed?';
            const confirmColor = this.getAttribute('data-color') || '#4318FF';

            Swal.fire({
                title: actionText,
                text: messageText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#94A3B8',
                confirmButtonText: 'Yes, ' + actionText.toLowerCase(),
                cancelButtonText: 'Cancel',
                borderRadius: '16px',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
