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
        <div class="stars">
          @php $stars = $product['stars'] ?? 5; @endphp
          {{ str_repeat('★', $stars) }}{{ str_repeat('☆', 5 - $stars) }}
        </div>
        <span>(128 {{ __('Reviews') }})</span>
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
      @php $colors = $product->colors ?? collect(); @endphp
      @if($colors->count() > 0)
      <div class="options-group">
        <div class="opt-label">
          <span>{{ __('Color') }}</span>
          <span class="opt-val" id="color-val">{{ $colors->first()->name ?? 'Standard' }}</span>
        </div>
        <div class="color-row">
          @foreach($colors as $i => $color)
            <div class="color-swatch {{ $i === 0 ? 'active' : '' }}" style="background:{{ $color->hex_code ?? '#4A3626' }}" title="{{ $color->name }}" onclick="selectColor(this, '{{ addslashes($color->name ?? 'Standard') }}')"></div>
          @endforeach
        </div>
        <input type="hidden" id="selected-color" value="{{ $colors->first()->name ?? 'Standard' }}">
      </div>
      @else
      <input type="hidden" id="selected-color" value="Standard">
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
          <h5>{{ __('Free Delivery') }}</h5>
          <p>{{ __('Estimated arrival: 3-5 business days.') }}</p>
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
    <div class="tab" id="tab-reviews" onclick="switchTab('reviews')">{{ __('Reviews') }} (128)</div>
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
            <h3>4.8</h3>
            <div class="stars">★★★★★</div>
            <div style="font-size:13px;color:var(--color-text-secondary)">{{ __('Based on 128 reviews') }}</div>
            
            <div class="review-bars">
              <div class="r-bar-row">
                <div class="r-bar-label">5★</div>
                <div class="r-bar-track"><div class="r-bar-fill" style="width:85%"></div></div>
                <div class="r-bar-count">109</div>
              </div>
              <div class="r-bar-row">
                <div class="r-bar-label">4★</div>
                <div class="r-bar-track"><div class="r-bar-fill" style="width:10%"></div></div>
                <div class="r-bar-count">12</div>
              </div>
              <div class="r-bar-row">
                <div class="r-bar-label">3★</div>
                <div class="r-bar-track"><div class="r-bar-fill" style="width:4%"></div></div>
                <div class="r-bar-count">5</div>
              </div>
              <div class="r-bar-row">
                <div class="r-bar-label">2★</div>
                <div class="r-bar-track"><div class="r-bar-fill" style="width:1%"></div></div>
                <div class="r-bar-count">2</div>
              </div>
              <div class="r-bar-row">
                <div class="r-bar-label">1★</div>
                <div class="r-bar-track"><div class="r-bar-fill" style="width:0%"></div></div>
                <div class="r-bar-count">0</div>
              </div>
            </div>
            
            <button class="btn-outline" style="width:100%;margin-top:24px">{{ __('Write a Review') }}</button>
          </div>
        </div>
        
        <div class="review-list">
          <div class="review-item">
            <div class="review-head">
              <div>
                <div class="review-user">Ahmed M.</div>
                <div class="stars" style="font-size:12px;margin-top:2px;color:var(--color-accent)">★★★★★</div>
              </div>
              <div class="review-date">2 weeks ago</div>
            </div>
            <div class="review-comment">
              "{{ __('Absolutely stunning! The build quality is excellent and it fits perfectly in my living room. Delivery was on time and the setup was a breeze.') }}"
            </div>
          </div>
          <div class="review-item">
            <div class="review-head">
              <div>
                <div class="review-user">Sara K.</div>
                <div class="stars" style="font-size:12px;margin-top:2px;color:var(--color-accent)">★★★★★</div>
              </div>
              <div class="review-date">1 month ago</div>
            </div>
            <div class="review-comment">
              "{{ __('Very comfortable and looks exactly like the pictures. I was hesitant to buy furniture online but DecoHomz exceeded my expectations.') }}"
            </div>
          </div>
          <button class="btn-outline" style="margin-top:16px">{{ __('Load More Reviews') }}</button>
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

window.changeImage = function(el, url) {
  document.querySelectorAll('.thumb').forEach(function(t) { t.classList.remove('active'); });
  el.classList.add('active');

  const mainImg = document.getElementById('main-image');
  if (!mainImg) return;
  mainImg.style.opacity = '0';
  setTimeout(function() {
    mainImg.src = url;
    mainImg.style.opacity = '1';
  }, 200);
};

window.selectColor = function(el, color) {
  document.querySelectorAll('.color-swatch').forEach(function(sw) { sw.classList.remove('active'); });
  el.classList.add('active');
  document.getElementById('selected-color').value = color;
  const colorVal = document.getElementById('color-val');
  if (colorVal) colorVal.textContent = color;
};

window.updateLocalQty = function(delta) {
  const input = document.getElementById('selected-qty');
  const display = document.getElementById('qty-val');
  let current = parseInt(input.value, 10) || 1;
  current += delta;
  if (current < 1) current = 1;
  if (current > 10) current = 10;

  input.value = current;
  display.textContent = current;
};

window.submitAddToCart = async function() {
  if (PRODUCT.stock <= 0) {
    showToast("{{ __('This product is out of stock.') }}", 'error');
    return;
  }

  const qty = parseInt(document.getElementById('selected-qty').value, 10) || 1;
  const color = document.getElementById('selected-color').value;
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
      variant: color
    });

    if (typeof openCart === 'function') openCart();
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.querySelector('span').textContent = "{{ __('Add to Cart') }}";
    }
  }
};

window.switchTab = function(tabId) {
  document.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
  document.querySelectorAll('.tab-pane').forEach(function(p) { p.classList.remove('active'); });

  const targetTab = document.getElementById('tab-' + tabId);
  if (targetTab) targetTab.classList.add('active');

  const pane = document.getElementById('pane-' + tabId);
  if (pane) pane.classList.add('active');

  updateTabIndicator(targetTab);
};

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

document.addEventListener('DOMContentLoaded', function() {
  if (typeof Cart !== 'undefined' && Cart.updateBadge) Cart.updateBadge();

  setTimeout(function() {
    updateTabIndicator(document.getElementById('tab-desc'));
  }, 100);

  window.addEventListener('resize', function() {
    updateTabIndicator(document.querySelector('.tab.active'));
  });

  loadRelated();
});
</script>
@endsection
