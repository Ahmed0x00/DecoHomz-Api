@extends('layouts.app')

@section('title', 'Checkout — DecoHomz')

@section('extra_css')
  <link rel="stylesheet" href="/css/checkout.css">
@endsection

@section('content')

  <div class="steps">
    <div class="step done">
      <div class="step-num">✓</div>
      <div class="step-name">{{ __('Cart') }}</div>
    </div>
    <div class="step-line done"></div>
    <div class="step done">
      <div class="step-num">✓</div>
      <div class="step-name">{{ __('Review') }}</div>
    </div>
    <div class="step-line done"></div>
    <div class="step active">
      <div class="step-num">3</div>
      <div class="step-name">{{ __('Shipping') }}</div>
    </div>
    <div class="step-line"></div>
    <div class="step inactive">
      <div class="step-num">4</div>
      <div class="step-name">{{ __('Payment') }}</div>
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
              <svg viewBox="0 0 24 24" stroke-width="1.5">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              {{ __('Contact Information') }}
            </div>
            <div class="form-grid">
              <div class="field">
                <label>{{ __('First Name') }}</label>
                <input type="text" name="first_name" placeholder="{{ __('Sara') }}" required>
              </div>
              <div class="field">
                <label>{{ __('Last Name') }}</label>
                <input type="text" name="last_name" placeholder="{{ __('Ahmed') }}" required>
              </div>
              <div class="field">
                <label>{{ __('Email Address') }}</label>
                <input type="email" name="email" placeholder="sara@email.com" required>
              </div>
              <div class="field">
                <label>{{ __('Phone Number') }}</label>
                <input type="tel" name="phone" placeholder="+20 1XX XXX XXXX" required>
              </div>
            </div>
          </div>

          <!-- Shipping Address -->
          <div class="form-block">
            <div class="block-title">
              <svg viewBox="0 0 24 24" stroke-width="1.5">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
              {{ __('Shipping Address') }}
            </div>
            <div class="form-grid">
              <div class="field full">
                <label>{{ __('Street Address') }}</label>
                <input type="text" name="address_line_1" placeholder="{{ __('14 El Nasr Street, Apt 5') }}" required>
              </div>
              <div class="field full" style="display:none">
                <input type="text" name="address_line_2" placeholder="{{ __('Apartment, suite, etc. (optional)') }}">
              </div>
              <div class="field">
                <label>{{ __('City') }}</label>
                <input type="text" name="city" placeholder="{{ __('Cairo') }}" required>
              </div>
              <div class="field">
                <label>{{ __('Governorate') }}</label>
                <select name="state" required onchange="onGovernorateChange(this.value)">
                  <option value="">{{ __('Select Governorate') }}</option>
                  <option value="Cairo">{{ __('Cairo') }}</option>
                  <option value="Giza">{{ __('Giza') }}</option>
                  <option value="Alexandria">{{ __('Alexandria') }}</option>
                  <option value="Sharm El Sheikh">{{ __('Sharm El Sheikh') }}</option>
                  <option value="Luxor">{{ __('Luxor') }}</option>
                  <option value="Aswan">{{ __('Aswan') }}</option>
                  <option value="Port Said">Port Said</option>
                  <option value="Suez">Suez</option>
                  <option value="Ismailia">Ismailia</option>
                  <option value="Fayoum">Fayoum</option>
                  <option value="Beni Suef">Beni Suef</option>
                  <option value="Minya">Minya</option>
                  <option value="Asyut">Asyut</option>
                  <option value="Sohag">Sohag</option>
                  <option value="Qena">Qena</option>
                  <option value="Hurghada">Hurghada</option>
                  <option value="Damanhur">Damanhur</option>
                  <option value="Zagazig">Zagazig</option>
                  <option value="Mansoura">Mansoura</option>
                  <option value="Tanta">Tanta</option>
                  <option value="Kafr El Sheikh">Kafr El Sheikh</option>
                  <option value="Arish">Arish</option>
                  <option value="Mallawi">Mallawi</option>
                </select>
              </div>
              <div class="field">
                <label>{{ __('Postal Code') }}</label>
                <input type="text" name="postal_code" placeholder="11511" required>
              </div>
              <div class="field">
                <label>{{ __('Country') }}</label>
                <select name="country" required>
                  <option>{{ __('Egypt') }}</option>
                </select>
              </div>
              <div class="field full">
                <label>{{ __('Delivery Notes (optional)') }}</label>
                <input type="text" name="notes" placeholder="{{ __('Floor, landmark, or special instructions...') }}">
              </div>
            </div>
          </div>

          <!-- Delivery Method -->
          <div class="form-block">
            <div class="block-title">
              <svg viewBox="0 0 24 24" stroke-width="1.5">
                <rect x="1" y="3" width="15" height="13" rx="1" />
                <path d="M16 8h4l3 5v3h-7V8z" />
                <circle cx="5.5" cy="18.5" r="2.5" />
                <circle cx="18.5" cy="18.5" r="2.5" />
              </svg>
              {{ __('Delivery Method') }}
            </div>
            <div class="delivery-opts">
              <div class="delivery-opt selected" id="opt-standard" data-base="0">
                <div class="delivery-opt-left">
                  <input type="radio" name="delivery_method" value="standard" checked>
                  <div class="delivery-opt-info">
                    <div class="opt-name">{{ __('Standard Delivery') }}</div>
                    <div class="opt-desc">{{ __('Estimated 5–7 business days · White glove assembly included') }}</div>
                  </div>
                </div>
                <div class="opt-price free" id="opt-standard-price">{{ __('Free') }}</div>
              </div>
              <div class="delivery-opt" id="opt-express" data-base="299">
                <div class="delivery-opt-left">
                  <input type="radio" name="delivery_method" value="express">
                  <div class="delivery-opt-info">
                    <div class="opt-name">{{ __('Express Delivery') }}</div>
                    <div class="opt-desc">{{ __('Estimated 2–3 business days · Priority handling') }}</div>
                  </div>
                </div>
                <div class="opt-price" id="opt-express-price">{{ __('EGP 299') }}</div>
              </div>
              <div class="delivery-opt" id="opt-scheduled" data-base="149">
                <div class="delivery-opt-left">
                  <input type="radio" name="delivery_method" value="scheduled">
                  <div class="delivery-opt-info">
                    <div class="opt-name">{{ __('Scheduled Delivery') }}</div>
                    <div class="opt-desc">{{ __('Choose your preferred date & time slot') }}</div>
                  </div>
                </div>
                <div class="opt-price" id="opt-scheduled-price">{{ __('EGP 149') }}</div>
              </div>
            </div>
          </div>

        </div>
        <!-- END STEP 1 -->

        <!-- STEP 2: Payment -->
        <div id="checkout-step-2" style="display:none">

          <div class="form-block">
            <div class="block-title">
              <svg viewBox="0 0 24 24" stroke-width="1.5">
                <rect x="1" y="4" width="22" height="16" rx="2" />
                <line x1="1" y1="10" x2="23" y2="10" />
              </svg>
              {{ __('Payment Method') }}
            </div>
            <div class="payment-info-box" style="margin-bottom:18px">
              <div class="pay-tab active" style="border-radius:8px; width:fit-content; padding:10px 20px">{{ __('Cash on Delivery') }}
              </div>
            </div>


            <div id="pay-cod" class="alt-pay show">
              <strong style="color:#2C1F14">{{ __('Cash on Delivery') }}</strong><br>
              {{ __('Pay in cash when your order arrives. Please have the exact amount ready.') }}<br>
              {{ __('Available on orders up to') }} <strong>{{ __('EGP 30,000') }}</strong>.
            </div>

            <div id="checkout-deposit-info" class="deposit-info-box" style="margin-top:16px;background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:16px 18px;display:none;">
              <div style="font-size:13px;font-weight:700;color:#92400e;margin-bottom:6px;">{{ __('Deposit Required') }}</div>
              <div style="font-size:12px;color:#92400e;line-height:1.6;">
                {{ __('A deposit of') }} <strong><span id="step2-deposit-amt">EGP 0</span></strong> {{ __('is required to confirm your order.') }}<br>
                {{ __('The remaining balance') }} (<strong>EGP <span id="step2-balance-amt">0</span></strong>) {{ __('is paid on delivery.') }}
              </div>
            </div>
          </div>

          <button type="button" class="btn-outline"
            style="width:100%;margin-top:16px;padding:12px;cursor:pointer;background:#fff;border:1px solid #E0D8CE;border-radius:6px;color:#2C1F14;font-weight:600"
            onclick="backToShipping()">← {{ __('Back to Shipping') }}</button>

        </div>
        <!-- END STEP 2 -->

      </form>
    </div>

    <!-- ORDER SUMMARY SIDEBAR -->
    <div class="summary-box">
      <div class="sum-title">{{ __('Order Summary') }}</div>
      <div id="checkout-summary-items" class="sum-items"></div>

      <div class="coupon-row">
        <input type="text" id="checkout-coupon-input" placeholder="{{ __('Coupon code') }}">
        <button id="checkout-apply-coupon">{{ __('Apply') }}</button>
      </div>

      <div class="sum-row">
        <span class="k" id="co-subtotal-label">{{ __('Subtotal') }}</span>
        <span class="v" id="co-subtotal">EGP 0</span>
      </div>
      <div class="sum-row" id="co-discount-row" style="display:none">
        <span class="k">{{ __('Discount') }}</span>
        <span class="v discount" id="co-discount" style="color:#C0392B">-EGP 0</span>
      </div>
      <div class="sum-row">
        <span class="k">{{ __('Delivery Fee') }}</span>
        <span class="v free" id="co-delivery">{{ __('Free') }}</span>
      </div>
      <div class="sum-row" id="co-vat-row">
        <span class="k">{{ __('VAT (14%)') }}</span>
        <span class="v" id="co-vat">EGP 0</span>
      </div>
      <div class="sum-row" id="co-deposit-row">
        <span class="k">Deposit Required</span>
        <span class="v" id="co-deposit">EGP 0</span>
      </div>
      <div class="sum-total">
        <span>Total</span>
        <span id="co-total">EGP 0</span>
      </div>
      <div class="deposit-note" id="co-deposit-note" style="display:none;margin-top:8px;font-size:11px;color:#92400e;background:#fef3c7;border:1px solid #fde68a;border-radius:6px;padding:8px 10px;line-height:1.5;">
        A deposit of <strong id="co-deposit-note-amount">EGP 0</strong> is required to confirm your order. The remaining balance is paid on delivery.
      </div>

      <button type="button" class="btn-place" id="btn-place-order" onclick="handlePlaceOrder()">Continue to Payment
        →</button>
      <div class="secure-note">
        <svg viewBox="0 0 24 24" stroke-width="1.5">
          <rect x="3" y="11" width="18" height="11" rx="2" />
          <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </svg>
        Secure & encrypted checkout
      </div>
    </div>

  </div>

@endsection

@section('extra_js')
  <script>
    (function () {
      let currentStep = 1;
      let deliveryFee = 0;
      let governorateFeeData = null; // { fee, min_free }
      let baseGovernorateFee = 0; // stripped of any delivery option surcharge
      let selectedPayment = 'cod';
      let cartItems = [];
      let cartData = null;
      let depositRule = null; // { percentage, minimum_amount }
      const VAT_RATE = 0.14;

      // ── Governorate change → fetch delivery fee ─────────────────────
      window.onGovernorateChange = async function(governorate) {
        var el = document.querySelector('select[name="state"]');
        var state = governorate || (el ? el.value : '');
        if (!state) {
          deliveryFee = 0;
          governorateFeeData = null;
          updateDeliveryOptionsUI(0, null);
          updateFinalTotal();
          return;
        }
        try {
        var res = await API.get('/shipping/governorate-fees/active');
          var fees = res.fees || res.data || res || [];
          var match = fees.find(function(f) { return f.governorate_name.toLowerCase() === state.toLowerCase(); });
          if (match && match.is_active) {
            var rawFee = parseFloat(match.delivery_fee) || 0;
            deliveryFee = rawFee;
            baseGovernorateFee = rawFee; // store base without any option surcharge
            governorateFeeData = { fee: rawFee, min_free: parseFloat(match.min_free_delivery_order) || 0 };
          } else {
            deliveryFee = 0;
            baseGovernorateFee = 0;
            governorateFeeData = null;
          }
        } catch(e) {
          deliveryFee = 0;
          baseGovernorateFee = 0;
          governorateFeeData = null;
        }
        // Update all option prices using base fee, preserving the currently-selected option's surcharge
        updateDeliveryOptionsUI(deliveryFee, governorateFeeData, null);
        updateFinalTotal();
      };

      function updateDeliveryOptionsUI(baseFee, data, explicitSelectedFee) {
        var minFree = data ? (data.min_free || 0) : 0;
        // explicitSelectedFee: if provided, use it to derive the selected option's surcharge
        // Otherwise use the current global deliveryFee
        var selectedTotal = (explicitSelectedFee != null) ? explicitSelectedFee : deliveryFee;

        // Derive what the "base" (standard) fee is from the selected total
        // The selected total = baseFee + option_surcharge
        // option_surcharge = selectedTotal - baseFee (clamped to >= 0)
        var optionSurcharge = Math.max(0, selectedTotal - baseFee);
        var effectiveFee = baseFee;

        // Standard price = baseFee
        var stdPriceEl = document.getElementById('opt-standard-price');
        if (stdPriceEl) {
          if (effectiveFee === 0) {
            stdPriceEl.textContent = 'Free';
            stdPriceEl.className = 'opt-price free';
          } else {
            stdPriceEl.textContent = 'EGP ' + effectiveFee.toLocaleString();
            stdPriceEl.className = 'opt-price';
          }
        }
        // Express = baseFee + 299
        var expPriceEl = document.getElementById('opt-express-price');
        if (expPriceEl) {
          var expFee = baseFee + 299;
          expPriceEl.textContent = 'EGP ' + expFee.toLocaleString();
          expPriceEl.className = 'opt-price';
        }
        // Scheduled = baseFee + 149
        var schPriceEl = document.getElementById('opt-scheduled-price');
        if (schPriceEl) {
          var schFee = baseFee + 149;
          schPriceEl.textContent = 'EGP ' + schFee.toLocaleString();
          schPriceEl.className = 'opt-price';
        }

        // Re-select the correct option visually based on which one matches selectedTotal
        var optStandard = document.getElementById('opt-standard');
        var optExpress = document.getElementById('opt-express');
        var optScheduled = document.getElementById('opt-scheduled');
        [optStandard, optExpress, optScheduled].forEach(function(opt) {
          if (opt) opt.classList.remove('selected');
        });
        if (selectedTotal === baseFee && optStandard) {
          optStandard.classList.add('selected');
          optStandard.querySelector('input').checked = true;
          selectDelivery(optStandard, baseFee); // actually update deliveryFee + summary
        } else if (selectedTotal === baseFee + 299 && optExpress) {
          optExpress.classList.add('selected');
          optExpress.querySelector('input').checked = true;
          selectDelivery(optExpress, baseFee + 299);
        } else if (selectedTotal === baseFee + 149 && optScheduled) {
          optScheduled.classList.add('selected');
          optScheduled.querySelector('input').checked = true;
          selectDelivery(optScheduled, baseFee + 149);
        }

        // Update delivery summary
        var deliveryEl = document.getElementById('co-delivery');
        if (deliveryEl) {
          if (effectiveFee === 0) {
            deliveryEl.textContent = minFree > 0 ? 'Free (' + minFree + ' EGP+)' : 'Free';
            deliveryEl.className = 'v free';
          } else {
            deliveryEl.textContent = 'EGP ' + effectiveFee.toLocaleString();
            deliveryEl.className = 'v';
          }
        }
      }

      // ── Init ─────────────────────────────────────────────────────
      (async function init() {
        Cart.updateBadge();

        const wrap = document.getElementById('checkout-wrap');
        if (wrap) wrap.classList.add('step-1-active');

        // Always reset to step 1 on page load
        currentStep = 1;
        showStep(1);

        // Always fetch cart from API
        try {
          const res = await API.get('/cart');
          cartData = res.cart;
          cartItems = cartData?.items || [];
        } catch (e) {
          cartItems = [];
        }

        // Fetch active deposit rule
        try {
          const drRes = await API.get('/admin/deposit-rules?is_active=1');
          const rules = drRes.data || drRes || [];
          depositRule = rules.find(function(r) { return r.is_active; }) || null;
          // Fallback: if none active, grab first rule
          if (!depositRule && rules.length) depositRule = rules[0];
        } catch (e) {
          depositRule = null;
        }

        if (cartItems.length === 0) {
          location.href = '/cart';
          return;
        }

        // Auto-fill if logged in
        if (Auth.token()) {
          loadUserAddresses();
        } else {
          // Guest: default to Cairo so they see a real delivery fee
          var stateSelect = document.querySelector('[name="state"]');
          if (stateSelect) {
            stateSelect.value = 'Cairo';
            onGovernorateChange('Cairo');
          }
        }

        // Attach click listeners to delivery options (cannot use inline onclick because
        // deliveryFee is a local variable — inline handlers run in global scope)
        document.querySelectorAll('.delivery-opt').forEach(function(opt) {
          opt.addEventListener('click', function() {
            var surcharge = parseInt(opt.getAttribute('data-base')) || 0;
            // baseGovernorateFee is the raw governorate fee (no surcharge baked in)
            selectDelivery(opt, baseGovernorateFee + surcharge);
          });
        });

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

            // Trigger delivery fee fetch for the auto-filled governorate
            var stateVal = addr.state || addr.governorate;
            if (stateVal) onGovernorateChange(stateVal);
          }
        } catch (e) { }
      }

      // ── Step visibility helper ───────────────────────────────────
      function showStep(step) {
        document.getElementById('checkout-step-1').style.display = step === 1 ? 'block' : 'none';
        document.getElementById('checkout-step-2').style.display = step === 2 ? 'block' : 'none';
        const btn = document.getElementById('btn-place-order');
        if (btn) btn.textContent = step === 1 ? 'Continue to Payment →' : 'Place Order →';
        const wrap = document.getElementById('checkout-wrap');
        if (wrap) {
          wrap.classList.remove('step-1-active', 'step-2-active');
          wrap.classList.add(step === 1 ? 'step-1-active' : 'step-2-active');
        }
        if (step === 2) {
          updateStep2DepositBox();
        }
      }

      function updateStep2DepositBox() {
        var baseTotal = Math.max(0,
          cartItems.reduce((s, i) => s + (parseFloat(i.price) || 0) * (parseInt(i.quantity) || 1), 0)
          - (parseFloat(cartData?.discount) || 0)
          + deliveryFee
        );
        var vatAmount = baseTotal * VAT_RATE;
        var grandTotal = baseTotal + vatAmount;
        var depositAmount = 0;
        if (depositRule) {
          var pct = parseFloat(depositRule.percentage) || 0;
          var minAmt = parseFloat(depositRule.minimum_amount) || 0;
          depositAmount = Math.max(minAmt, (baseTotal * pct) / 100);
          depositAmount = Math.round(depositAmount * 100) / 100;
        }
        var balanceDue = Math.max(0, grandTotal - depositAmount);

        var box = document.getElementById('checkout-deposit-info');
        var depositAmtEl = document.getElementById('step2-deposit-amt');
        var balanceAmtEl = document.getElementById('step2-balance-amt');
        if (box) {
          if (depositAmount > 0) {
            box.style.display = 'block';
            if (depositAmtEl) depositAmtEl.textContent = 'EGP ' + depositAmount.toLocaleString();
            if (balanceAmtEl) balanceAmtEl.textContent = balanceDue.toLocaleString();
          } else {
            box.style.display = 'none';
          }
        }
      }

      // ── Place order / advance step ──────────────────────────────
      window.handlePlaceOrder = async function () {
        if (currentStep === 1) {
          const form = document.getElementById('checkout-form');
          if (!form.checkValidity()) {
            form.reportValidity();
            return;
          }
          // Re-fetch cart before advancing (handles any expiry)
          try {
            const res = await API.get('/cart');
            cartItems = res.cart?.items || [];
          } catch (e) {
            cartItems = [];
          }
          if (cartItems.length === 0) {
            showToast('Your cart is empty. Please add items before checkout.', 'error');
            location.href = '/cart';
            return;
          }
          currentStep = 2;
          showStep(2);
          window.scrollTo(0, 0);
        } else {
          // Re-fetch cart one more time before submitting
          try {
            const res = await API.get('/cart');
            cartItems = res.cart?.items || [];
          } catch (e) {
            cartItems = [];
          }
          if (cartItems.length === 0) {
            showToast('Your cart is empty. Please add items before checkout.', 'error');
            location.href = '/cart';
            return;
          }
          await submitOrder();
        }
      };

      window.backToShipping = function () {
        currentStep = 1;
        showStep(1);
        window.scrollTo(0, 0);
      };

      // ── Delivery selection ─────────────────────────────────────
      window.selectDelivery = function (el, fee) {
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

      // ── Submit order ───────────────────────────────────────────
      async function submitOrder() {
        const form = document.getElementById('checkout-form');
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        // Map UI payment method to API payment_method
        const paymentMethodMap = { card: 'card', fawry: 'wallet', cod: 'cod' };
        const payment_method = paymentMethodMap[selectedPayment] || 'cod';

        try {
          const res = await API.post('/orders', {
            shipping_address: {
              first_name: payload.first_name,
              last_name: payload.last_name,
              email: payload.email,
              phone: payload.phone,
              address_line_1: payload.address_line_1,
              address_line_2: payload.address_line_2 || '',
              city: payload.city,
              state: payload.state || '',
              governorate: payload.state || '', // use state as governorate
              postal_code: payload.postal_code,
              country: payload.country || 'Egypt',
            },
            payment_method: payment_method,
            notes: payload.notes || '',
          });

          const order = res.order;
          const orderNumber = order?.order_number || order?.id || 'N/A';
          const vatAmount = parseFloat(order?.vat_amount) || 0;
          const depositAmount = parseFloat(order?.deposit_amount) || 0;
          const grandTotal = parseFloat(order?.total) || 0;
          const serverSubtotal = parseFloat(order?.subtotal) || 0;
          const serverDiscount = parseFloat(order?.discount) || 0;
          const serverDelivery = parseFloat(order?.delivery_fee) || 0;
          const balanceDue = Math.max(0, grandTotal - depositAmount);

          let message = `*New Order: #${orderNumber}*\n\n`;
          message += `*Customer Details:*\n`;
          message += `- Name: ${payload.first_name} ${payload.last_name}\n`;
          message += `- Phone: ${payload.phone}\n`;
          message += `- Email: ${payload.email}\n`;
          message += `- Address: ${payload.address_line_1}, ${payload.city}, ${payload.state}\n`;
          if (payload.notes) message += `- Notes: ${payload.notes}\n`;
          message += `\n*Order Items:*\n`;

          cartItems.forEach(item => {
            message += `- ${item.name} (x${item.quantity}) - EGP ${(parseFloat(item.price) * parseInt(item.quantity)).toLocaleString()}\n`;
          });

          message += `\n*Order Summary:*\n`;
          message += `- Subtotal: EGP ${serverSubtotal.toLocaleString()}\n`;
          if (serverDiscount > 0) message += `- Discount: -EGP ${serverDiscount.toLocaleString()}\n`;
          message += `- Delivery: ${serverDelivery === 0 ? 'Free' : 'EGP ' + serverDelivery.toLocaleString()}\n`;
          if (vatAmount > 0) message += `- VAT (14%): EGP ${vatAmount.toLocaleString()}\n`;
          if (depositAmount > 0) message += `- Deposit Required: EGP ${depositAmount.toLocaleString()}\n`;
          message += `*Total: EGP ${grandTotal.toLocaleString()}*\n`;
          if (depositAmount > 0) message += `*Balance Due on Delivery: EGP ${balanceDue.toLocaleString()}*\n\n`;
          message += `Payment Method: Cash on Delivery`;

          const encodedMessage = encodeURIComponent(message);
          const whatsappUrl = `https://wa.me/201037743273?text=${encodedMessage}`;

          Cart.clear();

          // Open WhatsApp in a new tab
          window.open(whatsappUrl, '_blank');

          // Redirect to confirmation page
          location.href = '/orders/confirmation/' + (order?.id || order?.order_number || '');

        } catch (e) {
          showToast(e.data?.message || 'Checkout failed. Please try again.', 'error');
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
        const discount = parseFloat(cartData?.discount || 0);

        // Apply free delivery threshold if set; otherwise use the selected deliveryFee
        var minFree = governorateFeeData ? (governorateFeeData.min_free || 0) : 0;
        var effectiveFee = (minFree > 0 && subtotal >= minFree) ? 0 : deliveryFee;

        // Base total = subtotal - discount + delivery
        var baseTotal = Math.max(0, subtotal - discount + effectiveFee);

        // VAT = 14% of base total
        var vatAmount = baseTotal * VAT_RATE;

        // Grand total = base + VAT
        var grandTotal = baseTotal + vatAmount;

        // Deposit = percentage of base total
        var depositAmount = 0;
        if (depositRule) {
          var pct = parseFloat(depositRule.percentage) || 0;
          var minAmt = parseFloat(depositRule.minimum_amount) || 0;
          depositAmount = Math.max(minAmt, (baseTotal * pct) / 100);
          depositAmount = Math.round(depositAmount * 100) / 100;
        }

        const subtotalEl = document.getElementById('co-subtotal');
        const subtotalLabel = document.getElementById('co-subtotal-label');
        const discountRow = document.getElementById('co-discount-row');
        const discountEl = document.getElementById('co-discount');
        const vatRow = document.getElementById('co-vat-row');
        const vatEl = document.getElementById('co-vat');
        const depositRow = document.getElementById('co-deposit-row');
        const depositEl = document.getElementById('co-deposit');
        const totalEl = document.getElementById('co-total');
        const depositNote = document.getElementById('co-deposit-note');
        const depositNoteAmt = document.getElementById('co-deposit-note-amount');

        if (subtotalEl) subtotalEl.textContent = 'EGP ' + subtotal.toLocaleString();
        if (subtotalLabel) subtotalLabel.textContent = 'Subtotal' + (cartItems.length ? ` (${cartItems.length} item${cartItems.length > 1 ? 's' : ''})` : '');

        if (discountRow) {
          if (discount > 0) {
            discountRow.style.display = 'flex';
            discountEl.textContent = '-EGP ' + discount.toLocaleString();
          } else {
            discountRow.style.display = 'none';
          }
        }

        // VAT row
        if (vatRow) {
          if (vatAmount > 0) {
            vatRow.style.display = 'flex';
            if (vatEl) vatEl.textContent = 'EGP ' + vatAmount.toLocaleString();
          } else {
            vatRow.style.display = 'none';
          }
        }

        // Deposit row
        if (depositRow) {
          if (depositAmount > 0) {
            depositRow.style.display = 'flex';
            if (depositEl) depositEl.textContent = 'EGP ' + depositAmount.toLocaleString();
          } else {
            depositRow.style.display = 'none';
          }
        }

        if (totalEl) totalEl.textContent = 'EGP ' + grandTotal.toLocaleString();

        // Deposit note
        if (depositNote) {
          if (depositAmount > 0) {
            depositNote.style.display = 'block';
            if (depositNoteAmt) depositNoteAmt.textContent = 'EGP ' + depositAmount.toLocaleString();
          } else {
            depositNote.style.display = 'none';
          }
        }

        // Update delivery fee in summary
        var deliveryEl = document.getElementById('co-delivery');
        var minFree = governorateFeeData ? (governorateFeeData.min_free || 0) : 0;
        if (deliveryEl) {
          if (effectiveFee === 0) {
            deliveryEl.textContent = minFree > 0 ? 'Free (' + minFree + ' EGP+)' : 'Free';
            deliveryEl.className = 'v free';
          } else {
            deliveryEl.textContent = 'EGP ' + effectiveFee.toLocaleString();
            deliveryEl.className = 'v';
          }
        }
      }

      // ── Coupon on checkout ─────────────────────────────────────
      document.getElementById('checkout-apply-coupon').onclick = async function () {
        const code = document.getElementById('checkout-coupon-input').value.trim();
        if (!code) return;
        try {
          const res = await API.post('/cart/coupon', { code });
          showToast('Coupon applied: ' + (res.cart?.coupon?.code || code));

          // Refresh cart data
          const cartRes = await API.get('/cart');
          cartData = cartRes.cart;
          cartItems = cartData?.items || [];
          renderCheckoutSummary();
        } catch (e) {
          showToast(e.data?.message || 'Invalid coupon code', 'error');
        }
      };
    })();
  </script>
@endsection