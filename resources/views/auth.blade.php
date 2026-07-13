@extends('layouts.app')

@section('title', 'Sign In / Register — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="{{ asset_v('/css/signin.css') }}">
@endsection

@section('content')

<div class="auth-page">
  <div class="auth-container animate-scale-in">
    
    {{-- Tabs --}}
    <div class="auth-tabs">
      <div class="auth-tab active" id="tab-login" onclick="switchAuthTab('login')">{{ __('Sign In') }}</div>
      <div class="auth-tab" id="tab-register" onclick="switchAuthTab('register')">{{ __('Create Account') }}</div>
      <div class="auth-tab-indicator" id="auth-indicator"></div>
    </div>

    {{-- Forms Wrapper --}}
    <div class="auth-form-wrap" id="auth-wrap">
      
      {{-- Login Form --}}
      <div class="auth-form" id="form-login">
        <h2 class="auth-title">{{ __('Welcome Back') }}</h2>
        <p class="auth-sub">{{ __('Sign in to access your saved items and history.') }}</p>
        
        <form id="login-form" onsubmit="event.preventDefault(); submitLogin();">
          <div class="field">
            <label>{{ __('Email Address') }}</label>
            <input type="email" id="login-email" placeholder="name@example.com" required>
          </div>
          <div class="field">
            <label>{{ __('Password') }}</label>
            <input type="password" id="login-password" placeholder="••••••••" required>
          </div>
          <div id="login-error" style="display:none;color:var(--color-error);font-size:13px;margin-bottom:12px;padding:10px;background:rgba(192,57,43,.06);border-radius:var(--radius-sm)"></div>
          
          <button type="submit" class="btn-dark auth-btn" id="btn-login">{{ __('Sign In') }}</button>
          
          <div class="auth-links">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
              <input type="checkbox" style="width:16px;height:16px;accent-color:var(--color-primary)">
              <span style="color:var(--color-text-secondary);font-weight:500">{{ __('Remember me') }}</span>
            </label>
            <a href="#" class="auth-link">{{ __('Forgot Password?') }}</a>
          </div>
        </form>

        <div class="auth-divider">Or continue with</div>
        <div class="social-btns">
          <button class="btn-social">
            <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            Google
          </button>
          <button class="btn-social">
            <svg viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            Facebook
          </button>
        </div>
      </div>
      
      {{-- Register Form --}}
      <div class="auth-form" id="form-register">
        <h2 class="auth-title">{{ __('Join DecoHomz') }}</h2>
        <p class="auth-sub">{{ __('Create an account to track your orders and save items.') }}</p>
        
        <form id="register-form" onsubmit="event.preventDefault(); submitRegister();">
          <div class="field">
            <label>{{ __('Full Name') }}</label>
            <input type="text" id="reg-name" placeholder="Ahmed Ali" required>
          </div>
          <div class="field">
            <label>{{ __('Email Address') }}</label>
            <input type="email" id="reg-email" placeholder="name@example.com" required>
          </div>
          <div class="field">
            <label>{{ __('Password') }}</label>
            <input type="password" id="reg-password" placeholder="••••••••" required minlength="8">
          </div>
          <div class="field">
            <label>{{ __('Confirm Password') }}</label>
            <input type="password" id="reg-password-confirm" placeholder="••••••••" required minlength="8">
          </div>
          <div id="register-error" style="display:none;color:var(--color-error);font-size:13px;margin-bottom:12px;padding:10px;background:rgba(192,57,43,.06);border-radius:var(--radius-sm)"></div>
          
          <button type="submit" class="btn-dark auth-btn" id="btn-register">{{ __('Create Account') }}</button>
          
          <div style="text-align:center;margin-top:16px;font-size:12px;color:var(--color-text-faint)">
            {{ __('By creating an account, you agree to our') }} <br><a href="#" class="auth-link" style="font-size:12px">{{ __('Terms of Service & Privacy Policy') }}</a>.
          </div>
        </form>
      </div>
      
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
window.switchAuthTab = function(tab) {
  document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  
  const wrap = document.getElementById('auth-wrap');
  const indicator = document.getElementById('auth-indicator');
  
  const isRtl = document.dir === 'rtl';
  
  if (tab === 'login') {
    wrap.style.transform = 'translateX(0)';
    indicator.style.transform = isRtl ? 'translateX(0)' : 'translateX(0)';
  } else {
    wrap.style.transform = isRtl ? 'translateX(50%)' : 'translateX(-50%)';
    indicator.style.transform = isRtl ? 'translateX(-100%)' : 'translateX(100%)';
  }
  
  // Clear errors
  document.getElementById('login-error').style.display = 'none';
  document.getElementById('register-error').style.display = 'none';
};

function showError(containerId, message) {
  const el = document.getElementById(containerId);
  el.textContent = message;
  el.style.display = 'block';
}

// ── LOGIN ────────────────────────────────────────────────
window.submitLogin = async function() {
  const btn = document.getElementById('btn-login');
  const email = document.getElementById('login-email').value.trim();
  const password = document.getElementById('login-password').value;
  
  document.getElementById('login-error').style.display = 'none';
  
  if (!email || !password) {
    showError('login-error', "{{ __('Please fill in all fields.') }}");
    return;
  }
  
  btn.classList.add('btn-loading');
  btn.disabled = true;
  btn.textContent = "{{ __('Signing in...') }}";
  
  try {
    const user = await Auth.login(email, password);
    
    showToast("{{ __('Welcome back!') }}", 'success');
    
    // Redirect based on role
    if (user.role === 'admin' || user.role === 'support') {
      setTimeout(() => { window.location.href = '/admin/dashboard'; }, 800);
    } else if (user.role === 'vendor' && user.vendor && user.vendor.status === 'active') {
      setTimeout(() => { window.location.href = '/vendor/portal'; }, 800);
    } else {
      // Check if there's an intended redirect (e.g. from checkout)
      const intended = new URLSearchParams(window.location.search).get('redirect');
      setTimeout(() => { window.location.href = intended || '/account'; }, 800);
    }
  } catch (e) {
    const msg = e.data?.message || "{{ __('Invalid email or password.') }}";
    showError('login-error', msg);
    btn.classList.remove('btn-loading');
    btn.disabled = false;
    btn.textContent = "{{ __('Sign In') }}";
  }
};

// ── REGISTER ─────────────────────────────────────────────
window.submitRegister = async function() {
  const btn = document.getElementById('btn-register');
  const name = document.getElementById('reg-name').value.trim();
  const email = document.getElementById('reg-email').value.trim();
  const password = document.getElementById('reg-password').value;
  const passwordConfirm = document.getElementById('reg-password-confirm').value;
  
  document.getElementById('register-error').style.display = 'none';
  
  if (!name || !email || !password || !passwordConfirm) {
    showError('register-error', "{{ __('Please fill in all fields.') }}");
    return;
  }
  
  if (password !== passwordConfirm) {
    showError('register-error', "{{ __('Passwords do not match.') }}");
    return;
  }
  
  if (password.length < 8) {
    showError('register-error', "{{ __('Password must be at least 8 characters.') }}");
    return;
  }
  
  btn.classList.add('btn-loading');
  btn.disabled = true;
  btn.textContent = "{{ __('Creating account...') }}";
  
  try {
    const user = await Auth.register({
      name: name,
      email: email,
      password: password,
      password_confirmation: passwordConfirm,
    });
    
    showToast("{{ __('Account created successfully!') }}", 'success');
    
    // Redirect
    if (user.role === 'admin' || user.role === 'support') {
      setTimeout(() => { window.location.href = '/admin/dashboard'; }, 800);
    } else {
      const intended = new URLSearchParams(window.location.search).get('redirect');
      setTimeout(() => { window.location.href = intended || '/account'; }, 800);
    }
  } catch (e) {
    let msg = e.data?.message || "{{ __('Registration failed. Please try again.') }}";
    // Handle Laravel validation errors
    if (e.data?.errors) {
      const firstError = Object.values(e.data.errors)[0];
      msg = Array.isArray(firstError) ? firstError[0] : firstError;
    }
    showError('register-error', msg);
    btn.classList.remove('btn-loading');
    btn.disabled = false;
    btn.textContent = "{{ __('Create Account') }}";
  }
};

// If already logged in, redirect
document.addEventListener('DOMContentLoaded', () => {
  if (Auth.token()) {
    const user = Auth.user();
    if (user && (user.role === 'admin' || user.role === 'support')) {
      window.location.href = '/admin/dashboard';
    } else if (user && user.role === 'vendor' && user.vendor && user.vendor.status === 'active') {
      window.location.href = '/vendor/portal';
    } else {
      window.location.href = '/account';
    }
  }
  
  if (document.dir === 'rtl') {
    document.getElementById('auth-indicator').style.transformOrigin = 'right';
  }
});
</script>
@endsection