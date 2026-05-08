@extends('layouts.app')

@section('title', 'Order Confirmed — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/order-confirmation.css">
@endsection

@section('content')
<?php
$orderNumber = $order->order_number ?? $order->id;
$status = $order->status ?? 'pending';
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

// Coupon discount for display
$couponDiscount = $coupon ? ($coupon->discount_amount ?? $coupon->discount ?? 0) : 0;
?>

<div class="confirm-wrap">

  {{-- Success Banner --}}
  <div class="success-banner">
    <div class="check-circle">
      <svg viewBox="0 0 24 24">
        <polyline points="20 6 9 17 4 12" />
      </svg>
    </div>
    <div class="confirm-title">Order Placed Successfully!</div>
    <div class="confirm-sub">Thank you! We've received your order and will start processing it shortly.</div>
    <div class="order-num">Order ID: <span>#{{ $orderNumber }}</span></div>
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
        <div class="item-name">{{ $item->product?->name ?? 'Product' }}</div>
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

  {{-- CTA Buttons --}}
  <div class="cta-row">
    <button class="btn-outline" onclick="location.href='/account'">Track Order</button>
    <button class="btn-dark" onclick="location.href='/account'">My Orders</button>
    <button class="btn-gold" onclick="location.href='/shop'">Continue Shopping</button>
  </div>

</div>
@endsection

@section('extra_js')
<script>
(function() {
  Cart.updateBadge();
})();
</script>
@endsection
