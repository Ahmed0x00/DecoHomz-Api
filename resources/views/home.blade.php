@extends('layouts.app')

@section('title', 'DecoHomz — Premium Furniture')

@section('extra_css')
<link rel="stylesheet" href="/css/home.css">
@endsection

@section('content')

<div class="hero">
  <div class="hero-text">
    <div class="hero-label">{{ __('New Collection 2026') }}</div>
    <h1 class="hero-h1">{!! __('Design Your<br>Space with Style') !!}</h1>
    <p class="hero-sub">{{ __('Premium furniture crafted for comfort and elegance.') }}</p>
    <div class="hero-btns">
      <button class="btn-dark" onclick="location.href='/shop'">{{ __('Shop Now') }}</button>
      <button class="btn-outline" onclick="location.href='/categories'">{{ __('Explore Collections') }}</button>
    </div>
  </div>
  <div class="hero-img">
    <svg viewBox="0 0 320 200" fill="none" xmlns="http://www.w3.org/2000/svg">
      <rect x="30" y="110" width="260" height="60" rx="10" fill="#8B6A48" />
      <rect x="30" y="90" width="44" height="58" rx="8" fill="#A07858" />
      <rect x="246" y="90" width="44" height="58" rx="8" fill="#A07858" />
      <rect x="74" y="102" width="72" height="68" rx="5" fill="#C4A882" />
      <rect x="174" y="102" width="72" height="68" rx="5" fill="#C4A882" />
      <rect x="48" y="170" width="18" height="20" rx="3" fill="#6B4832" />
      <rect x="254" y="170" width="18" height="20" rx="3" fill="#6B4832" />
      <ellipse cx="160" cy="195" rx="130" ry="6" fill="#C4A882" opacity="0.3" />
      <rect x="120" y="60" width="80" height="55" rx="30" fill="#D4B896" opacity="0.6" />
      <circle cx="220" cy="60" r="22" fill="#C4A882" opacity="0.7" />
      <rect x="217" y="30" width="3" height="30" rx="2" fill="#8B7060" />
      <ellipse cx="218" cy="28" rx="10" ry="6" fill="#5C7A40" opacity="0.75" />
    </svg>
  </div>
</div>

<div class="cats">
  <div class="sec-row">
    <div class="sec-title">{{ __('Shop by Category') }}</div>
    <a href="/shop" class="sec-link">{{ __('View All →') }}</a>
  </div>
  <div class="cat-row" id="cat-row">
    <!-- Loaded dynamically via JS -->
  </div>
</div>

<div class="products">
  <div class="sec-row">
    <div class="sec-title">{{ __('Best Sellers') }}</div>
    <a href="/shop" class="sec-link">{{ __('View All →') }}</a>
  </div>
  <div class="prod-grid" id="prod-grid">
    <!-- Loaded dynamically via JS -->
  </div>
</div>

<div class="banner">
  <div class="ban-left">
    <div class="ban-tag">{{ __('Limited Offer') }}</div>
    <div class="ban-h">{!! __('Up to 30% Off<br>Living Room Sets') !!}</div>
    <div class="ban-sub">{{ __('Refresh your home this season with our curated collection.') }}</div>
    <button class="btn-gold" onclick="location.href='/shop'">{{ __('Shop the Sale') }}</button>
  </div>
  <div class="ban-right">
    <svg viewBox="0 0 220 160" fill="none" width="220">
      <rect x="20" y="80" width="180" height="52" rx="8" fill="#8B6A48" opacity="0.7" />
      <rect x="20" y="62" width="38" height="58" rx="6" fill="#A07858" opacity="0.7" />
      <rect x="162" y="62" width="38" height="58" rx="6" fill="#A07858" opacity="0.7" />
      <rect x="58" y="72" width="46" height="60" rx="4" fill="#C4A882" opacity="0.6" />
      <rect x="116" y="72" width="46" height="60" rx="4" fill="#C4A882" opacity="0.6" />
      <rect x="28" y="132" width="14" height="18" rx="3" fill="#5C3D25" opacity="0.8" />
      <rect x="178" y="132" width="14" height="18" rx="3" fill="#5C3D25" opacity="0.8" />
    </svg>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  // Category SVG fallbacks keyed by category name
  const fallbackSvgs = {
    'Living Room': '<svg viewBox="0 0 48 48" fill="none"><rect x="4" y="24" width="40" height="16" rx="3" fill="#8B6A48"/><rect x="4" y="18" width="10" height="18" rx="2" fill="#A07858"/><rect x="34" y="18" width="10" height="18" rx="2" fill="#A07858"/><rect x="14" y="22" width="20" height="18" rx="2" fill="#C4A882" opacity="0.6"/></svg>',
    'Bedroom': '<svg viewBox="0 0 48 48" fill="none"><rect x="6" y="14" width="36" height="24" rx="2" fill="#8B6A48"/><rect x="6" y="26" width="36" height="12" rx="2" fill="#C4A882"/><rect x="10" y="38" width="4" height="6" rx="1" fill="#6B4832"/><rect x="34" y="38" width="4" height="6" rx="1" fill="#6B4832"/><rect x="10" y="18" width="12" height="6" rx="1" fill="#fff" opacity="0.3"/><rect x="26" y="18" width="12" height="6" rx="1" fill="#fff" opacity="0.3"/></svg>',
    'Dining': '<svg viewBox="0 0 48 48" fill="none"><rect x="8" y="24" width="32" height="6" rx="2" fill="#8B6A48"/><rect x="12" y="30" width="4" height="12" rx="1" fill="#6B4832"/><rect x="32" y="30" width="4" height="12" rx="1" fill="#6B4832"/><rect x="16" y="10" width="16" height="14" rx="2" fill="#A07858" opacity="0.8"/></svg>',
    'Office': '<svg viewBox="0 0 48 48" fill="none"><rect x="6" y="12" width="36" height="24" rx="2" fill="#8B6A48"/><rect x="10" y="16" width="28" height="6" rx="1" fill="#C4A882" opacity="0.4"/><rect x="10" y="24" width="28" height="6" rx="1" fill="#C4A882" opacity="0.4"/><rect x="16" y="36" width="16" height="4" rx="1" fill="#6B4832"/></svg>',
    'Outdoor': '<svg viewBox="0 0 48 48" fill="none"><rect x="12" y="20" width="24" height="20" rx="4" fill="#8B6A48"/><rect x="8" y="18" width="32" height="4" rx="2" fill="#A07858"/><rect x="16" y="40" width="4" height="6" rx="1" fill="#6B4832"/><rect x="28" y="40" width="4" height="6" rx="1" fill="#6B4832"/><circle cx="24" cy="12" r="6" fill="#B8860B" opacity="0.5"/></svg>',
    'Decor': '<svg viewBox="0 0 48 48" fill="none"><rect x="18" y="30" width="12" height="14" rx="2" fill="#6B4832"/><circle cx="24" cy="18" r="12" fill="#C4A882" opacity="0.6"/><path d="M24 6v6" stroke="#8B6A48" stroke-width="2"/></svg>',
  };

  function getCategorySvg(name) {
    return fallbackSvgs[name] || '<svg viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="20" fill="#C4A882"/></svg>';
  }

  function renderCategories(categories) {
    const row = document.getElementById('cat-row');
    if (!row) return;
    row.innerHTML = categories.map(function(cat) {
      return '<a href="/shop?category=' + encodeURIComponent(cat.name) + '" class="cat-item">' +
        '<div class="cat-box">' + getCategorySvg(cat.name) + '</div>' +
        '<div class="cat-name">' + cat.name + '</div>' +
        '</a>';
    }).join('');
  }

  function renderBestSellers(products) {
    const grid = document.getElementById('prod-grid');
    if (!grid) return;
    if (products.length === 0) {
      grid.innerHTML = '<p style="padding:20px;color:#888;text-align:center;grid-column:1/-1">' + "{{ __('No products found.') }}" + '</p>';
      return;
    }
    grid.innerHTML = products.map(function(p) {
      var imgUrl = (p.primary_image && p.primary_image.url) ? p.primary_image.url : '/img/placeholder.svg';
      var stars = p.stars || 5;
      var starsStr = '★'.repeat(stars) + '☆'.repeat(5 - stars);
      var badgeHtml = p.badge
        ? '<div class="prod-badge" style="background:' + (p.badge_color || '#B8860B') + '">' + p.badge + '</div>'
        : '';
      return '<div class="prod-card" data-id="' + p.id + '" style="cursor:pointer">' +
        '<div class="prod-img">' + badgeHtml +
        '<img src="' + imgUrl + '" alt="' + p.name + '" onerror="this.src=\'/img/placeholder.svg\'">' +
        '</div>' +
        '<div class="stars">' + starsStr + '</div>' +
        '<div class="prod-name">' + p.name + '</div>' +
        '<div class="prod-cat">' + (p.category ? p.category.name : '') + '</div>' +
        '<div class="prod-price">EGP ' + parseFloat(p.price).toLocaleString() + '</div>' +
        '<button class="btn-add-cart" onclick="event.stopPropagation(); homeAddToCart(' + p.id + ', \'' + escHtml(p.name) + '\', ' + p.price + ')" style="margin-top:8px;background:#2C1F14;color:#fff;border:none;padding:8px 12px;border-radius:4px;cursor:pointer;font-size:12px;width:100%">' + "{{ __('Add to Cart') }}" + '</button>' +
        '</div>';
    }).join('');

    grid.querySelectorAll('.prod-card').forEach(function(card) {
      card.addEventListener('click', function() {
        location.href = '/product/' + card.dataset.id;
      });
    });
  }

  window.homeAddToCart = function(id, name, price) {
    if (window.Cart && typeof Cart.add === 'function') {
      Cart.add({ id: id, name: name, price: price, quantity: 1, variant: 'Standard' });
      Cart.updateBadge();
    }
  };

  function escHtml(str) {
    return String(str).replace(/'/g, "\\'").replace(/"/g, '\\"');
  }

  (async function() {
    Cart.updateBadge();

    // Categories
    try {
      var res = await API.get('/categories');
      var categories = res.categories || [];
      renderCategories(categories);
    } catch(e) {}

    // Featured products
    try {
      var res2 = await API.get('/products/featured');
      var products = res2.products || [];
      renderBestSellers(products);
    } catch(e) {}
  })();
})();
</script>
@endsection
