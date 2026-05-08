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
          <div class="filter-item"><input type="checkbox" value="Wood" id="mat-wood"><label for="mat-wood">Wood</label>
          </div>
          <div class="filter-item"><input type="checkbox" value="Metal" id="mat-metal"><label
              for="mat-metal">Metal</label></div>
          <div class="filter-item"><input type="checkbox" value="Fabric" id="mat-fabric"><label
              for="mat-fabric">Fabric</label></div>
          <div class="filter-item"><input type="checkbox" value="Marble" id="mat-marble"><label
              for="mat-marble">Marble</label></div>
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
    (function () {
      let urlParams = new URLSearchParams(window.location.search);
      let searchQuery = urlParams.get('search');
      let catQuery = urlParams.get('category');
      let sortQuery = urlParams.get('sort') || 'featured';
      let pageQuery = parseInt(urlParams.get('page')) || 1;

      let allCategories = [];
      let selectedColor = urlParams.get('color') || null;

      // ── Init ────────────────────────────────────────────────
      (async function init() {
        Cart.updateBadge();
        updateSortSelect();
        initColorDots();
        
        // Load categories for sidebar
        try {
          const res = await API.get('/categories');
          allCategories = res.categories || [];
          renderCategoryFilters();
        } catch (e) { }

        // Sync URL params to DOM
        syncURLToDOM();
        
        // Update breadcrumb initially
        updateBreadcrumb();

        // Load products initially
        await loadProducts({ page: pageQuery });

        // Add automatic listeners
        initAutoFilters();
      })();

      function syncURLToDOM() {
        if (urlParams.has('min_price')) document.getElementById('min-price').value = urlParams.get('min_price');
        if (urlParams.has('max_price')) document.getElementById('max-price').value = urlParams.get('max_price');
        
        if (catQuery) markCategoryCheckbox(catQuery);
        
        if (urlParams.has('material')) {
          const mat = urlParams.get('material');
          const cb = document.querySelector(`#material-filters input[value="${mat}"]`);
          if (cb) cb.checked = true;
        }
        
        if (selectedColor) {
          const dot = document.querySelector(`.color-dot[data-color="${selectedColor}"]`);
          if (dot) dot.classList.add('active');
        }
      }

      function initAutoFilters() {
        // Checkboxes (Category, Material)
        document.querySelectorAll('.sidebar input[type="checkbox"]').forEach(cb => {
          cb.addEventListener('change', () => applyFilters(true));
        });

        // Price inputs (Debounced)
        const priceInputs = [document.getElementById('min-price'), document.getElementById('max-price')];
        priceInputs.forEach(input => {
          input.addEventListener('input', debounce(() => applyFilters(true), 500));
        });

        // Sort select
        document.getElementById('sort-select').addEventListener('change', () => applyFilters(true));
      }

      function debounce(fn, delay) {
        let timeout;
        return function(...args) {
          clearTimeout(timeout);
          timeout = setTimeout(() => fn.apply(this, args), delay);
        };
      }

      // ── Breadcrumb ───────────────────────────────────────────
      function updateBreadcrumb() {
        const label = document.getElementById('breadcrumb-label');
        const params = getFilterParams();
        
        if (params.search) {
          label.textContent = 'Search results for "' + params.search + '"';
        } else if (params.category) {
          label.textContent = params.category;
        } else {
          label.textContent = 'Shop All Products';
        }
      }

      // ── Sort select ──────────────────────────────────────────
      function updateSortSelect() {
        const select = document.getElementById('sort-select');
        select.value = sortQuery;
      }

      // ── Color dots ──────────────────────────────────────────
      function initColorDots() {
        document.querySelectorAll('.color-dot').forEach(function (dot) {
          dot.addEventListener('click', function () {
            document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('active'));
            if (selectedColor === dot.dataset.color) {
              selectedColor = null;
            } else {
              dot.classList.add('active');
              selectedColor = dot.dataset.color;
            }
            applyFilters(true);
          });
        });
      }

      // ── Category filters ─────────────────────────────────────
      function renderCategoryFilters() {
        const container = document.getElementById('category-filters');
        if (!container || allCategories.length === 0) return;
        container.innerHTML = allCategories.map(function (cat) {
          const count = cat.products_count || '';
          return `<div class="filter-item">
            <input type="checkbox" value="${cat.name}" id="cat-${cat.id}">
            <label for="cat-${cat.id}">${cat.name} ${count ? `<span>(${count})</span>` : ''}</label>
          </div>`;
        }).join('');
        
        // Re-run sync if we just rendered
        if (catQuery) markCategoryCheckbox(catQuery);
        
        // Re-attach listeners to new checkboxes
        container.querySelectorAll('input').forEach(cb => {
          cb.addEventListener('change', () => applyFilters(true));
        });
      }

      function markCategoryCheckbox(catName) {
        const checkboxes = document.querySelectorAll('#category-filters input');
        checkboxes.forEach(cb => {
          if (cb.value === catName) cb.checked = true;
        });
      }

      // ── Load products ────────────────────────────────────────
      async function loadProducts(overrides) {
        const params = getFilterParams();
        if (overrides) Object.assign(params, overrides);

        const query = buildQuery(params);
        const grid = document.getElementById('product-grid');
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:100px;"><div class="spinner"></div><p style="margin-top:16px;color:#888">Finding products...</p></div>';

        try {
          const res = await API.get('/products?' + query);
          renderProducts(res.products || []);
          renderPagination(res.pagination || {});
          updateBreadcrumb();
        } catch (e) {
          grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:40px;color:#aaa">Failed to load products.</p>';
        }
      }

      // ── Filter params from UI ────────────────────────────────
      function getFilterParams() {
        const params = {};
        
        // Persist search if present in URL
        if (searchQuery) params.search = searchQuery;

        // Sorting
        params.sort = document.getElementById('sort-select').value;

        // Categories
        const checkedCats = Array.from(document.querySelectorAll('#category-filters input:checked'))
          .map(cb => cb.value);
        if (checkedCats.length > 0) params.category = checkedCats[0]; // API takes one

        // Price
        const minPrice = document.getElementById('min-price').value;
        const maxPrice = document.getElementById('max-price').value;
        if (minPrice) params.min_price = minPrice;
        if (maxPrice) params.max_price = maxPrice;

        // Material
        const checkedMaterials = Array.from(document.querySelectorAll('#material-filters input:checked'))
          .map(cb => cb.value);
        if (checkedMaterials.length > 0) params.material = checkedMaterials[0];

        // Color
        if (selectedColor) params.color = selectedColor;

        return params;
      }

      function buildQuery(params) {
        return Object.keys(params)
          .filter(k => params[k] !== '' && params[k] !== null && params[k] !== undefined)
          .map(k => encodeURIComponent(k) + '=' + encodeURIComponent(params[k]))
          .join('&');
      }

      function applyFilters(resetPage = false) {
        const params = getFilterParams();
        if (resetPage) params.page = 1;
        
        updateURL(params);
        loadProducts(params);
      }

      function updateURL(params) {
        const url = new URL(window.location);
        // Clear existing params to avoid accumulation
        url.search = '';
        Object.keys(params).forEach(k => {
          if (params[k]) url.searchParams.set(k, params[k]);
        });
        window.history.pushState({}, '', url);
      }

      // ── Render products ──────────────────────────────────────
      function renderProducts(products) {
        const grid = document.getElementById('product-grid');
        const countEl = document.getElementById('result-count');

        const total = (window._lastPagination && window._lastPagination.total) || products.length;
        if (countEl) countEl.textContent = total + ' products found';

        if (products.length === 0) {
          grid.innerHTML = `
            <div style="grid-column:1/-1;text-align:center;padding:100px;background:#fff;border-radius:20px;border:1px solid #F0F0F0">
              <svg viewBox="0 0 24 24" width="60" height="60" stroke="#DDD" fill="none" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
              <h3 style="margin-top:20px;font-size:18px;color:#1A1A1A">No products found</h3>
              <p style="color:#888;margin-top:8px">Try adjusting your filters or search query.</p>
            </div>
          `;
          return;
        }

        grid.innerHTML = products.map(function (p) {
          const imgUrl = (p.primaryImage && p.primaryImage.url) ? p.primaryImage.url : '/img/placeholder.svg';
          const stars = p.stars || 5;
          const starsStr = '★'.repeat(stars) + '☆'.repeat(5 - stars);
          const badgeHtml = p.badge
            ? '<div class="prod-badge" style="background:' + (p.badge_color || '#B8860B') + '">' + p.badge + '</div>'
            : '';
          const oldPriceHtml = p.old_price
            ? ' <s style="color:#aaa;font-size:13px">EGP ' + parseFloat(p.old_price).toLocaleString() + '</s>'
            : '';
          return `
            <div class="prod-card" onclick="location.href='/product/${p.slug || p.id}'">
              <div class="prod-img">
                ${badgeHtml}
                <img src="${imgUrl}" alt="${p.name}" onerror="this.src='/img/placeholder.svg'">
              </div>
              <div class="stars">${starsStr}</div>
              <div class="prod-name">${p.name}</div>
              <div class="prod-cat">${p.category ? p.category.name : ''}</div>
              <div class="prod-price">EGP ${parseFloat(p.price).toLocaleString()} ${oldPriceHtml}</div>
              <button class="btn-cart" onclick="event.stopPropagation(); addToCartFromShop(${p.id}, '${esc(p.name)}', ${p.price})">Add to Cart</button>
            </div>
          `;
        }).join('');
      }

      function renderPagination(pagination) {
        window._lastPagination = pagination;
        const container = document.getElementById('pagination');
        if (!container || pagination.last_page <= 1) {
          container.innerHTML = '';
          return;
        }

        const current = pagination.current_page;
        const last = pagination.last_page;
        const pages = [];

        pages.push(1);
        if (current > 3) pages.push('...');

        for (let i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) {
          pages.push(i);
        }

        if (current < last - 2) pages.push('...');
        if (last > 1) pages.push(last);

        let html = `<button class="page-btn" ${current <= 1 ? 'disabled' : ''} onclick="gotoPage(${current - 1})">‹</button>`;
        pages.forEach(p => {
          if (p === '...') {
            html += '<button class="page-btn" disabled>…</button>';
          } else {
            html += `<button class="page-btn ${p === current ? 'active' : ''}" onclick="gotoPage(${p})">${p}</button>`;
          }
        });
        html += `<button class="page-btn" ${current >= last ? 'disabled' : ''} onclick="gotoPage(${current + 1})">›</button>`;

        container.innerHTML = html;
      }

      window.gotoPage = function (page) {
        applyFilters(false);
        loadProducts({ page: page });
        window.scrollTo({ top: 0, behavior: 'smooth' });
      };

      window.addToCartFromShop = function(productId, name, price) {
        Cart.add({ id: productId, name: name, price: price, quantity: 1, variant: 'Standard' });
        Cart.updateBadge();
        showToast('Added ' + name + ' to cart');
      };

      function esc(str) {
        return String(str).replace(/'/g, "\\'").replace(/"/g, '\\"');
      }
    })();
  </script>
@endsection