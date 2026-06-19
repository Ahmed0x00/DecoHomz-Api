@extends('admin.layouts.app')

@section('title', 'Products')
@section('page_title', 'Products')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Products</h1>
  <a href="/admin/products/create" style="background:#c9a96e;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;">+ Add Product</a>
</div>

<!-- Stats Cards -->
<div class="stat-grid" id="stats-grid">
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#fef3c7">
      <svg viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="1.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
    </div>
    <div class="stat-card-num" id="stat-total">—</div>
    <div class="stat-card-label">Total Products</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#d1fae5">
      <svg viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    </div>
    <div class="stat-card-num" id="stat-active">—</div>
    <div class="stat-card-label">Active</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#dbeafe">
      <svg viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
    </div>
    <div class="stat-card-num" id="stat-featured">—</div>
    <div class="stat-card-label">Featured</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#fee2e2">
      <svg viewBox="0 0 24 24" fill="none" stroke="#991b1b" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <div class="stat-card-num" id="stat-out">—</div>
    <div class="stat-card-label">Out of Stock</div>
  </div>
</div>

<!-- Filter Bar -->
<div class="admin-card" style="margin-bottom:24px;">
  <div style="padding:16px 24px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
    <input type="text" id="filter-search" placeholder="Search products..." style="flex:1;min-width:200px;padding:8px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;" oninput="debounceLoadProducts()">
    <select id="filter-category" style="padding:8px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;min-width:150px;" onchange="loadProducts()">
      <option value="">All Categories</option>
    </select>
    <select id="filter-featured" style="padding:8px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;min-width:130px;" onchange="loadProducts()">
      <option value="">Featured</option>
      <option value="1">Featured</option>
      <option value="0">Not Featured</option>
    </select>
    <select id="filter-stock" style="padding:8px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;min-width:130px;" onchange="loadProducts()">
      <option value="">Stock</option>
      <option value="in">In Stock</option>
      <option value="out">Out of Stock</option>
    </select>
    <button onclick="clearFilters()" style="background:#f3f4f6;color:#666;border:1px solid #e5e5e5;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;">Clear</button>
  </div>
</div>

<!-- Products Table -->
<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th style="width:60px;">Thumbnail</th>
        <th>Name</th>
        <th>Category</th>
        <th>Price</th>
        <th>Old Price</th>
        <th>Stock</th>
        <th>Featured</th>
        <th>Active</th>
        <th style="width:160px;text-align:center;">Actions</th>
      </tr>
    </thead>
    <tbody id="products-tbody">
      <tr class="loading-row"><td colspan="9"></td></tr>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<div id="pagination" style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:24px;">
</div>

@endsection

@section('extra_js')
<script>
(function() {
  var currentPage = 1;
  var lastParams = {};
  var _searchTimer;

  window.debounceLoadProducts = function() {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(loadProducts, 350);
  };

  function esc(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str || ''));
    return div.innerHTML;
  }

  document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    loadStats();
    loadProducts();
  });

  async function loadCategories() {
    try {
      var res = await API.get('/admin/categories');
      var categories = res.data || res || [];
      var select = document.getElementById('filter-category');
      categories.forEach(function(c) {
        select.innerHTML += '<option value="' + c.id + '">' + c.name + '</option>';
      });
    } catch(e) {
      console.warn('Failed to load categories', e);
    }
  }

  async function loadStats() {
    try {
      var res = await API.get('/admin/products', { params: { per_page: 1 } });
      if (res.stats) {
        document.getElementById('stat-total').textContent = res.stats.total;
        document.getElementById('stat-active').textContent = res.stats.active;
        document.getElementById('stat-featured').textContent = res.stats.featured;
        document.getElementById('stat-out').textContent = res.stats.out_of_stock;
      } else {
        var products = res.products || [];
        document.getElementById('stat-total').textContent = res.pagination ? (res.pagination.total || 0) : products.length;
        document.getElementById('stat-active').textContent = '—';
        document.getElementById('stat-featured').textContent = '—';
        document.getElementById('stat-out').textContent = '—';
      }
    } catch(e) {
      document.getElementById('stat-total').textContent = '—';
      document.getElementById('stat-active').textContent = '—';
      document.getElementById('stat-featured').textContent = '—';
      document.getElementById('stat-out').textContent = '—';
    }
  }

  window.loadProducts = function(page) {
    if (page) currentPage = page;
    var search = document.getElementById('filter-search').value;
    var category = document.getElementById('filter-category').value;
    var featured = document.getElementById('filter-featured').value;
    var stock = document.getElementById('filter-stock').value;

    lastParams = { search: search, category: category, featured: featured, stock: stock, page: currentPage };

    var params = { page: currentPage };
    if (search) params.search = search;
    if (category) params.category_id = category;
    if (featured !== '') params.is_featured = featured;
    if (stock === 'in') params.stock_min = 1;
    if (stock === 'out') params.stock = 0;

    renderTableLoading();
    API.get('/admin/products', { params: params }).then(function(res) {
      var products = res.data || res.products || res || [];
      if (!Array.isArray(products) && products.data) products = products.data;
      renderTable(products);
      renderPagination(res);
    }).catch(function(e) {
      document.getElementById('products-tbody').innerHTML = '<tr><td colspan="9" style="text-align:center;color:#ef4444;padding:30px">Failed to load products.</td></tr>';
    });
  };

  function renderTableLoading() {
    document.getElementById('products-tbody').innerHTML = '<tr class="loading-row"><td colspan="9"></td></tr>';
  }

  function renderTable(products) {
    var tbody = document.getElementById('products-tbody');
    if (!products || products.length === 0) {
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#aaa;padding:40px">No products found.</td></tr>';
      return;
    }
    tbody.innerHTML = products.map(function(p) {
      var img = p.primary_image && p.primary_image.url 
        ? '<img src="' + p.primary_image.url + '" style="width:44px;height:44px;object-fit:cover;border-radius:6px;">' 
        : '<img src="/img/placeholder.svg" style="width:44px;height:44px;object-fit:cover;border-radius:6px;opacity:0.4;">';
      var catName = p.category ? p.category.name : (p.category_name || '—');
      var price = 'EGP ' + parseFloat(p.price || 0).toLocaleString();
      var oldPrice = p.old_price && p.old_price > 0 ? 'EGP ' + parseFloat(p.old_price).toLocaleString() : '—';
      var stockBadge = p.stock == 0 || p.stock === '0'
        ? '<span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:50px;font-size:11px;font-weight:600;">Out</span>'
        : '<span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:50px;font-size:11px;font-weight:600;">' + p.stock + '</span>';
      var featBtn = '<button onclick="toggleFeatured(' + p.id + ', ' + !(p.is_featured == 1 || p.is_featured === true) + ')" style="padding:4px 10px;border-radius:50px;font-size:11px;font-weight:600;cursor:pointer;border:' + (p.is_featured == 1 || p.is_featured === true ? '1px solid #d1fae5;background:#d1fae5;color:#065f46;' : '1px solid #f3f4f6;background:#f3f4f6;color:#6b7280;') + '">' + (p.is_featured == 1 || p.is_featured === true ? 'Yes' : 'No') + '</button>';
      var actBtn = '<button onclick="toggleActive(' + p.id + ', ' + !(p.is_active == 1 || p.is_active === true) + ')" style="padding:4px 10px;border-radius:50px;font-size:11px;font-weight:600;cursor:pointer;border:' + (p.is_active == 1 || p.is_active === true ? '1px solid #d1fae5;background:#d1fae5;color:#065f46;' : '1px solid #fee2e2;background:#fee2e2;color:#991b1b;') + '">' + (p.is_active == 1 || p.is_active === true ? 'Active' : 'Inactive') + '</button>';
      return '<tr>' +
        '<td>' + img + '</td>' +
        '<td style="font-weight:600;">' + esc(p.name || '—') + '</td>' +
        '<td style="color:#666;">' + esc(catName) + '</td>' +
        '<td style="font-weight:600;">' + price + '</td>' +
        '<td style="color:#999;text-decoration:line-through;">' + oldPrice + '</td>' +
        '<td>' + stockBadge + '</td>' +
        '<td>' + featBtn + '</td>' +
        '<td>' + actBtn + '</td>' +
        '<td style="white-space:nowrap;text-align:center;">' + 
          '<a href="/admin/products/' + p.id + '/edit" style="display:inline-block;padding:6px 12px;border:1px solid #c9a96e;background:#c9a96e;color:#fff;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;margin-right:8px;box-shadow:0 1px 2px rgba(0,0,0,0.05);transition:all 0.15s ease;">Edit</a>' +
          '<button onclick="deleteProduct(' + p.id + ')" style="display:inline-block;padding:6px 12px;border:1px solid #fee2e2;background:#fee2e2;color:#991b1b;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,0.05);transition:all 0.15s ease;">Delete</button>' +
        '</td>' +
        '</tr>';
    }).join('');
  }

  window.deleteProduct = function(id) {
    if (!confirm('Are you sure you want to delete this product?')) return;
    API.del('/admin/products/' + id).then(function() {
      showToast('Product deleted successfully.', 'success');
      loadProducts();
      loadStats();
    }).catch(function() {
      showToast('Failed to delete product.', 'error');
    });
  };

  function renderPagination(res) {
    var container = document.getElementById('pagination');
    var pagination = res.pagination || {};
    var total = pagination.total || 0;
    var perPage = pagination.per_page || 15;
    var current = pagination.current_page || 1;
    var last = Math.ceil(total / perPage);
    if (last <= 1) { container.innerHTML = ''; return; }

    var html = '';
    if (current > 1) {
      html += '<button onclick="loadProducts(' + (current - 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">← Prev</button>';
    }
    for (var i = 1; i <= last; i++) {
      if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
        html += '<button onclick="loadProducts(' + i + ')" style="padding:6px 12px;border:1px solid ' + (i === current ? '#c9a96e' : '#e5e5e5') + ';background:' + (i === current ? '#c9a96e' : '#fff') + ';color:' + (i === current ? '#fff' : '#333') + ';border-radius:6px;cursor:pointer;font-size:13px;">' + i + '</button>';
      } else if (i === current - 2 || i === current + 2) {
        html += '<span style="color:#aaa;padding:0 4px;">...</span>';
      }
    }
    if (current < last) {
      html += '<button onclick="loadProducts(' + (current + 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">Next →</button>';
    }
    container.innerHTML = html;
  }

  window.toggleFeatured = function(id, value) {
    API.patch('/admin/products/' + id + '/toggle-featured', { is_featured: value ? 1 : 0 }).then(function() {
      showToast(value ? 'Product marked as featured.' : 'Product unmarked as featured.', 'success');
      loadProducts();
      loadStats();
    }).catch(function() {
      showToast('Failed to update featured status.', 'error');
    });
  };

  window.toggleActive = function(id, value) {
    API.patch('/admin/products/' + id + '/toggle-active', { is_active: value ? 1 : 0 }).then(function() {
      showToast(value ? 'Product activated.' : 'Product deactivated.', 'success');
      loadProducts();
      loadStats();
    }).catch(function() {
      showToast('Failed to update active status.', 'error');
    });
  };

  window.clearFilters = function() {
    document.getElementById('filter-search').value = '';
    document.getElementById('filter-category').value = '';
    document.getElementById('filter-featured').value = '';
    document.getElementById('filter-stock').value = '';
    loadProducts(1);
  };
})();
</script>
@endsection
