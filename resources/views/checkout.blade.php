@extends('layouts.app')

@section('title', 'Checkout — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="{{ asset_v('/css/checkout.css') }}">
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

<!-- No external payment SDKs needed - native card form -->

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
  paymentMethod: 'card',
  paymentToken: null,
  savedCards: [],
  formSnapshot: {
    shipFirstName: '',
    shipLastName: '',
    shipEmail: '',
    shipAddress: '',
    shipAddress2: '',
    shipCity: '',
    shipGovernorate: '',
    shipPostal: '',
    shipPhone: '',
    cardName: '',
    cardNumber: '',
    cardExpiry: '',
    cardCvv: '',
    orderNotes: '',
  },
};

const CHECKOUT_STEPS = [
  { id: 1, label: "{{ __('Shipping') }}" },
  { id: 2, label: "{{ __('Review') }}" },
  { id: 3, label: "{{ __('Payment') }}" },
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

    // Update badge globally
    if (window.Cart && Cart.updateBadge) Cart.updateBadge(checkoutState.cart?.items_count);

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
              '<div class="field"><label>' + "{{ __('First Name') }}" + ' *</label><input type="text" id="ship-first-name" value="' + esc(checkoutState.formSnapshot.shipFirstName) + '" required></div>' +
              '<div class="field"><label>' + "{{ __('Last Name') }}" + ' *</label><input type="text" id="ship-last-name" value="' + esc(checkoutState.formSnapshot.shipLastName) + '" required></div>' +
              '<div class="field full"><label>' + "{{ __('Email') }}" + ' *</label><input type="email" id="ship-email" value="' + esc(checkoutState.formSnapshot.shipEmail) + '" required></div>' +
              '<div class="field full"><label>' + "{{ __('Street Address') }}" + ' *</label><input type="text" id="ship-address" value="' + esc(checkoutState.formSnapshot.shipAddress) + '" required></div>' +
              '<div class="field full"><label>' + "{{ __('Address Line 2') }}" + '</label><input type="text" id="ship-address2" value="' + esc(checkoutState.formSnapshot.shipAddress2) + '"></div>' +
              '<div class="field"><label>' + "{{ __('City') }}" + ' *</label><input type="text" id="ship-city" value="' + esc(checkoutState.formSnapshot.shipCity) + '" required></div>' +
              '<div class="field"><label>' + "{{ __('Governorate') }}" + ' *</label>' +
                '<select id="ship-governorate">' +
                  '<option value="">' + "{{ __('Select Governorate') }}" + '</option>';

  checkoutState.governorates.forEach(function(gov) {
    var isSelected = (checkoutState.formSnapshot.shipGovernorate === gov.governorate_name) ? ' selected' : '';
    html += '<option value="' + esc(gov.governorate_name) + '" data-fee="' + (gov.delivery_fee || 0) + '" data-free="' + (gov.min_free_delivery_order || 0) + '"' + isSelected + '>' +
              esc(gov.governorate_name) + ' — EGP ' + (gov.delivery_fee || 0) +
            '</option>';
  });

  html +=       '</select></div>' +
              '<div class="field"><label>' + "{{ __('Postal Code') }}" + ' *</label><input type="text" id="ship-postal" value="' + esc(checkoutState.formSnapshot.shipPostal) + '" required></div>' +
              '<div class="field"><label>' + "{{ __('Phone') }}" + ' *</label><input type="tel" id="ship-phone" value="' + esc(checkoutState.formSnapshot.shipPhone) + '" required></div>' +
            '</div>' +
          '</div>';

  html += '<div id="step-1-error" class="step-error" style="display:none"></div>' +
          '<div class="step-actions">' +
            '<a href="/cart" class="btn-outline step-back-link">' + "{{ __('Back to Cart') }}" + '</a>' +
            '<button type="button" class="btn-dark step-continue" id="btn-step-1-continue">' + "{{ __('Continue to Review') }}" + '</button>' +
          '</div>';

  html += '</div></div>';
  return html;
}

function captureFormState() {
  var s = checkoutState.formSnapshot;
  var el;
  el = document.getElementById('ship-first-name'); if (el) s.shipFirstName = el.value;
  el = document.getElementById('ship-last-name');  if (el) s.shipLastName = el.value;
  el = document.getElementById('ship-email');      if (el) s.shipEmail = el.value;
  el = document.getElementById('ship-address');    if (el) s.shipAddress = el.value;
  el = document.getElementById('ship-address2');   if (el) s.shipAddress2 = el.value;
  el = document.getElementById('ship-city');       if (el) s.shipCity = el.value;
  el = document.getElementById('ship-governorate');if (el) s.shipGovernorate = el.value;
  el = document.getElementById('ship-postal');     if (el) s.shipPostal = el.value;
  el = document.getElementById('ship-phone');      if (el) s.shipPhone = el.value;
  el = document.getElementById('card-name');       if (el) s.cardName = el.value;
  el = document.getElementById('card-number');     if (el) s.cardNumber = el.value;
  el = document.getElementById('card-expiry');     if (el) s.cardExpiry = el.value;
  el = document.getElementById('card-cvv');        if (el) s.cardCvv = el.value;
  el = document.getElementById('order-notes');     if (el) s.orderNotes = el.value;
}

function renderPaymentStep() {
  var savedCards = checkoutState.savedCards || [];
  var isLoggedIn = !checkoutState.isGuest;
  var html = '<div class="checkout-step-panel' + (checkoutState.currentStep === 3 ? ' active' : '') + '" id="step-3" data-step="3">' +
               '<div class="checkout-section">' +
                 '<div class="checkout-section-header">' +
                   '<div class="step-circle">3</div>' +
                   '<div>' +
                     '<h2>' + "{{ __('Card Payment') }}" + '</h2>' +
                     '<p class="section-desc">' + "{{ __('Enter your card details to complete your purchase') }}" + '</p>' +
                   '</div>' +
                 '</div>';

  // Loading state (shown while preparing payment)
  html += '<div id="payment-loading" style="display:none; text-align:center; padding:40px 20px;">' +
            '<div style="width:32px; height:32px; border:3px solid #e2e8f0; border-top-color:#c9a96e; border-radius:50%; animation:spin 0.8s linear infinite; margin:0 auto 12px;"></div>' +
            '<p style="font-size:13px; color:#64748b; margin:0;">' + "{{ __('Preparing secure payment...') }}" + '</p>' +
          '</div>';

  // Card form (visible after payment prepared)
  html += '<div id="card-form-wrapper" style="display:none;">';

  // Saved cards section
  if (isLoggedIn && savedCards.length > 0) {
    html += '<div class="checkout-subsection" style="margin-bottom:16px;">' +
              '<h3>' + "{{ __('Saved Cards') }}" + '</h3>';
    for (var i = 0; i < savedCards.length; i++) {
      var sc = savedCards[i];
      html += '<label class="pay-method" style="cursor:pointer; margin-bottom:8px;" data-saved-card-id="' + sc.id + '">' +
                '<input type="radio" name="card-choice" value="saved_' + sc.id + '" style="margin-right:10px;">' +
                '<div class="pay-method-info" style="flex:1;">' +
                  '<div class="pay-method-name">' + esc(sc.masked_pan) + (sc.brand ? ' <span style="color:#64748b; font-weight:400;">(' + esc(sc.brand) + ')</span>' : '') + '</div>' +
                  (sc.expiry_month && sc.expiry_year ? '<div class="pay-method-desc">' + "{{ __('Expires') }}" + ' ' + sc.expiry_month + '/' + sc.expiry_year + '</div>' : '') +
                '</div>' +
              '</label>';
    }
    html += '<label class="pay-method" style="cursor:pointer; margin-bottom:8px;">' +
              '<input type="radio" name="card-choice" value="new" checked style="margin-right:10px;">' +
              '<div class="pay-method-info">' +
                '<div class="pay-method-name">' + "{{ __('Use a new card') }}" + '</div>' +
              '</div>' +
            '</label>' +
          '</div>';
  }

  // New card form
  html += '<div id="new-card-fields">' +
            '<div class="form-group" style="margin-bottom:14px;">' +
              '<label for="card-number" style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">' + "{{ __('Card Number') }}" + '</label>' +
              '<div style="position:relative;">' +
                '<input type="text" id="card-number" inputmode="numeric" autocomplete="cc-number" maxlength="19" placeholder="4111 1111 1111 1111" style="width:100%; padding:12px 14px 12px 42px; border:1px solid #d1d5db; border-radius:8px; font-size:15px; font-family:monospace; outline:none; transition:border-color .2s;">' +
                '<div id="card-brand-icon" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); width:24px; height:16px; display:flex; align-items:center;">' +
                  '<svg viewBox="0 0 24 24"' + SVG + ' stroke-width="1.5" style="width:20px; height:20px; color:#94a3b8;"><rect width="22" height="16" x="1" y="4" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>' +
                '</div>' +
              '</div>' +
            '</div>' +
            '<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">' +
              '<div class="form-group">' +
                '<label for="card-expiry" style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">' + "{{ __('Expiry Date') }}" + '</label>' +
                '<input type="text" id="card-expiry" inputmode="numeric" autocomplete="cc-exp" maxlength="5" placeholder="MM/YY" style="width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:15px; font-family:monospace; outline:none; transition:border-color .2s;">' +
              '</div>' +
              '<div class="form-group">' +
                '<label for="card-cvv" style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">' + "{{ __('CVV') }}" + '</label>' +
                '<input type="text" id="card-cvv" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="123" style="width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:15px; font-family:monospace; outline:none; transition:border-color .2s;">' +
              '</div>' +
            '</div>' +
            '<div class="form-group" style="margin-bottom:14px;">' +
              '<label for="card-name" style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">' + "{{ __('Name on Card') }}" + '</label>' +
              '<input type="text" id="card-name" autocomplete="cc-name" placeholder="' + "{{ __('Ahmed Mohamed') }}" + '" style="width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:15px; outline:none; transition:border-color .2s;">' +
            '</div>';

  // Save card checkbox (logged-in users only)
  if (isLoggedIn) {
    html += '<label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-bottom:14px; font-size:13px; color:#475569;">' +
              '<input type="checkbox" id="save-card-checkbox">' +
              "{{ __('Save this card for future purchases') }}" +
            '</label>';
  }

  html += '</div>'; // close new-card-fields

  // Security badge
  html += '<p class="card-secure-note" style="margin-top:12px;">' +
            '<svg viewBox="0 0 24 24"' + SVG + ' stroke-width="2" style="stroke:var(--color-success)"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>' +
            "{{ __('Your card details are sent directly to our payment processor. They never touch our servers.') }}" +
          '</p>';

  html += '</div>'; // close card-form-wrapper

  html += '<div id="step-3-error" class="step-error" style="display:none"></div>' +
          '<div class="step-actions">' +
            '<button type="button" class="btn-outline step-back" id="btn-step-3-back">' + "{{ __('Back') }}" + '</button>' +
          '</div>' +
        '</div>' +
      '</div>';
  return html;
}

function renderReviewStep(cart, discount, affiliateDiscount) {
  var snap = checkoutState.formSnapshot;
  var hasCode = cart.coupon || cart.affiliate;
  var codeStr = cart.coupon ? cart.coupon.code : (cart.affiliate ? cart.affiliate.code : '');
  var html = '<div class="checkout-step-panel' + (checkoutState.currentStep === 2 ? ' active' : '') + '" id="step-2" data-step="2">' +
               '<div class="checkout-section">' +
                 '<div class="checkout-section-header">' +
                   '<div class="step-circle">2</div>' +
                   '<div>' +
                     '<h2>' + "{{ __('Review & Extras') }}" + '</h2>' +
                     '<p class="section-desc">' + "{{ __('Add a promo code or notes before payment') }}" + '</p>' +
                   '</div>' +
                 '</div>' +
                 '<div class="review-summary-box" id="review-shipping-summary"></div>' +
                 '<div class="checkout-subsection">' +
                   '<h3>' + "{{ __('Promo / Affiliate Code') }}" + '</h3>' +
                   '<div class="promo-box">' +
                     '<input type="text" id="promo-input" placeholder="' + "{{ __('Enter code') }}" + '"' +
                       (hasCode ? ' value="' + esc(codeStr) + '" disabled' : '') + '>' +
                     (hasCode
                       ? '<button type="button" class="btn-outline" id="btn-remove-promo">' + "{{ __('Remove') }}" + '</button>'
                       : '<button type="button" class="btn-dark" id="btn-apply-promo">' + "{{ __('Apply') }}" + '</button>') +
                   '</div>' +
                   (cart.coupon ? '<div class="promo-applied">✓ ' + "{{ __('Coupon applied!') }}" + ' (-EGP ' + discount.toLocaleString() + ')</div>' : '') +
                   (cart.affiliate ? '<div class="promo-applied">✓ ' + "{{ __('Affiliate code applied!') }}" + ' (-EGP ' + affiliateDiscount.toLocaleString() + ')</div>' : '') +
                 '</div>' +
                 '<div class="checkout-subsection">' +
                   '<h3>' + "{{ __('Order Notes') }}" + '</h3>' +
                   '<textarea id="order-notes" placeholder="' + "{{ __('Any special instructions for your order...') }}" + '">' + esc(snap.orderNotes) + '</textarea>' +
                 '</div>' +
                 '<div id="step-2-error" class="step-error" style="display:none"></div>' +
                 '<div class="step-actions">' +
                   '<button type="button" class="btn-outline step-back" id="btn-step-2-back">' + "{{ __('Back') }}" + '</button>' +
                   '<button type="button" class="btn-dark step-continue" id="btn-step-2-continue">' + "{{ __('Continue to Payment') }}" + '</button>' +
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
  const affiliateDiscount = cart.affiliate ? parseFloat(cart.affiliate.discount) : 0;
  const savedStep = checkoutState.currentStep;

  captureFormState();

  if (checkoutState.addresses.length > 0 && checkoutState.selectedAddressId == null) {
    checkoutState.selectedAddressId = checkoutState.addresses[0].id;
  }

  let html = renderStepNav();
  html += '<div class="checkout-main animate-fade-up">';
  html += renderShippingStep();
  html += renderReviewStep(cart, discount, affiliateDiscount);
  html += renderPaymentStep();
  html += '</div>';

  html += '<div class="checkout-summary-wrap animate-fade-up stagger-2">';
  html += '<div class="checkout-summary">' +
            '<h2 class="summary-heading">' + "{{ __('Order Summary') }}" + '</h2>' +
            '<div class="sum-items">';

  cart.items.forEach(function(item) {
    var imgSrc = item.product?.image || '/img/placeholder.svg';
    html += '<div class="sum-item">' +
              '<div style="position: relative; flex-shrink: 0;">' +
                '<div class="sum-item-img">' +
                  '<img src="' + imgSrc + '" alt="' + esc(item.name) + '" onerror="this.src=\'/img/placeholder.svg\'">' +
                '</div>' +
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
  
  if (affiliateDiscount > 0) {
    html += '<div class="sum-row"><span class="k">' + "{{ __('Affiliate Discount') }}" + '</span><span class="v sum-discount">-EGP ' + affiliateDiscount.toLocaleString() + '</span></div>';
  }

  html += '<div class="sum-row"><span class="k">' + "{{ __('Delivery') }}" + '</span><span class="v" id="summary-delivery">' + "{{ __('Select governorate') }}" + '</span></div>' +
          '<div class="sum-row total"><span>' + "{{ __('Total') }}" + '</span><span id="summary-total">EGP ' + Math.max(0, subtotal - discount - affiliateDiscount).toLocaleString() + '</span></div>' +
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
  document.getElementById('btn-step-2-continue')?.addEventListener('click', function() { preparePaymentAndGoToStep3(); });

  document.getElementById('btn-step-3-back')?.addEventListener('click', function() { goToStep(2); });
  document.getElementById('btn-place')?.addEventListener('click', placeOrder);

  document.getElementById('btn-apply-promo')?.addEventListener('click', applyPromo);
  document.getElementById('btn-remove-promo')?.addEventListener('click', removePromo);

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

  var orderNotes = document.getElementById('order-notes');
  if (orderNotes) {
    orderNotes.addEventListener('input', function() {
      checkoutState.formSnapshot.orderNotes = orderNotes.value;
    });
  }

  // Card input formatting
  bindCardInputs();
}

function bindCardInputs() {
  var cardNumber = document.getElementById('card-number');
  if (cardNumber) {
    cardNumber.addEventListener('input', function() {
      var v = cardNumber.value.replace(/\D/g, '').substring(0, 16);
      cardNumber.value = v.replace(/(.{4})/g, '$1 ').trim();
      updateCardBrandIcon(v);
    });
    cardNumber.addEventListener('focus', function() { cardNumber.style.borderColor = '#c9a96e'; });
    cardNumber.addEventListener('blur', function() { cardNumber.style.borderColor = '#d1d5db'; });
  }

  var cardExpiry = document.getElementById('card-expiry');
  if (cardExpiry) {
    cardExpiry.addEventListener('input', function() {
      var v = cardExpiry.value.replace(/\D/g, '').substring(0, 4);
      if (v.length >= 3) v = v.substring(0, 2) + '/' + v.substring(2);
      cardExpiry.value = v;
    });
    cardExpiry.addEventListener('focus', function() { cardExpiry.style.borderColor = '#c9a96e'; });
    cardExpiry.addEventListener('blur', function() { cardExpiry.style.borderColor = '#d1d5db'; });
  }

  var cardCvv = document.getElementById('card-cvv');
  if (cardCvv) {
    cardCvv.addEventListener('input', function() {
      cardCvv.value = cardCvv.value.replace(/\D/g, '').substring(0, 4);
    });
    cardCvv.addEventListener('focus', function() { cardCvv.style.borderColor = '#c9a96e'; });
    cardCvv.addEventListener('blur', function() { cardCvv.style.borderColor = '#d1d5db'; });
  }

  var cardName = document.getElementById('card-name');
  if (cardName) {
    cardName.addEventListener('focus', function() { cardName.style.borderColor = '#c9a96e'; });
    cardName.addEventListener('blur', function() { cardName.style.borderColor = '#d1d5db'; });
  }

  // Saved card radio toggle
  document.querySelectorAll('input[name="card-choice"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
      var newFields = document.getElementById('new-card-fields');
      if (newFields) newFields.style.display = radio.value === 'new' ? 'block' : 'none';
    });
  });
}

function updateCardBrandIcon(digits) {
  var el = document.getElementById('card-brand-icon');
  if (!el) return;
  var brand = '';
  if (/^4/.test(digits)) brand = 'VISA';
  else if (/^5[1-5]/.test(digits) || /^2[2-7]/.test(digits)) brand = 'MC';
  else if (/^50/.test(digits) || /^60/.test(digits) || /^62/.test(digits)) brand = 'MEEZA';

  if (brand) {
    el.innerHTML = '<span style="font-size:11px; font-weight:800; color:#0f172a; letter-spacing:0.5px;">' + brand + '</span>';
  } else {
    el.innerHTML = '<svg viewBox="0 0 24 24"' + SVG + ' stroke-width="1.5" style="width:20px; height:20px; color:#94a3b8;"><rect width="22" height="16" x="1" y="4" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>';
  }
}

function goToStep(step) {
  checkoutState.currentStep = step;
  updateStepUI();
  updateReviewSummaries();
  if (step === 3) bindCardInputs();
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
  if (placeBtn) {
    placeBtn.style.display = checkoutState.currentStep === 3 ? 'block' : 'none';
    if (checkoutState.currentStep === 3 && checkoutState.paymentToken) {
      placeBtn.textContent = "{{ __('Pay Now') }}";
    }
  }
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
  showStepError(3, '');
  checkoutState.paymentMethod = 'card';
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

// Payment is always card — no selectPayment needed

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
  var affiliateDiscount = checkoutState.cart.affiliate ? parseFloat(checkoutState.cart.affiliate.discount) : 0;
  var delivery = checkoutState.deliveryFee;
  var total = Math.max(0, subtotal - discount - affiliateDiscount + delivery);

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
  if (!shipEl) return;

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
}

async function applyPromo() {
  captureFormState();
  var code = document.getElementById('promo-input')?.value?.trim();
  if (!code) {
    showToast("{{ __('Please enter a code.') }}", 'warning');
    return;
  }
  
  try {
    var res = await API.post('/cart/affiliate', { code: code });
    checkoutState.cart = res.cart;
    showToast("{{ __('Affiliate code applied!') }}", 'success');
    renderCheckout();
    goToStep(3);
    return;
  } catch (e) {}

  try {
    var res = await API.post('/cart/coupon', { code: code });
    checkoutState.cart = res.cart;
    showToast("{{ __('Promo code applied!') }}", 'success');
    renderCheckout();
    goToStep(3);
  } catch (e) {
    showToast(e.data?.message || "{{ __('Invalid or expired code.') }}", 'error');
  }
}

async function removePromo() {
  captureFormState();
  if (checkoutState.cart.coupon) {
      try {
        var res = await API.del('/cart/coupon');
        checkoutState.cart = res.cart;
        showToast("{{ __('Promo code removed.') }}", 'info');
        renderCheckout();
        goToStep(3);
      } catch (e) {
        showToast(e.data?.message || "{{ __('Failed to remove coupon.') }}", 'error');
      }
  } else if (checkoutState.cart.affiliate) {
      try {
        var res = await API.del('/cart/affiliate');
        checkoutState.cart = res.cart;
        showToast("{{ __('Affiliate code removed.') }}", 'info');
        renderCheckout();
        goToStep(3);
      } catch (e) {
        showToast(e.data?.message || "{{ __('Failed to remove affiliate code.') }}", 'error');
      }
  }
}

// Build order payload from form data
function buildOrderPayload() {
  var notes = document.getElementById('order-notes')?.value?.trim() || checkoutState.formSnapshot.orderNotes?.trim() || null;
  var saveCard = document.getElementById('save-card-checkbox')?.checked;
  if (saveCard && notes) notes = notes + ' [SAVE_CARD]';
  else if (saveCard) notes = '[SAVE_CARD]';

  var payload = {
    payment_method: 'card',
    notes: notes,
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
  return payload;
}

// Step 2 → Step 3: Create order and prepare payment token
async function preparePaymentAndGoToStep3() {
  if (!validateShippingStep()) { goToStep(1); return; }

  // If already prepared, just go to step 3
  if (checkoutState.paymentToken && checkoutState.activeOrderId) {
    goToStep(3);
    return;
  }

  goToStep(3);
  var loadingEl = document.getElementById('payment-loading');
  var formEl = document.getElementById('card-form-wrapper');
  if (loadingEl) loadingEl.style.display = 'block';
  if (formEl) formEl.style.display = 'none';

  var btn = document.getElementById('btn-place');
  if (btn) { btn.disabled = true; btn.textContent = "{{ __('Preparing...') }}"; }

  try {
    var payload = buildOrderPayload();
    var res = await API.post('/orders', payload);
    if (window.Cart && Cart.updateBadge) Cart.updateBadge();

    checkoutState.activeOrderId = res.order?.id;
    checkoutState.paymentToken = res.paymob?.payment_token || null;
    checkoutState.savedCards = res.saved_cards || [];

    if (loadingEl) loadingEl.style.display = 'none';
    if (formEl) formEl.style.display = 'block';
    if (btn) { btn.disabled = false; btn.textContent = "{{ __('Pay Now') }}"; }

    // Re-render step 3 with saved cards if any
    if (checkoutState.savedCards.length > 0) {
      var step3 = document.getElementById('step-3');
      if (step3) {
        step3.outerHTML = renderPaymentStep();
        var newLoadingEl = document.getElementById('payment-loading');
        var newFormEl = document.getElementById('card-form-wrapper');
        if (newLoadingEl) newLoadingEl.style.display = 'none';
        if (newFormEl) newFormEl.style.display = 'block';
        bindCardInputs();
        document.getElementById('btn-step-3-back')?.addEventListener('click', function() { goToStep(2); });
      }
    } else {
      bindCardInputs();
    }

    if (!checkoutState.paymentToken) {
      showStepError(3, "{{ __('Payment setup failed. Please go back and try again.') }}");
    }

  } catch (e) {
    if (loadingEl) loadingEl.style.display = 'none';
    var msg = e.data?.message || "{{ __('Failed to prepare payment. Please try again.') }}";
    if (e.data?.errors) {
      var firstError = Object.values(e.data.errors)[0];
      msg = Array.isArray(firstError) ? firstError[0] : firstError;
    }
    showStepError(3, msg);
    if (btn) { btn.disabled = false; btn.textContent = "{{ __('Place Order') }}"; }
  }
}

// Validate card fields
function validateCardFields() {
  var cardChoice = document.querySelector('input[name="card-choice"]:checked');
  if (cardChoice && cardChoice.value !== 'new') return true; // saved card selected

  var num = (document.getElementById('card-number')?.value || '').replace(/\s/g, '');
  var exp = (document.getElementById('card-expiry')?.value || '').trim();
  var cvv = (document.getElementById('card-cvv')?.value || '').trim();
  var name = (document.getElementById('card-name')?.value || '').trim();

  if (num.length < 13 || num.length > 19) { showStepError(3, "{{ __('Please enter a valid card number.') }}"); return false; }
  if (!/^\d{2}\/\d{2}$/.test(exp)) { showStepError(3, "{{ __('Please enter expiry as MM/YY.') }}"); return false; }
  if (cvv.length < 3) { showStepError(3, "{{ __('Please enter a valid CVV.') }}"); return false; }
  if (name.length < 2) { showStepError(3, "{{ __('Please enter the name on your card.') }}"); return false; }

  showStepError(3, '');
  return true;
}

// Submit payment: browser → Paymob API directly
async function placeOrder() {
  if (!checkoutState.paymentToken || !checkoutState.activeOrderId) {
    showStepError(3, "{{ __('Payment not ready. Please go back and try again.') }}");
    return;
  }

  if (!validateCardFields()) return;

  var btn = document.getElementById('btn-place');
  var errorEl = document.getElementById('checkout-error');
  if (errorEl) errorEl.style.display = 'none';
  showStepError(3, '');

  btn.classList.add('btn-loading');
  btn.disabled = true;
  btn.textContent = "{{ __('Processing payment...') }}";

  // Determine if using saved card or new card
  var cardChoice = document.querySelector('input[name="card-choice"]:checked');
  var useSavedCard = cardChoice && cardChoice.value !== 'new';
  var source = {};

  if (useSavedCard) {
    var savedCardId = cardChoice.value.replace('saved_', '');
    var savedCard = (checkoutState.savedCards || []).find(function(c) { return c.id == savedCardId; });
    if (!savedCard || !savedCard.card_token) {
      showStepError(3, "{{ __('Saved card data not available. Please use a new card.') }}");
      btn.classList.remove('btn-loading'); btn.disabled = false; btn.textContent = "{{ __('Pay Now') }}";
      return;
    }
    source = { identifier: savedCard.card_token, subtype: 'TOKEN' };
  } else {
    var num = (document.getElementById('card-number')?.value || '').replace(/\s/g, '');
    var exp = (document.getElementById('card-expiry')?.value || '').split('/');
    var cvv = (document.getElementById('card-cvv')?.value || '').trim();
    var name = (document.getElementById('card-name')?.value || '').trim();
    source = {
      identifier: num,
      sourceholder_name: name,
      subtype: 'CARD',
      expiry_month: exp[0] || '',
      expiry_year: exp[1] || '',
      cvn: cvv,
    };
  }

  try {
    // Send card data directly from browser to Paymob API
    var response = await fetch('https://accept.paymob.com/api/acceptance/payments/pay', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        source: source,
        payment_token: checkoutState.paymentToken,
      }),
    });

    var data = await response.json();

    if (data.redirect_url || data.redirection_url || data.iframe_redirection_url) {
      // 3D Secure redirect — redirect the whole page
      var redirectUrl = data.redirect_url || data.redirection_url || data.iframe_redirection_url;
      showToast("{{ __('Redirecting to bank verification...') }}", 'info');
      window.location.href = redirectUrl;
      return;
    }

    if (data.success === true || data.is_captured === true || data.pending === false && !data.error_occured) {
      showToast("{{ __('Payment successful! Redirecting...') }}", 'success');
      setTimeout(function() {
        window.location.href = '/orders/confirmation/' + checkoutState.activeOrderId + '?payment_status=success';
      }, 1000);
      return;
    }

    // Payment failed
    var errMsg = data.data?.message || data.message || "{{ __('Payment was declined. Please check your card details and try again.') }}";
    showStepError(3, errMsg);
    btn.classList.remove('btn-loading');
    btn.disabled = false;
    btn.textContent = "{{ __('Try Again') }}";

  } catch (e) {
    console.error('Payment error:', e);
    showStepError(3, "{{ __('Payment failed. Please check your connection and try again.') }}");
    btn.classList.remove('btn-loading');
    btn.disabled = false;
    btn.textContent = "{{ __('Try Again') }}";
  }
}
</script>
@endsection
