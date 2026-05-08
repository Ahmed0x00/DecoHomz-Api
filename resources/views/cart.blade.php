@extends('layouts.app')

@section('title', 'Shopping Cart — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/cart.css">
@endsection

@section('content')

<div class="breadcrumb">{{ __('Home') }} › <span>{{ __('Shopping Cart') }}</span></div>

<div class="cart-layout">
  <div class="cart-items">
    <div class="cart-header">
      <h1 class="cart-title" id="cart-title">{{ __('Your Cart') }}</h1>
      <button class="clear-btn" id="clear-cart-btn">{{ __('Clear All') }}</button>
    </div>
    
    <div id="cart-page-container">
      <!-- Loading state -->
      <div style="text-align:center; padding:60px;">
        <div class="spinner"></div>
      </div>
    </div>
  </div>

  <div class="summary">
    <h2 class="summary-title">{{ __('Order Summary') }}</h2>
    
    <div class="sum-row">
      <span class="key" id="summary-subtotal-label">{{ __('Subtotal') }}</span>
      <span class="val" id="summary-subtotal">EGP 0</span>
    </div>
    
    <div class="sum-row">
      <span class="key">{{ __('Shipping') }}</span>
      <span class="val" style="color:#27AE60">{{ __('Calculated at checkout') }}</span>
    </div>
    
    <div class="sum-row total">
      <span class="key">{{ __('Total') }}</span>
      <span class="val" id="summary-total">EGP 0</span>
    </div>
    
    <a href="/checkout" class="btn-checkout" id="btn-checkout">
      <span>{{ __('Proceed to Checkout') }}</span>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
    
    <div class="secure-note">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      {{ __('Secure & encrypted checkout') }}
    </div>
    
    <div class="payment-icons">
      <span class="p-icon">{{ __('Visa') }}</span>
      <span class="p-icon">{{ __('Mastercard') }}</span>
      <span class="p-icon">{{ __('COD') }}</span>
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  Cart.updateBadge();
  initCartPage();

  async function initCartPage() {
    const clearBtn = document.getElementById('clear-cart-btn');
    if (clearBtn) clearBtn.addEventListener('click', clearCart);
    await renderCartPage();
  }

  async function renderCartPage() {
    let items = [];
    try {
      const res = await API.get('/cart');
      items = res.cart?.items || [];
    } catch(e) {
      items = [];
    }

    const container = document.getElementById('cart-page-container');
    const titleEl = document.getElementById('cart-title');

    if (items.length === 0) {
      if (titleEl) titleEl.textContent = "{{ __('Your Cart') }}";
      container.innerHTML = `
        <div class="empty-cart">
          <svg viewBox="0 0 24 24" width="80" height="80" stroke="#2C1F14" fill="none" stroke-width="1">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
          <h2>${"{{ __('Your cart is empty') }}"}</h2>
          <p>${"{{ __('Looks like you haven\\'t added anything to your cart yet.') }}"}</p>
          <a href="/shop" class="btn-dark" style="display:inline-block; background:#2C1F14; color:#fff; padding:16px 32px; border-radius:12px; text-decoration:none; font-weight:700;">${"{{ __('Continue Shopping') }}"}</a>
        </div>
      `;
      updateCartSummary([]);
      return;
    }

    if (titleEl) titleEl.textContent = "{{ __('Your Cart') }}" + " (" + items.length + ")";

    container.innerHTML = items.map(item => {
      const imgSrc = item.product?.image || '/img/placeholder.svg';
      const itemTotal = parseFloat(item.price) * (parseInt(item.quantity) || 1);
      return `
        <div class="cart-item" data-id="${item.id}">
          <div class="item-img">
            <img src="${imgSrc}" alt="${item.name}" onerror="this.src='/img/placeholder.svg'">
          </div>
          <div class="item-info">
            <a href="/product/${item.product?.slug}" class="item-name">${item.name}</a>
            <div class="item-meta">${item.variant || "{{ __('Standard') }}"}</div>
            <div class="qty-ctrl">
              <button class="qty-btn" onclick="window.cartPageQty(${item.id}, -1)">−</button>
              <span class="qty-num">${item.quantity}</span>
              <button class="qty-btn" onclick="window.cartPageQty(${item.id}, 1)">+</button>
            </div>
          </div>
          <div class="item-price-col">
            <button class="remove-btn" onclick="window.cartPageRemove(${item.id})" title="Remove item">&times;</button>
            <div class="item-price">EGP ${itemTotal.toLocaleString()}</div>
          </div>
        </div>
      `;
    }).join('');

    updateCartSummary(items);
  }

  window.cartPageQty = async function(itemId, delta) {
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
    await renderCartPage();
    Cart.updateBadge();
  };

  window.cartPageRemove = async function(itemId) {
    await Cart.remove(itemId);
    await renderCartPage();
    Cart.updateBadge();
  };

  async function clearCart() {
    if (!confirm("{{ __('Are you sure you want to clear your cart?') }}")) return;
    await Cart.clear();
    await renderCartPage();
    Cart.updateBadge();
  }

  function updateCartSummary(items) {
    const subtotal = items.reduce((s, i) => s + (parseFloat(i.price) * (parseInt(i.quantity) || 1)), 0);

    const subtotalEl = document.getElementById('summary-subtotal');
    const subtotalLabel = document.getElementById('summary-subtotal-label');
    const totalEl = document.getElementById('summary-total');

    if (subtotalEl) subtotalEl.textContent = 'EGP ' + subtotal.toLocaleString();
    if (subtotalLabel) subtotalLabel.textContent = "{{ __('Subtotal') }}" + " (" + items.length + " " + "{{ __('items') }}" + ")";
    if (totalEl) totalEl.textContent = 'EGP ' + subtotal.toLocaleString();
  }
})();
</script>
@endsection
