@extends('layouts.app')

@section('title', 'Become a Vendor — DecoHomz')

@section('extra_css')
<style>
  .vendor-hero {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    color: #fff;
    padding: 80px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .vendor-hero::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: radial-gradient(circle at center, rgba(196,168,130,0.15) 0%, transparent 70%);
  }
  .vendor-hero-content {
    position: relative;
    z-index: 1;
    max-width: 600px;
    margin: 0 auto;
  }
  .vendor-hero h1 {
    font-size: 42px;
    margin-bottom: 15px;
    font-weight: 700;
    letter-spacing: -0.5px;
  }
  .vendor-hero p {
    font-size: 18px;
    color: #ccc;
    line-height: 1.6;
  }
  .vendor-apply-container {
    max-width: 800px;
    margin: -40px auto 80px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    position: relative;
    z-index: 2;
    padding: 40px;
  }
  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
  }
  .field {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .field.full {
    grid-column: 1 / -1;
  }
  .field label {
    font-size: 13px;
    font-weight: 600;
    color: #333;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .field input, .field textarea {
    padding: 14px 16px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 15px;
    font-family: inherit;
    transition: all 0.3s ease;
    background: #fafafa;
  }
  .field input:focus, .field textarea:focus {
    outline: none;
    border-color: #8B6A48;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(139,106,72,0.1);
  }
  .field textarea {
    resize: vertical;
    min-height: 100px;
  }
  .btn-submit {
    background: #1a1a1a;
    color: #fff;
    border: none;
    padding: 16px 32px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    margin-top: 32px;
  }
  .btn-submit:hover {
    background: #333;
    transform: translateY(-2px);
  }
  .btn-submit:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
  }
  .section-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 24px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
    color: #1a1a1a;
  }
  .optional-badge {
    font-size: 11px;
    background: #f0f0f0;
    color: #666;
    padding: 2px 6px;
    border-radius: 4px;
    margin-left: 8px;
    vertical-align: middle;
  }
  
  /* Success State */
  .success-state {
    text-align: center;
    padding: 60px 20px;
    display: none;
  }
  .success-icon {
    width: 80px;
    height: 80px;
    background: #F0F7EC;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
  }
  .success-icon svg {
    width: 40px;
    height: 40px;
    stroke: #4A7C3F;
  }
  .success-state h2 {
    font-size: 28px;
    margin-bottom: 16px;
    color: #1a1a1a;
  }
  .success-state p {
    font-size: 16px;
    color: #666;
    margin-bottom: 32px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
  }
  
  /* Unauthenticated State */
  .auth-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(4px);
    border-radius: 16px;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 40px;
    display: none;
  }
  .auth-overlay h3 {
    font-size: 24px;
    margin-bottom: 12px;
  }
  .auth-overlay p {
    color: #666;
    margin-bottom: 24px;
  }
  .btn-login {
    background: #8B6A48;
    color: #fff;
    padding: 12px 32px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: background 0.3s;
  }
  .btn-login:hover {
    background: #6d5236;
  }

  @media (max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; }
    .vendor-hero { padding: 60px 20px 80px; }
    .vendor-apply-container { margin: -40px 16px 60px; padding: 24px; }
  }
</style>
@endsection

@section('content')

<div class="vendor-hero">
  <div class="vendor-hero-content">
    <h1>Partner with DecoHomz</h1>
    <p>Join our curated marketplace of premium furniture makers and reach thousands of customers nationwide.</p>
  </div>
</div>

<div class="vendor-apply-container">
  
  <!-- Auth Required Overlay -->
  <div class="auth-overlay" id="auth-overlay">
    <h3>Sign In Required</h3>
    <p>You need to be logged into your DecoHomz account to apply as a vendor.</p>
    <a href="/auth?redirect=/vendor/apply" class="btn-login">Sign In / Register</a>
  </div>

  <!-- Success State -->
  <div class="success-state" id="success-state">
    <div class="success-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
      </svg>
    </div>
    <h2>Application Submitted!</h2>
    <p>Thank you for your interest in joining DecoHomz. Our team will review your application and contact you shortly.</p>
    <a href="/account" class="btn-login" style="background:#1a1a1a;">Go to My Account</a>
  </div>

  <!-- Application Form -->
  <form id="vendor-form">
    <div class="section-title">Company Information</div>
    <div class="form-grid">
      <div class="field">
        <label>Company/Store Name *</label>
        <input type="text" id="company_name" required placeholder="e.g. Elegance Furniture">
      </div>
      <div class="field">
        <label>Contact Person Name *</label>
        <input type="text" id="contact_name" required placeholder="Full Name">
      </div>
      <div class="field">
        <label>Email Address <span class="optional-badge">Optional</span></label>
        <input type="email" id="email" placeholder="vendor@example.com">
      </div>
      <div class="field">
        <label>Phone Number *</label>
        <input type="tel" id="phone" required placeholder="01xxxxxxxxx">
      </div>
      <div class="field full">
        <label>Main Address *</label>
        <textarea id="address" required placeholder="Full address of your main office or showroom"></textarea>
      </div>
      <div class="field full">
        <label>Workshop/Factory Address <span class="optional-badge">Optional</span></label>
        <textarea id="workshop_address" placeholder="If different from main address"></textarea>
      </div>
    </div>

    <div class="section-title" style="margin-top:40px;">Financial Details <span class="optional-badge" style="font-weight:normal;">Optional</span></div>
    <div class="form-grid">
      <div class="field">
        <label>Bank Account Number</label>
        <input type="text" id="bank_account_number" placeholder="For payouts">
      </div>
      <div class="field">
        <label>E-Wallet Number</label>
        <input type="text" id="e_wallet_number" placeholder="e.g. Vodafone Cash">
      </div>
    </div>

    <div class="section-title" style="margin-top:40px;">Legal Documents <span style="color:#ef4444">*</span></div>
    <div class="form-grid">
      <div class="field">
        <label>Commercial Register (السجل التجاري) *</label>
        <input type="file" id="commercial_register" accept=".pdf,.jpg,.jpeg,.png" required style="padding: 10px;">
        <small style="color: #666; margin-top: 4px;">PDF, JPG, or PNG (Max 5MB)</small>
      </div>
      <div class="field">
        <label>Tax Card (البطاقة الضريبية) *</label>
        <input type="file" id="tax_card" accept=".pdf,.jpg,.jpeg,.png" required style="padding: 10px;">
        <small style="color: #666; margin-top: 4px;">PDF, JPG, or PNG (Max 5MB)</small>
      </div>
    </div>

    <div class="field full" style="flex-direction:row;align-items:center;margin-top:16px;">
      <input type="checkbox" id="agree_terms" required style="width:auto;margin-right:12px;transform:scale(1.2);">
      <label for="agree_terms" style="text-transform:none;letter-spacing:normal;">I have read and agree to the <a href="/vendor-terms" target="_blank" style="color:#8B6A48;text-decoration:underline;">DecoHomz Vendor Policies and Terms & Conditions</a>.</label>
    </div>

    <button type="submit" class="btn-submit" id="submit-btn">Submit Application</button>
  </form>

</div>

@endsection

@section('extra_js')
<script>
  document.addEventListener('DOMContentLoaded', async () => {
    // Check authentication
    if (!Auth.token()) {
      document.getElementById('auth-overlay').style.display = 'flex';
      return;
    }

    // Pre-fill user data if possible
    try {
      const userRes = await API.get('/auth/user');
      const user = userRes.data || userRes;
      if (user) {
        if (!document.getElementById('contact_name').value) {
          document.getElementById('contact_name').value = user.name || [user.first_name, user.last_name].filter(Boolean).join(' ');
        }
        if (!document.getElementById('email').value) {
          document.getElementById('email').value = user.email || '';
        }
        if (!document.getElementById('phone').value) {
          document.getElementById('phone').value = user.phone || '';
        }
        
        // If already a vendor or pending, show message
        if (user.role === 'vendor' || user.role === 'pending_vendor') {
          showSuccessState("You have already applied or are already a vendor.");
        }
      }
    } catch (e) {
      // ignore
    }

    const form = document.getElementById('vendor-form');
    const submitBtn = document.getElementById('submit-btn');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const formData = new FormData();
      formData.append('company_name', document.getElementById('company_name').value.trim());
      formData.append('contact_name', document.getElementById('contact_name').value.trim());
      
      const email = document.getElementById('email').value.trim();
      if(email) formData.append('email', email);
      
      formData.append('phone', document.getElementById('phone').value.trim());
      formData.append('address', document.getElementById('address').value.trim());
      
      const workshop = document.getElementById('workshop_address').value.trim();
      if(workshop) formData.append('workshop_address', workshop);
      
      const bank = document.getElementById('bank_account_number').value.trim();
      if(bank) formData.append('bank_account_number', bank);
      
      const wallet = document.getElementById('e_wallet_number').value.trim();
      if(wallet) formData.append('e_wallet_number', wallet);
      
      const commRegFile = document.getElementById('commercial_register').files[0];
      if (commRegFile) formData.append('commercial_register', commRegFile);
      
      const taxCardFile = document.getElementById('tax_card').files[0];
      if (taxCardFile) formData.append('tax_card', taxCardFile);

      submitBtn.disabled = true;
      submitBtn.textContent = 'Submitting...';

      try {
        await API.post('/vendor/register', formData);
        showSuccessState();
      } catch (err) {
        const msg = err.data?.message || err.data?.errors?.[Object.keys(err.data.errors)[0]]?.[0] || 'An error occurred during application.';
        showToast(msg);
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Application';
      }
    });

    function showSuccessState(customMsg) {
      document.getElementById('vendor-form').style.display = 'none';
      const successState = document.getElementById('success-state');
      successState.style.display = 'block';
      if (customMsg) {
        successState.querySelector('p').textContent = customMsg;
        successState.querySelector('h2').textContent = 'Application Received';
      }
    }
  });
</script>
@endsection
