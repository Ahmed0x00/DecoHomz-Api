@extends('layouts.app')

@section('title', 'Checkout — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/checkout.css">
@endsection

@section('content')

<div class="checkout-page">
  <div class="checkout-header animate-fade-down">
    <h1>{{ __('Secure Checkout') }}</h1>
    <p class="checkout-subtitle">{{ __('Complete each step to place your order') }}</p>
  </div>

  <div class="checkout-grid" id="checkout-container">
    <div style="grid-column:1/-1; padding:100px 0; text-align:center;">
      <div class="spinner spinner-lg"></div>
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
let checkoutState = {
  cart: null,
  governorates: [],
  addresses: [],
  selectedGovernorate: '',
  selectedAddressId: null,
  deliveryFee: 0,
  deliveryFeeStatus: 'unset',
  freeDeliveryThreshold: 0,
  couponApplied: false,
  isGuest: !Auth.token(),
  currentStep: 1,
  paymentMethod: 'cod',
  formSnapshot: {
    cardName: '',
    cardNumber: '',
    cardExpiry: '',
    cardCvv: '',
    orderNotes: '',
  },
};

const CHECKOUT_STEPS = [
  { id: 1, label: "{{ __('Shipping') }}" },
  { id: 2, label: "{{ __('Payment') }}" },
  { id: 3, label: "{{ __('Review') }}" },
];

var SVG = ' fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"';

function esc(str) {
  var div = document.createElement('div');
  div.appendChild(document.createTextNode(str || ''));
  return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
  loadCheckout();
});

async function loadCheckout() {
  const container = document.getElementById('checkout-container');

  try {
    const cartRes = await API.get('/cart');
    checkoutState.cart = cartRes.cart;

    if (!checkoutState.cart || !checkoutState.cart.items || checkoutState.cart.items.length === 0) {
      container.innerHTML =
        '<div class="cart-empty" style="grid-column:1/-1">' +
          '<svg class="icon-stroke" viewBox="0 0 24 24"' + SVG + ' stroke-width="1.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>' +
          '<h2>' + "{{ __('Your cart is empty') }}" + '</h2>' +
          '<p>' + "{{ __('You need items in your cart to checkout.') }}" + '</p>' +
          '<a href="/shop" class="btn-dark" style="display:inline-block;padding:14px 32px">' + "{{ __('Start Shopping') }}" + '</a>' +
        '</div>';
      return;
    }

    try {
      const govRes = await API.get('/shipping/governorate-fees/active');
      checkoutState.governorates = govRes.fees || govRes.data || govRes || [];
    } catch (e) {
      checkoutState.governorates = [];
    }

    if (Auth.token()) {
      try {
        const addrRes = await API.get('/addresses');
        checkoutState.addresses = addrRes.data?.addresses || addrRes.addresses || addrRes.data || [];
      } catch (e) {
        checkoutState.addresses = [];
      }
    }

    renderCheckout();
  } catch (e) {
    container.innerHTML = '<div class="shop-empty"><p class="text-error">' + "{{ __('Failed to load checkout details.') }}" + '</p></div>';
  }
}

function renderStepNav() {
  var html = '<div class="checkout-steps-nav animate-fade-up">';
  CHECKOUT_STEPS.forEach(function(step) {
    var cls = 'checkout-step-pill';
    if (step.id === checkoutState.currentStep) cls += ' active';
    else if (step.id < checkoutState.currentStep) cls += ' done';
    html += '<div class="' + cls + '" data-step="' + step.id + '">' +
              '<span class="step-num">' + (step.id < checkoutState.currentStep ? '✓' : step.id) + '</span>' +
              '<span class="step-label">' + step.label + '</span>' +
            '</div>';
    if (step.id < CHECKOUT_STEPS.length) {
      html += '<div class="checkout-step-line' + (step.id < checkoutState.currentStep ? ' done' : '') + '"></div>';
    }
  });
  html += '</div>';
  return html;
}

function renderShippingStep() {
  var hasSaved = Auth.token() && checkoutState.addresses.length > 0;
  var html = '<div class="checkout-step-panel' + (checkoutState.currentStep === 1 ? ' active' : '') + '" id="step-1" data-step="1">' +
               '<div class="checkout-section">' +
                 '<div class="checkout-section-header">' +
                   '<div class="step-circle">1</div>' +
                   '<div>' +
                     '<h2>' + "{{ __('Shipping Address') }}" + '</h2>' +
                     '<p class="section-desc">' + "{{ __('Where should we deliver your order?') }}" + '</p>' +
                   '</div>' +
                 '</div>';

  if (hasSaved) {
    html += '<div class="address-grid" id="saved-addresses">';
    checkoutState.addresses.forEach(function(addr) {
      var isSelected = checkoutState.selectedAddressId == addr.id;
      html += '<div class="address-card' + (isSelected ? ' selected' : '') + '" data-id="' + addr.id + '" role="button" tabindex="0">' +
                '<h4>' + esc(addr.first_name || '') + ' ' + esc(addr.last_name || '') + '</h4>' +
                '<p>' + esc(addr.address_line_1 || addr.address || '') +
                  (addr.address_line_2 ? '<br>' + esc(addr.address_line_2) : '') +
                  '<br>' + esc(addr.city || '') + ', ' + esc(addr.governorate || addr.state || '') +
                  '<br>' + esc(addr.phone || '') + '</p>' +
              '</div>';
    });
    html += '<div class="address-card address-card-new" id="btn-new-address" role="button" tabindex="0">' +
              '<div class="address-card-new-inner">+ ' + "{{ __('New Address') }}" + '</div>' +
            '</div>';
    html += '</div>';
  }

  html += '<div id="address-delivery-hint" class="address-delivery-hint" style="display:none"></div>';

  var formDisplay = hasSaved && checkoutState.selectedAddressId ? 'none' : 'block';
  html += '<div id="new-address-form" style="display:' + formDisplay + '">' +
            '<div class="form-grid">' +
              '<div class="field"><label>' + "{{ __('First Name') }}" + ' *</label><input type="text" id="ship-first-name" required></div>' +
              '<div class="field"><label>' + "{{ __('Last Name') }}" + ' *</label><input type="text" id="ship-last-name" required></div>' +
              '<div class="field full"><label>' + "{{ __('Email') }}" + ' *</label><input type="email" id="ship-email" required></div>' +
              '<div class="field full"><label>' + "{{ __('Street Address') }}" + ' *</label><input type="text" id="ship-address" required></div>' +
              '<div class="field full"><label>' + "{{ __('Address Line 2') }}" + '</label><input type="text" id="ship-address2"></div>' +
              '<div class="field"><label>' + "{{ __('City') }}" + ' *</label><input type="text" id="ship-city" required></div>' +
              '<div class="field"><label>' + "{{ __('Governorate') }}" + ' *</label>' +
                '<select id="ship-governorate">' +
                  '<option value="">' + "{{ __('Select Governorate') }}" + '</option>';

  checkoutState.governorates.forEach(function(gov) {
    html += '<option value="' + esc(gov.governorate_name) + '" data-fee="' + (gov.delivery_fee || 0) + '" data-free="' + (gov.min_free_delivery_order || 0) + '">' +
              esc(gov.governorate_name) + ' — EGP ' + (gov.delivery_fee || 0) +
            '</option>';
  });

  html +=       '</select></div>' +
              '<div class="field"><label>' + "{{ __('Postal Code') }}" + ' *</label><input type="text" id="ship-postal" required></div>' +
              '<div class="field"><label>' + "{{ __('Phone') }}" + ' *</label><input type="tel" id="ship-phone" required></div>' +
            '</div>' +
          '</div>';

  html += '<div id="step-1-error" class="step-error" style="display:none"></div>' +
          '<div class="step-actions">' +
            '<a href="/cart" class="btn-outline step-back-link">' + "{{ __('Back to Cart') }}" + '</a>' +
            '<button type="button" class="btn-dark step-continue" id="btn-step-1-continue">' + "{{ __('Continue to Payment') }}" + '</button>' +
          '</div>';

  html += '</div></div>';
  return html;
}

function captureFormState() {
  var s = checkoutState.formSnapshot;
  var el;
  el = document.getElementById('card-name');       if (el) s.cardName = el.value;
  el = document.getElementById('card-number');     if (el) s.cardNumber = el.value;
  el = document.getElementById('card-expiry');     if (el) s.cardExpiry = el.value;
  el = document.getElementById('card-cvv');        if (el) s.cardCvv = el.value;
  el = document.getElementById('order-notes');    if (el) s.orderNotes = el.value;
}

function renderPaymentStep() {
  var isCard = checkoutState.paymentMethod === 'card';
  var snap = checkoutState.formSnapshot;
  var html = '<div class="checkout-step-panel' + (checkoutState.currentStep === 2 ? ' active' : '') + '" id="step-2" data-step="2">' +
               '<div class="checkout-section">' +
                 '<div class="checkout-section-header">' +
                   '<div class="step-circle">2</div>' +
                   '<div>' +
                     '<h2>' + "{{ __('Payment Method') }}" + '</h2>' +
                     '<p class="section-desc">' + "{{ __('Choose how you would like to pay') }}" + '</p>' +
                   '</div>' +
                 '</div>' +
                 '<div class="payment-methods">' +
                   '<label class="pay-method' + (checkoutState.paymentMethod === 'cod' ? ' selected' : '') + '" data-method="cod">' +
                     '<input type="radio" name="payment" value="cod"' + (checkoutState.paymentMethod === 'cod' ? ' checked' : '') + '>' +
                     '<div class="pay-icon">' +
                       '<svg viewBox="0 0 24 24"' + SVG + ' stroke-width="2"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>' +
                     '</div>' +
                     '<div class="pay-method-info">' +
                       '<div class="pay-method-name">' + "{{ __('Cash on Delivery') }}" + '</div>' +
                       '<div class="pay-method-desc">' + "{{ __('Pay when your order arrives') }}" + '</div>' +
                     '</div>' +
                   '</label>' +
                   '<label class="pay-method' + (isCard ? ' selected' : '') + '" data-method="card">' +
                     '<input type="radio" name="payment" value="card"' + (isCard ? ' checked' : '') + '>' +
                     '<div class="pay-icon">' +
                       '<svg viewBox="0 0 24 24"' + SVG + ' stroke-width="2"><rect width="22" height="16" x="1" y="4" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>' +
                     '</div>' +
                     '<div class="pay-method-info">' +
                       '<div class="pay-method-name">' + "{{ __('Credit / Debit Card') }}" + '</div>' +
                       '<div class="pay-method-desc">' + "{{ __('Visa, Mastercard, and more') }}" + '</div>' +
                     '</div>' +
                   '</label>' +
                 '</div>' +
                 '<div id="card-payment-details" class="card-payment-panel" style="display:' + (isCard ? 'block' : 'none') + '">' +
                   '<div class="card-panel-header">' +
                     '<svg viewBox="0 0 24 24"' + SVG + ' stroke-width="2"><rect width="22" height="16" x="1" y="4" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>' +
                     '<span>' + "{{ __('Enter your card details') }}" + '</span>' +
                   '</div>' +
                   '<div class="form-grid">' +
                     '<div class="field full"><label>' + "{{ __('Cardholder Name') }}" + ' *</label><input type="text" id="card-name" value="' + esc(snap.cardName) + '" placeholder="John Doe" autocomplete="cc-name"></div>' +
                     '<div class="field full"><label>' + "{{ __('Card Number') }}" + ' *</label><input type="text" id="card-number" value="' + esc(snap.cardNumber) + '" placeholder="1234 5678 9012 3456" maxlength="19" inputmode="numeric" autocomplete="cc-number"></div>' +
                     '<div class="field"><label>' + "{{ __('Expiry Date') }}" + ' *</label><input type="text" id="card-expiry" value="' + esc(snap.cardExpiry) + '" placeholder="MM/YY" maxlength="5" inputmode="numeric" autocomplete="cc-exp"></div>' +
                     '<div class="field"><label>' + "{{ __('CVV') }}" + ' *</label><input type="text" id="card-cvv" value="' + esc(snap.cardCvv) + '" placeholder="123" maxlength="4" inputmode="numeric" autocomplete="cc-csc"></div>' +
                   '</div>' +
                   '<p class="card-secure-note">' +
                     '<svg viewBox="0 0 24 24"' + SVG + ' stroke-width="2" style="stroke:var(--color-success)"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>' +
                     "{{ __('Your payment is encrypted and secure. A deposit may be charged upon order confirmation.') }}" +
                   '</p>' +
                 '</div>' +
                 '<div id="step-2-error" class="step-error" style="display:none"></div>' +
                 '<div class="step-actions">' +
                   '<button type="button" class="btn-outline step-back" id="btn-step-2-back">' + "{{ __('Back') }}" + '</button>' +
                   '<button type="button" class="btn-dark step-continue" id="btn-step-2-continue">' + "{{ __('Continue to Review') }}" + '</button>' +
                 '</div>' +
               '</div>' +
             '</div>';
  return html;
}

function renderReviewStep(cart, discount) {
  var snap = checkoutState.formSnapshot;
  var html = '<div class="checkout-step-panel' + (checkoutState.currentStep === 3 ? ' active' : '') + '" id="step-3" data-step="3">' +
               '<div class="checkout-section">' +
                 '<div class="checkout-section-header">' +
                   '<div class="step-circle">3</div>' +
                   '<div>' +
                     '<h2>' + "{{ __('Review & Confirm') }}" + '</h2>' +
                     '<p class="section-desc">' + "{{ __('Add a promo code or notes, then place your order') }}" + '</p>' +
                   '</div>' +
                 '</div>' +
                 '<div class="review-summary-box" id="review-shipping-summary"></div>' +
                 '<div class="review-summary-box" id="review-payment-summary"></div>' +
                 '<div class="checkout-subsection">' +
                   '<h3>' + "{{ __('Promo Code') }}" + '</h3>' +
                   '<div class="promo-box">' +
                     '<input type="text" id="promo-input" placeholder="' + "{{ __('Enter promo code') }}" + '"' +
                       (cart.coupon ? ' value="' + esc(cart.coupon.code) + '" disabled' : '') + '>' +
                     (cart.coupon
                       ? '<button type="button" class="btn-outline" id="btn-remove-coupon">' + "{{ __('Remove') }}" + '</button>'
                       : '<button type="button" class="btn-dark" id="btn-apply-coupon">' + "{{ __('Apply') }}" + '</button>') +
                   '</div>' +
                   (cart.coupon ? '<div class="promo-applied">✓ ' + "{{ __('Coupon applied!') }}" + ' (-EGP ' + discount.toLocaleString() + ')</div>' : '') +
                 '</div>' +
                 '<div class="checkout-subsection">' +
                   '<h3>' + "{{ __('Order Notes') }}" + '</h3>' +
                   '<textarea id="order-notes" placeholder="' + "{{ __('Any special instructions for your order...') }}" + '">' + esc(snap.orderNotes) + '</textarea>' +
                 '</div>' +
                 '<div id="step-3-error" class="step-error" style="display:none"></div>' +
                 '<div class="step-actions">' +
                   '<button type="button" class="btn-outline step-back" id="btn-step-3-back">' + "{{ __('Back') }}" + '</button>' +
                 '</div>' +
               '</div>' +
             '</div>';
  return html;
}

function renderCheckout() {
  const container = document.getElementById('checkout-container');
  const cart = checkoutState.cart;
  const subtotal = parseFloat(cart.subtotal) || 0;
  const discount = parseFloat(cart.discount) || 0;
  const savedStep = checkoutState.currentStep;

  captureFormState();

  if (checkoutState.addresses.length > 0 && checkoutState.selectedAddressId == null) {
    checkoutState.selectedAddressId = checkoutState.addresses[0].id;
  }

  let html = renderStepNav();
  html += '<div class="checkout-main animate-fade-up">';
  html += renderShippingStep();
  html += renderPaymentStep();
  html += renderReviewStep(cart, discount);
  html += '</div>';

  html += '<div class="checkout-summary-wrap animate-fade-up stagger-2">';
  html += '<div class="checkout-summary">' +
            '<h2 class="summary-heading">' + "{{ __('Order Summary') }}" + '</h2>' +
            '<div class="sum-items">';

  cart.items.forEach(function(item) {
    var imgSrc = item.product?.image || '/img/placeholder.svg';
    html += '<div class="sum-item">' +
              '<div class="sum-item-img">' +
                '<img src="' + imgSrc + '" alt="' + esc(item.name) + '" onerror="this.src=\'/img/placeholder.svg\'">' +
                '<div class="sum-item-qty">' + item.quantity + '</div>' +
              '</div>' +
              '<div class="sum-item-info">' +
                '<div class="sum-item-name">' + esc(item.name) + '</div>' +
                '<div class="sum-item-meta">' + (item.variant || 'Standard') + '</div>' +
              '</div>' +
              '<div class="sum-item-price">EGP ' + (parseFloat(item.price) * parseInt(item.quantity)).toLocaleString() + '</div>' +
            '</div>';
  });

  html += '</div>' +
            '<div class="sum-row"><span class="k">' + "{{ __('Subtotal') }}" + '</span><span class="v">EGP ' + subtotal.toLocaleString() + '</span></div>';

  if (discount > 0) {
    html += '<div class="sum-row"><span class="k">' + "{{ __('Discount') }}" + '</span><span class="v sum-discount">-EGP ' + discount.toLocaleString() + '</span></div>';
  }

  html += '<div class="sum-row"><span class="k">' + "{{ __('Delivery') }}" + '</span><span class="v" id="summary-delivery">' + "{{ __('Select governorate') }}" + '</span></div>' +
          '<div class="sum-row total"><span>' + "{{ __('Total') }}" + '</span><span id="summary-total">EGP ' + Math.max(0, subtotal - discount).toLocaleString() + '</span></div>' +
          '<div id="checkout-error" class="checkout-error" style="display:none"></div>' +
          '<button class="btn-place-order" id="btn-place" style="display:' + (checkoutState.currentStep === 3 ? 'block' : 'none') + '">' + "{{ __('Place Order') }}" + '</button>' +
          '<p class="checkout-step-hint" id="checkout-step-hint" style="display:' + (checkoutState.currentStep < 3 ? 'block' : 'none') + '">' + "{{ __('Complete all steps to place your order') }}" + '</p>' +
          '<div class="checkout-secure-badge">' +
            '<svg viewBox="0 0 24 24"' + SVG + ' stroke-width="2" style="stroke:var(--color-success)"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>' +
            '<span>' + "{{ __('Secure encrypted checkout') }}" + '</span>' +
          '</div>' +
        '</div></div>';

  container.innerHTML = html;
  checkoutState.currentStep = savedStep;

  bindCheckoutEvents();

  if (checkoutState.addresses.length > 0 && checkoutState.selectedAddressId) {
    var addr = checkoutState.addresses.find(function(a) { return a.id == checkoutState.selectedAddressId; });
    if (addr) {
      updateDeliveryFeeFromGovernorate(getAddressGovernorate(addr));
    }
  } else if (checkoutState.selectedGovernorate) {
    updateDeliveryFeeFromGovernorate(checkoutState.selectedGovernorate);
  }

  if (Auth.token() && Auth.user()) {
    var emailInput = document.getElementById('ship-email');
    if (emailInput && Auth.user().email && !emailInput.value) emailInput.value = Auth.user().email;
  }

  updateStepUI();
  updateReviewSummaries();
}

function bindCheckoutEvents() {
  document.getElementById('btn-step-1-continue')?.addEventListener('click', function() {
    if (validateShippingStep()) goToStep(2);
  });

  document.getElementById('btn-step-2-back')?.addEventListener('click', function() { goToStep(1); });
  document.getElementById('btn-step-2-continue')?.addEventListener('click', function() {
    if (validatePaymentStep()) goToStep(3);
  });

  document.getElementById('btn-step-3-back')?.addEventListener('click', function() { goToStep(2); });
  document.getElementById('btn-place')?.addEventListener('click', placeOrder);

  document.getElementById('btn-apply-coupon')?.addEventListener('click', applyCoupon);
  document.getElementById('btn-remove-coupon')?.addEventListener('click', removeCoupon);

  document.getElementById('ship-governorate')?.addEventListener('change', onGovernorateChange);

  document.querySelectorAll('.address-card[data-id]').forEach(function(card) {
    card.addEventListener('click', function() {
      selectSavedAddress(card, card.dataset.id);
    });
    card.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        selectSavedAddress(card, card.dataset.id);
      }
    });
  });

  document.getElementById('btn-new-address')?.addEventListener('click', showNewAddressForm);
  document.getElementById('btn-new-address')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      showNewAddressForm();
    }
  });

  document.querySelectorAll('.pay-method').forEach(function(el) {
    el.addEventListener('click', function() {
      selectPayment(el);
    });
  });

  document.querySelectorAll('input[name="payment"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
      var label = radio.closest('.pay-method');
      if (label) selectPayment(label);
    });
  });

  var cardNumber = document.getElementById('card-number');
  if (cardNumber) {
    cardNumber.addEventListener('input', function() {
      var v = cardNumber.value.replace(/\D/g, '').substring(0, 16);
      cardNumber.value = v.replace(/(.{4})/g, '$1 ').trim();
      checkoutState.formSnapshot.cardNumber = cardNumber.value;
    });
  }

  var cardExpiry = document.getElementById('card-expiry');
  if (cardExpiry) {
    cardExpiry.addEventListener('input', function() {
      var v = cardExpiry.value.replace(/\D/g, '').substring(0, 4);
      if (v.length >= 3) v = v.substring(0, 2) + '/' + v.substring(2);
      cardExpiry.value = v;
      checkoutState.formSnapshot.cardExpiry = cardExpiry.value;
    });
  }

  var cardCvv = document.getElementById('card-cvv');
  if (cardCvv) {
    cardCvv.addEventListener('input', function() {
      cardCvv.value = cardCvv.value.replace(/\D/g, '').substring(0, 4);
      checkoutState.formSnapshot.cardCvv = cardCvv.value;
    });
  }

  var cardName = document.getElementById('card-name');
  if (cardName) {
    cardName.addEventListener('input', function() {
      checkoutState.formSnapshot.cardName = cardName.value;
    });
  }

  var orderNotes = document.getElementById('order-notes');
  if (orderNotes) {
    orderNotes.addEventListener('input', function() {
      checkoutState.formSnapshot.orderNotes = orderNotes.value;
    });
  }
}

function goToStep(step) {
  checkoutState.currentStep = step;
  updateStepUI();
  updateReviewSummaries();
  var panel = document.getElementById('step-' + step);
  if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function updateStepUI() {
  document.querySelectorAll('.checkout-step-panel').forEach(function(panel) {
    panel.classList.toggle('active', parseInt(panel.dataset.step, 10) === checkoutState.currentStep);
  });

  document.querySelectorAll('.checkout-step-pill').forEach(function(pill) {
    var step = parseInt(pill.dataset.step, 10);
    pill.classList.remove('active', 'done');
    if (step === checkoutState.currentStep) pill.classList.add('active');
    else if (step < checkoutState.currentStep) pill.classList.add('done');
    var num = pill.querySelector('.step-num');
    if (num) num.textContent = step < checkoutState.currentStep ? '✓' : step;
  });

  document.querySelectorAll('.checkout-step-line').forEach(function(line, i) {
    line.classList.toggle('done', i + 1 < checkoutState.currentStep);
  });

  var placeBtn = document.getElementById('btn-place');
  var hint = document.getElementById('checkout-step-hint');
  if (placeBtn) placeBtn.style.display = checkoutState.currentStep === 3 ? 'block' : 'none';
  if (hint) hint.style.display = checkoutState.currentStep < 3 ? 'block' : 'none';
}

function showStepError(stepId, message) {
  var el = document.getElementById('step-' + stepId + '-error');
  if (!el) return;
  el.textContent = message;
  el.style.display = message ? 'block' : 'none';
}

function validateShippingStep() {
  showStepError(1, '');

  if (checkoutState.selectedAddressId) {
    var addr = checkoutState.addresses.find(function(a) { return a.id == checkoutState.selectedAddressId; });
    if (!addr) {
      showStepError(1, "{{ __('Please select a valid shipping address.') }}");
      return false;
    }
    var gov = getAddressGovernorate(addr);
    if (!gov) {
      showStepError(1, "{{ __('Selected address is missing a governorate. Please edit it or use a new address.') }}");
      return false;
    }
    updateDeliveryFeeFromGovernorate(gov);
    return true;
  }

  var firstName = document.getElementById('ship-first-name')?.value?.trim();
  var lastName = document.getElementById('ship-last-name')?.value?.trim();
  var email = document.getElementById('ship-email')?.value?.trim();
  var address = document.getElementById('ship-address')?.value?.trim();
  var city = document.getElementById('ship-city')?.value?.trim();
  var governorate = document.getElementById('ship-governorate')?.value;
  var phone = document.getElementById('ship-phone')?.value?.trim();
  var postal = document.getElementById('ship-postal')?.value?.trim();

  if (!firstName || !lastName || !email || !address || !city || !governorate || !phone || !postal) {
    showStepError(1, "{{ __('Please fill in all required shipping fields.') }}");
    return false;
  }

  updateDeliveryFeeFromGovernorate(governorate);
  return true;
}

function validatePaymentStep() {
  showStepError(2, '');
  var paymentRadio = document.querySelector('input[name="payment"]:checked');
  checkoutState.paymentMethod = paymentRadio ? paymentRadio.value : 'cod';

  if (checkoutState.paymentMethod !== 'card') return true;

  var snap = checkoutState.formSnapshot;
  var name = document.getElementById('card-name')?.value?.trim() || snap.cardName?.trim();
  var number = (document.getElementById('card-number')?.value || snap.cardNumber || '').replace(/\s/g, '');
  var expiry = document.getElementById('card-expiry')?.value?.trim() || snap.cardExpiry?.trim();
  var cvv = document.getElementById('card-cvv')?.value?.trim() || snap.cardCvv?.trim();

  if (!name || !number || !expiry || !cvv) {
    showStepError(2, "{{ __('Please fill in all card details.') }}");
    return false;
  }
  if (number.length < 13 || number.length > 19) {
    showStepError(2, "{{ __('Please enter a valid card number.') }}");
    return false;
  }
  if (!/^\d{2}\/\d{2}$/.test(expiry)) {
    showStepError(2, "{{ __('Please enter expiry as MM/YY.') }}");
    return false;
  }
  if (cvv.length < 3) {
    showStepError(2, "{{ __('Please enter a valid CVV.') }}");
    return false;
  }
  return true;
}

function selectSavedAddress(el, addrId) {
  document.querySelectorAll('.address-card').forEach(function(c) { c.classList.remove('selected'); });
  el.classList.add('selected');
  document.getElementById('new-address-form').style.display = 'none';
  checkoutState.selectedAddressId = addrId;
  showStepError(1, '');

  var addr = checkoutState.addresses.find(function(a) { return a.id == addrId; });
  if (addr) {
    updateDeliveryFeeFromGovernorate(getAddressGovernorate(addr));
  }
}

function showNewAddressForm() {
  document.querySelectorAll('.address-card').forEach(function(c) { c.classList.remove('selected'); });
  var newCard = document.getElementById('btn-new-address');
  if (newCard) newCard.classList.add('selected');
  document.getElementById('new-address-form').style.display = 'block';
  checkoutState.selectedAddressId = null;
  showStepError(1, '');
}

function selectPayment(el) {
  document.querySelectorAll('.pay-method').forEach(function(c) { c.classList.remove('selected'); });
  el.classList.add('selected');
  var radio = el.querySelector('input[type="radio"]');
  if (radio) {
    radio.checked = true;
    checkoutState.paymentMethod = radio.value;
  }

  var cardPanel = document.getElementById('card-payment-details');
  if (cardPanel) {
    cardPanel.style.display = checkoutState.paymentMethod === 'card' ? 'block' : 'none';
  }
  showStepError(2, '');
}

function getAddressGovernorate(addr) {
  if (!addr) return '';
  return (addr.governorate || addr.state || '').trim();
}

function normalizeGovName(name) {
  return (name || '')
    .trim()
    .toLowerCase()
    .replace(/\s*governorate\s*/gi, '')
    .replace(/^محافظة\s*/, '')
    .replace(/\s+/g, ' ');
}

function findGovernorateFee(govName) {
  if (!govName || !checkoutState.governorates.length) return null;
  var normalized = normalizeGovName(govName);
  if (!normalized) return null;

  var exact = checkoutState.governorates.find(function(g) {
    return normalizeGovName(g.governorate_name) === normalized;
  });
  if (exact) return exact;

  var arMatch = checkoutState.governorates.find(function(g) {
    return g.governorate_name_ar && normalizeGovName(g.governorate_name_ar) === normalized;
  });
  if (arMatch) return arMatch;

  return checkoutState.governorates.find(function(g) {
    var en = normalizeGovName(g.governorate_name);
    var ar = g.governorate_name_ar ? normalizeGovName(g.governorate_name_ar) : '';
    return (en && (en === normalized || en.indexOf(normalized) !== -1 || normalized.indexOf(en) !== -1)) ||
           (ar && (ar === normalized || ar.indexOf(normalized) !== -1 || normalized.indexOf(ar) !== -1));
  }) || null;
}

function onGovernorateChange() {
  var select = document.getElementById('ship-governorate');
  var opt = select.options[select.selectedIndex];
  if (!opt || !opt.value) {
    checkoutState.deliveryFee = 0;
    checkoutState.selectedGovernorate = '';
    updateSummaryTotals();
    return;
  }
  updateDeliveryFeeFromGovernorate(opt.value);
}

function updateDeliveryFeeFromGovernorate(govName) {
  if (!govName) {
    checkoutState.deliveryFee = 0;
    checkoutState.selectedGovernorate = '';
    checkoutState.deliveryFeeStatus = 'unset';
    checkoutState.freeDeliveryThreshold = 0;
    updateSummaryTotals();
    return;
  }

  var gov = findGovernorateFee(govName);

  if (!gov) {
    checkoutState.deliveryFee = 0;
    checkoutState.selectedGovernorate = govName.trim();
    checkoutState.deliveryFeeStatus = 'unmatched';
    checkoutState.freeDeliveryThreshold = 0;
    updateSummaryTotals();
    return;
  }

  var fee = parseFloat(gov.delivery_fee || 0);
  var freeAbove = parseFloat(gov.min_free_delivery_order || 0);
  var subtotal = parseFloat(checkoutState.cart.subtotal) || 0;

  checkoutState.selectedGovernorate = gov.governorate_name;
  checkoutState.freeDeliveryThreshold = freeAbove;

  if (freeAbove > 0 && subtotal >= freeAbove) {
    checkoutState.deliveryFee = 0;
    checkoutState.deliveryFeeStatus = 'free_threshold';
  } else if (fee === 0) {
    checkoutState.deliveryFee = 0;
    checkoutState.deliveryFeeStatus = 'free_zero_fee';
  } else {
    checkoutState.deliveryFee = fee;
    checkoutState.deliveryFeeStatus = 'matched';
  }

  updateSummaryTotals();
}

function updateSummaryTotals() {
  var deliveryEl = document.getElementById('summary-delivery');
  var totalEl = document.getElementById('summary-total');
  if (!deliveryEl || !totalEl) return;

  var subtotal = parseFloat(checkoutState.cart.subtotal) || 0;
  var discount = parseFloat(checkoutState.cart.discount) || 0;
  var delivery = checkoutState.deliveryFee;
  var total = Math.max(0, subtotal - discount + delivery);

  deliveryEl.removeAttribute('title');

  switch (checkoutState.deliveryFeeStatus) {
    case 'unset':
      deliveryEl.textContent = "{{ __('Select governorate') }}";
      deliveryEl.className = 'v';
      break;
    case 'unmatched':
      deliveryEl.textContent = "{{ __('To be confirmed') }}";
      deliveryEl.className = 'v pending';
      deliveryEl.title = "{{ __('We could not match this governorate to a delivery rate. The final fee will be confirmed with your order.') }}";
      break;
    case 'free_threshold':
      deliveryEl.textContent = "{{ __('Free') }}";
      deliveryEl.className = 'v free';
      if (checkoutState.freeDeliveryThreshold > 0) {
        deliveryEl.title = "{{ __('Free delivery on orders over') }}" + ' EGP ' + checkoutState.freeDeliveryThreshold.toLocaleString();
      }
      break;
    case 'free_zero_fee':
      deliveryEl.textContent = "{{ __('Free') }}";
      deliveryEl.className = 'v free';
      break;
    case 'matched':
    default:
      deliveryEl.textContent = 'EGP ' + delivery.toLocaleString();
      deliveryEl.className = 'v';
      break;
  }

  totalEl.textContent = 'EGP ' + total.toLocaleString();
  updateAddressDeliveryHint();
}

function getDeliveryFeeLabel() {
  switch (checkoutState.deliveryFeeStatus) {
    case 'matched':
      return "{{ __('Estimated delivery') }}" + ': EGP ' + checkoutState.deliveryFee.toLocaleString();
    case 'free_threshold':
    case 'free_zero_fee':
      return "{{ __('Delivery') }}" + ': ' + "{{ __('Free') }}";
    case 'unmatched':
      return "{{ __('Delivery fee') }}" + ': ' + "{{ __('To be confirmed') }}";
    default:
      return '';
  }
}

function updateAddressDeliveryHint() {
  var hint = document.getElementById('address-delivery-hint');
  if (!hint) return;

  if (!checkoutState.selectedAddressId) {
    hint.style.display = 'none';
    return;
  }

  var label = getDeliveryFeeLabel();
  if (!label) {
    hint.style.display = 'none';
    return;
  }

  hint.textContent = label;
  hint.className = 'address-delivery-hint ' + checkoutState.deliveryFeeStatus;
  hint.style.display = 'block';
}

function updateReviewSummaries() {
  var shipEl = document.getElementById('review-shipping-summary');
  var payEl = document.getElementById('review-payment-summary');
  if (!shipEl || !payEl) return;

  if (checkoutState.selectedAddressId) {
    var addr = checkoutState.addresses.find(function(a) { return a.id == checkoutState.selectedAddressId; });
    if (addr) {
      shipEl.innerHTML = '<div class="review-box-label">' + "{{ __('Delivering to') }}" + '</div>' +
        '<div class="review-box-value">' + esc(addr.first_name) + ' ' + esc(addr.last_name) + '<br>' +
        esc(addr.address_line_1 || addr.address || '') +
        (addr.address_line_2 ? ', ' + esc(addr.address_line_2) : '') + '<br>' +
        esc(addr.city) + ', ' + esc(addr.governorate || addr.state || '') + '<br>' +
        esc(addr.phone) + '</div>';
    }
  } else {
    var fn = document.getElementById('ship-first-name')?.value?.trim() || '';
    var ln = document.getElementById('ship-last-name')?.value?.trim() || '';
    var addr1 = document.getElementById('ship-address')?.value?.trim() || '';
    var city = document.getElementById('ship-city')?.value?.trim() || '';
    var gov = document.getElementById('ship-governorate')?.value || '';
    var phone = document.getElementById('ship-phone')?.value?.trim() || '';
    shipEl.innerHTML = '<div class="review-box-label">' + "{{ __('Delivering to') }}" + '</div>' +
      '<div class="review-box-value">' + esc(fn) + ' ' + esc(ln) + '<br>' +
      esc(addr1) + '<br>' + esc(city) + ', ' + esc(gov) + '<br>' + esc(phone) + '</div>';
  }

  var payLabel = checkoutState.paymentMethod === 'card'
    ? "{{ __('Credit / Debit Card') }}"
    : "{{ __('Cash on Delivery') }}";
  payEl.innerHTML = '<div class="review-box-label">' + "{{ __('Payment') }}" + '</div>' +
    '<div class="review-box-value">' + payLabel + '</div>';
}

async function applyCoupon() {
  captureFormState();
  var code = document.getElementById('promo-input')?.value?.trim();
  if (!code) {
    showToast("{{ __('Please enter a promo code.') }}", 'warning');
    return;
  }
  try {
    var res = await API.post('/cart/coupon', { code: code });
    checkoutState.cart = res.cart;
    showToast("{{ __('Promo code applied!') }}", 'success');
    renderCheckout();
    goToStep(3);
  } catch (e) {
    showToast(e.data?.message || "{{ __('Invalid promo code.') }}", 'error');
  }
}

async function removeCoupon() {
  captureFormState();
  try {
    var res = await API.del('/cart/coupon');
    checkoutState.cart = res.cart;
    showToast("{{ __('Promo code removed.') }}", 'info');
    renderCheckout();
    goToStep(3);
  } catch (e) {
    showToast(e.data?.message || "{{ __('Failed to remove coupon.') }}", 'error');
  }
}

async function placeOrder() {
  if (!validateShippingStep() || !validatePaymentStep()) {
    if (!validateShippingStep()) goToStep(1);
    else if (!validatePaymentStep()) goToStep(2);
    return;
  }

  var btn = document.getElementById('btn-place');
  var errorEl = document.getElementById('checkout-error');
  errorEl.style.display = 'none';

  var payload = {
    payment_method: checkoutState.paymentMethod,
    notes: document.getElementById('order-notes')?.value?.trim() || checkoutState.formSnapshot.orderNotes?.trim() || null,
  };

  if (checkoutState.selectedAddressId) {
    payload.shipping_address_id = checkoutState.selectedAddressId;
  } else {
    payload.shipping_address = {
      first_name: document.getElementById('ship-first-name').value.trim(),
      last_name: document.getElementById('ship-last-name').value.trim(),
      email: document.getElementById('ship-email').value.trim(),
      phone: document.getElementById('ship-phone').value.trim(),
      address_line_1: document.getElementById('ship-address').value.trim(),
      address_line_2: document.getElementById('ship-address2')?.value?.trim() || '',
      city: document.getElementById('ship-city').value.trim(),
      state: document.getElementById('ship-governorate').value,
      governorate: document.getElementById('ship-governorate').value,
      postal_code: document.getElementById('ship-postal')?.value?.trim() || '',
      country: 'Egypt',
    };
  }

  btn.classList.add('btn-loading');
  btn.disabled = true;
  btn.textContent = "{{ __('Processing...') }}";

  try {
    var res = await API.post('/orders', payload);
    if (window.Cart && Cart.updateBadge) Cart.updateBadge();
    showToast("{{ __('Order placed successfully!') }}", 'success');
    var orderId = res.order?.id;
    setTimeout(function() {
      window.location.href = orderId ? '/orders/confirmation/' + orderId : '/account';
    }, 800);
  } catch (e) {
    var msg = e.data?.message || "{{ __('Failed to place order. Please try again.') }}";
    if (e.data?.errors) {
      var firstError = Object.values(e.data.errors)[0];
      msg = Array.isArray(firstError) ? firstError[0] : firstError;
    }
    errorEl.textContent = msg;
    errorEl.style.display = 'block';
    errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    btn.classList.remove('btn-loading');
    btn.disabled = false;
    btn.textContent = "{{ __('Place Order') }}";
  }
}
</script>
@endsection
