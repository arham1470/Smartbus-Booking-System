/**
 * SmartBus Booking System
 * Main JavaScript File
 * Phase 1: Interactive Components & Foundation
 */

document.addEventListener('DOMContentLoaded', function() {
    initMobileNavigation();
    initFormEnhancements();
    initAlertsAutoDismiss();
    initPasswordToggle();
});

/* ============================================
   MOBILE NAVIGATION (Navbar + Sidebar)
   ============================================ */
function initMobileNavigation() {
    // Public navbar hamburger
    const navbarToggle = document.querySelector('.navbar-toggle');
    const navbarNav = document.querySelector('.navbar-nav');
    
    if (navbarToggle && navbarNav) {
        navbarToggle.addEventListener('click', function() {
            navbarNav.classList.toggle('active');
            
            // Change icon
            const icon = navbarToggle.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-times');
            }
        });
        
        // Close mobile menu when clicking a link
        navbarNav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navbarNav.classList.remove('active');
            });
        });
    }
    
    // Dashboard sidebar toggle (for future phases)
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('active')) {
            if (!sidebar.contains(e.target) && !sidebarToggle?.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
}

/* ============================================
   FORM ENHANCEMENTS
   ============================================ */
function initFormEnhancements() {
    // Add loading state to submit buttons
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            
            if (submitBtn && !submitBtn.disabled) {
                const originalText = submitBtn.innerHTML || submitBtn.value;
                
                // Prevent double submission
                submitBtn.disabled = true;
                
                if (submitBtn.tagName === 'BUTTON') {
                    submitBtn.innerHTML = `<span class="spinner"></span> Processing...`;
                } else {
                    submitBtn.value = 'Processing...';
                }
                
                // Re-enable after 8 seconds as safety net (in case of network issues)
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        if (submitBtn.tagName === 'BUTTON') {
                            submitBtn.innerHTML = originalText;
                        } else {
                            submitBtn.value = originalText;
                        }
                    }
                }, 8000);
            }
        });
    });
    
    // Real-time validation feedback for required fields
    const requiredInputs = document.querySelectorAll('input[required], select[required], textarea[required]');
    
    requiredInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.style.borderColor = 'var(--danger)';
            } else {
                this.style.borderColor = '';
            }
        });
        
        input.addEventListener('input', function() {
            if (this.style.borderColor && this.value.trim()) {
                this.style.borderColor = '';
            }
        });
    });
}

/* ============================================
   AUTO DISMISS ALERTS
   ============================================ */
function initAlertsAutoDismiss() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        // Auto dismiss after 6 seconds
        setTimeout(() => {
            if (alert && alert.parentNode) {
                alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-8px)';
                
                setTimeout(() => {
                    if (alert.parentNode) alert.parentNode.removeChild(alert);
                }, 400);
            }
        }, 6000);
        
        // Allow manual close with click
        alert.addEventListener('click', function() {
            this.style.transition = 'opacity 0.3s ease';
            this.style.opacity = '0';
            setTimeout(() => {
                if (this.parentNode) this.parentNode.removeChild(this);
            }, 300);
        });
    });
}

/* ============================================
   PASSWORD VISIBILITY TOGGLE
   ============================================ */
function initPasswordToggle() {
    // Look for password fields with data attribute or common pattern
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(field => {
        // Only add toggle if not already wrapped
        if (field.parentNode.classList.contains('password-wrapper')) return;
        
        const wrapper = document.createElement('div');
        wrapper.className = 'password-wrapper';
        wrapper.style.position = 'relative';
        
        // Move the input into wrapper
        field.parentNode.insertBefore(wrapper, field);
        wrapper.appendChild(field);
        
        // Create toggle button
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'password-toggle';
        toggle.innerHTML = '👁️';
        toggle.setAttribute('aria-label', 'Toggle password visibility');
        toggle.style.cssText = `
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; font-size: 1rem;
            padding: 4px; opacity: 0.7;
        `;
        
        wrapper.appendChild(toggle);
        
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (field.type === 'password') {
                field.type = 'text';
                toggle.innerHTML = '🙈';
            } else {
                field.type = 'password';
                toggle.innerHTML = '👁️';
            }
        });
    });
}

/* ============================================
   UTILITY FUNCTIONS (Available globally)
   ============================================ */

// Show toast notification (can be used in later phases)
window.showToast = function(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.cssText = 'position:fixed; bottom:24px; right:24px; z-index:9999; max-width:320px;';
    toast.innerHTML = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transition = 'all 0.4s ease';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 400);
    }, 4500);
};

// Confirm action helper
window.confirmAction = function(message, callback) {
    if (confirm(message)) {
        callback();
    }
};

console.log('%c[SmartBus] Phase 1 JavaScript initialized successfully.', 'color:#1565C0');
