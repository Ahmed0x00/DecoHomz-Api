/**
 * DecoHomz — Sign In / Register Logic
 */

function switchTab(type) {
    const signinForm = document.getElementById('signin-form');
    const registerForm = document.getElementById('register-form');
    const signinTab = document.getElementById('tab-signin');
    const registerTab = document.getElementById('tab-register');

    if (type === 'signin') {
        signinForm.style.display = 'block';
        registerForm.style.display = 'none';
        signinTab.classList.add('active');
        registerTab.classList.remove('active');
    } else {
        signinForm.style.display = 'none';
        registerForm.style.display = 'block';
        signinTab.classList.remove('active');
        registerTab.classList.add('active');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const signinBtn = document.querySelector('#signin-form .btn-submit');
    if (signinBtn) {
        signinBtn.addEventListener('click', () => {
            const email = document.querySelector('#signin-form input[type="email"]').value;
            const pass = document.querySelector('#signin-form input[type="password"]').value;
            
            if (!email || !pass) {
                alert('Please fill in all fields.');
                return;
            }
            
            // Mock login
            alert('Welcome back! Redirecting to your account...');
            location.href = 'account.html';
        });
    }

    const registerBtn = document.querySelector('#register-form .btn-submit');
    if (registerBtn) {
        registerBtn.addEventListener('click', () => {
            const inputs = document.querySelectorAll('#register-form input');
            let valid = true;
            inputs.forEach(input => {
                if (!input.value) valid = false;
            });

            if (!valid) {
                alert('Please fill in all fields to create your account.');
                return;
            }

            alert('Account created successfully! Welcome to DecoHomz.');
            location.href = 'account.html';
        });
    }
});