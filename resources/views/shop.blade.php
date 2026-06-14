@extends('layouts.app')

@section('title', 'Shop — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/shop.css">
@endsection

@section('content')

{{-- ═══ HEADER ═══ --}}
<div class="shop-header">
  <div class="shop-header-inner animate-fade-up">
    <h1>{{ __('Our Collection') }}</h1>
    <div class="shop-header-sub">{{ __('Explore our premium range of furniture, handpicked for quality and style.') }}</div>
  </div>
</div>

<div class="breadcrumb" style="border-bottom:none">
  <a href="/">{{ __('Home') }}</a> › <span>{{ __('Shop') }}</span>
</div>

{{-- ═══ MAIN LAYOUT ═══ --}}
<div class="shop-layout">

  {{-- ═══ DESKTOP SIDEBAR FILTERS ═══ --}}
  <aside class="shop-sidebar" id="desktop-sidebar">
    <div class="sidebar-title">
      {{ __('Filters') }}
      <button class="filter-clear-all" id="clear-filters-btn" style="display:none" onclick="clearFilters()">{{ __('Clear All') }}</button>
    </div>

    <!-- Categories -->
    <div class="filter-group">
      <div class="filter-head" onclick="toggleFilter(this)">
        <h4>{{ __('Categories') }}</h4>
        <div class="filter-toggle">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </div>
      </div>
      <div class="filter-body" id="filter-categories-container">
        {{-- Loading skeleton for categories --}}
        <div class="skeleton-text wide skeleton"></div>
        <div class="skeleton-text medium skeleton"></div>
        <div class="skeleton-text wide skeleton"></div>
      </div>
    </div>

    <!-- Price -->
    <div class="filter-group">
      <div class="filter-head" onclick="toggleFilter(this)">
        <h4>{{ __('Price Range') }}</h4>
        <div class="filter-toggle">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </div>
      </div>
      <div class="filter-body">
        <div class="price-range">
          <input type="range" id="price-slider" min="0" max="50000" step="500" value="50000" oninput="updatePriceLabel(this.value)" onchange="applyFilters()">
          <div class="price-labels">
            <span>{{ __('EGP 0') }}</span>
            <span id="price-max-label">{{ __('EGP 50,000') }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Availability -->
    <div class="filter-group" style="border-bottom:none; margin-bottom:0; padding-bottom:0">
      <div class="filter-head" onclick="toggleFilter(this)">
        <h4>{{ __('Availability') }}</h4>
        <div class="filter-toggle">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </div>
      </div>
      <div class="filter-body">
        <label class="filter-check">
          <input type="checkbox" id="filter-in-stock" value="1" onchange="applyFilters()">
          <span>{{ __('In Stock Only') }}</span>
        </label>
      </div>
    </div>
  </aside>

  {{-- ═══ MOBILE FILTER OVERLAY ═══ --}}
  <div class="filter-drawer-overlay" id="filter-overlay"></div>
  <div class="filter-drawer" id="mobile-filter-drawer">
    <div class="filter-drawer-head">
      <h3>{{ __('Filters') }}</h3>
      <button class="filter-drawer-close" onclick="closeMobileFilters()">&times;</button>
    </div>
    <div id="mobile-filter-content">
      {{-- Cloned via JS from desktop sidebar --}}
    </div>
    <div style="margin-top:24px">
      <button class="btn-dark" style="width:100%" onclick="closeMobileFilters()">{{ __('Apply Filters') }}</button>
    </div>
  </div>

  {{-- ═══ PRODUCTS GRID AREA ═══ --}}
  <div>
    
    {{-- Active Filter Chips --}}
    <div class="active-filters" id="active-filters-container"></div>

    {{-- Toolbar --}}
    <div class="shop-toolbar animate-fade-up">
      <button class="mobile-filter-btn" onclick="openMobileFilters()">
        <svg viewBox="0 0 24 24"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
        {{ __('Filter') }}
      </button>

      <div class="result-count" id="result-count">{{ __('Loading products...') }}</div>

      <div class="sort-wrapper">
        <div class="sort-label">{{ __('Sort by:') }}</div>
        <select class="sort-select" id="sort-select" onchange="applyFilters()">
          <option value="">{{ __('Featured') }}</option>
          <option value="price_asc">{{ __('Price: Low to High') }}</option>
          <option value="price_desc">{{ __('Price: High to Low') }}</option>
          <option value="newest">{{ __('Newest Arrivals') }}</option>
        </select>
        
        <div class="view-toggle">
          <button class="view-btn active" title="{{ __('Grid View') }}" onclick="setViewMode('grid', this)">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          </button>
          <button class="view-btn" title="{{ __('List View') }}" onclick="setViewMode('list', this)">
            <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
          </button>
        </div>
      </div>
    </div>

    {{-- Grid --}}
    <div class="prod-grid animate-fade-up stagger-2" id="product-grid">
      {{-- Initial Loading Skeletons --}}
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

    {{-- Pagination --}}
    <div class="pagination" id="pagination-container"></div>
  </div>

</div>

@endsection

@section('extra_js')
<script>
let currentFilters = {
  category: '',
  search: '',
  max_price: 50000,
  color: '',
  in_stock: false,
  sort: '',
  page: 1
};

let currentViewMode = 'grid';

function parseUrlParams() {
  const urlParams = new URLSearchParams(window.location.search);
  currentFilters.category = urlParams.get('category') || '';
  currentFilters.search = urlParams.get('search') || '';
  currentFilters.max_price = parseInt(urlParams.get('max_price'), 10) || 50000;
  currentFilters.color = urlParams.get('color') || '';
  currentFilters.in_stock = urlParams.get('in_stock') === '1';
  currentFilters.sort = urlParams.get('sort') || '';
  currentFilters.page = parseInt(urlParams.get('page'), 10) || 1;

  const sortSelect = document.getElementById('sort-select');
  if (sortSelect) sortSelect.value = currentFilters.sort;

  const stockCheck = document.getElementById('filter-in-stock');
  if (stockCheck) stockCheck.checked = currentFilters.in_stock;

  const priceSlider = document.getElementById('price-slider');
  if (priceSlider) {
    priceSlider.value = currentFilters.max_price;
    updatePriceLabel(currentFilters.max_price);
  }

  syncColorDots();
}

function syncColorDots() {
  document.querySelectorAll('.color-dot').forEach(function(dot) {
    dot.classList.toggle('active', dot.getAttribute('title') === currentFilters.color);
  });
}

function updateUrlParams() {
  const params = new URLSearchParams();
  if (currentFilters.category) params.set('category', currentFilters.category);
  if (currentFilters.search) params.set('search', currentFilters.search);
  if (currentFilters.max_price < 50000) params.set('max_price', currentFilters.max_price);
  if (currentFilters.color) params.set('color', currentFilters.color);
  if (currentFilters.in_stock) params.set('in_stock', '1');
  if (currentFilters.sort) params.set('sort', currentFilters.sort);
  if (currentFilters.page > 1) params.set('page', currentFilters.page);

  const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
  window.history.pushState({ path: newUrl }, '', newUrl);
}

window.toggleFilter = function(el) {
  el.parentElement.classList.toggle('collapsed');
};

window.updatePriceLabel = function(val) {
  document.querySelectorAll('#price-max-label').forEach(function(el) {
    el.textContent = 'EGP ' + parseInt(val, 10).toLocaleString();
  });
};

window.toggleColor = function(el, color) {
  if (currentFilters.color === color) {
    currentFilters.color = '';
  } else {
    currentFilters.color = color;
  }

  syncColorDots();
  currentFilters.page = 1;
  applyFilters();
};

window.setViewMode = function(mode, el) {
  document.querySelectorAll('.view-btn').forEach(function(btn) { btn.classList.remove('active'); });
  if (el) el.classList.add('active');

  currentViewMode = mode;
  const grid = document.getElementById('product-grid');
  if (!grid) return;

  grid.classList.toggle('list-view', mode === 'list');
};

function ensureMobileFilters() {
  const content = document.getElementById('mobile-filter-content');
  const sidebar = document.getElementById('desktop-sidebar');
  if (!content || !sidebar) return;

  content.innerHTML = '';
  const clone = sidebar.cloneNode(true);
  clone.id = 'mobile-sidebar-clone';
  clone.classList.add('mobile-filter-sidebar');
  content.appendChild(clone);
}

window.openMobileFilters = function() {
  ensureMobileFilters();
  document.getElementById('mobile-filter-drawer').classList.add('active');
  document.getElementById('filter-overlay').classList.add('active');
  document.body.style.overflow = 'hidden';
};

window.closeMobileFilters = function() {
  document.getElementById('mobile-filter-drawer').classList.remove('active');
  document.getElementById('filter-overlay').classList.remove('active');
  document.body.style.overflow = '';
};

async function loadCategories() {
  const container = document.getElementById('filter-categories-container');
  if (!container) return;

  try {
    const res = await API.get('/categories');
    const categories = res.categories || [];

    let html = '<label class="filter-check">' +
      '<input type="radio" name="category" value="" ' + (!currentFilters.category ? 'checked' : '') + '>' +
      '<span>' + "{{ __('All Categories') }}" + '</span>' +
      '</label>';

    categories.forEach(function(cat) {
      const isChecked = currentFilters.category === cat.name ? 'checked' : '';
      html += '<label class="filter-check">' +
        '<input type="radio" name="category" value="' + esc(cat.name) + '" ' + isChecked + '>' +
        '<span>' + esc(cat.name) + '</span>' +
        '</label>';
    });

    container.innerHTML = html;
    container.addEventListener('change', function(e) {
      if (e.target.name === 'category') {
        selectCategory(e.target.value);
      }
    });
  } catch (e) {
    container.innerHTML = '<p class="text-error">' + "{{ __('Failed to load categories') }}" + '</p>';
  }
}

window.selectCategory = function(cat) {
  currentFilters.category = cat;
  currentFilters.page = 1;
  document.querySelectorAll('input[name="category"]').forEach(function(radio) {
    radio.checked = radio.value === cat;
  });
  applyFilters();
};

window.clearFilters = function() {
  currentFilters = {
    category: '', search: '', max_price: 50000, color: '', in_stock: false, sort: '', page: 1
  };

  const sortSelect = document.getElementById('sort-select');
  if (sortSelect) sortSelect.value = '';

  const priceSlider = document.getElementById('price-slider');
  if (priceSlider) priceSlider.value = 50000;
  updatePriceLabel(50000);

  const stockCheck = document.getElementById('filter-in-stock');
  if (stockCheck) stockCheck.checked = false;

  syncColorDots();
  document.querySelectorAll('input[name="category"]').forEach(function(radio) {
    radio.checked = radio.value === '';
  });

  applyFilters();
};

window.applyFilters = function() {
  const priceSlider = document.getElementById('price-slider');
  const stockCheck = document.getElementById('filter-in-stock');
  const sortSelect = document.getElementById('sort-select');

  currentFilters.max_price = priceSlider ? parseInt(priceSlider.value, 10) : 50000;
  currentFilters.in_stock = stockCheck ? stockCheck.checked : false;
  currentFilters.sort = sortSelect ? sortSelect.value : '';

  updateUrlParams();
  renderActiveFilterChips();
  loadProducts();
};

function renderActiveFilterChips() {
  const container = document.getElementById('active-filters-container');
  const clearBtn = document.getElementById('clear-filters-btn');
  let html = '';
  let hasFilters = false;

  if (currentFilters.category) {
    html += '<div class="filter-chip">' + esc(currentFilters.category) + '<span class="chip-remove" onclick="selectCategory(\'\')">&times;</span></div>';
    hasFilters = true;
  }
  if (currentFilters.search) {
    html += '<div class="filter-chip">' + "{{ __('Search') }}" + ': ' + esc(currentFilters.search) + '<span class="chip-remove" onclick="currentFilters.search=\'\'; currentFilters.page=1; applyFilters();">&times;</span></div>';
    hasFilters = true;
  }
  if (currentFilters.color) {
    html += '<div class="filter-chip">' + "{{ __('Color') }}" + ': ' + esc(currentFilters.color) + '<span class="chip-remove" onclick="toggleColor(null, \'' + esc(currentFilters.color) + '\')">&times;</span></div>';
    hasFilters = true;
  }
  if (currentFilters.in_stock) {
    html += '<div class="filter-chip">' + "{{ __('In Stock Only') }}" + '<span class="chip-remove" onclick="document.getElementById(\'filter-in-stock\').checked=false; currentFilters.page=1; applyFilters();">&times;</span></div>';
    hasFilters = true;
  }
  if (currentFilters.max_price < 50000) {
    html += '<div class="filter-chip">' + "{{ __('Up to') }}" + ' EGP ' + parseInt(currentFilters.max_price, 10).toLocaleString() + '<span class="chip-remove" onclick="document.getElementById(\'price-slider\').value=50000; currentFilters.page=1; applyFilters();">&times;</span></div>';
    hasFilters = true;
  }

  if (container) container.innerHTML = html;
  if (clearBtn) clearBtn.style.display = hasFilters ? 'block' : 'none';
}

async function loadProducts() {
  const grid = document.getElementById('product-grid');
  if (!grid) return;

  grid.innerHTML = Array(6).fill(
    '<div class="skeleton-card"><div class="skeleton-img skeleton"></div><div class="skeleton-body"><div class="skeleton-text narrow skeleton"></div><div class="skeleton-text wide skeleton"></div><div class="skeleton-text medium skeleton"></div></div></div>'
  ).join('');

  try {
    const params = {};
    if (currentFilters.category) params.category = currentFilters.category;
    if (currentFilters.search) params.search = currentFilters.search;
    if (currentFilters.max_price < 50000) params.max_price = currentFilters.max_price;
    if (currentFilters.color) params.color = currentFilters.color;
    if (currentFilters.in_stock) params.in_stock = '1';
    if (currentFilters.sort) params.sort = currentFilters.sort;
    if (currentFilters.page > 1) params.page = currentFilters.page;

    const res = await API.get('/products', { params: params });
    const products = Array.isArray(res.products) ? res.products : (res.data || []);
    const meta = res.pagination || res.meta || {};

    const total = meta.total || products.length;
    const resultCount = document.getElementById('result-count');
    if (resultCount) {
      resultCount.innerHTML = "{{ __('Showing') }}" + ' <strong>' + products.length + '</strong> ' + "{{ __('of') }}" + ' <strong>' + total + '</strong> ' + "{{ __('products') }}";
    }

    if (products.length === 0) {
      grid.innerHTML = '<div class="shop-empty">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>' +
        '<h3>' + "{{ __('No products found') }}" + '</h3>' +
        '<p>' + "{{ __('Try adjusting your filters or search criteria.') }}" + '</p>' +
        '<button class="btn-outline" onclick="clearFilters()">' + "{{ __('Clear All Filters') }}" + '</button>' +
        '</div>';
      document.getElementById('pagination-container').innerHTML = '';
      return;
    }

    grid.innerHTML = products.map(function(p, i) {
      const imgUrl = (p.primary_image && p.primary_image.thumbnail_url) ? p.primary_image.thumbnail_url : ((p.primary_image && p.primary_image.url) ? p.primary_image.url : '/img/placeholder.svg');
      const stars = p.stars || 5;
      const starsStr = '★'.repeat(stars) + '☆'.repeat(5 - stars);
      const badgeHtml = p.badge
        ? '<div class="prod-badge" style="background:' + (p.badge_color || 'var(--color-accent)') + '">' + esc(p.badge) + '</div>'
        : '';
      const price = p.price ? parseFloat(p.price).toLocaleString() : '0';
      const oldPriceHtml = p.old_price
        ? ' <s>EGP ' + parseFloat(p.old_price).toLocaleString() + '</s>'
        : '';
      const catName = p.category ? esc(p.category.name) : '';
      const productUrl = '/product/' + (p.slug || p.id);

      return '<div class="prod-card animate-fade-up stagger-' + (i % 8 + 1) + '" data-id="' + p.id + '" onclick="location.href=\'' + productUrl + '\'">' +
        '<div class="prod-img">' +
          badgeHtml +
          '<img src="' + imgUrl + '" alt="' + esc(p.name) + '" loading="lazy" onerror="this.src=\'/img/placeholder.svg\'">' +
        '</div>' +
        '<div class="prod-info">' +
          '<div class="stars">' + starsStr + '</div>' +
          '<div class="prod-name">' + esc(p.name) + '</div>' +
          '<div class="prod-cat">' + catName + '</div>' +
          '<div class="prod-price">EGP ' + price + oldPriceHtml + '</div>' +
          '<button class="btn-add-cart" onclick="shopAddToCart(event, ' + p.id + ', \'' + esc(p.name).replace(/'/g, "\\'") + '\', ' + (p.price || 0) + ')">' + "{{ __('Add to Cart') }}" + '</button>' +
        '</div>' +
      '</div>';
    }).join('');

    grid.classList.toggle('list-view', currentViewMode === 'list');
    renderPagination(meta);
  } catch (e) {
    console.error('Products load failed', e);
    grid.innerHTML = '<div class="shop-empty"><p class="text-error">' + "{{ __('Failed to load products. Please try again later.') }}" + '</p></div>';
    const resultCount = document.getElementById('result-count');
    if (resultCount) resultCount.textContent = "{{ __('Error loading products') }}";
  }
}

function renderPagination(meta) {
  const container = document.getElementById('pagination-container');
  if (!container) return;

  const last = meta.last_page || 0;
  if (!last || last <= 1) {
    container.innerHTML = '';
    return;
  }

  let html = '';
  const current = currentFilters.page;

  html += '<button class="page-btn" ' + (current === 1 ? 'disabled' : '') + ' onclick="goToPage(' + (current - 1) + ')">&laquo;</button>';

  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
      html += '<button class="page-btn ' + (i === current ? 'active' : '') + '" onclick="goToPage(' + i + ')">' + i + '</button>';
    } else if (i === current - 2 || i === current + 2) {
      html += '<span style="color:var(--color-text-muted);padding:0 4px">...</span>';
    }
  }

  html += '<button class="page-btn" ' + (current === last ? 'disabled' : '') + ' onclick="goToPage(' + (current + 1) + ')">&raquo;</button>';
  container.innerHTML = html;
}

window.goToPage = function(page) {
  currentFilters.page = page;
  applyFilters();
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

window.shopAddToCart = async function(event, id, name, price) {
  if (event) event.stopPropagation();
  if (!window.Cart || typeof Cart.add !== 'function') return;

  await Cart.add({ id: id, name: name, price: price, quantity: 1, variant: 'Standard' });
  if (typeof openCart === 'function') openCart();
};

document.addEventListener('DOMContentLoaded', function() {
  if (typeof Cart !== 'undefined' && Cart.updateBadge) Cart.updateBadge();

  const overlay = document.getElementById('filter-overlay');
  if (overlay) overlay.addEventListener('click', closeMobileFilters);

  const mobileContent = document.getElementById('mobile-filter-content');
  if (mobileContent) {
    mobileContent.addEventListener('change', function(e) {
      if (e.target.name === 'category') {
        selectCategory(e.target.value);
      }
      if (e.target.id === 'filter-in-stock') {
        const desktopStock = document.getElementById('filter-in-stock');
        if (desktopStock) desktopStock.checked = e.target.checked;
        applyFilters();
      }
    });
    mobileContent.addEventListener('input', function(e) {
      if (e.target.id === 'price-slider') {
        const desktopSlider = document.getElementById('price-slider');
        if (desktopSlider) desktopSlider.value = e.target.value;
        updatePriceLabel(e.target.value);
      }
    });
    mobileContent.addEventListener('change', function(e) {
      if (e.target.id === 'price-slider') {
        const desktopSlider = document.getElementById('price-slider');
        if (desktopSlider) desktopSlider.value = e.target.value;
        applyFilters();
      }
    });
  }

  parseUrlParams();
  renderActiveFilterChips();
  loadCategories();
  loadProducts();
});
</script>
@endsection