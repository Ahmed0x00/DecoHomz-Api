@extends('layouts.app')

@section('title', 'Shop — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/shop.css">
@endsection

@section('content')

<div class="breadcrumb">Home › <span id="breadcrumb-label">Living Room</span></div>

<div class="shop-layout">
  <div class="sidebar">
    <div class="filter-group">
      <div class="filter-title">Category</div>
      <div id="category-filters">
        <!-- Loaded via JS -->
      </div>
    </div>
    <div class="filter-group">
      <div class="filter-title">Price (EGP)</div>
      <div class="price-range">
        <input type="number" id="min-price" placeholder="Min" value="500">
        <span>–</span>
        <input type="number" id="max-price" placeholder="Max" value="20000">
      </div>
    </div>
    <div class="filter-group">
      <div class="filter-title">Material</div>
      <div id="material-filters">
        <div class="filter-item"><input type="checkbox" value="Wood" id="mat-wood"><label for="mat-wood">Wood</label></div>
        <div class="filter-item"><input type="checkbox" value="Metal" id="mat-metal"><label for="mat-metal">Metal</label></div>
        <div class="filter-item"><input type="checkbox" value="Fabric" id="mat-fabric"><label for="mat-fabric">Fabric</label></div>
        <div class="filter-item"><input type="checkbox" value="Marble" id="mat-marble"><label for="mat-marble">Marble</label></div>
      </div>
    </div>
    <div class="filter-group">
      <div class="filter-title">Color</div>
      <div class="color-row" id="color-filters">
        <div class="color-dot" data-color="#C4A882" style="background:#C4A882"></div>
        <div class="color-dot" data-color="#5C3D2A" style="background:#5C3D2A"></div>
        <div class="color-dot" data-color="#E8E0D4" style="background:#E8E0D4"></div>
        <div class="color-dot" data-color="#4A5240" style="background:#4A5240"></div>
        <div class="color-dot" data-color="#888" style="background:#888"></div>
        <div class="color-dot" data-color="#E8E8E8" style="background:#E8E8E8;border:1px solid #ccc"></div>
      </div>
    </div>
    <button class="btn-apply" id="apply-filters">Apply Filters</button>
  </div>

  <div class="main">
    <div class="main-top">
      <div class="result-count" id="result-count">Loading...</div>
      <div class="sort-row">
        <select id="sort-select">
          <option value="featured">Sort: Featured</option>
          <option value="price-low">Price: Low to High</option>
          <option value="price-high">Price: High to Low</option>
          <option value="newest">Newest</option>
        </select>
        <div class="grid-toggle">
          <button class="grid-btn active" id="grid-view-btn" title="Grid view">⊞</button>
          <button class="grid-btn" id="list-view-btn" title="List view">☰</button>
        </div>
      </div>
    </div>
    <div class="prod-grid" id="product-grid"></div>
    <div class="pagination" id="pagination"></div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  const urlParams = new URLSearchParams(window.location.search);
  const searchQuery = urlParams.get('search');
  const catQuery = urlParams.get('category');
  const sortQuery = urlParams.get('sort') || 'featured';
  const pageQuery = parseInt(urlParams.get('page')) || 1;

  let allCategories = [];
  let selectedColor = null;

  // ── Init ────────────────────────────────────────────────
  (async function init() {
    Cart.updateBadge();
    updateBreadcrumb();
    updateSortSelect();
    initColorDots();
    document.getElementById('apply-filters').addEventListener('click', applyFilters);

    // Load categories for sidebar
    try {
      var res = await API.get('/categories');
      allCategories = res.categories || [];
      renderCategoryFilters();
    } catch(e) {}

    // Apply URL filters to sidebar
    if (catQuery) {
      markCategoryCheckbox(catQuery);
    }

    // Load first page
    await loadProducts({ page: pageQuery });
  })();

  // ── Breadcrumb ───────────────────────────────────────────
  function updateBreadcrumb() {
    var label = document.getElementById('breadcrumb-label');
    if (searchQuery) {
      label.textContent = 'Search results for "' + searchQuery + '"';
    } else if (catQuery) {
      label.textContent = catQuery;
    } else {
      label.textContent = 'Shop';
    }
  }

  // ── Sort select ──────────────────────────────────────────
  function updateSortSelect() {
    var select = document.getElementById('sort-select');
    select.value = sortQuery;
    select.addEventListener('change', function() {
      var params = getFilterParams();
      params.sort = select.value;
      params.page = 1;
      updateURL(params);
    });
  }

  // ── Color dots ──────────────────────────────────────────
  function initColorDots() {
    document.querySelectorAll('.color-dot').forEach(function(dot) {
      dot.addEventListener('click', function() {
        document.querySelectorAll('.color-dot').forEach(function(d) { d.classList.remove('active'); });
        if (selectedColor === dot.dataset.color) {
          selectedColor = null;
        } else {
          dot.classList.add('active');
          selectedColor = dot.dataset.color;
        }
      });
    });
  }

  // ── Category filters ─────────────────────────────────────
  function renderCategoryFilters() {
    var container = document.getElementById('category-filters');
    if (!container || allCategories.length === 0) return;
    container.innerHTML = allCategories.map(function(cat) {
      var count = cat.products_count || '';
      return '<div class="filter-item">' +
        '<input type="checkbox" value="' + cat.name + '" id="cat-' + cat.id + '">' +
        '<label for="cat-' + cat.id + '">' + cat.name + (count ? ' <span>(' + count + ')</span>' : '') + '</label>' +
        '</div>';
    }).join('');
  }

  function markCategoryCheckbox(catName) {
    var checkboxes = document.querySelectorAll('#category-filters input');
    checkboxes.forEach(function(cb) {
      if (cb.value === catName) cb.checked = true;
    });
  }

  // ── Load products ────────────────────────────────────────
  async function loadProducts(overrides) {
    var params = getFilterParams();
    Object.assign(params, overrides || {});

    var query = buildQuery(params);
    var grid = document.getElementById('product-grid');
    grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:40px;color:#aaa">Loading...</p>';

    try {
      var res = await API.get('/products?' + query);
      renderProducts(res.products || []);
      renderPagination(res.pagination || {});
    } catch(e) {
      grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:40px;color:#aaa">Failed to load products.</p>';
    }
  }

  // ── Filter params from UI ────────────────────────────────
  function getFilterParams() {
    var params = { sort: sortQuery, page: pageQuery };

    if (searchQuery) params.search = searchQuery;

    // Active category checkboxes
    var checkedCats = Array.from(document.querySelectorAll('#category-filters input:checked'))
      .map(function(cb) { return cb.value; });
    if (checkedCats.length === 1) {
      params.category = checkedCats[0];
    } else if (checkedCats.length > 1) {
      // API only supports single category, use first
      params.category = checkedCats[0];
    }

    var minPrice = document.getElementById('min-price').value;
    var maxPrice = document.getElementById('max-price').value;
    if (minPrice) params.min_price = minPrice;
    if (maxPrice) params.max_price = maxPrice;

    var checkedMaterials = Array.from(document.querySelectorAll('#material-filters input:checked'))
      .map(function(cb) { return cb.value; });
    if (checkedMaterials.length === 1) params.material = checkedMaterials[0];

    return params;
  }

  function buildQuery(params) {
    return Object.keys(params)
      .filter(function(k) { return params[k] !== '' && params[k] !== null && params[k] !== undefined; })
      .map(function(k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); })
      .join('&');
  }

  function applyFilters() {
    var params = getFilterParams();
    params.page = 1;
    updateURL(params);
  }

  function updateURL(params) {
    var url = new URL(window.location);
    Object.keys(params).forEach(function(k) {
      if (params[k]) url.searchParams.set(k, params[k]);
      else url.searchParams.delete(k);
    });
    window.location.href = url.toString();
  }

  // ── Render products ──────────────────────────────────────
  function renderProducts(products) {
    var grid = document.getElementById('product-grid');
    var countEl = document.getElementById('result-count');

    var total = (window._lastPagination && window._lastPagination.total) || products.length;
    if (countEl) countEl.textContent = total + ' products found';

    if (products.length === 0) {
      grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px;color:#aaa">No products match your filters.</div>';
      return;
    }

    grid.innerHTML = products.map(function(p) {
      var imgUrl = (p.primaryImage && p.primaryImage.url) ? p.primaryImage.url : '/img/placeholder.svg';
      var stars = p.stars || 5;
      var starsStr = '★'.repeat(stars) + '☆'.repeat(5 - stars);
      var badgeHtml = p.badge
        ? '<div class="prod-badge" style="background:' + (p.badge_color || '#B8860B') + '">' + p.badge + '</div>'
        : '';
      var oldPriceHtml = p.old_price
        ? ' <s style="color:#aaa;font-size:13px">EGP ' + parseFloat(p.old_price).toLocaleString() + '</s>'
        : '';
      return '<div class="prod-card" data-id="' + p.id + '" style="cursor:pointer">' +
        '<div class="prod-img">' + badgeHtml +
        '<img src="' + imgUrl + '" alt="' + p.name + '" onerror="this.src=\'/img/placeholder.svg\'">' +
        '</div>' +
        '<div class="stars">' + starsStr + '</div>' +
        '<div class="prod-name">' + p.name + '</div>' +
        '<div class="prod-cat">' + (p.category ? p.category.name : '') + '</div>' +
        '<div class="prod-price">EGP ' + parseFloat(p.price).toLocaleString() + oldPriceHtml + '</div>' +
        '</div>';
    }).join('');

    grid.querySelectorAll('.prod-card').forEach(function(card) {
      card.addEventListener('click', function() {
        window.location.href = '/product/' + card.dataset.id;
      });
    });
  }

  // ── Render pagination ────────────────────────────────────
  function renderPagination(pagination) {
    window._lastPagination = pagination;
    var container = document.getElementById('pagination');
    if (!container || pagination.last_page <= 1) {
      container.innerHTML = '';
      return;
    }

    var current = pagination.current_page;
    var last = pagination.last_page;
    var pages = [];

    // Always show first page
    pages.push(1);
    if (current > 3) pages.push('...');

    for (var i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) {
      pages.push(i);
    }

    if (current < last - 2) pages.push('...');
    pages.push(last);

    var html = '<button class="page-btn" ' + (current <= 1 ? 'disabled' : '') + ' onclick="gotoPage(' + (current - 1) + ')">‹</button>';
    pages.forEach(function(p) {
      if (p === '...') {
        html += '<button class="page-btn" disabled>…</button>';
      } else {
        html += '<button class="page-btn ' + (p === current ? 'active' : '') + '" onclick="gotoPage(' + p + ')">' + p + '</button>';
      }
    });
    html += '<button class="page-btn" ' + (current >= last ? 'disabled' : '') + ' onclick="gotoPage(' + (current + 1) + ')">›</button>';

    container.innerHTML = html;
  }

  window.gotoPage = function(page) {
    var params = getFilterParams();
    params.page = page;
    updateURL(params);
  };
})();
</script>
@endsection
