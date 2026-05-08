@extends('layouts.app')

@section('title', 'Product — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/product.css">
@endsection

@section('content')

<input type="hidden" id="product-id" value="{{ $id }}">

<div class="breadcrumb">Home › <span id="bc-category">…</span> › <span id="bc-product">Loading…</span></div>

<div class="product-page" id="product-page">
  <!-- Filled by JS -->
  <div style="text-align:center;padding:60px;color:#aaa">Loading product…</div>
</div>

<div class="tabs-section" id="tabs-section" style="display:none">
  <div class="tabs">
    <div class="tab active" data-tab="description">Description</div>
    <div class="tab" data-tab="specs">Specifications</div>
    <div class="tab" data-tab="reviews" id="reviews-tab">Reviews (0)</div>
  </div>
  <div class="tab-content" id="tab-content">
    <div id="tab-description"></div>
    <div class="spec-grid" id="tab-specs" style="display:none"></div>
    <div id="tab-reviews" style="display:none"></div>
  </div>
</div>

<div class="related" id="related-section" style="display:none">
  <div class="sec-row">
    <div class="sec-title">You might also like</div>
    <a href="/shop" class="sec-link">View All →</a>
  </div>
  <div class="rel-grid" id="related-grid"></div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  var productId = document.getElementById('product-id').value;
  var currentProduct = null;
  var selectedColor = null;
  var selectedSize = null;
  var quantity = 1;

  // ── Bootstrap ────────────────────────────────────────────
  (async function() {
    Cart.updateBadge();
    initTabs();
    initQuantityButtons();

    try {
      var res = await API.get('/products/' + productId);
      currentProduct = res.product;
      renderProduct(currentProduct, res.rating || {});
      initAddToCart();
      initColorSelection();
      initSizeSelection();
      loadRelated();
      loadReviews();
    } catch(e) {
      document.getElementById('product-page').innerHTML =
        '<div style="text-align:center;padding:60px;color:#aaa"><p>Product not found.</p><a href="/shop" class="btn-dark" style="display:inline-block;margin-top:16px">Back to Shop</a></div>';
    }
  })();

  // ── Render product ───────────────────────────────────────
  function renderProduct(p, rating) {
    // Page title
    document.title = p.name + ' — DecoHomz';

    // Breadcrumb
    document.getElementById('bc-category').textContent = p.category ? p.category.name : '';
    document.getElementById('bc-product').textContent = p.name;

    // Images
    var images = p.images || [];
    var primaryImg = p.primaryImage;
    var displayUrl = (primaryImg && primaryImg.url) ? primaryImg.url : (images[0] ? images[0].url : '/img/placeholder.svg');

    var mainImgHtml = '<img src="' + displayUrl + '" alt="' + p.name + '" style="width:100%;height:100%;object-fit:contain" onerror="this.outerHTML=\'<svg viewBox=&quot;0 0 240 200&quot; fill=&quot;none&quot;><rect width=&quot;240&quot; height=&quot;200&quot; fill=&quot;#F5F0E8&quot;/></svg>\'">';

    var thumbHtml = images.map(function(img, i) {
      var url = img.url || '/img/placeholder.svg';
      return '<div class="thumb' + (i === 0 ? ' active' : '') + '" data-url="' + url + '" style="cursor:pointer">' +
        '<img src="' + url + '" alt="" onerror="this.style.display=\'none\'"></div>';
    }).join('');

    // Rating
    var avgRating = rating.average || p.stars || 5;
    var reviewCount = rating.count || 0;
    var starsStr = '★'.repeat(Math.round(avgRating)) + '☆'.repeat(5 - Math.round(avgRating));

    // Price
    var priceHtml = '<span class="main-price">EGP ' + parseFloat(p.price).toLocaleString() + '</span>';
    if (p.old_price && parseFloat(p.old_price) > parseFloat(p.price)) {
      var discount = Math.round((1 - parseFloat(p.price) / parseFloat(p.old_price)) * 100);
      priceHtml += '<span class="old-price">EGP ' + parseFloat(p.old_price).toLocaleString() + '</span>';
      priceHtml += '<span class="sale-tag">' + discount + '% Off</span>';
    }

    // Colors
    var colorsHtml = '';
    if (p.colors && p.colors.length > 0) {
      colorsHtml = '<div class="option-label">Color</div>' +
        '<div class="color-row" id="color-row">' +
        p.colors.map(function(c, i) {
          return '<div class="color-swatch' + (i === 0 ? ' active' : '') + '" style="background:' + c + '" data-color="' + c + '"></div>';
        }).join('') +
        '</div>';
    }

    // Sizes (static for now — no size field in DB)
    var sizesHtml = '';
    // Sizes section kept for UX but driven by static data or could be extended

    // Perks
    var perksHtml = '<div class="perks">' +
      '<div class="perk"><svg viewBox="0 0 24 24" stroke-width="1.5"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg><div class="perk-text">Free delivery above EGP 2,000</div></div>' +
      '<div class="perk"><svg viewBox="0 0 24 24" stroke-width="1.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg><div class="perk-text">Easy 14-day returns</div></div>' +
      '<div class="perk"><svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg><div class="perk-text">5-year warranty</div></div>' +
      '<div class="perk"><svg viewBox="0 0 24 24" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg><div class="perk-text">Secure payment</div></div>' +
      '</div>';

    document.getElementById('product-page').innerHTML =
      '<div class="img-section">' +
        '<div class="main-img-wrapper">' +
          '<div class="main-img" id="main-img">' + mainImgHtml + '</div>' +
        '</div>' +
        '<div class="thumb-row" id="thumb-row">' + thumbHtml + '</div>' +
      '</div>' +
      '<div class="info-section">' +
        '<div class="prod-meta">' +
          '<span class="prod-cat-tag">' + (p.category ? p.category.name : 'Furniture') + '</span>' +
          (p.stock > 0 ? '<span class="stock-tag in">In Stock</span>' : '<span class="stock-tag out">Out of Stock</span>') +
        '</div>' +
        '<h1 class="prod-title">' + p.name + '</h1>' +
        '<div class="rating-row">' +
          '<div class="stars-outer"><div class="stars-inner" style="width:' + (avgRating / 5 * 100) + '%"></div></div>' +
          '<span class="rating-text">' + avgRating.toFixed(1) + ' (' + reviewCount + ' reviews)</span>' +
        '</div>' +
        '<div class="price-container">' +
          priceHtml +
        '</div>' +
        '<p class="short-desc">' + (p.description ? p.description.substring(0, 160) + '...' : 'Premium quality piece designed for modern living spaces.') + '</p>' +
        '<div class="options-container">' +
          colorsHtml +
        '</div>' +
        '<div class="purchase-section">' +
          '<div class="qty-selector">' +
            '<button class="qty-btn" id="qty-minus" aria-label="Decrease quantity">−</button>' +
            '<span class="qty-num" id="qty-num">1</span>' +
            '<button class="qty-btn" id="qty-plus" aria-label="Increase quantity">+</button>' +
          '</div>' +
          '<button class="btn-primary-lg" id="add-to-cart-btn">' +
            '<span>Add to Cart</span>' +
            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4H6z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>' +
          '</button>' +
        '</div>' +
        '<div class="trust-badges">' +
          '<div class="trust-item"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg><span>5-Year Warranty</span></div>' +
          '<div class="trust-item"><svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg><span>Fast Shipping</span></div>' +
          '<div class="trust-item"><svg viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg><span>Easy Returns</span></div>' +
        '</div>' +
      '</div>';

    // Show tabs now that product loaded
    document.getElementById('tabs-section').style.display = 'block';

    // Set initial color
    if (p.colors && p.colors.length > 0) {
      selectedColor = p.colors[0];
    }

    // Description content
    document.getElementById('tab-description').textContent =
      p.description || 'Premium quality furniture crafted for your home.';

    // Reviews tab label
    document.getElementById('reviews-tab').textContent = 'Reviews (' + reviewCount + ')';
  }

  // ── Image gallery ────────────────────────────────────────
  document.addEventListener('click', function(e) {
    var thumb = e.target.closest('.thumb');
    if (!thumb) return;
    var url = thumb.dataset.url;
    if (!url) return;
    var mainImg = document.getElementById('main-img');
    if (mainImg) {
      mainImg.innerHTML = '<img src="' + url + '" alt="" style="width:100%;height:100%;object-fit:contain" onerror="this.outerHTML=\'<svg viewBox=&quot;0 0 240 200&quot; fill=&quot;none&quot;><rect width=&quot;240&quot; height=&quot;200&quot; fill=&quot;#F5F0E8&quot;/></svg>\'">';
    }
    document.querySelectorAll('.thumb').forEach(function(t) { t.classList.remove('active'); });
    thumb.classList.add('active');
  });

  // ── Quantity ─────────────────────────────────────────────
  function initQuantityButtons() {
    document.addEventListener('click', function(e) {
      var btn = e.target.closest('.qty-btn');
      if (!btn) return;
      
      var numEl = document.getElementById('qty-num');
      if (!numEl) return;
      var val = parseInt(numEl.textContent) || 1;
      
      if (btn.id === 'qty-minus' && val > 1) {
        numEl.textContent = val - 1;
        quantity = val - 1;
      }
      if (btn.id === 'qty-plus') {
        numEl.textContent = val + 1;
        quantity = val + 1;
      }
    });
  }

  // ── Color selection ─────────────────────────────────────
  function initColorSelection() {
    document.addEventListener('click', function(e) {
      var swatch = e.target.closest('.color-swatch');
      if (!swatch) return;
      document.querySelectorAll('.color-swatch').forEach(function(s) { s.classList.remove('active'); });
      swatch.classList.add('active');
      selectedColor = swatch.dataset.color;
    });
  }

  // ── Size selection ───────────────────────────────────────
  function initSizeSelection() {
    document.addEventListener('click', function(e) {
      var btn = e.target.closest('.size-btn');
      if (!btn) return;
      document.querySelectorAll('.size-btn').forEach(function(b) { b.classList.remove('active'); });
      btn.classList.add('active');
      selectedSize = btn.textContent;
    });
  }

  // ── Add to Cart ──────────────────────────────────────────
  function initAddToCart() {
    document.addEventListener('click', function(e) {
      var btn = e.target.closest('#add-to-cart-btn');
      if (!btn) return;
      if (!currentProduct) return;
      var qtyEl = document.getElementById('qty-num');
      var qty = parseInt(qtyEl ? qtyEl.textContent : 1) || 1;
      var variant = selectedSize || 'Standard';
      Cart.add({
        id: currentProduct.id,
        name: currentProduct.name,
        price: parseFloat(currentProduct.price),
        quantity: qty,
        variant: variant,
        image: currentProduct.primaryImage ? currentProduct.primaryImage.url : null
      });
    });
  }

  // ── Tabs ────────────────────────────────────────────────
  function initTabs() {
    document.addEventListener('click', function(e) {
      var tab = e.target.closest('.tab');
      if (!tab) return;
      var tabName = tab.dataset.tab;
      document.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
      tab.classList.add('active');

      document.getElementById('tab-description').style.display = 'none';
      document.getElementById('tab-specs').style.display = 'none';
      document.getElementById('tab-reviews').style.display = 'none';

      if (tabName === 'description') {
        document.getElementById('tab-description').style.display = 'block';
      } else if (tabName === 'specs') {
        document.getElementById('tab-specs').style.display = 'grid';
        renderSpecs();
      } else if (tabName === 'reviews') {
        document.getElementById('tab-reviews').style.display = 'block';
      }
    });
  }

  function renderSpecs() {
    var el = document.getElementById('tab-specs');
    if (!el || el.dataset.loaded) return;
    el.dataset.loaded = '1';
    if (!currentProduct) return;

    var specs = [];
    if (currentProduct.dimensions) specs.push({ key: 'Dimensions', val: currentProduct.dimensions });
    if (currentProduct.weight) specs.push({ key: 'Weight', val: currentProduct.weight });
    if (currentProduct.material) specs.push({ key: 'Material', val: currentProduct.material });
    if (currentProduct.upholstery) specs.push({ key: 'Upholstery', val: currentProduct.upholstery });
    specs.push({ key: 'Availability', val: (currentProduct.stock > 0) ? 'In Stock (' + currentProduct.stock + ')' : 'Out of Stock' });

    // 2-column grid
    var half = Math.ceil(specs.length / 2);
    var col1 = specs.slice(0, half);
    var col2 = specs.slice(half);

    var html = '<div>';
    col1.forEach(function(s) { html += '<div class="spec-row"><span class="key">' + s.key + '</span><span class="val">' + s.val + '</span></div>'; });
    html += '</div><div>';
    col2.forEach(function(s) { html += '<div class="spec-row"><span class="key">' + s.key + '</span><span class="val">' + s.val + '</span></div>'; });
    html += '</div>';
    el.innerHTML = html;
  }

  // ── Reviews ──────────────────────────────────────────────
  async function loadReviews() {
    try {
      var res = await API.get('/products/' + currentProduct.id + '/reviews');
      renderReviews(res.reviews || [], res.stats || {});
    } catch(e) {}
  }

  function renderReviews(reviews, stats) {
    document.getElementById('reviews-tab').textContent = 'Reviews (' + (stats.count || 0) + ')';

    var container = document.getElementById('tab-reviews');
    if (!container) return;

    var isLoggedIn = !!Auth.token();
    var formHtml = '';

    if (isLoggedIn) {
      formHtml = '<div style="margin-bottom:28px;padding:20px;border:1px solid #EDE8E2;border-radius:10px;background:#FAFAF7">' +
        '<div style="font-weight:600;margin-bottom:14px">Write a Review</div>' +
        '<div style="margin-bottom:12px">' +
          '<label style="font-size:13px;color:#666;display:block;margin-bottom:6px">Your Rating</label>' +
          '<div class="star-picker" id="star-picker">' +
            '<span class="star-btn" data-val="1">★</span>' +
            '<span class="star-btn" data-val="2">★</span>' +
            '<span class="star-btn" data-val="3">★</span>' +
            '<span class="star-btn" data-val="4">★</span>' +
            '<span class="star-btn" data-val="5">★</span>' +
          '</div>' +
        '</div>' +
        '<div style="margin-bottom:12px">' +
          '<label style="font-size:13px;color:#666;display:block;margin-bottom:6px">Your Review</label>' +
          '<textarea id="review-comment" rows="4" placeholder="Share your experience with this product..." style="width:100%;padding:10px;border:1px solid #DDD;border-radius:6px;font-size:14px;resize:vertical;font-family:inherit"></textarea>' +
        '</div>' +
        '<button class="btn-dark" id="btn-submit-review" onclick="submitReview()" style="padding:10px 24px;font-size:14px;border:none;cursor:pointer;border-radius:6px">Submit Review</button>' +
        '<div id="review-msg" style="margin-top:10px;font-size:13px;display:none"></div>' +
        '</div>';
    } else {
      formHtml = '<div style="margin-bottom:24px;padding:16px;background:#FFF8E6;border:1px solid #F0E0B0;border-radius:8px;text-align:center">' +
        '<a href="/auth" onclick="location.href=\'/auth\'" style="color:#C9A96E;font-weight:600">Sign in</a> to write a review' +
        '</div>';
    }

    if (reviews.length === 0) {
      container.innerHTML = formHtml + '<p style="color:#aaa;text-align:center;padding:20px">No reviews yet. Be the first to review this product!</p>';
      initStarPicker(5);
      return;
    }

    var reviewsHtml = '';
    reviews.forEach(function(r) {
      var starsStr = '★'.repeat(r.rating) + '☆'.repeat(5 - r.rating);
      var userName = r.user ? r.user.name : 'Anonymous';
      var date = r.created_at ? new Date(r.created_at).toLocaleDateString() : '';
      reviewsHtml += '<div style="margin-bottom:20px;padding:16px;border:1px solid #EDE8E2;border-radius:8px">' +
        '<div style="color:#B8860B;margin-bottom:6px">' + starsStr + '</div>' +
        '<div style="font-weight:600;margin-bottom:4px">' + userName + '</div>' +
        '<div style="font-size:11px;color:#888;margin-bottom:8px">Verified Purchase' + (date ? ' · ' + date : '') + '</div>' +
        '<div style="color:#444;font-size:14px">' + (r.comment || '') + '</div>' +
        '</div>';
    });

    container.innerHTML = formHtml + reviewsHtml;
    initStarPicker(5);
  }

  function initStarPicker(defaultVal) {
    var picker = document.getElementById('star-picker');
    if (!picker) return;
    window._reviewRating = defaultVal;
    function setStars(val) {
      window._reviewRating = val;
      picker.querySelectorAll('.star-btn').forEach(function(s) {
        s.style.color = parseInt(s.dataset.val) <= val ? '#C9A96E' : '#DDD';
      });
    }
    picker.querySelectorAll('.star-btn').forEach(function(s) {
      s.style.cursor = 'pointer';
      s.style.fontSize = '20px';
      s.style.transition = 'color .1s';
      s.addEventListener('click', function() { setStars(parseInt(this.dataset.val)); });
    });
    setStars(defaultVal);
  }

  window.submitReview = async function() {
    var btn = document.getElementById('btn-submit-review');
    var msg = document.getElementById('review-msg');
    var comment = document.getElementById('review-comment').value.trim();
    var rating = window._reviewRating || 5;

    if (!rating) { msg.style.color = '#D44'; msg.style.display = 'block'; msg.textContent = 'Please select a star rating.'; return; }

    btn.disabled = true;
    btn.textContent = 'Submitting...';
    msg.style.display = 'none';

    try {
      await API.post('/reviews', { product_id: currentProduct.id, rating: rating, comment: comment });
      msg.style.color = '#2A2'; msg.style.display = 'block'; msg.textContent = 'Review submitted! It will appear after approval.';
      document.getElementById('review-comment').value = '';
      initStarPicker(5);
    } catch(e) {
      msg.style.color = '#D44'; msg.style.display = 'block';
      msg.textContent = e.data?.message || 'Failed to submit review. Please try again.';
    } finally {
      btn.disabled = false;
      btn.textContent = 'Submit Review';
    }
  }

  // ── Related products ─────────────────────────────────────
  async function loadRelated() {
    try {
      var res = await API.get('/products/' + currentProduct.id + '/related');
      var products = res.products || [];
      if (products.length > 0) {
        renderRelated(products);
        document.getElementById('related-section').style.display = 'block';
      }
    } catch(e) {}
  }

  function renderRelated(products) {
    var grid = document.getElementById('related-grid');
    grid.innerHTML = products.map(function(p) {
      var imgUrl = (p.primaryImage && p.primaryImage.url) ? p.primaryImage.url : '/img/placeholder.svg';
      return '<div class="rel-card" onclick="location.href=\'/product/' + p.id + '\'" style="cursor:pointer">' +
        '<div class="rel-img"><img src="' + imgUrl + '" alt="' + p.name + '" onerror="this.src=\'/img/placeholder.svg\'"></div>' +
        '<div class="rel-info">' +
          '<div class="rel-name">' + p.name + '</div>' +
          '<div class="rel-price">EGP ' + parseFloat(p.price).toLocaleString() + '</div>' +
        '</div>' +
      '</div>';
    }).join('');
  }
})();
</script>
@endsection
