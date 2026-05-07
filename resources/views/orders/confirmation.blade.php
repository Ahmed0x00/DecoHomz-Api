@extends('layouts.app')

@section('title', 'Order Confirmed — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/order-confirmation.css">
@endsection

@section('content')

<input type="hidden" id="order-id" value="{{ $orderId ?? '' }}">

<div class="confirm-wrap">

  <!-- Success Banner -->
  <div class="success-banner">
    <div class="check-circle">
      <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <div class="confirm-title">Order Placed Successfully!</div>
    <div class="confirm-sub" id="confirm-sub">Thank you! We've received your order and will start processing it shortly.</div>
    <div class="order-num" id="confirm-order-id">Order ID: <span>#—</span></div>
  </div>

  <!-- Info Cards -->
  <div class="confirm-grid">
    <div class="info-card">
      <div class="card-title">
        <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        Delivery Address
      </div>
      <div id="addr-container">
        <div class="info-row"><span class="key">Name</span><span class="val" id="addr-name">—</span></div>
        <div class="info-row"><span class="key">Phone</span><span class="val" id="addr-phone">—</span></div>
        <div class="info-row"><span class="key">Address</span><span class="val" id="addr-line">—</span></div>
        <div class="info-row"><span class="key">City</span><span class="val" id="addr-city">—</span></div>
      </div>
    </div>
    <div class="info-card">
      <div class="card-title">
        <svg viewBox="0 0 24 24" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        Payment Details
      </div>
      <div id="payment-container">
        <div class="info-row"><span class="key">Method</span><span class="val" id="pay-method">—</span></div>
        <div class="info-row"><span class="key">Subtotal</span><span class="val" id="pay-subtotal">—</span></div>
        <div class="info-row"><span class="key">Discount</span><span class="val" id="pay-discount" style="color:#c0392b">EGP 0</span></div>
        <div class="info-row"><span class="key">Delivery</span><span class="val" id="pay-delivery" style="color:#4A7C3F">Free</span></div>
        <div class="info-row" style="padding-top:8px;border-top:1px solid #F5F0E8;margin-top:4px">
          <span class="key" style="font-weight:700;color:#2C1F14">Total Paid</span>
          <span class="val" id="pay-total" style="font-size:15px">EGP 0</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Delivery Timeline -->
  <div class="timeline">
    <div class="tl-title">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <rect x="1" y="3" width="15" height="13" rx="1"/>
        <path d="M16 8h4l3 5v3h-7V8z"/>
        <circle cx="5.5" cy="18.5" r="2.5"/>
        <circle cx="18.5" cy="18.5" r="2.5"/>
      </svg>
      Delivery Status
    </div>
    <div class="tl-steps">
      <div class="tl-step" id="tl-placed">
        <div class="tl-dot done" id="tl-dot-0">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="tl-label done" id="tl-label-0">Order<br>Placed</div>
      </div>
      <div class="tl-step" id="tl-processing">
        <div class="tl-dot active" id="tl-dot-1">
          <svg viewBox="0 0 24 24" stroke-width="2">
            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="1"/>
          </svg>
        </div>
        <div class="tl-label active" id="tl-label-1">Processing</div>
      </div>
      <div class="tl-step" id="tl-shipped">
        <div class="tl-dot" id="tl-dot-2">
          <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div class="tl-label" id="tl-label-2">Shipped</div>
      </div>
      <div class="tl-step" id="tl-delivered">
        <div class="tl-dot" id="tl-dot-3">
          <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <div class="tl-label" id="tl-label-3">Delivered</div>
      </div>
    </div>
  </div>

  <!-- Items Ordered -->
  <div id="confirm-items-container" class="order-items"></div>

  <!-- Summary Bar -->
  <div class="summary-bar">
    <div class="sum-item">
      <div class="sum-label">Order Total</div>
      <div class="sum-val gold" id="summary-total">EGP 0</div>
    </div>
    <div class="sum-divider"></div>
    <div class="sum-item">
      <div class="sum-label">Est. Delivery</div>
      <div class="sum-val" id="summary-delivery">—</div>
    </div>
    <div class="sum-divider"></div>
    <div class="sum-item">
      <div class="sum-label">Items</div>
      <div class="sum-val" id="summary-items">0 pieces</div>
    </div>
    <div class="sum-divider"></div>
    <div class="sum-item">
      <div class="sum-label">You Saved</div>
      <div class="sum-val" id="summary-saved" style="color:#7BC67E">EGP 0</div>
    </div>
  </div>

  <!-- CTA Buttons -->
  <div class="cta-row">
    <button class="btn-outline" id="btn-track">Track Order</button>
    <button class="btn-dark" id="btn-orders">My Orders</button>
    <button class="btn-gold" id="btn-continue">Continue Shopping</button>
  </div>

</div>

@endsection

@section('extra_js')
<script>
(function() {
  Cart.updateBadge();

  document.getElementById('btn-orders').onclick = () => location.href = '/account';
  document.getElementById('btn-continue').onclick = () => location.href = '/shop';

  const orderId = document.getElementById('order-id')?.value;
  if (!orderId) {
    showError();
    return;
  }

  loadOrder(orderId);

  async function loadOrder(id) {
    try {
      const res = await API.get('/orders/' + id);
      const order = res.data?.order || res.order;
      if (order) {
        renderConfirmation(order);
      } else {
        showError();
      }
    } catch(e) {
      showError();
    }
  }

  function renderConfirmation(order) {
    // Order ID
    const idSpan = document.querySelector('#confirm-order-id span');
    if (idSpan) idSpan.textContent = '#' + (order.order_number || order.id);

    // Subtitle
    const subEl = document.getElementById('confirm-sub');
    if (subEl) subEl.textContent = "Thank you! We've received your order and will start processing it shortly.";

    // Address
    const addr = order.shipping_address || {};
    const setAddr = (id, val) => { const el = document.getElementById(id); if (el && val) el.textContent = val; };
    setAddr('addr-name', [addr.first_name, addr.last_name].filter(Boolean).join(' '));
    setAddr('addr-phone', addr.phone || '—');
    setAddr('addr-line', addr.address_line_1 || '—');
    setAddr('addr-city', [addr.city, addr.state, addr.country].filter(Boolean).join(', '));

    // Payment
    const methodMap = { cod: 'Cash on Delivery', card: 'Credit / Debit Card', fawry: 'Fawry' };
    const payMethod = document.getElementById('pay-method');
    if (payMethod) payMethod.textContent = methodMap[order.payment_method] || order.payment_method || '—';

    const items = order.items || [];
    const subtotal = items.reduce((s, i) => s + (parseFloat(i.price) || 0) * (parseInt(i.quantity) || 1), 0);
    const discount = parseFloat(order.discount || 0);
    const deliveryFee = parseFloat(order.delivery_fee || 0);
    const total = parseFloat(order.total || subtotal);

    const setPay = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setPay('pay-subtotal', 'EGP ' + subtotal.toLocaleString());
    setPay('pay-discount', discount > 0 ? '− EGP ' + discount.toLocaleString() : 'EGP 0');
    setPay('pay-delivery', deliveryFee === 0 ? 'Free' : 'EGP ' + deliveryFee.toLocaleString());
    setPay('pay-total', 'EGP ' + total.toLocaleString());

    // Summary bar
    const saved = discount;
    const setSum = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setSum('summary-total', 'EGP ' + total.toLocaleString());
    setSum('summary-items', items.length + ' piece' + (items.length !== 1 ? 's' : ''));
    setSum('summary-saved', discount > 0 ? 'EGP ' + discount.toLocaleString() : 'EGP 0');
    setSum('summary-delivery', order.estimated_delivery || '5–7 business days');

    // Timeline
    const statusOrder = ['placed', 'processing', 'shipped', 'delivered'];
    const statusMap = { pending: 0, placed: 0, processing: 1, shipped: 2, delivered: 3, completed: 3 };
    const currentIdx = statusMap[order.status?.toLowerCase()] ?? 0;

    for (let i = 0; i < 4; i++) {
      const dot = document.getElementById('tl-dot-' + i);
      const label = document.getElementById('tl-label-' + i);
      if (!dot || !label) continue;
      dot.classList.remove('done', 'active');
      label.classList.remove('done', 'active');

      if (i < currentIdx) {
        dot.classList.add('done');
        label.classList.add('done');
      } else if (i === currentIdx) {
        dot.classList.add('active');
        label.classList.add('active');
      }
    }

    // Items
    const container = document.getElementById('confirm-items-container');
    if (container) {
      container.innerHTML = `
        <div class="items-title">
          <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="23" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Items Ordered (${items.length})
        </div>
        ${items.map(item => `
          <div class="order-item">
            <div class="item-thumb">
              ${item.image
                ? `<img src="${item.image}" alt="${item.name}" style="width:72%;height:72%;object-fit:contain" onerror="this.style.display='none'">`
                : `<svg viewBox="0 0 80 80" fill="none">
                    <rect x="10" y="30" width="60" height="30" rx="5" fill="#C4A882"/>
                    <rect x="18" y="20" width="10" height="20" rx="3" fill="#A07858"/>
                    <rect x="52" y="20" width="10" height="20" rx="3" fill="#A07858"/>
                  </svg>`
              }
            </div>
            <div>
              <div class="item-name">${item.name}</div>
              <div class="item-meta">${item.variant || 'Standard'} · Qty: ${item.quantity}</div>
            </div>
            <div class="item-price">EGP ${((parseFloat(item.price) || 0) * (parseInt(item.quantity) || 1)).toLocaleString()}</div>
          </div>
        `).join('')}
      `;
    }
  }

  function showError() {
    const wrap = document.querySelector('.confirm-wrap');
    if (wrap) {
      wrap.innerHTML = `
        <div class="success-banner" style="padding:48px">
          <div class="confirm-title" style="color:#c0392b">Order Not Found</div>
          <div class="confirm-sub">We couldn't load your order details. Please check your order history.</div>
          <a href="/account" style="display:inline-block;margin-top:20px;background:#2C1F14;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none">View My Orders</a>
        </div>
      `;
    }
  }
})();
</script>
@endsection
