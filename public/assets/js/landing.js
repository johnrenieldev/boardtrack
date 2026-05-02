/**
 * BoardTrack — Landing Page JS
 * Minimal: mobile menu toggle, smooth scroll, form validation
 */
document.addEventListener('DOMContentLoaded', () => {

  // Mobile menu toggle
  const menuBtn = document.getElementById('mobileMenuBtn');
  const mobileMenu = document.getElementById('mobileMenu');
  
  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('open');
      const icon = menuBtn.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-xmark');
      }
    });

    // Close menu when link clicked
    mobileMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.remove('open');
        const icon = menuBtn.querySelector('i');
        if (icon) {
          icon.classList.remove('fa-xmark');
          icon.classList.add('fa-bars');
        }
      });
    });
  }

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
      const targetId = anchor.getAttribute('href');
      if (targetId === '#') return;
      
      const target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        const offset = 80; // navbar height
        window.scrollTo({
          top: target.offsetTop - offset,
          behavior: 'smooth'
        });
      }
    });
  });

  // Room preference selection (register page)
  const roomOptions = document.querySelectorAll('.room-option');
  roomOptions.forEach(option => {
    option.addEventListener('click', () => {
      roomOptions.forEach(opt => opt.classList.remove('selected'));
      option.classList.add('selected');
      const radio = option.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
    });
  });

  // File upload preview (register page)
  const fileInput = document.getElementById('government_id');
  const fileLabel = document.getElementById('fileLabel');
  const filePreview = document.getElementById('filePreview');
  
  if (fileInput) {
    fileInput.addEventListener('change', () => {
      const file = fileInput.files[0];
      if (file) {
        if (fileLabel) fileLabel.textContent = file.name;
        if (filePreview && file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = (e) => {
            filePreview.innerHTML = `<img src="${e.target.result}" alt="ID Preview" style="max-height: 100px; border-radius: 4px; margin-top: 8px;">`;
          };
          reader.readAsDataURL(file);
        }
      }
    });
  }

  // Password visibility toggle
  window.togglePassword = function(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input && icon) {
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    }
  };

  // Password match check (register page)
  const password = document.getElementById('password');
  const confirmPassword = document.getElementById('confirm_password');
  const matchMsg = document.getElementById('matchMsg');
  
  if (confirmPassword && password && matchMsg) {
    confirmPassword.addEventListener('input', () => {
      if (confirmPassword.value) {
        matchMsg.style.display = 'block';
        if (confirmPassword.value === password.value) {
          matchMsg.textContent = 'Passwords match';
          matchMsg.style.color = '#16a34a';
        } else {
          matchMsg.textContent = 'Passwords do not match';
          matchMsg.style.color = '#dc2626';
        }
      } else {
        matchMsg.style.display = 'none';
      }
    });
  }

});
