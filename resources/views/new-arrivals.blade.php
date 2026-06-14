@extends('layouts.app')

@section('title', 'New Arrivals — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/shop.css">
@endsection

@section('content')

{{-- ═══ HEADER ═══ --}}
<div class="shop-header">
  <div class="shop-header-inner animate-fade-up">
    <h1>{{ __('New Arrivals') }}</h1>
    <div class="shop-header-sub">{{ __('Explore our latest additions to the DecoHomz family. Modern designs for modern homes.') }}</div>
  </div>
</div>

<div class="breadcrumb animate-fade-up" style="border-bottom:none">
  <a href="/">{{ __('Home') }}</a> › <span>{{ __('New Arrivals') }}</span>
</div>

<div class="shop-layout">
  <aside class="shop-sidebar animate-fade-up">
    <div class="sidebar-title">{{ __('Newest Collections') }}</div>
    <div class="filter-group" style="border-bottom:none; margin-bottom:0; padding-bottom:0">
      <p style="font-size:14px; color:var(--color-text-secondary); line-height:1.6">{{ __('Explore our latest additions to the DecoHomz family. Modern designs for modern homes.') }}</p>
    </div>
  </aside>
  
  <div class="main">
    <div class="main-top animate-fade-up stagger-1">
      <div class="result-count">{{ __('Showing') }} <span id="count">0</span> {{ __('latest products') }}</div>
    </div>
    <div id="product-grid" class="prod-grid animate-fade-up stagger-2">
      {{-- Loading skeleton cards --}}
      @for($i = 0; $i < 6; $i++)
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

@endsection

@section('extra_js')
<script>
(function() {
  Cart.updateBadge();
  loadNewArrivals();

  window.shopAddToCart = async function(event, id, name, price) {
    if (event) event.stopPropagation();
    if (!window.Cart || typeof Cart.add !== 'function') return;

    await Cart.add({ id: id, name: name, price: price, quantity: 1, variant: 'Standard' });
    if (typeof openCart === 'function') openCart();
  };

  async function loadNewArrivals() {
    var grid = document.getElementById('product-grid');

    try {
      var res = await API.get('/products?sort=newest');
      var products = res.products || [];
      document.getElementById('count').textContent = products.length;
      renderProducts(products);
    } catch(e) {
      grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:40px;color:var(--color-error)">' + "{{ __('Failed to load products.') }}" + '</p>';
    }
  }

  function renderProducts(products) {
    var grid = document.getElementById('product-grid');
    if (products.length === 0) {
      grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:40px;color:var(--color-text-faint)">' + "{{ __('No new arrivals yet.') }}" + '</p>';
      return;
    }

    grid.innerHTML = products.map(function(p, i) {
      var imgUrl = (p.primary_image && p.primary_image.thumbnail_url) ? p.primary_image.thumbnail_url : ((p.primary_image && p.primary_image.url) ? p.primary_image.url : '/img/placeholder.svg');
      var stars = p.stars || 5;
      var starsStr = '★'.repeat(stars) + '☆'.repeat(5 - stars);
      var badgeColor = p.badge_color || 'var(--color-accent)';
      var badgeHtml = p.badge ? '<div class="prod-badge" style="background:' + badgeColor + '">' + esc(p.badge) + '</div>' : '';
      var oldPriceHtml = p.old_price ? ' <s>EGP ' + parseFloat(p.old_price).toLocaleString() + '</s>' : '';
      var productUrl = '/product/' + (p.slug || p.id);

      return '<div class="prod-card animate-fade-up stagger-' + (i % 8 + 1) + '" data-id="' + p.id + '" onclick="location.href=\'' + productUrl + '\'">' +
        '<div class="prod-img">' + badgeHtml +
        '<img src="' + imgUrl + '" alt="' + esc(p.name) + '" loading="lazy" onerror="this.src=\'/img/placeholder.svg\'">' +
        '</div>' +
        '<div class="prod-info">' +
        '<div class="stars">' + starsStr + '</div>' +
        '<div class="prod-name">' + esc(p.name) + '</div>' +
        '<div class="prod-cat">' + (p.category ? esc(p.category.name) : '') + '</div>' +
        '<div class="prod-price">EGP ' + parseFloat(p.price).toLocaleString() + oldPriceHtml + '</div>' +
        '<button class="btn-add-cart" onclick="shopAddToCart(event, ' + p.id + ', \'' + esc(p.name).replace(/'/g, "\\'") + '\', ' + (p.price || 0) + ')">' + "{{ __('Add to Cart') }}" + '</button>' +
        '</div>' +
        '</div>';
    }).join('');
  }
})();
</script>
@endsection
