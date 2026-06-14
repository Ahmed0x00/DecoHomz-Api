@extends('layouts.app')

@section('title', 'New Arrivals — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/shop.css">
@endsection

@section('content')

<div class="breadcrumb">Home › <span>New Arrivals</span></div>

<div class="shop-layout">
  <div class="sidebar">
    <div class="filter-group">
      <div class="filter-title">Newest Collections</div>
      <p style="font-size:12px; color:#888; line-height:1.6">Explore our latest additions to the DecoHomz family. Modern designs for modern homes.</p>
    </div>
  </div>
  <div class="main">
    <div class="main-top">
      <div class="result-count">Showing <span id="count">0</span> latest products</div>
    </div>
    <div id="product-grid" class="prod-grid"></div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  Cart.updateBadge();
  loadNewArrivals();

  async function loadNewArrivals() {
    var grid = document.getElementById('product-grid');
    grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:40px;color:#aaa">Loading...</p>';

    try {
      var res = await API.get('/products?sort=newest');
      var products = res.products || [];
      document.getElementById('count').textContent = products.length;
      renderProducts(products);
    } catch(e) {
      grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:40px;color:#aaa">Failed to load products.</p>';
    }
  }

  function renderProducts(products) {
    var grid = document.getElementById('product-grid');
    if (products.length === 0) {
      grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:40px;color:#aaa">No new arrivals yet.</p>';
      return;
    }

    grid.innerHTML = products.map(function(p) {
      var imgUrl = (p.primary_image && p.primary_image.url) ? p.primary_image.url : '/img/placeholder.svg';
      var stars = p.stars || 5;
      var starsStr = '★'.repeat(stars) + '☆'.repeat(5 - stars);
      var badgeColor = p.badge_color || '#B8860B';
      var badgeHtml = p.badge ? '<div class="prod-badge" style="background:' + badgeColor + '">' + p.badge + '</div>' : '';
      var oldPriceHtml = p.old_price ? '<s style="color:#aaa;font-size:13px">EGP ' + parseFloat(p.old_price).toLocaleString() + '</s>' : '';

      return '<div class="prod-card" data-id="' + p.id + '" style="cursor:pointer">' +
        '<div class="prod-img">' + badgeHtml +
        '<img src="' + imgUrl + '" alt="' + p.name + '" onerror="this.src=\'/img/placeholder.svg\'">' +
        '</div>' +
        '<div class="stars">' + starsStr + '</div>' +
        '<div class="prod-name">' + p.name + '</div>' +
        '<div class="prod-cat">' + (p.category ? p.category.name : '') + '</div>' +
        '<div class="prod-price">EGP ' + parseFloat(p.price).toLocaleString() + ' ' + oldPriceHtml + '</div>' +
        '</div>';
    }).join('');

    grid.querySelectorAll('.prod-card').forEach(function(card) {
      card.addEventListener('click', function() {
        window.location.href = '/product/' + card.dataset.id;
      });
    });
  }
})();
</script>
@endsection
