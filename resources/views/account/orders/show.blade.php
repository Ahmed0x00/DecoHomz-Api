@extends('layouts.app')
@section('title', 'Order #' . ($order->order_number ?? $order->id) . ' — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/account.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ── Reset & Base ─────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }

:root {
  --cream:       #FAF7F2;
  --cream-mid:   #F2EDE4;
  --cream-dark:  #E8DFD0;
  --sand:        #D4C4A8;
  --gold:        #B8860B;
  --gold-light:  #C9A84C;
  --ink:         #1C1209;
  --ink-mid:     #3D2B14;
  --ink-soft:    #6B5340;
  --ink-muted:   #9C876E;
  --white:       #FFFFFF;
  --radius-sm:   8px;
  --radius-md:   14px;
  --radius-lg:   20px;
  --shadow-sm:   0 1px 4px rgba(0,0,0,.06);
  --shadow-md:   0 4px 16px rgba(0,0,0,.08);
  --shadow-lg:   0 12px 40px rgba(0,0,0,.10);
}

/* ── Page Wrapper ─────────────────────────────────────── */
.od-wrap {
  max-width: 960px;
  margin: 0 auto;
  padding: 40px 24px 80px;
  font-family: 'DM Sans', sans-serif;
  color: var(--ink);
  background: var(--cream);
  min-height: 100vh;
}

/* ── Back Link ────────────────────────────────────────── */
.od-back {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--ink-soft);
  text-decoration: none;
  margin-bottom: 32px;
  transition: color .2s;
}
.od-back:hover { color: var(--gold); }
.od-back svg { width: 14px; height: 14px; }

/* ── Flash Messages ───────────────────────────────────── */
.od-flash {
  padding: 14px 18px;
  border-radius: var(--radius-sm);
  font-size: 13px;
  margin-bottom: 20px;
  font-family: 'DM Sans', sans-serif;
}
.od-flash-success { background: #e6f5ee; color: #1a6640; border-left: 3px solid #2ECC71; }
.od-flash-error   { background: #fef0ee; color: #8B1A1A; border-left: 3px solid #E74C3C; }

/* ── Order Hero Header ────────────────────────────────── */
.od-hero {
  background: var(--ink);
  border-radius: var(--radius-lg);
  padding: 28px 32px 28px;
  margin-bottom: 24px;
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  gap: 24px;
  position: relative;
  overflow: hidden;
}
.od-hero::before {
  content: '';
  position: absolute;
  top: -60px; right: -60px;
  width: 240px; height: 240px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(184,134,11,.18) 0%, transparent 70%);
  pointer-events: none;
}
.od-hero-left {
  display: flex;
  flex-direction: column;
  gap: 0;
  min-width: 0; /* allow text truncation */
}
.od-hero-eyebrow {
  font-family: 'DM Sans', sans-serif;
  font-size: 10px;
  font-weight: 500;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: rgba(255,255,255,.35);
  margin-bottom: 6px;
}
.od-hero-number {
  font-family: 'DM Sans', sans-serif;
  font-size: clamp(22px, 4vw, 34px);
  font-weight: 700;
  color: var(--white);
  line-height: 1.1;
  margin-bottom: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.od-hero-badges {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
}

.od-hero-right {
  text-align: right;
  flex-shrink: 0;
}
.od-hero-total-label {
  font-size: 10px;
  font-weight: 500;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgba(255,255,255,.35);
  margin-bottom: 6px;
}
.od-hero-total {
  font-family: 'DM Sans', sans-serif;
  font-size: clamp(26px, 3.5vw, 36px);
  font-weight: 700;
  color: var(--gold-light);
  line-height: 1;
  white-space: nowrap;
}

/* ── Status Badges ────────────────────────────────────── */
/* Light-surface badges (meta bar, cards) */
.od-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 12px;
  border-radius: 50px;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  white-space: nowrap;
}
.od-badge::before {
  content: '';
  width: 5px; height: 5px;
  border-radius: 50%;
  background: currentColor;
  opacity: 0.7;
  flex-shrink: 0;
}
.badge-pending    { background: rgba(234,179,8,.12);   color: #B45309; }
.badge-processing { background: rgba(59,130,246,.12);  color: #1D4ED8; }
.badge-shipped    { background: rgba(139,92,246,.12);  color: #6D28D9; }
.badge-delivered  { background: rgba(34,197,94,.12);   color: #15803D; }
.badge-cancelled  { background: rgba(239,68,68,.12);   color: #B91C1C; }
.badge-unpaid     { background: rgba(239,68,68,.12);   color: #B91C1C; }
.badge-paid_deposit { background: rgba(234,179,8,.12); color: #B45309; }
.badge-full_paid  { background: rgba(34,197,94,.12);   color: #15803D; }
.badge-refunded   { background: rgba(139,92,246,.12);  color: #6D28D9; }

/* Dark-surface badge overrides (inside .od-hero) */
.od-hero .badge-pending    { background: rgba(234,179,8,.2);  color: #FCD34D; }
.od-hero .badge-processing { background: rgba(59,130,246,.2); color: #93C5FD; }
.od-hero .badge-shipped    { background: rgba(139,92,246,.2); color: #C4B5FD; }
.od-hero .badge-delivered  { background: rgba(34,197,94,.2);  color: #86EFAC; }
.od-hero .badge-cancelled  { background: rgba(239,68,68,.2);  color: #FCA5A5; }
.od-hero .badge-unpaid     { background: rgba(239,68,68,.2);  color: #FCA5A5; }
.od-hero .badge-paid_deposit { background: rgba(234,179,8,.2); color: #FCD34D; }
.od-hero .badge-full_paid  { background: rgba(34,197,94,.2);  color: #86EFAC; }
.od-hero .badge-refunded   { background: rgba(139,92,246,.2); color: #C4B5FD; }

/* ── Meta Bar ─────────────────────────────────────────── */
.od-meta {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1px;
  background: var(--cream-dark);
  border: 1px solid var(--cream-dark);
  border-radius: var(--radius-md);
  overflow: hidden;
  margin-bottom: 24px;
}
.od-meta-item {
  background: var(--white);
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.od-meta-label {
  font-size: 10px;
  font-weight: 500;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--ink-muted);
}
.od-meta-value {
  font-size: 14px;
  font-weight: 500;
  color: var(--ink);
  line-height: 1.4;
}

/* ── Card ─────────────────────────────────────────────── */
.od-card {
  background: var(--white);
  border: 1px solid var(--cream-dark);
  border-radius: var(--radius-md);
  overflow: hidden;
  margin-bottom: 16px;
  box-shadow: var(--shadow-sm);
}
.od-card-head {
  padding: 14px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--cream);
  border-bottom: 1px solid var(--cream-dark);
}
.od-card-title {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--ink-soft);
}
.od-card-body { padding: 24px; }

/* ── Items Table ──────────────────────────────────────── */
.items-tbl { width: 100%; border-collapse: collapse; }
.items-tbl th {
  text-align: left;
  padding: 10px 16px;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--ink-muted);
  background: var(--cream);
  border-bottom: 1px solid var(--cream-dark);
}
.items-tbl th:last-child { text-align: right; }
.items-tbl td {
  padding: 16px;
  font-size: 13px;
  color: var(--ink);
  border-bottom: 1px solid var(--cream-mid);
  vertical-align: middle;
}
.items-tbl tr:last-child td { border-bottom: none; }

.item-thumb {
  width: 54px; height: 54px;
  border-radius: var(--radius-sm);
  background: var(--cream-mid);
  overflow: hidden;
  border: 1px solid var(--cream-dark);
  flex-shrink: 0;
}
.item-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.item-thumb-placeholder {
  width: 100%; height: 100%;
  display: flex; align-items: center; justify-content: center;
}
.item-thumb-placeholder svg { width: 22px; height: 22px; color: var(--sand); }

.item-name {
  font-size: 14px;
  font-weight: 500;
  color: var(--ink);
  line-height: 1.3;
}
.item-variant {
  font-size: 11px;
  color: var(--ink-muted);
  margin-top: 3px;
  font-weight: 400;
}
.item-qty {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px; height: 28px;
  border-radius: 50%;
  background: var(--cream-mid);
  font-size: 12px;
  font-weight: 600;
  color: var(--ink-soft);
}
.item-price-cell {
  text-align: right;
  font-size: 14px;
  font-weight: 600;
  color: var(--ink);
  white-space: nowrap;
}

/* ── Two Column Grid ──────────────────────────────────── */
.od-two-col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

/* ── Summary Table ────────────────────────────────────── */
.sum-tbl { width: 100%; border-collapse: collapse; }
.sum-tbl tr td { padding: 8px 0; font-size: 13px; color: var(--ink-soft); }
.sum-tbl tr td:last-child { text-align: right; font-weight: 500; color: var(--ink); }
.sum-tbl .sum-divider td { border-top: 1px solid var(--cream-dark); padding-top: 14px; }
.sum-tbl .sum-total td {
  font-size: 16px;
  font-weight: 700;
  color: var(--ink);
  padding-top: 12px;
  font-family: 'DM Sans', sans-serif;
}
.sum-tbl .sum-discount td { color: #B91C1C; }
.sum-tbl .sum-deposit  td { color: #92400E; }
.sum-tbl .sum-balance td:last-child { color: #B91C1C; font-weight: 700; }

/* ── Address Block ────────────────────────────────────── */
.addr-block { line-height: 1.7; font-size: 13.5px; color: var(--ink-soft); }
.addr-name { font-size: 15px; font-weight: 600; color: var(--ink); margin-bottom: 4px; }
.addr-phone { margin-top: 10px; font-size: 13px; font-weight: 600; color: var(--ink); }
.addr-email { font-size: 12px; color: var(--ink-muted); }

/* ── Support / Refund Card ────────────────────────────── */
.od-support {
  background: linear-gradient(135deg, var(--ink) 0%, #2C1F14 100%);
  border-radius: var(--radius-lg);
  padding: 0;
  overflow: hidden;
  margin-bottom: 16px;
}
.od-support-inner {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
}
.od-support-col {
  padding: 32px 36px;
}
.od-support-col + .od-support-col {
  border-left: 1px solid rgba(255,255,255,.07);
}
.od-support-eyebrow {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--ink-muted);
  margin-bottom: 10px;
}
.od-support-heading {
  font-family: 'DM Sans', sans-serif;
  font-size: 22px;
  font-weight: 600;
  color: var(--white);
  margin-bottom: 10px;
  line-height: 1.3;
}
.od-support-text {
  font-size: 12.5px;
  color: rgba(255,255,255,.5);
  line-height: 1.65;
}
.od-support-text strong { color: rgba(255,255,255,.8); font-weight: 500; }

/* Refund Form */
.refund-textarea {
  width: 100%;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: var(--radius-sm);
  color: var(--white);
  font-size: 13px;
  font-family: 'DM Sans', sans-serif;
  padding: 12px 14px;
  resize: vertical;
  min-height: 72px;
  outline: none;
  transition: border-color .2s;
  margin-top: 14px;
}
.refund-textarea::placeholder { color: rgba(255,255,255,.3); }
.refund-textarea:focus { border-color: var(--gold-light); }

.btn-refund {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 10px;
  padding: 9px 20px;
  border-radius: var(--radius-sm);
  background: var(--gold);
  color: var(--white);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  border: none;
  cursor: pointer;
  transition: background .2s, transform .1s;
}
.btn-refund:hover { background: var(--gold-light); transform: translateY(-1px); }
.btn-refund:active { transform: translateY(0); }

#customerRefundMsg {
  margin-top: 10px;
  font-size: 12px;
  min-height: 16px;
}

/* Refund Status Badge */
.refund-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 14px;
  border-radius: 50px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}
.refund-pending  { background: rgba(234,179,8,.2);  color: #FDE68A; }
.refund-approved { background: rgba(34,197,94,.2);  color: #86EFAC; }
.refund-rejected { background: rgba(239,68,68,.2);  color: #FCA5A5; }

.refund-reason-label {
  font-size: 10px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--ink-muted);
  margin-top: 14px;
  margin-bottom: 4px;
}
.refund-reason-text {
  font-size: 12.5px;
  color: rgba(255,255,255,.55);
  line-height: 1.6;
}

/* ── Responsive ───────────────────────────────────────── */
@media (max-width: 680px) {
  .od-hero { grid-template-columns: 1fr; padding: 24px; gap: 16px; }
  .od-hero-right { text-align: left; }
  .od-hero-total { font-size: 26px; }

  .od-meta { grid-template-columns: 1fr 1fr; }
  .od-meta-item:last-child { grid-column: 1 / -1; border-top: 1px solid var(--cream-dark); }

  .od-two-col { grid-template-columns: 1fr; }

  .od-support-inner { grid-template-columns: 1fr; }
  .od-support-col + .od-support-col { border-left: none; border-top: 1px solid rgba(255,255,255,.07); }
  .od-support-col { padding: 24px; }

  .items-tbl th:nth-child(3),
  .items-tbl td:nth-child(3) { display: none; }
}

@media (max-width: 480px) {
  .od-wrap { padding: 24px 16px 60px; }
  .od-meta { grid-template-columns: 1fr; }
  .od-meta-item:last-child { grid-column: auto; }
}
</style>
@endsection

@section('content')
<?php
$orderNumber    = $order->order_number ?? $order->id;
$status         = $order->status ?? 'pending';
$paymentStatus  = $order->payment_status ?? 'unpaid';
$addr           = $order->shippingAddress;
$items          = $order->items ?? collect([]);
$coupon         = $order->coupon;
$paymentMethod  = $order->payment_method ?? 'cod';
$subtotal       = $order->subtotal ?? 0;
$discount       = $order->discount ?? 0;
$deliveryFee    = $order->delivery_fee ?? 0;
$vatAmount      = $order->vat_amount ?? 0;
$depositAmount  = $order->deposit_amount ?? 0;
$total          = $order->total ?? 0;
$itemCount      = $items->sum('quantity') ?? 0;
$balanceDue     = max(0, $total - $depositAmount);

$paymentDisplay = match($paymentMethod) {
    'cod'    => 'Cash on Delivery',
    'card'   => 'Credit / Debit Card',
    'wallet' => 'Fawry Wallet',
    default  => ucfirst($paymentMethod),
};

$statusBadge = match($status) {
    'pending'    => 'badge-pending',
    'processing' => 'badge-processing',
    'shipped'    => 'badge-shipped',
    'delivered'  => 'badge-delivered',
    'cancelled'  => 'badge-cancelled',
    default      => 'badge-pending',
};

$paymentBadge = match($paymentStatus) {
    'unpaid'       => 'badge-unpaid',
    'paid_deposit' => 'badge-paid_deposit',
    'full_paid'    => 'badge-full_paid',
    'refunded'     => 'badge-refunded',
    default        => 'badge-unpaid',
};

$paymentLabel = match($paymentStatus) {
    'paid_deposit' => 'Deposit Paid',
    'full_paid'    => 'Paid in Full',
    'refunded'     => 'Refunded',
    default        => ucfirst($paymentStatus),
};

$refundStatus    = $order->refund_status ?? null;
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
?>

<div class="od-wrap">

  {{-- Back --}}
  <a href="/account" class="od-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
    My Account
  </a>

  {{-- Flash Messages --}}
  @if(session('success'))
    <div class="od-flash od-flash-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="od-flash od-flash-error">{{ session('error') }}</div>
  @endif

  {{-- ── Hero Header ───────────────────────── --}}
  <div class="od-hero">
    <div class="od-hero-left">
      <div class="od-hero-eyebrow">DecoHomz Order</div>
      <div class="od-hero-number">#{{ $orderNumber }}</div>
      <div class="od-hero-badges">
        <span class="od-badge {{ $statusBadge }}">{{ ucfirst($status) }}</span>
        <span class="od-badge {{ $paymentBadge }}">{{ $paymentLabel }}</span>
      </div>
    </div>
    <div class="od-hero-right">
      <div class="od-hero-total-label">Order Total</div>
      <div class="od-hero-total">EGP {{ number_format($total) }}</div>
    </div>
  </div>

  {{-- ── Meta Bar ──────────────────────────── --}}
  <div class="od-meta">
    <div class="od-meta-item">
      <span class="od-meta-label">Order Date</span>
      <span class="od-meta-value">{{ \Carbon\Carbon::parse($order->created_at)->format('M j, Y') }}</span>
    </div>
    <div class="od-meta-item">
      <span class="od-meta-label">Payment</span>
      <span class="od-meta-value">{{ $paymentDisplay }}</span>
    </div>
    <div class="od-meta-item">
      <span class="od-meta-label">Items</span>
      <span class="od-meta-value">{{ $itemCount }} piece{{ $itemCount !== 1 ? 's' : '' }}</span>
    </div>
  </div>

  {{-- ── Items ─────────────────────────────── --}}
  <div class="od-card">
    <div class="od-card-head">
      <span class="od-card-title">Order Items &ensp;·&ensp; {{ $itemCount }}</span>
    </div>
    <table class="items-tbl">
      <thead>
        <tr>
          <th style="width:70px; padding-left:24px"></th>
          <th>Product</th>
          <th style="text-align:center">Qty</th>
          <th style="padding-right:24px">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $item)
        <tr>
          <td style="padding-left:24px">
            <div class="item-thumb">
              <img src="{{ $item->product?->primaryImage?->url ?? '' }}"
                   alt="{{ $item->product?->name ?? 'Product' }}"
                   onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
              <div class="item-thumb-placeholder" style="display:none">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="3" y="3" width="18" height="18" rx="3"/>
                  <circle cx="8.5" cy="8.5" r="1.5"/>
                  <path d="m21 15-5-5L5 21"/>
                </svg>
              </div>
            </div>
          </td>
          <td>
            <div class="item-name">{{ $item->product?->name ?? $item->name }}</div>
            @if($item->variant && $item->variant !== 'Standard')
              <div class="item-variant">{{ $item->variant }}</div>
            @endif
          </td>
          <td style="text-align:center">
            <span class="item-qty">{{ $item->quantity }}</span>
          </td>
          <td class="item-price-cell" style="padding-right:24px">
            EGP {{ number_format($item->price * $item->quantity) }}
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- ── Summary + Shipping ────────────────── --}}
  <div class="od-two-col">

    {{-- Order Summary --}}
    <div class="od-card">
      <div class="od-card-head">
        <span class="od-card-title">Order Summary</span>
      </div>
      <div class="od-card-body">
        <table class="sum-tbl">
          <tr>
            <td>Subtotal</td>
            <td>EGP {{ number_format($subtotal) }}</td>
          </tr>
          @if($discount > 0)
          <tr class="sum-discount">
            <td>Discount</td>
            <td>− EGP {{ number_format($discount) }}</td>
          </tr>
          @endif
          <tr>
            <td>Delivery</td>
            <td>{{ $deliveryFee == 0 ? 'Free' : 'EGP ' . number_format($deliveryFee) }}</td>
          </tr>
          @if($vatAmount > 0)
          <tr>
            <td>VAT (14%)</td>
            <td>EGP {{ number_format($vatAmount) }}</td>
          </tr>
          @endif
          @if($depositAmount > 0)
          <tr class="sum-deposit">
            <td>Deposit {{ $paymentStatus === 'refunded' ? 'Refunded' : 'Required' }}</td>
            <td>{{ $paymentStatus === 'refunded' ? '− ' : '' }}EGP {{ number_format($depositAmount) }}</td>
          </tr>
          @endif
          <tr class="sum-divider sum-total">
            <td>Total</td>
            <td>EGP {{ number_format($total) }}</td>
          </tr>
          @if($depositAmount > 0 && $paymentStatus !== 'refunded')
          <tr class="sum-balance">
            <td style="font-size:12px;color:var(--ink-muted)">Balance Due on Delivery</td>
            <td>EGP {{ number_format($balanceDue) }}</td>
          </tr>
          @endif
        </table>
      </div>
    </div>

    {{-- Shipping Address --}}
    <div class="od-card">
      <div class="od-card-head">
        <span class="od-card-title">Shipping Address</span>
      </div>
      <div class="od-card-body">
        @if($addr)
          <div class="addr-block">
            <div class="addr-name">{{ $addr->first_name }} {{ $addr->last_name }}</div>
            <div>{{ $addr->address_line_1 }}{{ $addr->address_line_2 ? ', ' . $addr->address_line_2 : '' }}</div>
            <div>{{ $addr->city }}{{ $addr->state ? ', ' . $addr->state : '' }}, {{ $addr->country }}</div>
            @if($addr->postal_code)
              <div style="margin-top:4px;font-size:12px;color:var(--ink-muted)">Postal Code: {{ $addr->postal_code }}</div>
            @endif
            <div class="addr-phone">{{ $addr->phone }}</div>
            @if($addr->email)
              <div class="addr-email">{{ $addr->email }}</div>
            @endif
          </div>
        @else
          <div style="color:var(--ink-muted);font-size:13px;font-style:italic">No shipping address on file.</div>
        @endif
      </div>
    </div>

  </div>{{-- /od-two-col --}}

  {{-- ── Help & Support ────────────────────── --}}
  <div class="od-support">
    <div class="od-support-inner">

      {{-- Contact --}}
      <div class="od-support-col">
        <div class="od-support-eyebrow">Customer Support</div>
        <div class="od-support-heading">Need help with your order?</div>
        <div class="od-support-text">
          Our team is available on WhatsApp to assist you with any questions about your order, delivery, or our return policy.<br><br>
          <strong>+20 103 774 3273</strong>
        </div>
      </div>

      {{-- Refund --}}
      <div class="od-support-col">
        @if($canRequestRefund)
          <div class="od-support-eyebrow">Refund Request</div>
          <div class="od-support-heading">Request a Refund</div>
          <div class="od-support-text">Your order is eligible for a refund. Please share your reason below and our team will review it shortly.</div>
          <textarea id="customerRefundReason" class="refund-textarea" placeholder="Please describe the reason for your refund request…" maxlength="500"></textarea>
          <br>
          <button type="button" class="btn-refund" onclick="submitCustomerRefund()">
            Submit Request
          </button>
          <div id="customerRefundMsg"></div>

        @elseif($refundStatus)
          <div class="od-support-eyebrow">Refund Status</div>
          <div class="od-support-heading">Your Refund</div>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
            <span class="refund-status-badge {{ $refundBadgeClass }}">{{ $refundBadgeLabel }}</span>
            @if($order->refund_handled_at)
              <span style="font-size:11px;color:rgba(255,255,255,.35)">
                {{ \Carbon\Carbon::parse($order->refund_handled_at)->format('M j, Y') }}
              </span>
            @endif
          </div>
          @if($order->refund_reason)
            <div class="refund-reason-label">Your Reason</div>
            <div class="refund-reason-text">{{ $order->refund_reason }}</div>
          @endif

        @else
          <div class="od-support-eyebrow">Refund Policy</div>
          <div class="od-support-heading">Returns & Refunds</div>
          <div class="od-support-text">Refunds are available for orders with a confirmed payment. If you believe your paid order isn't showing the refund option, please contact our support team directly.</div>
        @endif
      </div>

    </div>
  </div>

</div>{{-- /od-wrap --}}
@endsection

@section('extra_js')
<script>
(function () { Cart.updateBadge(); })();

function submitCustomerRefund() {
  var reason = document.getElementById('customerRefundReason').value.trim();
  var msg    = document.getElementById('customerRefundMsg');

  if (!reason) {
    msg.textContent  = 'Please enter a reason for your refund request.';
    msg.style.color  = '#FCA5A5';
    return;
  }
  if (!confirm('Are you sure you want to request a refund for this order?')) return;

  msg.textContent = 'Submitting…';
  msg.style.color = 'rgba(255,255,255,.5)';

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
      msg.style.color = '#FCA5A5';
      return null;
    }
    return res.json().catch(() => ({}));
  })
  .then(data => {
    if (!data) return;
    if (data.error) {
      msg.textContent = data.error;
      msg.style.color = '#FCA5A5';
    } else {
      msg.textContent = 'Refund request submitted successfully.';
      msg.style.color = '#86EFAC';
      document.getElementById('customerRefundReason').value = '';
      setTimeout(() => location.reload(), 1500);
    }
  })
  .catch(() => {
    msg.textContent = 'Failed to submit refund request. Please try again.';
    msg.style.color = '#FCA5A5';
  });
}
</script>
@endsection