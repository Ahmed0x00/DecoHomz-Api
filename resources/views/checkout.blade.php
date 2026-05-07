@extends('layouts.app')

@section('title', 'Checkout — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/checkout.css">
@endsection

@section('content')

<div class="steps">
  <div class="step done">
    <div class="step-num">✓</div>
    <div class="step-name">Cart</div>
  </div>
  <div class="step-line done"></div>
  <div class="step done">
    <div class="step-num">✓</div>
    <div class="step-name">Review</div>
  </div>
  <div class="step-line done"></div>
  <div class="step active">
    <div class="step-num">3</div>
    <div class="step-name">Shipping</div>
  </div>
  <div class="step-line"></div>
  <div class="step inactive">
    <div class="step-num">4</div>
    <div class="step-name">Payment</div>
  </div>
</div>

<div class="checkout-wrap" id="checkout-wrap">
  <div>

    <form id="checkout-form">

      <!-- STEP 1: Shipping -->
      <div id="checkout-step-1">

        <!-- Contact Information -->
        <div class="form-block">
          <div class="block-title">
            <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Contact Information
          </div>
          <div class="form-grid">
            <div class="field">
              <label>First Name</label>
              <input type="text" name="first_name" placeholder="Sara" required>
            </div>
            <div class="field">
              <label>Last Name</label>
              <input type="text" name="last_name" placeholder="Ahmed" required>
            </div>
            <div class="field">
              <label>Email Address</label>
              <input type="email" name="email" placeholder="sara@email.com" required>
            </div>
            <div class="field">
              <label>Phone Number</label>
              <input type="tel" name="phone" placeholder="+20 1XX XXX XXXX" required>
            </div>
          </div>
        </div>

        <!-- Shipping Address -->
        <div class="form-block">
          <div class="block-title">
            <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Shipping Address
          </div>
          <div class="form-grid">
            <div class="field full">
              <label>Street Address</label>
              <input type="text" name="address_line_1" placeholder="14 El Nasr Street, Apt 5" required>
            </div>
            <div class="field full" style="display:none">
              <input type="text" name="address_line_2" placeholder="Apartment, suite, etc. (optional)">
            </div>
            <div class="field">
              <label>City</label>
              <input type="text" name="city" placeholder="Cairo" required>
            </div>
            <div class="field">
              <label>Governorate</label>
              <select name="state" required>
                <option value="">Select Governorate</option>
                <option>Cairo</option>
                <option>Giza</option>
                <option>Alexandria</option>
                <option>Sharm El Sheikh</option>
                <option>Luxor</option>
                <option>Aswan</option>
                <option>Port Said</option>
                <option>Suez</option>
                <option>Ismailia</option>
                <option>Fayoum</option>
                <option>Mansoura</option>
                <option>Tanta</option>
              </select>
            </div>
            <div class="field">
              <label>Postal Code</label>
              <input type="text" name="postal_code" placeholder="11511" required>
            </div>
            <div class="field">
              <label>Country</label>
              <select name="country" required>
                <option>Egypt</option>
              </select>
            </div>
            <div class="field full">
              <label>Delivery Notes (optional)</label>
              <input type="text" name="notes" placeholder="Floor, landmark, or special instructions...">
            </div>
          </div>
        </div>

        <!-- Delivery Method -->
        <div class="form-block">
          <div class="block-title">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <rect x="1" y="3" width="15" height="13" rx="1"/>
              <path d="M16 8h4l3 5v3h-7V8z"/>
              <circle cx="5.5" cy="18.5" r="2.5"/>
              <circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
            Delivery Method
          </div>
          <div class="delivery-opts">
            <div class="delivery-opt selected" onclick="selectDelivery(this, 0)">
              <div class="delivery-opt-left">
                <input type="radio" name="delivery_method" value="standard" checked>
                <div class="delivery-opt-info">
                  <div class="opt-name">Standard Delivery</div>
                  <div class="opt-desc">Estimated 5–7 business days · White glove assembly included</div>
                </div>
              </div>
              <div class="opt-price free">Free</div>
            </div>
            <div class="delivery-opt" onclick="selectDelivery(this, 299)">
              <div class="delivery-opt-left">
                <input type="radio" name="delivery_method" value="express">
                <div class="delivery-opt-info">
                  <div class="opt-name">Express Delivery</div>
                  <div class="opt-desc">Estimated 2–3 business days · Priority handling</div>
                </div>
              </div>
              <div class="opt-price">EGP 299</div>
            </div>
            <div class="delivery-opt" onclick="selectDelivery(this, 149)">
              <div class="delivery-opt-left">
                <input type="radio" name="delivery_method" value="scheduled">
                <div class="delivery-opt-info">
                  <div class="opt-name">Scheduled Delivery</div>
                  <div class="opt-desc">Choose your preferred date & time slot</div>
                </div>
              </div>
              <div class="opt-price">EGP 149</div>
            </div>
          </div>
        </div>

      </div>
      <!-- END STEP 1 -->

      <!-- STEP 2: Payment -->
      <div id="checkout-step-2" style="display:none">

        <div class="form-block">
          <div class="block-title">
            <svg viewBox="0 0 24 24" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Payment Method
          </div>
          <div class="payment-tabs">
            <button type="button" class="pay-tab active" onclick="switchPay('card', this)">Credit / Debit Card</button>
            <button type="button" class="pay-tab" onclick="switchPay('fawry', this)">Fawry</button>
            <button type="button" class="pay-tab" onclick="switchPay('cod', this)">Cash on Delivery</button>
          </div>

          <div id="pay-card">
            <div class="card-icons">
              <div class="c-icon">VISA</div>
              <div class="c-icon">MC</div>
              <div class="c-icon">AMEX</div>
            </div>
            <div class="form-grid">
              <div class="field full">
                <label>Cardholder Name</label>
                <input type="text" name="card_name" placeholder="Sara Ahmed" autocomplete="cc-name">
              </div>
              <div class="field full">
                <label>Card Number</label>
                <input type="text" name="card_number" placeholder="1234  5678  9012  3456" maxlength="19" autocomplete="cc-number">
              </div>
              <div class="field">
                <label>Expiry Date</label>
                <input type="text" name="card_expiry" placeholder="MM / YY" maxlength="7" autocomplete="cc-exp">
              </div>
              <div class="field">
                <label>CVV</label>
                <input type="text" name="card_cvv" placeholder="•••" maxlength="4" autocomplete="cc-csc">
              </div>
            </div>
            <div class="card-security">
              <svg viewBox="0 0 24 24" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              Your card details are encrypted and never stored.
            </div>
          </div>

          <div id="pay-fawry" class="alt-pay">
            <strong style="color:#2C1F14">How to pay with Fawry:</strong><br>
            1. Complete your order and note the Fawry reference code.<br>
            2. Visit any Fawry outlet or use the Fawry app.<br>
            3. Enter the reference code and pay the total amount.<br>
            4. Your order will be confirmed within 1 hour of payment.
          </div>

          <div id="pay-cod" class="alt-pay">
            <strong style="color:#2C1F14">Cash on Delivery</strong><br>
            Pay in cash when your order arrives. Please have the exact amount ready.<br>
            Available on orders up to <strong>EGP 30,000</strong>. A refundable deposit of <strong>EGP 500</strong> may be required to confirm your order.
          </div>
        </div>

        <button type="button" class="btn-outline" style="width:100%;margin-top:16px;padding:12px;cursor:pointer;background:#fff;border:1px solid #E0D8CE;border-radius:6px;color:#2C1F14;font-weight:600" onclick="backToShipping()">← Back to Shipping</button>

      </div>
      <!-- END STEP 2 -->

    </form>
  </div>

  <!-- ORDER SUMMARY SIDEBAR -->
  <div class="summary-box">
    <div class="sum-title">Order Summary</div>
    <div id="checkout-summary-items" class="sum-items"></div>

    <div class="coupon-row">
      <input type="text" id="checkout-coupon-input" placeholder="Coupon code">
      <button id="checkout-apply-coupon">Apply</button>
    </div>

    <div class="sum-row">
      <span class="k" id="co-subtotal-label">Subtotal</span>
      <span class="v" id="co-subtotal">EGP 0</span>
    </div>
    <div class="sum-row">
      <span class="k">Delivery Fee</span>
      <span class="v free" id="co-delivery">Free</span>
    </div>
    <div class="sum-total">
      <span>Total</span>
      <span id="co-total">EGP 0</span>
    </div>

    <button class="btn-place" id="btn-place-order" onclick="handlePlaceOrder()">Continue to Payment →</button>
    <div class="secure-note">
      <svg viewBox="0 0 24 24" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      Secure & encrypted checkout
    </div>
  </div>

</div>

@endsection

@section('extra_js')
<script>
(function() {
  let currentStep = 1;
  let deliveryFee = 0;
  let selectedPayment = 'card';
  let cartItems = [];

  // ── Init ─────────────────────────────────────────────────────
  (async function init() {
    Cart.updateBadge();

    const wrap = document.getElementById('checkout-wrap');
    if (wrap) wrap.classList.add('step-1-active');

    // Redirect if cart empty
    cartItems = Cart.get();
    if (cartItems.length === 0) {
      if (Auth.token()) {
        try {
          const res = await API.get('/cart');
          cartItems = res.data?.items || res.items || [];
        } catch(e) {}
      }
    }
    if (cartItems.length === 0) {
      location.href = '/cart';
      return;
    }

    // Auto-fill if logged in
    if (Auth.token()) {
      loadUserAddresses();
    }

    renderCheckoutSummary();
  })();

  // ── Load user addresses ─────────────────────────────────────
  async function loadUserAddresses() {
    try {
      const res = await API.get('/addresses');
      const addresses = res.data || res.addresses || [];
      if (addresses.length > 0) {
        const addr = addresses[0];
        const set = (name, val) => {
          const el = document.querySelector(`[name="${name}"]`);
          if (el && val) el.value = val;
        };
        set('first_name', addr.first_name);
        set('last_name', addr.last_name);
        set('email', addr.email || undefined);
        set('phone', addr.phone);
        set('address_line_1', addr.address_line_1);
        set('city', addr.city);
        set('state', addr.state || addr.governorate);
        set('postal_code', addr.postal_code);
      }
    } catch(e) {}
  }

  // ── Place order / advance step ──────────────────────────────
  window.handlePlaceOrder = function() {
    if (currentStep === 1) {
      const form = document.getElementById('checkout-form');
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }
      advanceToPayment();
    } else {
      submitOrder();
    }
  };

  function advanceToPayment() {
    document.getElementById('checkout-step-1').style.display = 'none';
    document.getElementById('checkout-step-2').style.display = 'block';

    const steps = document.querySelectorAll('.step');
    const stepLines = document.querySelectorAll('.step-line');
    if (steps[2]) {
      steps[2].classList.remove('active');
      steps[2].classList.add('done');
      const num = steps[2].querySelector('.step-num');
      if (num) num.textContent = '✓';
    }
    if (stepLines[2]) stepLines[2].classList.add('done');
    if (steps[3]) {
      steps[3].classList.remove('inactive');
      steps[3].classList.add('active');
    }

    const btn = document.getElementById('btn-place-order');
    if (btn) btn.textContent = 'Place Order →';

    const wrap = document.getElementById('checkout-wrap');
    if (wrap) {
      wrap.classList.remove('step-1-active');
      wrap.classList.add('step-2-active');
    }

    currentStep = 2;
    window.scrollTo(0, 0);
  }

  window.backToShipping = function() {
    document.getElementById('checkout-step-2').style.display = 'none';
    document.getElementById('checkout-step-1').style.display = 'block';

    const steps = document.querySelectorAll('.step');
    const stepLines = document.querySelectorAll('.step-line');
    if (steps[2]) {
      steps[2].classList.remove('done');
      steps[2].classList.add('active');
      const num = steps[2].querySelector('.step-num');
      if (num) num.textContent = '3';
    }
    if (stepLines[2]) stepLines[2].classList.remove('done');
    if (steps[3]) {
      steps[3].classList.remove('active');
      steps[3].classList.add('inactive');
    }

    const btn = document.getElementById('btn-place-order');
    if (btn) btn.textContent = 'Continue to Payment →';

    const wrap = document.getElementById('checkout-wrap');
    if (wrap) {
      wrap.classList.remove('step-2-active');
      wrap.classList.add('step-1-active');
    }

    currentStep = 1;
    window.scrollTo(0, 0);
  };

  // ── Delivery selection ─────────────────────────────────────
  window.selectDelivery = function(el, fee) {
    document.querySelectorAll('.delivery-opt').forEach(opt => opt.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
    deliveryFee = fee;

    const deliveryEl = document.getElementById('co-delivery');
    if (deliveryEl) {
      deliveryEl.textContent = fee === 0 ? 'Free' : 'EGP ' + fee.toLocaleString();
      deliveryEl.className = fee === 0 ? 'v free' : 'v';
    }
    updateFinalTotal();
  };

  // ── Payment tabs ───────────────────────────────────────────
  window.switchPay = function(method, btn) {
    document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    selectedPayment = method;

    document.getElementById('pay-card').style.display = method === 'card' ? 'block' : 'none';
    document.getElementById('pay-fawry').style.display = method === 'fawry' ? 'block' : 'none';
    document.getElementById('pay-cod').style.display = method === 'cod' ? 'block' : 'none';
  };

  // ── Submit order ───────────────────────────────────────────
  async function submitOrder() {
    const form = document.getElementById('checkout-form');
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());

    const paymentMethod = selectedPayment === 'card' ? 'card' : selectedPayment === 'fawry' ? 'fawry' : 'cod';

    try {
      const res = await API.post('/orders', {
        email: payload.email,
        phone: payload.phone,
        shipping_address: {
          first_name: payload.first_name,
          last_name: payload.last_name,
          address_line_1: payload.address_line_1,
          address_line_2: payload.address_line_2 || '',
          city: payload.city,
          state: payload.state || '',
          postal_code: payload.postal_code,
          country: payload.country || 'Egypt',
          phone: payload.phone,
        },
        payment_method: paymentMethod,
        delivery_method: payload.delivery_method || 'standard',
        notes: payload.notes || '',
      });

      const orderId = res.data?.order?.id || res.order?.id;
      Cart.set([]);
      location.href = '/orders/confirmation/' + orderId;

    } catch(e) {
      showToast(e.data?.message || 'Checkout failed. Please try again.');
    }
  }

  // ── Render summary ─────────────────────────────────────────
  function renderCheckoutSummary() {
    const container = document.getElementById('checkout-summary-items');
    if (!container) return;

    if (cartItems.length === 0) {
      container.innerHTML = '<p style="color:#aaa;text-align:center;font-size:12px;padding:20px">No items in cart</p>';
      return;
    }

    container.innerHTML = cartItems.map(item => `
      <div class="sum-item">
        <div class="sum-thumb">
          ${item.image
            ? `<img src="${item.image}" alt="${item.name}" style="width:70%;height:70%;object-fit:contain" onerror="this.style.display='none'">`
            : `<svg viewBox="0 0 80 80" fill="none">
                <rect x="10" y="30" width="60" height="30" rx="5" fill="#C4A882"/>
                <rect x="18" y="20" width="10" height="20" rx="3" fill="#A07858"/>
                <rect x="52" y="20" width="10" height="20" rx="3" fill="#A07858"/>
              </svg>`
          }
          <div class="qty-badge">${item.quantity}</div>
        </div>
        <div class="sum-item-info">
          <div class="sum-item-name">${item.name}</div>
          <div class="sum-item-meta">${item.variant || 'Standard'}</div>
        </div>
        <div class="sum-item-price">EGP ${(parseFloat(item.price) * (parseInt(item.quantity) || 1)).toLocaleString()}</div>
      </div>
    `).join('');

    updateFinalTotal();
  }

  function updateFinalTotal() {
    const subtotal = cartItems.reduce((s, i) => s + (parseFloat(i.price) || 0) * (parseInt(i.quantity) || 1), 0);
    const total = subtotal + deliveryFee;

    const subtotalEl = document.getElementById('co-subtotal');
    const subtotalLabel = document.getElementById('co-subtotal-label');
    const totalEl = document.getElementById('co-total');

    if (subtotalEl) subtotalEl.textContent = 'EGP ' + subtotal.toLocaleString();
    if (subtotalLabel) subtotalLabel.textContent = 'Subtotal' + (cartItems.length ? ` (${cartItems.length} item${cartItems.length > 1 ? 's' : ''})` : '');
    if (totalEl) totalEl.textContent = 'EGP ' + total.toLocaleString();
  }

  // ── Coupon on checkout ─────────────────────────────────────
  document.getElementById('checkout-apply-coupon').onclick = async function() {
    const code = document.getElementById('checkout-coupon-input').value.trim();
    if (!code) return;
    try {
      const res = await API.post('/cart/coupon', { code });
      showToast('Coupon applied: ' + (res.data?.coupon?.code || code));
    } catch(e) {
      showToast(e.data?.message || 'Invalid coupon code');
    }
  };
})();
</script>
@endsection
