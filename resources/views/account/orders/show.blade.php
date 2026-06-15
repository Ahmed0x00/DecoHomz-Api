@extends('layouts.app')

@section('title', 'Order #' . ($order->order_number ?? $order->id) . ' — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="{{ asset_v('/css/order-confirmation.css') }}">
<style>
/* ── Extra Styles for Order Details ── */
.od-back {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-secondary);
  text-decoration: none;
  margin-bottom: 24px;
  transition: color 0.2s;
}
.od-back:hover { color: var(--color-primary); }
.od-back svg { width: 16px; height: 16px; }

.od-flash {
  padding: 16px 24px;
  border-radius: var(--radius-md);
  font-size: 14px;
  margin-bottom: 24px;
  font-weight: 500;
}
.od-flash-success { background: var(--color-success-bg); color: var(--color-success); border: 1px solid rgba(46, 204, 113, 0.2); }
.od-flash-error   { background: #fef0ee; color: #E74C3C; border: 1px solid rgba(231, 76, 60, 0.2); }

.refund-section {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: 32px;
  margin-bottom: 32px;
}
.refund-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--color-primary);
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.refund-title svg {
  width: 18px;
  height: 18px;
  stroke: var(--color-accent);
  fill: none;
}
.refund-text {
  font-size: 14px;
  color: var(--color-text-secondary);
  margin-bottom: 16px;
  line-height: 1.5;
}
.refund-textarea {
  width: 100%;
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  color: var(--color-text);
  font-size: 14px;
  padding: 12px 16px;
  resize: vertical;
  min-height: 80px;
  outline: none;
  margin-bottom: 12px;
  font-family: inherit;
}
.refund-textarea:focus { border-color: var(--color-accent); }
.btn-refund {
  background: var(--color-accent);
  color: #fff;
  border: none;
  padding: 10px 24px;
  border-radius: var(--radius-sm);
  font-weight: 600;
  cursor: pointer;
  font-size: 13px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  transition: background 0.2s;
}
.btn-refund:hover { background: var(--color-accent-dark); }
.refund-status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: var(--radius-full);
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.refund-pending  { background: rgba(234,179,8,.15);  color: #B45309; }
.refund-approved { background: rgba(34,197,94,.15);  color: #15803D; }
.refund-rejected { background: rgba(239,68,68,.15);  color: #B91C1C; }

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: var(--radius-full);
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.status-pending { background: rgba(234,179,8,.15); color: #B45309; }
.status-processing { background: rgba(59,130,246,.15); color: #1D4ED8; }
.status-shipped { background: rgba(139,92,246,.15); color: #6D28D9; }
.status-delivered { background: rgba(34,197,94,.15); color: #15803D; }
.status-cancelled { background: rgba(239,68,68,.15); color: #B91C1C; }

.od-banner {
  padding: 32px 40px;
}

.od-banner .confirm-title {
  margin-bottom: 12px;
}

.od-banner .status-badge {
  margin-top: 12px;
}

.od-banner-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-top: 20px;
  flex-wrap: wrap;
}
</style>
@endsection

@section('content')
<?php
$orderNumber = $order->order_number ?? $order->id;
$status = $order->status ?? 'pending';
$paymentStatus = $order->payment_status ?? 'unpaid';
$addr = $order->shippingAddress;
$items = $order->items ?? collect([]);
$coupon = $order->coupon;
$paymentMethod = $order->payment_method ?? 'cod';
$subtotal = $order->subtotal ?? 0;
$discount = $order->discount ?? 0;
$deliveryFee = $order->delivery_fee ?? 0;
$vatAmount = $order->vat_amount ?? 0;
$depositAmount = $order->deposit_amount ?? 0;
$total = $order->total ?? 0;
$itemCount = $items->sum('quantity') ?? 0;
$balanceDue = max(0, $total - $depositAmount);

// Payment method display
$paymentDisplay = match($paymentMethod) {
    'cod' => 'Cash on Delivery',
    'card' => 'Credit/Debit Card',
    'wallet' => 'Fawry Wallet',
    default => ucfirst($paymentMethod),
};

// Delivery fee display
$deliveryDisplay = $deliveryFee == 0 ? 'Free' : 'EGP ' . number_format($deliveryFee);

// Delivery estimate: 3-5 days from order
$orderDate = \Carbon\Carbon::parse($order->created_at);
$estStart = $orderDate->copy()->addDays(3)->format('M d');
$estEnd = $orderDate->copy()->addDays(6)->format('M d');
$estDelivery = "$estStart – $estEnd";

// Status steps
$steps = [
    ['key' => 'placed',    'label' => "Order\nPlaced",      'icon' => 'check'],
    ['key' => 'processing','label' => "Processing",           'icon' => 'doc'],
    ['key' => 'shipped',   'label' => "Shipped",             'icon' => 'truck'],
    ['key' => 'delivered', 'label' => "Delivered",           'icon' => 'home'],
];
$statusOrder = ['pending','confirmed','processing','shipped','delivered'];
$currentIdx = array_search($status, $statusOrder);
if ($currentIdx === false) $currentIdx = 0;

if ($status === 'cancelled') {
    $currentIdx = -1; // hide timeline progress
}

// Coupon discount for display
$couponDiscount = $coupon ? ($coupon->discount_amount ?? $coupon->discount ?? 0) : 0;

// Refund Status
$refundStatus = $order->refund_status ?? null;
$canRequestRefund = in_array($paymentStatus, ['paid_deposit', 'full_paid']) && is_null($refundStatus);

$refundBadgeClass = match($refundStatus) {
    'pending'  => 'refund-pending',
    'approved' => 'refund-approved',
    'rejected' => 'refund-rejected',
    default    => '',
};

$refundBadgeLabel = match($refundStatus) {
    'pending'  => 'Refund Pending',
    'approved' => 'Refund Approved',
    'rejected' => 'Refund Rejected',
    default    => '',
};

$statusBadgeClass = match($status) {
    'pending', 'confirmed' => 'status-pending',
    'processing' => 'status-processing',
    'shipped' => 'status-shipped',
    'delivered' => 'status-delivered',
    'cancelled' => 'status-cancelled',
    default => 'status-pending',
};
?>

<div class="confirm-wrap">

  <a href="/account" class="od-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
    Back to My Account
  </a>

  @if(session('success'))
    <div class="od-flash od-flash-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="od-flash od-flash-error">{{ session('error') }}</div>
  @endif

  {{-- Top Banner --}}
  <div class="success-banner od-banner">
    <div class="confirm-title">Order Details</div>
    <div class="order-num">Order ID: <span>#{{ $orderNumber }}</span></div>
    <div>
        <span class="status-badge {{ $statusBadgeClass }}">{{ ucfirst($status) }}</span>
    </div>
  </div>

  {{-- Info Cards --}}
  <div class="confirm-grid">
    {{-- Delivery Address --}}
    <div class="info-card">
      <div class="card-title">
        <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
          <circle cx="12" cy="10" r="3" />
        </svg>
        Delivery Address
      </div>
      @if($addr)
      <div class="info-row"><span class="key">Name</span><span class="val">{{ $addr->first_name }} {{ $addr->last_name }}</span></div>
      <div class="info-row"><span class="key">Phone</span><span class="val">{{ $addr->phone }}</span></div>
      <div class="info-row"><span class="key">Address</span><span class="val">{{ $addr->address_line_1 }}{{ $addr->address_line_2 ? ', ' . $addr->address_line_2 : '' }}</span></div>
      <div class="info-row"><span class="key">City</span><span class="val">{{ $addr->city }}{{ $addr->state ? ', ' . $addr->state : '' }}, {{ $addr->country }}</span></div>
      @if($addr->postal_code)
      <div class="info-row"><span class="key">Postal Code</span><span class="val">{{ $addr->postal_code }}</span></div>
      @endif
      @else
      <div class="info-row"><span class="val" style="color:#888">No address on file</span></div>
      @endif
    </div>

    {{-- Payment Details --}}
    <div class="info-card">
      <div class="card-title">
        <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
          <rect x="1" y="4" width="22" height="16" rx="2" />
          <line x1="1" y1="10" x2="23" y2="10" />
        </svg>
        Payment Details
      </div>
      <div class="info-row"><span class="key">Method</span><span class="val">{{ $paymentDisplay }}</span></div>
      <div class="info-row"><span class="key">Status</span><span class="val">{{ ucfirst(str_replace('_', ' ', $paymentStatus)) }}</span></div>
      <div class="info-row"><span class="key">Subtotal</span><span class="val">EGP {{ number_format($subtotal) }}</span></div>
      @if($couponDiscount > 0)
      <div class="info-row">
        <span class="key">Coupon ({{ $coupon->code ?? 'DISCOUNT' }})</span>
        <span class="val" style="color:#c0392b">− EGP {{ number_format($couponDiscount) }}</span>
      </div>
      @elseif($discount > 0)
      <div class="info-row">
        <span class="key">Discount</span>
        <span class="val" style="color:#c0392b">− EGP {{ number_format($discount) }}</span>
      </div>
      @endif
      <div class="info-row"><span class="key">Delivery</span><span class="val" style="color:#4A7C3F">{{ $deliveryDisplay }}</span></div>
      @if($vatAmount > 0)
      <div class="info-row"><span class="key">VAT (14%)</span><span class="val">EGP {{ number_format($vatAmount) }}</span></div>
      @endif
      @if($depositAmount > 0)
      <div class="info-row"><span class="key">Deposit Required</span><span class="val" style="color:#92400e">EGP {{ number_format($depositAmount) }}</span></div>
      <div class="info-row"><span class="key">Balance Due</span><span class="val" style="color:#c0392b">EGP {{ number_format($balanceDue) }}</span></div>
      @endif
      <div class="info-row" style="padding-top:8px;border-top:1px solid #F5F0E8;margin-top:4px">
        <span class="key" style="font-weight:700;color:#2C1F14">Total</span>
        <span class="val" style="font-size:15px">EGP {{ number_format($total) }}</span>
      </div>
    </div>
  </div>

  @if(!empty($order->notes))
  <div class="info-card" style="margin-bottom: 32px;">
    <div class="card-title">
      <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
        <polyline points="14 2 14 8 20 8" />
        <line x1="16" y1="13" x2="8" y2="13" />
        <line x1="16" y1="17" x2="8" y2="17" />
        <polyline points="10 9 9 9 8 9" />
      </svg>
      Order Notes
    </div>
    <div style="font-size: 14px; color: var(--color-text); line-height: 1.6; white-space: pre-line;">{{ $order->notes }}</div>
  </div>
  @endif

  @if($status !== 'cancelled')
  {{-- Timeline --}}
  <div class="timeline">
    <div class="tl-title">
      <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
        <rect x="1" y="3" width="15" height="13" rx="1" />
        <path d="M16 8h4l3 5v3h-7V8z" />
        <circle cx="5.5" cy="18.5" r="2.5" />
        <circle cx="18.5" cy="18.5" r="2.5" />
      </svg>
      Delivery Status
    </div>
    <div class="tl-steps">
      @foreach($steps as $i => $step)
        <?php
          $stepIdx = $i;
          $isDone = $stepIdx < $currentIdx;
          $isActive = $stepIdx === $currentIdx;
          $dotClass = $isDone ? 'done' : ($isActive ? 'active' : '');
          $labelClass = $isDone || $isActive ? 'done' : '';
        ?>
        <div class="tl-step">
          <div class="tl-dot {{ $dotClass }}">
            @if($isDone)
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            @elseif($isActive)
              @if($step['icon'] === 'doc')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
              @elseif($step['icon'] === 'truck')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
              @elseif($step['icon'] === 'home')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              @else
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
              @endif
            @else
              <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
                @if($step['icon'] === 'truck')
                  <rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                @elseif($step['icon'] === 'home')
                  <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                @else
                  <circle cx="12" cy="12" r="10"/>
                @endif
              </svg>
            @endif
          </div>
          <div class="tl-label {{ $labelClass }}">{!! nl2br(e($step['label'])) !!}</div>
        </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Order Items --}}
  <div class="order-items">
    <div class="items-title">
      <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
        <line x1="3" y1="6" x2="21" y2="6" />
        <path d="M16 10a4 4 0 0 1-8 0" />
      </svg>
      Order Items ({{ $itemCount }})
    </div>
    @foreach($items as $item)
    <div class="order-item">
      <div class="item-thumb">
        <img src="{{ $item->product?->primaryImage?->url ?? '/img/placeholder.svg' }}"
             alt="{{ $item->product?->name ?? 'Product' }}"
             onerror="this.src='/img/placeholder.svg'"
             style="width:100%;height:100%;object-fit:cover;border-radius:6px">
      </div>
      <div>
        <div class="item-name">{{ $item->product?->name ?? $item->name ?? 'Product' }}</div>
        @if($item->variant && $item->variant !== 'Standard')
        <div class="item-meta">{{ $item->variant }}</div>
        @endif
        <div class="item-meta">Qty: {{ $item->quantity }}</div>
      </div>
      <div class="item-price">EGP {{ number_format($item->total ?? ($item->price * $item->quantity)) }}</div>
    </div>
    @endforeach
  </div>

  {{-- Summary Bar --}}
  <div class="summary-bar">
    <div class="sum-item">
      <div class="sum-label">Order Total</div>
      <div class="sum-val gold">EGP {{ number_format($total) }}</div>
    </div>
    <div class="sum-divider"></div>
    <div class="sum-item">
      <div class="sum-label">Est. Delivery</div>
      <div class="sum-val">{{ $estDelivery }}</div>
    </div>
    <div class="sum-divider"></div>
    <div class="sum-item">
      <div class="sum-label">Items</div>
      <div class="sum-val">{{ $itemCount }} piece{{ $itemCount !== 1 ? 's' : '' }}</div>
    </div>
    @if(($couponDiscount ?? $discount) > 0)
    <div class="sum-divider"></div>
    <div class="sum-item">
      <div class="sum-label">You Saved</div>
      <div class="sum-val" style="color:#7BC67E">EGP {{ number_format($couponDiscount ?: $discount) }}</div>
    </div>
    @endif
  </div>

  {{-- Refund Section --}}
  <div class="refund-section">
    @if($canRequestRefund)
      <div class="refund-title">
        <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor"><path d="M3 10h11a8 8 0 0 1 0 16H8"/><polyline points="3 10 9 4 9 16"/></svg>
        Request a Refund
      </div>
      <div class="refund-text">Your order is eligible for a refund. Please share your reason below and our team will review it shortly.</div>
      <textarea id="customerRefundReason" class="refund-textarea" placeholder="Please describe the reason for your refund request…" maxlength="500"></textarea>
      <button type="button" class="btn-refund" onclick="submitCustomerRefund()">
        Submit Request
      </button>
      <div id="customerRefundMsg" style="margin-top: 10px; font-size: 13px;"></div>
    @elseif($refundStatus)
      <div class="refund-title">
        <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M9 12l2 2 4-4"/></svg>
        Refund Status
      </div>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <span class="refund-status-badge {{ $refundBadgeClass }}">{{ $refundBadgeLabel }}</span>
        @if($order->refund_handled_at)
          <span style="font-size:13px;color:var(--color-text-faint)">
            {{ \Carbon\Carbon::parse($order->refund_handled_at)->format('M j, Y') }}
          </span>
        @endif
      </div>
      @if($order->refund_reason)
        <div class="refund-text" style="margin-bottom:4px;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Your Reason</div>
        <div class="refund-text">{{ $order->refund_reason }}</div>
      @endif
    @else
      <div class="refund-title">
        <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        Returns & Refunds
      </div>
      <div class="refund-text" style="margin-bottom:0;">Refunds are available for orders with a confirmed payment. If you believe your paid order isn't showing the refund option, please contact our support team directly via WhatsApp: <strong>+20 103 774 3273</strong>.</div>
    @endif
  </div>

</div>
@endsection

@section('extra_js')
<script>
(function () { 
  if (typeof Cart !== 'undefined' && Cart.updateBadge) {
    Cart.updateBadge(); 
  }
})();

function submitCustomerRefund() {
  var reason = document.getElementById('customerRefundReason').value.trim();
  var msg    = document.getElementById('customerRefundMsg');

  if (!reason) {
    msg.textContent  = 'Please enter a reason for your refund request.';
    msg.style.color  = '#E74C3C';
    return;
  }
  if (!confirm('Are you sure you want to request a refund for this order?')) return;

  msg.textContent = 'Submitting…';
  msg.style.color = 'var(--color-text-secondary)';

  var fd = new FormData();
  fd.append('reason', reason);

  fetch('/account/orders/{{ $order->id }}/refund', {
    method : 'POST',
    headers: {
      'Accept'       : 'application/json',
      'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      'X-Session-ID' : localStorage.getItem('dh_session_id') ?? '',
      'Authorization': 'Bearer ' + (localStorage.getItem('dh_token') ?? ''),
    },
    body: fd,
  })
  .then(res => {
    if (res.status === 401 || res.status === 403) {
      msg.textContent = 'Access denied. Please make sure you are logged in.';
      msg.style.color = '#E74C3C';
      return null;
    }
    return res.json().catch(() => ({}));
  })
  .then(data => {
    if (!data) return;
    if (data.error) {
      msg.textContent = data.error;
      msg.style.color = '#E74C3C';
    } else {
      msg.textContent = 'Refund request submitted successfully.';
      msg.style.color = '#15803D';
      document.getElementById('customerRefundReason').value = '';
      setTimeout(() => location.reload(), 1500);
    }
  })
  .catch(() => {
    msg.textContent = 'Failed to submit refund request. Please try again.';
    msg.style.color = '#E74C3C';
  });
}
</script>
@endsection