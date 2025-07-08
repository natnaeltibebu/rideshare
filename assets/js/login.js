document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.auth-tab');
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const tabIndicator = document.getElementById('tabIndicator');
    const togglePassword = document.getElementById('togglePassword');
    const toggleSignupPassword = document.getElementById('toggleSignupPassword');
    const passwordInput = document.getElementById('passwordInput');
    const signupPasswordInput = document.getElementById('signupPasswordInput');
    const loginFormElement = document.getElementById('loginFormElement');
    const signupFormElement = document.getElementById('signupFormElement');
    const loginBtn = document.getElementById('loginBtn');
    const signupBtn = document.getElementById('signupBtn');
    const errorAlert = document.getElementById('errorAlert');
    const successAlert = document.getElementById('successAlert');
    const errorMessage = document.getElementById('errorMessage');
    const successMessage = document.getElementById('successMessage');

    // Tab switching
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            tab.classList.add('active');

            // Move indicator and switch forms
            if (tab.dataset.tab === 'signup') {
                tabIndicator.classList.add('right');
                loginForm.classList.add('hidden');
                setTimeout(() => {
                    signupForm.classList.remove('hidden');
                }, 150);
            } else {
                tabIndicator.classList.remove('right');
                signupForm.classList.add('hidden');
                setTimeout(() => {
                    loginForm.classList.remove('hidden');
                }, 150);
            }
        });
    });

    // Password toggle functionality
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? 
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' : 
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
        });
    }

    if (toggleSignupPassword && signupPasswordInput) {
        toggleSignupPassword.addEventListener('click', function() {
            const type = signupPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            signupPasswordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? 
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' : 
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
        });
    }

    // Alert functions
    function showAlert(type, message) {
        if (type === 'error') {
            errorMessage.textContent = message;
            errorAlert.style.display = 'block';
            successAlert.style.display = 'none';
        } else if (type === 'success') {
            successMessage.textContent = message;
            successAlert.style.display = 'block';
            errorAlert.style.display = 'none';
        }
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            errorAlert.style.display = 'none';
            successAlert.style.display = 'none';
        }, 5000);
    }

    // Form submission handlers
    function showLoading(btn) {
        btn.disabled = true;
        btn.querySelector('.btn-text').style.display = 'none';
        btn.querySelector('.loading').style.display = 'inline-block';
    }

    function hideLoading(btn) {
        btn.disabled = false;
        btn.querySelector('.btn-text').style.display = 'inline';
        btn.querySelector('.loading').style.display = 'none';
    }

    if (loginFormElement) {
        loginFormElement.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent actual form submission
            showLoading(loginBtn);
            
            // Simulate form processing
            setTimeout(() => {
                hideLoading(loginBtn);
                showAlert('success', 'Login simulation completed! (This is just a demo)');
            }, 2000);
        });
    }

    if (signupFormElement) {
        signupFormElement.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent actual form submission
            const password = signupPasswordInput.value;
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/;
            
            if (password.length < 8) {
                showAlert('error', 'Password must be at least 8 characters long');
                return;
            }
            
            if (!passwordRegex.test(password)) {
                showAlert('error', 'Password must contain at least one uppercase letter, one lowercase letter, and one number');
                return;
            }
            
            showLoading(signupBtn);
            
            // Simulate form processing
            setTimeout(() => {
                hideLoading(signupBtn);
                showAlert('success', 'Registration simulation completed! (This is just a demo)');
            }, 2000);
        });
    }

    // Add ripple effect to primary buttons
    const buttons = document.querySelectorAll('.btn-primary');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            this.appendChild(ripple);
            
            const x = e.clientX - e.target.getBoundingClientRect().left;
            const y = e.clientY - e.target.getBoundingClientRect().top;
            
            ripple.style.cssText = `
                position: absolute;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                pointer-events: none;
                width: 100px;
                height: 100px;
                top: ${y - 50}px;
                left: ${x - 50}px;
                transform: scale(0);
                animation: ripple 0.6s linear;
            `;
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});
