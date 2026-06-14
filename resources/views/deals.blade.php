@extends('layouts.app')

@section('title', 'Deals & Sales — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/shop.css">
@endsection

@section('content')

<div class="breadcrumb">Home › <span>Deals & Sales</span></div>

<div class="shop-layout">
  <div class="sidebar">
    <div class="filter-group">
      <div class="filter-title">Special Offers</div>
      <p style="font-size:12px; color:#888; line-height:1.6">Grab the best deals before they're gone. These items are limited in stock.</p>
    </div>
  </div>
  <div class="main">
    <div class="main-top">
      <div class="result-count">Found <span id="count">0</span> items on sale</div>
    </div>
    <div id="product-grid" class="prod-grid"></div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  Cart.updateBadge();
  loadDeals();

  async function loadDeals() {
    var grid = document.getElementById('product-grid');
    grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:40px;color:#aaa">Loading...</p>';

    try {
      var res = await API.get('/products');
      var allProducts = res.products || [];

      // Filter to items with a discount (old_price set)
      var deals = allProducts.filter(function(p) {
        return p.old_price && parseFloat(p.old_price) > 0;
      });

      document.getElementById('count').textContent = deals.length;
      renderProducts(deals);
    } catch(e) {
      grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:40px;color:#aaa">Failed to load deals.</p>';
    }
  }

  function renderProducts(products) {
    var grid = document.getElementById('product-grid');
    if (products.length === 0) {
      grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:40px;color:#aaa">No deals available right now. Check back soon!</p>';
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
