@extends('layouts.app')

@section('title')
  {{ $product['name'] ?? 'Product' }} — DecoHomz
@endsection

@section('extra_css')
<link rel="stylesheet" href="/css/product.css">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="breadcrumb">
  <a href="/">{{ __('Home') }}</a> › 
  <a href="/shop">{{ __('Shop') }}</a> › 
  @if(isset($product['category']))
    <a href="/shop?category={{ urlencode($product['category']['name']) }}">{{ $product['category']['name'] }}</a> › 
  @endif
  <span>{{ $product['name'] ?? 'Product' }}</span>
</div>

<div class="product-wrap">
  
  {{-- ═══ LEFT: GALLERY ═══ --}}
  <div class="product-gallery animate-fade-right">
    <div class="main-img" id="main-image-container">
      @if(isset($product['badge']))
        <div class="gallery-badges">
          <div class="tag-sale" style="background:{{ $product['badge_color'] ?? 'var(--color-accent)' }}">{{ $product['badge'] }}</div>
        </div>
      @endif
      @php
        $primaryImg = '/img/placeholder.svg';
        if(isset($product['primary_image'])) {
            $primaryImg = $product['primary_image']['url'] ?? $product['primary_image']['thumbnail_url'] ?? $primaryImg;
        }
      @endphp
      <img src="{{ $primaryImg }}" id="main-image" alt="{{ $product['name'] ?? 'Product Image' }}">
    </div>

    @if(isset($product['images']) && count($product['images']) > 1)
    <div class="thumb-row">
      @foreach($product['images'] as $index => $img)
        @php
          $thumbUrl = $img['thumbnail_url'] ?? $img['url'] ?? '/img/placeholder.svg';
          $fullUrl = $img['url'] ?? $thumbUrl;
        @endphp
        <div class="thumb {{ $index === 0 ? 'active' : '' }}" onclick="changeImage(this, '{{ $fullUrl }}')">
          <img src="{{ $thumbUrl }}" alt="Thumbnail {{ $index + 1 }}">
        </div>
      @endforeach
    </div>
    @endif
  </div>

  {{-- ═══ RIGHT: INFO ═══ --}}
  <div class="product-info animate-fade-left">
    <div class="prod-meta-top">
      <div class="prod-cat">{{ $product['category']['name'] ?? 'Furniture' }}</div>
      <div class="prod-reviews-mini" onclick="document.getElementById('tab-reviews').click(); window.scrollBy({top: document.querySelector('.tabs-section').getBoundingClientRect().top - 100, behavior: 'smooth'});">
        <div class="stars" id="mini-stars">
          ★★★★★
        </div>
        <span id="mini-reviews-count">(0 {{ __('Reviews') }})</span>
      </div>
    </div>

    <h1 class="prod-title">{{ $product['name'] ?? 'Product Name' }}</h1>

    <div class="price-block">
      <div class="main-price">EGP {{ number_format($product->price ?? 0, 2) }}</div>
      @if(isset($product->old_price) && $product->old_price > $product->price)
        <div class="old-price">EGP {{ number_format($product->old_price, 2) }}</div>
      @endif
      @if(($product->stock ?? 0) <= 0)
        <div class="stock-badge out-of-stock">{{ __('Out of Stock') }}</div>
      @endif
    </div>

    <div class="prod-desc-short">
      {{ $product['description'] ?? 'Premium quality furniture crafted to elevate your living space.' }}
    </div>

    {{-- Options --}}
    <form id="add-to-cart-form" onsubmit="event.preventDefault(); submitAddToCart();">
      
      {{-- Colors (from database) --}}
      @php $colors = $product->activeColors ?? collect(); @endphp
      @if($colors->count() > 0)
      <div class="options-group">
        <div class="opt-label">
          <span>{{ __('Color') }}</span>
          <span class="opt-val" id="color-val">{{ $colors->first()->name ?? 'Standard' }}</span>
        </div>
        <div class="color-row">
          <div class="color-swatch color-swatch-none active" style="background:var(--color-surface);border:2px dashed var(--color-border)" title="{{ __('No color') }}" data-color="{{ __('Standard') }}" data-color-slug="" data-price-modifier="0" onclick="selectColor(this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.4"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </div>
          @foreach($colors as $i => $color)
            <div class="color-swatch" style="background:{{ $color->hex_code ?? '#4A3626' }}" title="{{ $color->name }}" data-color="{{ $color->name ?? 'Standard' }}" data-color-slug="{{ $color->color_slug ?? '' }}" data-price-modifier="{{ $color->price_modifier ?? 0 }}" onclick="selectColor(this)"></div>
          @endforeach
        </div>
        <input type="hidden" id="selected-color" value="">
      </div>
      @else
      <input type="hidden" id="selected-color" value="">
      @endif

      {{-- Action Bar --}}
      <div class="action-bar">
        <div class="qty-ctrl">
          <button type="button" class="qty-btn" onclick="updateLocalQty(-1)">−</button>
          <span class="qty-num" id="qty-val">1</span>
          <button type="button" class="qty-btn" onclick="updateLocalQty(1)">+</button>
          <input type="hidden" id="selected-qty" value="1">
        </div>
        <button type="submit" class="btn-dark" id="add-btn" @if(($product->stock ?? 0) <= 0) disabled @endif>
          <span>{{ ($product->stock ?? 0) > 0 ? __('Add to Cart') : __('Out of Stock') }}</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </button>
      </div>

    </form>

    {{-- Delivery Info --}}
    <div class="delivery-info">
      <div class="del-item">
        <svg viewBox="0 0 24 24" fill="none"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        <div class="del-item-txt">
          <h5>{{ __('Delivery Options') }}</h5>
          <p>{{ __('Delivery fees are calculated at checkout based on your governorate. Estimated arrival: 3-5 business days.') }}</p>
        </div>
      </div>
      <div class="del-item">
        <svg viewBox="0 0 24 24" fill="none"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>
        <div class="del-item-txt">
          <h5>{{ __('Easy Returns') }}</h5>
          <p>{{ __('Return within 14 days if you are not satisfied.') }}</p>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- ═══ TABS ═══ --}}
<div class="tabs-section">
  <div class="tab-nav" id="tab-nav">
    <div class="tab active" id="tab-desc" onclick="switchTab('desc')">{{ __('Description') }}</div>
    <div class="tab" id="tab-specs" onclick="switchTab('specs')">{{ __('Specifications') }}</div>
    <div class="tab" id="tab-reviews" onclick="switchTab('reviews')">{{ __('Reviews') }} <span id="tab-reviews-count">(0)</span></div>
    <div class="tab-indicator" id="tab-indicator"></div>
  </div>

  <div class="tab-content">
    {{-- Description --}}
    <div class="tab-pane active" id="pane-desc">
      <div class="prod-details">
        <p>{{ $product['description'] ?? 'No description available.' }}</p>
        <p>{{ __('Crafted with precision and designed for ultimate comfort, this piece brings a touch of modern elegance to any room. The high-quality materials ensure durability, while the sleek finish complements various decor styles seamlessly.') }}</p>
      </div>
    </div>

    {{-- Specs --}}
    <div class="tab-pane" id="pane-specs">
      <div class="spec-grid">
        <div class="spec-row"><div class="k">{{ __('Material') }}</div><div class="v">{{ $product->material ?: __('N/A') }}</div></div>
        @if($product->upholstery)
        <div class="spec-row"><div class="k">{{ __('Upholstery') }}</div><div class="v">{{ $product->upholstery }}</div></div>
        @endif
        <div class="spec-row"><div class="k">{{ __('Dimensions') }}</div><div class="v">{{ $product->dimensions ?: __('N/A') }}</div></div>
        <div class="spec-row"><div class="k">{{ __('Weight') }}</div><div class="v">{{ $product->weight ? $product->weight : __('N/A') }}</div></div>
        <div class="spec-row"><div class="k">{{ __('Stock') }}</div><div class="v">{{ ($product->stock ?? 0) > 0 ? $product->stock . ' ' . __('available') : __('Out of stock') }}</div></div>
        <div class="spec-row"><div class="k">{{ __('SKU') }}</div><div class="v">#{{ $product->id }}</div></div>
      </div>
    </div>

    {{-- Reviews --}}
    <div class="tab-pane" id="pane-reviews">
      <div class="reviews-container">
        <div class="review-sidebar">
          <div class="review-summary">
            <h3 id="avg-rating">0.0</h3>
            <div class="stars" id="avg-stars">☆☆☆☆☆</div>
            <div style="font-size:13px;color:var(--color-text-secondary)" id="total-reviews-label">{{ __('Based on 0 reviews') }}</div>
            
            <div class="review-bars" id="review-bars-container">
              <!-- Dynamically populated -->
            </div>
            
            <button class="btn-outline" style="width:100%;margin-top:24px" onclick="openReviewModal()">{{ __('Write a Review') }}</button>
          </div>
        </div>
        
        <div class="review-list" id="review-list">
          <!-- Dynamically populated -->
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ═══ RELATED PRODUCTS ═══ --}}
<div class="related">
  <div class="related-inner">
  <div class="sec-row animate-on-scroll">
    <div class="sec-title">{{ __('You May Also Like') }}</div>
  </div>
  <div class="prod-grid" id="related-grid">
    {{-- Loaded via JS --}}
    @for($i = 0; $i < 4; $i++)
    <div class="skeleton-card">
      <div class="skeleton-img skeleton"></div>
      <div class="skeleton-body">
        <div class="skeleton-text narrow skeleton"></div>
        <div class="skeleton-text wide skeleton"></div>
        <div class="skeleton-text medium skeleton"></div>
      </div>
    </div>
    @endfor
  </div>
  </div>
</div>

{{-- ═══ MOBILE STICKY CART ═══ --}}
<div class="mobile-sticky-cart">
  <div class="sticky-price">
    @if(isset($product['old_price']) && $product['old_price'] > $product['price'])
      <div class="sticky-old-price">EGP {{ number_format($product['old_price'], 2) }}</div>
    @endif
    <div class="sticky-main-price">EGP {{ number_format($product['price'] ?? 0, 2) }}</div>
  </div>
  <button class="btn-dark" onclick="submitAddToCart()" @if(($product->stock ?? 0) <= 0) disabled @endif>{{ ($product->stock ?? 0) > 0 ? __('Add to Cart') : __('Out of Stock') }}</button>
</div>

{{-- Review Modal --}}
<div id="review-modal" class="review-modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
  <div class="review-modal" style="background:#fff; border-radius:16px; width:100%; max-width:450px; padding:32px; box-shadow:0 24px 64px rgba(0,0,0,0.15); position:relative; margin: 20px;">
    <button onclick="closeReviewModal()" style="position:absolute; top:20px; right:20px; background:none; border:none; font-size:24px; cursor:pointer; color:var(--color-text-faint); line-height:1;">&times;</button>
    <h3 style="font-size:20px; font-weight:800; color:var(--color-primary); margin-bottom:20px; text-align:start;">{{ __('Write a Review') }}</h3>
    
    <form id="review-submit-form" onsubmit="event.preventDefault(); submitReview();">
      <div style="margin-bottom:20px; text-align:start;">
        <label style="display:block; font-size:13px; font-weight:700; color:var(--color-text); margin-bottom:8px;">{{ __('Your Rating') }} *</label>
        <div class="rating-stars-select" style="display:flex; gap:8px; font-size:32px; color:var(--color-text-faint); cursor:pointer;">
          <span data-star="1" onclick="setRatingSelect(1)">★</span>
          <span data-star="2" onclick="setRatingSelect(2)">★</span>
          <span data-star="3" onclick="setRatingSelect(3)">★</span>
          <span data-star="4" onclick="setRatingSelect(4)">★</span>
          <span data-star="5" onclick="setRatingSelect(5)">★</span>
        </div>
        <input type="hidden" id="review-rating" value="5">
      </div>
      
      <div style="margin-bottom:24px; text-align:start;">
        <label style="display:block; font-size:13px; font-weight:700; color:var(--color-text); margin-bottom:8px;">{{ __('Your Review') }}</label>
        <textarea id="review-comment-textarea" style="width:100%; min-height:100px; padding:12px 16px; border:1.5px solid var(--color-border); border-radius:8px; font-size:14px; outline:none; font-family:inherit; resize:vertical; background:#fff; color:var(--color-text);" placeholder="{{ __('Tell us what you think about this product...') }}"></textarea>
      </div>
      
      <div style="display:flex; gap:12px;">
        <button type="button" class="btn-outline" style="flex:1; padding:12px;" onclick="closeReviewModal()">{{ __('Cancel') }}</button>
        <button type="submit" class="btn-dark" style="flex:1; padding:12px;" id="btn-submit-review-action">{{ __('Submit Review') }}</button>
      </div>
    </form>
  </div>
</div>

@endsection

@section('extra_js')
<script>
const PRODUCT = {
  id: {{ $product->id ?? 0 }},
  slug: "{{ addslashes($product->slug ?? '') }}",
  name: "{{ addslashes($product->name ?? 'Product') }}",
  price: {{ $product->price ?? 0 }},
  stock: {{ $product->stock ?? 0 }}
};

const BASE_PRICE = PRODUCT.price;

function esc(str) {
  if (!str) return '';
  return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
}

function changeImage(el, url) {
  document.querySelectorAll('.thumb').forEach(function(t) { t.classList.remove('active'); });
  el.classList.add('active');

  const mainImg = document.getElementById('main-image');
  if (!mainImg) return;
  mainImg.style.opacity = '0';
  setTimeout(function() {
    mainImg.src = url;
    mainImg.style.opacity = '1';
  }, 200);
}
window.changeImage = changeImage;

function selectColor(el) {
  const color = el.getAttribute('data-color') || 'Standard';
  const colorSlug = el.getAttribute('data-color-slug') || '';
  const priceModifier = parseFloat(el.getAttribute('data-price-modifier')) || 0;

  document.querySelectorAll('.color-swatch').forEach(function(sw) { sw.classList.remove('active'); });
  el.classList.add('active');
  document.getElementById('selected-color').value = colorSlug;
  const colorVal = document.getElementById('color-val');
  if (colorVal) colorVal.textContent = color;

  // Update displayed price based on color's price_modifier
  const newPrice = BASE_PRICE + priceModifier;
  const mainPrice = document.querySelector('.main-price');
  if (mainPrice) mainPrice.textContent = 'EGP ' + newPrice.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
  const stickyPrice = document.querySelector('.sticky-main-price');
  if (stickyPrice) stickyPrice.textContent = 'EGP ' + newPrice.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
window.selectColor = selectColor;

function updateLocalQty(delta) {
  const input = document.getElementById('selected-qty');
  const display = document.getElementById('qty-val');
  let current = parseInt(input.value, 10) || 1;
  current += delta;
  if (current < 1) current = 1;
  if (current > 10) current = 10;

  input.value = current;
  display.textContent = current;
}
window.updateLocalQty = updateLocalQty;

async function submitAddToCart() {
  if (PRODUCT.stock <= 0) {
    showToast("{{ __('This product is out of stock.') }}", 'error');
    return;
  }

  const qty = parseInt(document.getElementById('selected-qty').value, 10) || 1;
  const colorSlug = document.getElementById('selected-color').value;
  const btn = document.getElementById('add-btn');

  if (!window.Cart || typeof Cart.add !== 'function') return;

  if (btn) {
    btn.disabled = true;
    btn.querySelector('span').textContent = "{{ __('Adding...') }}";
  }

  try {
    await Cart.add({
      id: PRODUCT.id,
      name: PRODUCT.name,
      price: PRODUCT.price,
      quantity: qty,
      color_slug: colorSlug
    });

    if (typeof openCart === 'function') openCart();
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.querySelector('span').textContent = "{{ __('Add to Cart') }}";
    }
  }
}
window.submitAddToCart = submitAddToCart;

function switchTab(tabId) {
  document.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
  document.querySelectorAll('.tab-pane').forEach(function(p) { p.classList.remove('active'); });

  const targetTab = document.getElementById('tab-' + tabId);
  if (targetTab) targetTab.classList.add('active');

  const pane = document.getElementById('pane-' + tabId);
  if (pane) pane.classList.add('active');

  updateTabIndicator(targetTab);
}
window.switchTab = switchTab;

function updateTabIndicator(activeTabEl) {
  const indicator = document.getElementById('tab-indicator');
  if (!activeTabEl || !indicator) return;

  indicator.style.width = activeTabEl.offsetWidth + 'px';
  indicator.style.left = activeTabEl.offsetLeft + 'px';
}

async function loadRelated() {
  const grid = document.getElementById('related-grid');
  if (!grid) return;

  try {
    const res = await API.get('/products/' + (PRODUCT.slug || PRODUCT.id) + '/related');
    const products = (res.products || []).filter(function(p) { return p.id !== PRODUCT.id; });

    if (products.length === 0) {
      grid.innerHTML = '<p class="related-empty">' + "{{ __('No related products found.') }}" + '</p>';
      return;
    }

    grid.innerHTML = products.map(function(p, i) {
      const imgUrl = (p.primary_image && p.primary_image.thumbnail_url) ? p.primary_image.thumbnail_url : ((p.primary_image && p.primary_image.url) ? p.primary_image.url : '/img/placeholder.svg');
      const stars = p.stars || 5;
      const starsStr = '★'.repeat(stars) + '☆'.repeat(5 - stars);
      const price = p.price ? parseFloat(p.price).toLocaleString() : '0';
      const productUrl = '/product/' + (p.slug || p.id);

      return '<div class="prod-card animate-fade-up stagger-' + (i + 1) + '" onclick="location.href=\'' + productUrl + '\'">' +
        '<div class="prod-img">' +
          '<img src="' + imgUrl + '" alt="' + esc(p.name) + '" loading="lazy" onerror="this.src=\'/img/placeholder.svg\'">' +
        '</div>' +
        '<div class="prod-info">' +
          '<div class="stars">' + starsStr + '</div>' +
          '<div class="prod-name">' + esc(p.name) + '</div>' +
          '<div class="prod-price">EGP ' + price + '</div>' +
        '</div>' +
      '</div>';
    }).join('');
  } catch (e) {
    grid.innerHTML = '';
  }
}

let allReviews = [];
let displayedCount = 5;

async function loadReviews() {
  try {
    const res = await API.get('/products/' + PRODUCT.id + '/reviews');
    const reviews = res.reviews || [];
    const stats = res.stats || { average: 0, count: 0, 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
    
    allReviews = reviews;
    displayedCount = 5;

    // 1. Mini reviews (under title)
    const miniStars = document.getElementById('mini-stars');
    const miniCount = document.getElementById('mini-reviews-count');
    if (miniStars) {
      const avg = Math.round(stats.average || 0);
      miniStars.textContent = '★'.repeat(avg) + '☆'.repeat(5 - avg);
    }
    if (miniCount) {
      miniCount.textContent = `(${stats.count || 0} ${"{{ __('Reviews') }}"})`;
    }

    // 2. Tab header reviews count
    const tabCount = document.getElementById('tab-reviews-count');
    if (tabCount) {
      tabCount.textContent = `(${stats.count || 0})`;
    }

    // 3. Tab pane reviews summary
    const avgRating = document.getElementById('avg-rating');
    const avgStars = document.getElementById('avg-stars');
    const totalReviewsLabel = document.getElementById('total-reviews-label');

    if (avgRating) avgRating.textContent = stats.average || '0.0';
    if (avgStars) {
      const avg = Math.round(stats.average || 0);
      avgStars.textContent = '★'.repeat(avg) + '☆'.repeat(5 - avg);
    }
    if (totalReviewsLabel) {
      totalReviewsLabel.textContent = `Based on ${stats.count || 0} reviews`;
    }

    // 4. Progress bars
    const barsContainer = document.getElementById('review-bars-container');
    if (barsContainer) {
      let barsHtml = '';
      const totalCount = stats.count || 0;
      for (let star = 5; star >= 1; star--) {
        const starCount = stats[star] || 0;
        const pct = totalCount > 0 ? Math.round((starCount / totalCount) * 100) : 0;
        barsHtml += `
          <div class="r-bar-row">
            <div class="r-bar-label">${star}★</div>
            <div class="r-bar-track"><div class="r-bar-fill" style="width:${pct}%"></div></div>
            <div class="r-bar-count">${starCount}</div>
          </div>
        `;
      }
      barsContainer.innerHTML = barsHtml;
    }

    // 5. Render list
    renderReviewList();

  } catch (e) {
    console.error("Failed to load reviews:", e);
  }
}

function renderReviewList() {
  const container = document.getElementById('review-list');
  if (!container) return;
  
  if (allReviews.length === 0) {
    container.innerHTML = `
      <div style="text-align: center; color: var(--color-text-muted); padding: 40px 0;">
        <p>${"{{ __('No reviews yet. Be the first to review this product!') }}"}</p>
      </div>
    `;
    return;
  }
  
  const toDisplay = allReviews.slice(0, displayedCount);
  let html = toDisplay.map(r => {
    const name = r.user ? esc(r.user.name) : "{{ __('Anonymous') }}";
    const dateStr = new Date(r.created_at).toLocaleDateString('en-US', {
      year: 'numeric', month: 'short', day: 'numeric'
    });
    const stars = '★'.repeat(r.rating) + '☆'.repeat(5 - r.rating);
    const comment = r.comment ? esc(r.comment) : '';
    
    return `
      <div class="review-item">
        <div class="review-head">
          <div>
            <div class="review-user">${name}</div>
            <div class="stars" style="font-size:12px;margin-top:2px;color:var(--color-accent)">${stars}</div>
          </div>
          <div class="review-date">${dateStr}</div>
        </div>
        <div class="review-comment">
          ${comment ? `"${comment}"` : ''}
        </div>
      </div>
    `;
  }).join('');
  
  if (allReviews.length > displayedCount) {
    html += `
      <button class="btn-outline" id="btn-load-more-reviews" style="margin-top:16px" onclick="loadMoreReviews()">${"{{ __('Load More Reviews') }}"}</button>
    `;
  }
  
  container.innerHTML = html;
}

window.loadMoreReviews = function() {
  displayedCount += 5;
  renderReviewList();
};

function setRatingSelect(rating) {
  document.getElementById('review-rating').value = rating;
  const stars = document.querySelectorAll('.rating-stars-select span');
  stars.forEach((s, idx) => {
    if (idx < rating) {
      s.style.color = 'var(--color-accent)';
    } else {
      s.style.color = 'var(--color-text-faint)';
    }
  });
}
window.setRatingSelect = setRatingSelect;

function openReviewModal() {
  if (!Auth.token()) {
    showToast("{{ __('Please login to write a review.') }}", 'error');
    setTimeout(() => location.href = '/auth', 1500);
    return;
  }
  
  // Reset fields
  document.getElementById('review-rating').value = '5';
  document.getElementById('review-comment-textarea').value = '';
  setRatingSelect(5);
  
  document.getElementById('review-modal').style.display = 'flex';
}
window.openReviewModal = openReviewModal;

function closeReviewModal() {
  document.getElementById('review-modal').style.display = 'none';
}
window.closeReviewModal = closeReviewModal;

async function submitReview() {
  const rating = parseInt(document.getElementById('review-rating').value, 10) || 5;
  const comment = document.getElementById('review-comment-textarea').value.trim();
  const btn = document.getElementById('btn-submit-review-action');
  
  if (btn) {
    btn.disabled = true;
    btn.textContent = "{{ __('Submitting...') }}";
  }
  
  try {
    const res = await API.post('/reviews', {
      product_id: PRODUCT.id,
      rating: rating,
      comment: comment
    });
    
    showToast(res.message || "{{ __('Review submitted successfully! It will be visible after approval.') }}", 'success');
    closeReviewModal();
  } catch (e) {
    showToast(e.data?.message || "{{ __('Failed to submit review.') }}", 'error');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = "{{ __('Submit Review') }}";
    }
  }
}
window.submitReview = submitReview;

document.addEventListener('DOMContentLoaded', function() {
  if (typeof Cart !== 'undefined' && Cart.updateBadge) Cart.updateBadge();

  setTimeout(function() {
    updateTabIndicator(document.getElementById('tab-desc'));
  }, 100);

  window.addEventListener('resize', function() {
    updateTabIndicator(document.querySelector('.tab.active'));
  });

  loadRelated();
  loadReviews();
});
</script>
@endsection
