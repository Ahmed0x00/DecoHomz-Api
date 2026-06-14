@extends('layouts.app')

@section('title', 'Shopping Cart — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/cart.css">
@endsection

@section('content')

<div class="cart-page">
  <div class="cart-progress animate-fade-up">
    <div class="cart-progress-step active">
      <span class="cart-progress-num">1</span>
      <span>{{ __('Cart') }}</span>
    </div>
    <div class="cart-progress-line"></div>
    <div class="cart-progress-step">
      <span class="cart-progress-num">2</span>
      <span>{{ __('Checkout') }}</span>
    </div>
    <div class="cart-progress-line"></div>
    <div class="cart-progress-step">
      <span class="cart-progress-num">3</span>
      <span>{{ __('Confirm') }}</span>
    </div>
  </div>

  <div class="breadcrumb" style="padding-inline-start:0; padding-top:0; background:transparent; border:none; margin-bottom:16px;">
    <a href="/">{{ __('Home') }}</a> › <span>{{ __('Shopping Cart') }}</span>
  </div>

  <div class="cart-header animate-fade-up">
    <h1>{{ __('Your Cart') }}</h1>
    <p class="cart-subtitle" id="cart-item-count"></p>
  </div>

  {{-- Main Cart Container --}}
  <div id="full-cart-container" class="animate-fade-up stagger-2">
    {{-- Loaded via JS --}}
    <div style="padding:100px 0; text-align:center;">
      <div class="spinner spinner-lg"></div>
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
  renderFullCart();
});

// HTML escape helper
function esc(str) {
  var div = document.createElement('div');
  div.appendChild(document.createTextNode(str || ''));
  return div.innerHTML;
}

// Standard stroke icon attributes
var SVG = ' fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"';

function renderFullCart() {
  const container = document.getElementById('full-cart-container');
  
  if (typeof API === 'undefined') {
    container.innerHTML = '<div class="shop-empty"><p class="text-error">API not loaded.</p></div>';
    return;
  }
  
  API.get('/cart').then(function(res) {
    const cart = res.cart || {};
    const items = cart.items || [];
    const subtotal = parseFloat(cart.subtotal) || 0;
    const discount = parseFloat(cart.discount) || 0;
    const coupon = cart.coupon || null;
    const total = Math.max(0, subtotal - discount);
    
    // Update badge globally
    if (window.Cart && Cart.updateBadge) Cart.updateBadge();
    
    if (items.length === 0) {
      container.innerHTML = 
        '<div class="cart-empty">' +
          '<svg class="icon-stroke" viewBox="0 0 24 24"' + SVG + ' stroke-width="1.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>' +
          '<h2>' + "{{ __('Your cart is empty') }}" + '</h2>' +
          '<p>' + "{{ __('Looks like you have not added anything to your cart yet.') }}" + '</p>' +
          '<a href="/shop" class="btn-dark" style="display:inline-block;padding:14px 32px">' + "{{ __('Start Shopping') }}" + '</a>' +
        '</div>';
      return;
    }
    
    let html = '<div class="cart-grid">';
    
    // Left: Items
    html += '<div class="cart-items-container">';
    html += '<div class="cart-table-head">' +
            '<div>' + "{{ __('Product') }}" + '</div>' +
            '<div>' + "{{ __('Quantity') }}" + '</div>' +
            '<div style="text-align:right">' + "{{ __('Total') }}" + '</div>' +
            '<div></div>' +
            '</div>';
            
    items.forEach(function(item) {
      const imgSrc = item.product?.image || '/img/placeholder.svg';
      const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
      
      html += '<div class="cart-item">' +
                '<div class="c-item-info">' +
                  '<a href="/product/' + (item.product?.slug || item.product_id) + '" class="c-item-img">' +
                    '<img src="' + imgSrc + '" alt="' + esc(item.name) + '" onerror="this.src=\'/img/placeholder.svg\'">' +
                  '</a>' +
                  '<div class="c-item-details">' +
                    '<a href="/product/' + (item.product?.slug || item.product_id) + '" class="c-item-name">' + esc(item.name) + '</a>' +
                    '<div class="c-item-meta">' + (item.variant || 'Standard') + '</div>' +
                    '<div class="c-item-unit-price">EGP ' + parseFloat(item.price).toLocaleString() + '</div>' +
                  '</div>' +
                '</div>' +
                '<div class="c-item-qty">' +
                  '<div class="qty-ctrl">' +
                    '<button class="qty-btn" onclick="pageUpdateQty(' + item.id + ', -1)">−</button>' +
                    '<span class="qty-num">' + item.quantity + '</span>' +
                    '<button class="qty-btn" onclick="pageUpdateQty(' + item.id + ', 1)">+</button>' +
                  '</div>' +
                '</div>' +
                '<div class="c-item-total" style="text-align:right">EGP ' + itemTotal.toLocaleString() + '</div>' +
                '<div style="display:flex;justify-content:flex-end">' +
                  '<button class="c-item-remove" onclick="pageRemoveItem(' + item.id + ')" aria-label="' + "{{ __('Remove item') }}" + '">' +
                    '<svg class="icon-stroke" viewBox="0 0 24 24"' + SVG + ' stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
                  '</button>' +
                '</div>' +
              '</div>';
    });
    
    html += '</div>'; // end cart-items-container
    
    // Right: Summary
    html += '<div class="cart-summary summary-box">' +
              '<h2 class="summary-title">' + "{{ __('Order Summary') }}" + '</h2>' +
              
              '<div class="sum-row">' +
                '<span class="k">' + "{{ __('Subtotal') }}" + '</span>' +
                '<span class="v">EGP ' + subtotal.toLocaleString() + '</span>' +
              '</div>';
    
    if (discount > 0) {
      html += '<div class="sum-row">' +
                '<span class="k">' + "{{ __('Discount') }}" + (coupon ? ' (' + esc(coupon.code) + ')' : '') + '</span>' +
                '<span class="v" style="color:var(--color-error)">-EGP ' + discount.toLocaleString() + '</span>' +
              '</div>';
    }
    
    html += '<div class="sum-row">' +
                '<span class="k">' + "{{ __('Delivery') }}" + '</span>' +
                '<span class="v" style="color:var(--color-text-faint);font-size:12px">' + "{{ __('Calculated at checkout') }}" + '</span>' +
              '</div>' +
              '<div class="sum-row total">' +
                '<span>' + "{{ __('Estimated Total') }}" + '</span>' +
                '<span>EGP ' + total.toLocaleString() + '</span>' +
              '</div>' +
              
              '<div style="margin-top:24px; margin-bottom:16px;">' +
                '<div class="k" style="font-size:12px;margin-bottom:8px">' + "{{ __('Promo Code') }}" + '</div>' +
                '<div class="promo-box">';
    
    if (coupon) {
      html +=   '<input type="text" id="cart-promo-input" value="' + esc(coupon.code) + '" disabled>' +
                '<button onclick="cartRemoveCoupon()">' + "{{ __('Remove') }}" + '</button>';
    } else {
      html +=   '<input type="text" id="cart-promo-input" placeholder="' + "{{ __('Enter code') }}" + '">' +
                '<button onclick="cartApplyCoupon()">' + "{{ __('Apply') }}" + '</button>';
    }
    
    html +=   '</div>' +
              '</div>' +
              
              '<button class="btn-dark checkout-btn" onclick="location.href=\'/checkout\'">' + "{{ __('Proceed to Checkout') }}" + '</button>' +
              '<a href="/shop" class="continue-shopping">' + "{{ __('Continue Shopping') }}" + '</a>' +
              
              '<div class="cart-secure-badge">' +
                '<svg viewBox="0 0 24 24"' + SVG + ' stroke-width="2" style="stroke:var(--color-success)"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>' +
                '<span>' + "{{ __('Secure encrypted checkout') }}" + '</span>' +
              '</div>' +
            '</div>'; // end summary
            
    html += '</div>'; // end grid
    
    container.innerHTML = html;

    var countEl = document.getElementById('cart-item-count');
    if (countEl) {
      var totalQty = items.reduce(function(sum, i) { return sum + parseInt(i.quantity, 10); }, 0);
      countEl.textContent = totalQty + ' ' + (totalQty === 1 ? "{{ __('item') }}" : "{{ __('items') }}") + ' ' + "{{ __('in your cart') }}";
    }
    
  }).catch(function(e) {
    container.innerHTML = '<div class="shop-empty"><p class="text-error">Failed to load cart.</p></div>';
  });
}

window.pageUpdateQty = async function(itemId, delta) {
  const res = await API.get('/cart').catch(() => ({}));
  const items = res.cart?.items || [];
  const item = items.find(i => i.id == itemId);
  if (!item) return;
  
  const newQty = Math.max(0, item.quantity + delta);
  if (newQty === 0) {
    await Cart.remove(itemId);
  } else {
    await Cart.updateQty(itemId, newQty);
  }
  
  renderFullCart();
};

window.pageRemoveItem = async function(itemId) {
  await Cart.remove(itemId);
  renderFullCart();
};

window.cartApplyCoupon = async function() {
  var code = document.getElementById('cart-promo-input').value.trim();
  if (!code) {
    showToast("{{ __('Please enter a promo code.') }}", 'warning');
    return;
  }
  try {
    await API.post('/cart/coupon', { code: code });
    showToast("{{ __('Promo code applied!') }}", 'success');
    renderFullCart();
  } catch(e) {
    showToast(e.data?.message || "{{ __('Invalid promo code.') }}", 'error');
  }
};

window.cartRemoveCoupon = async function() {
  try {
    await API.del('/cart/coupon');
    showToast("{{ __('Promo code removed.') }}", 'info');
    renderFullCart();
  } catch(e) {
    showToast(e.data?.message || "{{ __('Failed to remove coupon.') }}", 'error');
  }
};
</script>
@endsection