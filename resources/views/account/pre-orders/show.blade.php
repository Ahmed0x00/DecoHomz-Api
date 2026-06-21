@extends('layouts.app')

@section('title', 'Pre-Order #' . $preOrder->id . ' — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="{{ asset_v('/css/order-confirmation.css') }}">
<style>
/* ── Pre-Order Detail Overrides ── */
.confirm-wrap svg { width: 16px; height: 16px; flex-shrink: 0; }

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

.od-banner {
  padding: 32px 40px;
}
.od-banner .confirm-title {
  margin-bottom: 12px;
}
.od-banner .status-badge {
  margin-top: 12px;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: var(--radius-full);
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.status-pending   { background: rgba(234,179,8,.15);  color: #B45309; }
.status-contacted { background: rgba(59,130,246,.15); color: #1D4ED8; }
.status-confirmed { background: rgba(34,197,94,.15);  color: #15803D; }
.status-cancelled { background: rgba(239,68,68,.15);  color: #B91C1C; }

.pod-date-sub {
  font-size: 13px;
  color: var(--color-text-secondary);
  margin-top: 12px;
}

/* 10-Slot Inspiration Images Grid */
.pod-images-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
  margin-top: 16px;
}

.pod-img-thumb {
  position: relative;
  border-radius: var(--radius-md);
  overflow: hidden;
  aspect-ratio: 1;
  background: var(--color-bg-warm);
  cursor: default;
  transition: transform 0.3s var(--ease-spring), box-shadow 0.3s ease, border-color 0.3s ease;
}

/* Active Uploaded Image Slot */
.pod-img-thumb.active-slot {
  border: 1px solid var(--color-border);
  cursor: pointer;
}
.pod-img-thumb.active-slot:hover {
  transform: translateY(-4px) scale(1.02);
  box-shadow: var(--shadow-lg);
  border-color: var(--color-accent);
}
.pod-img-thumb.active-slot img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Empty Slot Placeholder */
.pod-img-thumb.empty-slot {
  border: 2px dashed var(--color-border);
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(var(--color-bg-warm-rgb), 0.3);
}
.empty-slot-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: var(--color-text-faint);
  text-align: center;
  padding: 12px;
}
.empty-slot-content svg {
  width: 24px;
  height: 24px;
  stroke: var(--color-text-faint);
  fill: none;
}
.empty-slot-content span {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Slot Index Badge */
.slot-badge {
  position: absolute;
  top: 8px;
  left: 8px;
  background: rgba(0, 0, 0, 0.5);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  width: 20px;
  height: 20px;
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(4px);
  z-index: 2;
}

/* Zoom Overlay */
.zoom-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
  z-index: 1;
}
.pod-img-thumb.active-slot:hover .zoom-overlay {
  opacity: 1;
}
.zoom-overlay svg {
  width: 24px;
  height: 24px;
  stroke: #fff;
  fill: none;
  transform: scale(0.8);
  transition: transform 0.3s var(--ease-spring);
}
.pod-img-thumb.active-slot:hover .zoom-overlay svg {
  transform: scale(1);
}

/* Notes Section Styling */
.pod-notes {
  font-size: 14px;
  color: var(--color-text);
  line-height: 1.7;
  white-space: pre-line;
  background: var(--color-bg-warm);
  padding: 20px;
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border);
}

/* Lightbox Styles */
.pod-lightbox {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(12px);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: zoom-out;
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transition: opacity 0.3s ease, visibility 0.3s ease;
}
.pod-lightbox.open {
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
}
.pod-lightbox img {
  max-width: 90vw;
  max-height: 90vh;
  border-radius: var(--radius-lg);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
  transform: scale(0.95);
  transition: transform 0.3s var(--ease-spring);
}
.pod-lightbox.open img {
  transform: scale(1);
}
.pod-lightbox-close {
  position: absolute;
  top: 24px;
  right: 28px;
  width: 44px;
  height: 44px;
  border-radius: var(--radius-full);
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
  font-size: 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}
.pod-lightbox-close:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.05);
}

@media (max-width: 992px) {
  .pod-images-grid { grid-template-columns: repeat(3, 1fr); gap: 12px; }
}
@media (max-width: 576px) {
  .pod-images-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
}
</style>
@endsection

@section('content')
<?php
$po = $preOrder;
$status = $po->status ?? 'pending';
$date = $po->created_at ? \Carbon\Carbon::parse($po->created_at)->format('M d, Y \a\t g:i A') : '—';
$dateShort = $po->created_at ? \Carbon\Carbon::parse($po->created_at)->format('M d, Y') : '—';
$images = $po->images ?? collect([]);
$statusBadgeClass = match($status) {
  'contacted' => 'status-contacted',
  'confirmed' => 'status-confirmed',
  'cancelled' => 'status-cancelled',
  default     => 'status-pending',
};
?>

<div class="confirm-wrap">

  <a href="/account#preorders" class="od-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
    {{ __('Back to My Account') }}
  </a>

  {{-- Banner --}}
  <div class="success-banner od-banner">
    <div class="confirm-title">{{ __('Pre-Order Details') }}</div>
    <div class="order-num">{{ __('Pre-Order ID:') }} <span>#{{ $po->id }}</span></div>
    <div>
      <span class="status-badge {{ $statusBadgeClass }}">{{ ucfirst($status) }}</span>
    </div>
    <div class="pod-date-sub">{{ $date }}</div>
  </div>

  {{-- Info Cards --}}
  <div class="confirm-grid">
    {{-- Contact Information --}}
    <div class="info-card">
      <div class="card-title">
        <svg width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="8.5" cy="7" r="4"/>
          <path d="M20 8v6M23 11h-6"/>
        </svg>
        {{ __('Contact Information') }}
      </div>
      <div class="info-row"><span class="key">{{ __('Name') }}</span><span class="val">{{ $po->name ?? '—' }}</span></div>
      <div class="info-row"><span class="key">{{ __('Phone') }}</span><span class="val">{{ $po->phone ?? '—' }}</span></div>
      @if($po->email)
      <div class="info-row"><span class="key">{{ __('Email') }}</span><span class="val">{{ $po->email }}</span></div>
      @endif
    </div>

    {{-- Status Details --}}
    <div class="info-card">
      <div class="card-title">
        <svg width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
        {{ __('Status') }}
      </div>
      <div class="info-row"><span class="key">{{ __('Current Status') }}</span><span class="val"><span class="status-badge {{ $statusBadgeClass }}" style="margin:0">{{ ucfirst($status) }}</span></span></div>
      <div class="info-row"><span class="key">{{ __('Submitted') }}</span><span class="val">{{ $dateShort }}</span></div>
      <div class="info-row"><span class="key">{{ __('Images') }}</span><span class="val">{{ $images->count() }} / 10 {{ __('uploaded') }}</span></div>
    </div>
  </div>

  {{-- Inspiration Images --}}
  <div class="order-items">
    <div class="items-title">
      <svg width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
        <circle cx="8.5" cy="8.5" r="1.5"/>
        <polyline points="21 15 16 10 5 21"/>
      </svg>
      {{ __('Inspiration Images') }} ({{ $images->count() }} / 10)
    </div>
    <div class="pod-images-grid">
      @for($i = 0; $i < 10; $i++)
        @if(isset($images[$i]))
          <div class="pod-img-thumb active-slot" onclick="openLightbox('{{ asset('storage/' . $images[$i]->image) }}')" title="{{ __('Click to zoom') }}">
            <img src="{{ asset('storage/' . $images[$i]->image) }}" alt="Inspiration {{ $i + 1 }}" loading="lazy">
            <div class="slot-badge">{{ $i + 1 }}</div>
            <div class="zoom-overlay">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
            </div>
          </div>
        @else
          <div class="pod-img-thumb empty-slot">
            <div class="slot-badge">{{ $i + 1 }}</div>
            <div class="empty-slot-content">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
              </svg>
              <span>{{ __('Empty Slot') }}</span>
            </div>
          </div>
        @endif
      @endfor
    </div>
  </div>

  {{-- Description / Notes --}}
  @if($po->notes)
  <div class="order-items">
    <div class="items-title">
      <svg width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="16" y1="13" x2="8" y2="13"/>
        <line x1="16" y1="17" x2="8" y2="17"/>
      </svg>
      {{ __('Description / Notes') }}
    </div>
    <div class="pod-notes">{{ $po->notes }}</div>
  </div>
  @endif

</div>

{{-- Lightbox --}}
<div class="pod-lightbox" id="pod-lightbox" onclick="closeLightbox()">
  <button class="pod-lightbox-close" onclick="closeLightbox()">&times;</button>
  <img id="pod-lightbox-img" src="" alt="Full size">
</div>
@endsection

@section('extra_js')
<script>
(function() { if (typeof Cart !== 'undefined' && Cart.updateBadge) Cart.updateBadge(); })();

window.openLightbox = function(src) {
  document.getElementById('pod-lightbox-img').src = src;
  document.getElementById('pod-lightbox').classList.add('open');
  document.body.style.overflow = 'hidden';
};
window.closeLightbox = function() {
  document.getElementById('pod-lightbox').classList.remove('open');
  document.body.style.overflow = '';
};
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeLightbox();
});
</script>
@endsection
