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

/* Inspiration Images Slider */
.pod-slider-wrapper {
  max-width: 600px;
  margin: 0 auto;
  position: relative;
}

.pod-slider {
  display: flex;
  align-items: center;
  gap: 20px;
  position: relative;
}

.slider-main {
  flex: 1;
  position: relative;
  aspect-ratio: 4/3;
  max-height: 450px;
  border-radius: var(--radius-lg);
  overflow: hidden;
  border: 1px solid var(--color-border);
  background: var(--color-bg-warm);
  cursor: pointer;
  box-shadow: var(--shadow-sm);
  transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}

.slider-main:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  border-color: var(--color-accent);
}

.slider-main img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
  transition: opacity 0.15s ease-in-out;
  pointer-events: none;
}

.slider-btn {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-full);
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  color: var(--color-text);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: var(--shadow-sm);
  transition: all 0.2s ease;
  flex-shrink: 0;
  z-index: 10;
}

.slider-btn:hover {
  background: var(--color-primary);
  color: #fff;
  border-color: var(--color-primary);
  transform: scale(1.08);
}

.slider-btn svg {
  width: 20px;
  height: 20px;
}

.slider-counter {
  position: absolute;
  bottom: 16px;
  right: 16px;
  background: rgba(0, 0, 0, 0.65);
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: var(--radius-full);
  backdrop-filter: blur(4px);
  z-index: 2;
  letter-spacing: 0.05em;
  pointer-events: none;
}

/* Lightbox Styles */
.pod-lightbox {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 99999;
  background: rgba(0, 0, 0, 0.88);
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
  inset-inline-end: 28px;
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
  pointer-events: none;
}

.slider-main:hover .zoom-overlay {
  opacity: 1;
}

.zoom-overlay svg {
  width: 28px;
  height: 28px;
  stroke: #fff;
  fill: none;
  transform: scale(0.8);
  transition: transform 0.3s var(--ease-spring);
}

.slider-main:hover .zoom-overlay svg {
  transform: scale(1);
}

.slider-dots {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 16px;
}

.slider-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--color-border);
  cursor: pointer;
  transition: all 0.25s ease;
}

.slider-dot:hover {
  background: var(--color-text-secondary);
}

.slider-dot.active {
  background: var(--color-accent);
  width: 24px;
  border-radius: 4px;
}

@media (max-width: 576px) {
  .pod-slider {
    gap: 8px;
  }
  .slider-btn {
    width: 36px;
    height: 36px;
  }
  .slider-btn svg {
    width: 16px;
    height: 16px;
  }
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
      {{ __('Inspiration Images') }}
    </div>
    @if($images->count())
      <div class="pod-slider-wrapper">
        <div class="pod-slider">
          @if($images->count() > 1)
            <button class="slider-btn prev-btn" onclick="slidePrev()" aria-label="{{ __('Previous image') }}">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
          @endif

          <div class="slider-main" onclick="zoomCurrentImage()" title="{{ __('Click to zoom') }}">
            <img id="slider-img" src="{{ asset('storage/' . $images[0]->image) }}" data-index="0" alt="{{ __('Inspiration Image') }}">
            <div class="zoom-overlay">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
            </div>
            @if($images->count() > 1)
              <div class="slider-counter" id="slider-counter">1 / {{ $images->count() }}</div>
            @endif
          </div>

          @if($images->count() > 1)
            <button class="slider-btn next-btn" onclick="slideNext()" aria-label="{{ __('Next image') }}">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
          @endif
        </div>

        @if($images->count() > 1)
          <div class="slider-dots">
            @foreach($images as $i => $img)
              <span class="slider-dot {{ $i === 0 ? 'active' : '' }}" onclick="goToSlide({{ $i }})"></span>
            @endforeach
          </div>
        @endif
      </div>
    @else
      <p style="color:var(--color-text-faint);font-size:14px;margin:0;">{{ __('No images uploaded.') }}</p>
    @endif
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

const sliderImages = [
  @foreach($images as $img)
    "{{ asset('storage/' . $img->image) }}",
  @endforeach
];
let currentSlideIndex = 0;

// Preload slider images on page load
(function() {
  sliderImages.forEach(function(src) {
    const img = new Image();
    img.src = src;
  });
})();

function slidePrev() {
  if (sliderImages.length <= 1) return;
  currentSlideIndex = (currentSlideIndex - 1 + sliderImages.length) % sliderImages.length;
  updateSlider();
}

function slideNext() {
  if (sliderImages.length <= 1) return;
  currentSlideIndex = (currentSlideIndex + 1) % sliderImages.length;
  updateSlider();
}

function goToSlide(index) {
  if (index < 0 || index >= sliderImages.length) return;
  currentSlideIndex = index;
  updateSlider();
}

function zoomCurrentImage() {
  if (sliderImages.length === 0) return;
  openLightbox(sliderImages[currentSlideIndex]);
}

function updateSlider() {
  const imgEl = document.getElementById('slider-img');
  const counterEl = document.getElementById('slider-counter');
  
  if (imgEl) {
    imgEl.style.opacity = '0';

    const onImageLoad = function() {
      imgEl.style.opacity = '1';
      imgEl.removeEventListener('load', onImageLoad);
      imgEl.removeEventListener('error', onImageError);
    };

    const onImageError = function() {
      imgEl.style.opacity = '1';
      imgEl.removeEventListener('load', onImageLoad);
      imgEl.removeEventListener('error', onImageError);
    };

    imgEl.addEventListener('load', onImageLoad);
    imgEl.addEventListener('error', onImageError);
    
    setTimeout(() => {
      imgEl.src = sliderImages[currentSlideIndex];
      imgEl.dataset.index = currentSlideIndex;
    }, 150);
  }
  
  if (counterEl) {
    counterEl.textContent = `${currentSlideIndex + 1} / ${sliderImages.length}`;
  }

  const dots = document.querySelectorAll('.slider-dot');
  dots.forEach((dot, idx) => {
    if (idx === currentSlideIndex) {
      dot.classList.add('active');
    } else {
      dot.classList.remove('active');
    }
  });
}

function openLightbox(src) {
  const lightbox = document.getElementById('pod-lightbox');
  const img = document.getElementById('pod-lightbox-img');
  if (lightbox && img) {
    img.src = src;
    lightbox.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
}

function closeLightbox() {
  const lightbox = document.getElementById('pod-lightbox');
  if (lightbox) {
    lightbox.classList.remove('open');
    document.body.style.overflow = '';
  }
}

// Bind variables and functions to window for global inline HTML execution
window.slidePrev = slidePrev;
window.slideNext = slideNext;
window.goToSlide = goToSlide;
window.zoomCurrentImage = zoomCurrentImage;
window.openLightbox = openLightbox;
window.closeLightbox = closeLightbox;

// Relocate lightbox modal to end of <body> on script load to prevent ancestor transform/overflow issues
(function() {
  const lightbox = document.getElementById('pod-lightbox');
  if (lightbox) {
    document.body.appendChild(lightbox);
  }
})();

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeLightbox();
});
</script>
@endsection
