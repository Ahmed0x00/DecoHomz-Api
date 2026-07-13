@extends('admin.layouts.app')

@section('title', 'Vendor Products')
@section('page_title', 'Vendor Products')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Vendor Products (Review Queue)</h1>
</div>

<!-- Products Table -->
<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Product</th>
        <th>Vendor</th>
        <th>Status</th>
        <th>Price</th>
        <th style="width:100px;">Actions</th>
      </tr>
    </thead>
    <tbody id="products-tbody">
      <tr class="loading-row"><td colspan="6"></td></tr>
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

  document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
  });

  function esc(str) {
    if (!str) return '';
    return String(str).replace(/[&<>'"]/g, function(tag) {
      var chars = {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'};
      return chars[tag] || tag;
    });
  }

  window.deleteProduct = async function(id) {
    if(!confirm('Are you sure you want to delete this product?')) return;
    try {
      await API.delete('/admin/vendor-products/' + id);
      showToast('Product deleted successfully', 'success');
      loadProducts();
    } catch(err) {
      showToast(err.data?.message || 'Failed to delete product', 'error');
    }
  }

  async function loadProducts(page) {
    if (page) currentPage = page;
    renderTableLoading();
    try {
      var res = await API.get('/admin/vendor-products', { params: { page: currentPage } });
      var products = res.data && res.data.data ? res.data.data : (res.data || []);
      renderTable(products);
      renderPagination(res.data || res);
    } catch(e) {
      document.getElementById('products-tbody').innerHTML = '<tr><td colspan="6" style="text-align:center;color:#ef4444;padding:30px">Failed to load products.</td></tr>';
    }
  }

  function renderTableLoading() {
    document.getElementById('products-tbody').innerHTML = '<tr class="loading-row"><td colspan="6"></td></tr>';
  }

  function renderTable(products) {
    var tbody = document.getElementById('products-tbody');
    if (!products || products.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#aaa;padding:40px">No products found.</td></tr>';
      return;
    }
    tbody.innerHTML = products.map(function(p) {
      var statusBadge = '';
      if (p.vendor_status === 'published') {
        statusBadge = '<span class="badge-status badge-active">Published</span>';
      } else if (p.vendor_status === 'approved') {
        statusBadge = '<span class="badge-status badge-pending" style="background:#dcfce7;color:#166534;">Approved for Warehouse</span>';
      } else if (p.vendor_status === 'submitted') {
        statusBadge = '<span class="badge-status badge-pending" style="background:#bae6fd;color:#0369a1;">Submitted</span>';
      } else if (p.vendor_status === 'under_review') {
        statusBadge = '<span class="badge-status badge-pending">Under Review</span>';
      } else if (p.vendor_status === 'changes_requested') {
        statusBadge = '<span class="badge-status badge-pending" style="background:#fef08a;color:#854d0e;">Changes Requested</span>';
      } else {
        statusBadge = '<span class="badge-status badge-inactive">' + (p.vendor_status || 'Draft') + '</span>';
      }
      
      var vendorName = (p.vendor && p.vendor.company_name) ? p.vendor.company_name : 'Unknown';
      var price = p.vendor_price ? p.vendor_price : p.price;
      
      return '<tr onclick="location.href=\'/admin/vendor-products/' + p.id + '\'" style="cursor:pointer;">' +
        '<td>#' + p.id + '</td>' +
        '<td style="font-weight:600;">' + esc(p.name || '—') + '</td>' +
        '<td style="color:#666;">' + esc(vendorName) + '</td>' +
        '<td>' + statusBadge + '</td>' +
        '<td>EGP ' + parseFloat(price).toLocaleString() + '</td>' +
        '<td onclick="event.stopPropagation()">' + 
          '<a href="/admin/vendor-products/' + p.id + '" style="color:#c9a96e;font-size:13px;font-weight:600;text-decoration:none;margin-right:12px;">Review</a>' +
          '<button type="button" onclick="deleteProduct(' + p.id + ')" style="background:none;border:none;color:#ef4444;font-size:13px;font-weight:600;cursor:pointer;padding:0;">Delete</button>' +
        '</td>' +
        '</tr>';
    }).join('');
  }

  function renderPagination(res) {
    var container = document.getElementById('pagination');
    var total = res.total || 0;
    var perPage = res.per_page || 15;
    var current = res.current_page || 1;
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
  
  window.loadProducts = loadProducts;
})();
</script>
@endsection
