@extends('layouts.app')

@section('title', 'Your Cart — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/cart.css">
@endsection

@section('content')

<div class="steps">
  <div class="step done">
    <div class="step-num">✓</div>
    <div class="step-name">Cart</div>
  </div>
  <div class="step-line done"></div>
  <div class="step active">
    <div class="step-num">2</div>
    <div class="step-name">Review</div>
  </div>
  <div class="step-line"></div>
  <div class="step inactive">
    <div class="step-num">3</div>
    <div class="step-name">Shipping</div>
  </div>
  <div class="step-line"></div>
  <div class="step inactive">
    <div class="step-num">4</div>
    <div class="step-name">Payment</div>
  </div>
</div>

<div class="cart-layout">
  <div class="cart-items">
    <div class="cart-header">
      <div class="cart-title" id="cart-title">Your Cart</div>
      <button class="clear-btn" id="clear-cart-btn">Clear all</button>
    </div>
    <div id="cart-page-container">
      <!-- Dynamic items rendered by JS -->
    </div>

    <div class="coupon-row">
      <input type="text" id="coupon-input" placeholder="Enter coupon code">
      <button id="apply-coupon">Apply</button>
    </div>
  </div>

  <div class="summary">
    <div class="summary-title">Order Summary</div>
    <div class="sum-row"><span class="key" id="summary-subtotal-label">Subtotal</span><span class="val" id="summary-subtotal">EGP 0</span></div>
    <div class="sum-row discount"><span class="key">Discount</span><span class="val" id="summary-discount">EGP 0</span></div>
    <div class="sum-row"><span class="key">Delivery</span><span class="val" id="summary-delivery" style="color:#3B6D11">Free</span></div>
    <div class="sum-row total"><span class="key">Total</span><span class="val" id="summary-total">EGP 0</span></div>
    <a href="/checkout" class="btn-checkout" id="btn-checkout">Proceed to Checkout</a>
    <div class="secure-note">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
      </svg>
      Secure & encrypted checkout
    </div>
    <div class="payment-icons">
      <div class="p-icon">VISA</div>
      <div class="p-icon">MC</div>
      <div class="p-icon">Fawry</div>
      <div class="p-icon">COD</div>
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  Cart.updateBadge();
  initCartPage();

  // ── Init ─────────────────────────────────────────────────────
  async function initCartPage() {
    document.getElementById('clear-cart-btn').addEventListener('click', clearCart);
    document.getElementById('apply-coupon').addEventListener('click', applyCoupon);
    await renderCartPage();
  }

  // ── Main render ─────────────────────────────────────────────
  async function renderCartPage() {
    let items = Cart.get();

    // Merge with API cart if logged in
    if (Auth.token()) {
      try {
        const res = await API.get('/cart');
        const serverItems = res.data?.items || res.items || [];
        if (serverItems.length > 0) {
          // API cart takes priority; add local items not on server
          const serverIds = new Set(serverItems.map(i => i.id));
          const localOnly = items.filter(i => !serverIds.has(i.id));
          items = [...serverItems.map(i => ({
            id: i.id,
            name: i.name,
            price: parseFloat(i.price),
            quantity: parseInt(i.quantity),
            variant: i.variant || 'Standard',
            image: i.image || null
          })), ...localOnly];
        }
      } catch(e) {}
    }

    const container = document.getElementById('cart-page-container');
    const titleEl = document.getElementById('cart-title');

    if (items.length === 0) {
      if (titleEl) titleEl.textContent = 'Your Cart';
      container.innerHTML = `
        <div style="text-align:center; padding:60px; color:#888">
          <svg viewBox="0 0 24 24" width="64" stroke="#C4A882" fill="none" stroke-width="1.5">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
          <p style="margin-top:16px; font-size:16px; color:#2C1F14">Your cart is empty</p>
          <a href="/shop" class="btn-dark" style="display:inline-block; margin-top:16px; background:#2C1F14; color:#fff; padding:10px 20px; border-radius:4px; text-decoration:none;">Browse Products</a>
        </div>
      `;
      updateCartSummary([]);
      return;
    }

    if (titleEl) titleEl.textContent = `Your Cart (${items.length} item${items.length > 1 ? 's' : ''})`;

    container.innerHTML = items.map((item, i) => `
      <div class="cart-item" data-index="${i}">
        <div class="item-img">
          ${item.image
            ? `<img src="${item.image}" alt="${item.name}" onerror="this.style.display='none'">`
            : `<svg viewBox="0 0 80 80" fill="none">
                <rect x="10" y="30" width="60" height="30" rx="5" fill="#C4A882"/>
                <rect x="18" y="20" width="10" height="20" rx="3" fill="#A07858"/>
                <rect x="52" y="20" width="10" height="20" rx="3" fill="#A07858"/>
              </svg>`
          }
        </div>
        <div class="item-info">
          <div class="item-name">${item.name}</div>
          <div class="item-meta">${item.variant || 'Standard'}</div>
          <div class="qty-ctrl">
            <button class="qty-btn" onclick="updateCartQty(${i}, -1)">−</button>
            <span class="qty-num">${item.quantity}</span>
            <button class="qty-btn" onclick="updateCartQty(${i}, 1)">+</button>
          </div>
        </div>
        <div class="item-price-col">
          <button class="remove-btn" onclick="removeCartItem(${i})">×</button>
          <div class="item-price">EGP ${(item.price * item.quantity).toLocaleString()}</div>
        </div>
      </div>
    `).join('');

    updateCartSummary(items);
  }

  // ── Update quantity ─────────────────────────────────────────
  window.updateCartQty = async function(index, delta) {
    const items = Cart.get();
    if (items.length === 0) return;

    // Sync with server if logged in
    if (Auth.token()) {
      const item = items[index];
      if (item && item.id) {
        try {
          await API.put('/cart/items/' + item.id, {
            quantity: Math.max(0, (item.quantity || 1) + delta)
          });
        } catch(e) {}
      }
    }

    items[index].quantity += delta;
    if (items[index].quantity < 1) items.splice(index, 1);
    Cart.set(items);
    await renderCartPage();
  };

  // ── Remove item ─────────────────────────────────────────────
  window.removeCartItem = async function(index) {
    const items = Cart.get();
    const item = items[index];
    if (!item) return;

    if (Auth.token() && item.id) {
      try {
        await API.del('/cart/items/' + item.id);
      } catch(e) {}
    }

    items.splice(index, 1);
    Cart.set(items);
    await renderCartPage();
  };

  // ── Clear cart ─────────────────────────────────────────────
  async function clearCart() {
    if (!confirm('Are you sure you want to clear your cart?')) return;
    if (Auth.token()) {
      try {
        await API.del('/cart');
      } catch(e) {}
    }
    Cart.set([]);
    await renderCartPage();
  }

  // ── Apply coupon ────────────────────────────────────────────
  document.getElementById('apply-coupon').onclick = async function() {
    const code = document.getElementById('coupon-input').value.trim();
    if (!code) return;
    try {
      const res = await API.post('/cart/coupon', { code });
      showToast('Coupon applied: ' + (res.data?.coupon?.code || code));
      await renderCartPage();
    } catch(e) {
      showToast(e.data?.message || 'Invalid coupon code');
    }
  };

  // ── Update summary ─────────────────────────────────────────
  function updateCartSummary(items) {
    const subtotal = items.reduce((s, i) => s + (parseFloat(i.price) * (parseInt(i.quantity) || 1)), 0);
    const discount = 0;

    document.getElementById('summary-subtotal').textContent = 'EGP ' + subtotal.toLocaleString();
    document.getElementById('summary-subtotal-label').textContent = 'Subtotal' + (items.length ? ` (${items.length} item${items.length > 1 ? 's' : ''})` : '');
    document.getElementById('summary-discount').textContent = 'EGP ' + discount.toLocaleString();
    document.getElementById('summary-total').textContent = 'EGP ' + subtotal.toLocaleString();
  }
})();
</script>
@endsection
