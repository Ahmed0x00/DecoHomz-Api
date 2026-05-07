@extends('admin.layouts.app')
@section('title', 'Orders')

@section('content')
<div style="padding: 32px">

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <h1 style="font-size:20px;font-weight:700;color:#1a1a1a">Orders</h1>
    <div style="display:flex;gap:8px">
      <button class="filter-btn active" data-status="">All</button>
      <button class="filter-btn" data-status="pending">Pending</button>
      <button class="filter-btn" data-status="processing">Processing</button>
      <button class="filter-btn" data-status="shipped">Shipped</button>
      <button class="filter-btn" data-status="delivered">Delivered</button>
      <button class="filter-btn" data-status="cancelled">Cancelled</button>
    </div>
  </div>

  <div style="margin-bottom:16px">
    <input type="text" id="searchInput" placeholder="Search by order number or customer name..." style="width:320px;padding:8px 14px;border:1px solid #ddd;border-radius:8px;font-size:13px">
  </div>

  <div class="admin-card">
    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Customer</th>
          <th>Total (EGP)</th>
          <th>Status</th>
          <th>Payment</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="ordersTableBody">
        <tr><td colspan="7" style="text-align:center;color:#aaa;padding:40px">Loading...</td></tr>
      </tbody>
    </table>
  </div>

  <div id="paginationContainer" style="margin-top:16px;display:flex;gap:6px;flex-wrap:wrap"></div>

</div>
@endsection

@push('scripts')
<style>
  .filter-btn { padding: 6px 14px; border: 1px solid #e5e5e5; background: #fff; border-radius: 50px; font-size: 12px; font-weight: 500; cursor: pointer; color: #666; transition: all .15s; }
  .filter-btn:hover { border-color: #c9a96e; color: #c9a96e; }
  .filter-btn.active { background: #2C1F14; color: #fff; border-color: #2C1F14; }
</style>
<script>
(function() {
  var currentStatus = '';
  var currentSearch = '';
  var currentPage = 1;

  loadOrders();

  // Filter tabs
  document.querySelectorAll('.filter-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.filter-btn').forEach(function(b) { b.classList.remove('active'); });
      btn.classList.add('active');
      currentStatus = btn.dataset.status;
      currentPage = 1;
      loadOrders();
    });
  });

  // Search
  var searchTimer;
  document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() {
      currentSearch = document.getElementById('searchInput').value.trim();
      currentPage = 1;
      loadOrders();
    }, 400);
  });

  function loadOrders() {
    var params = { page: currentPage };
    if (currentStatus) params.status = currentStatus;
    if (currentSearch) params.search = currentSearch;

    document.getElementById('ordersTableBody').innerHTML = '<tr><td colspan="7" style="text-align:center;color:#aaa;padding:40px">Loading...</td></tr>';

    API.get('/admin/orders', { params: params }).then(function(response) {
      var orders = response.data || [];
      renderOrders(orders);
      renderPagination(response);
    }).catch(function() {
      document.getElementById('ordersTableBody').innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:30px">Failed to load orders.</td></tr>';
    });
  }

  function renderOrders(orders) {
    var tbody = document.getElementById('ordersTableBody');
    if (!orders || orders.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#aaa;padding:40px">No orders found.</td></tr>';
      return;
    }
    tbody.innerHTML = orders.map(function(o) {
      var statusBadge = getStatusBadge(o.status);
      var payBadge = getPaymentBadge(o.payment_status);
      var date = o.created_at ? new Date(o.created_at).toLocaleDateString('en-EG', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
      var userName = o.user ? esc(o.user.name) : 'Guest';
      return '<tr style="cursor:pointer" onclick="location.href=\'/admin/orders/' + o.id + '\'">' +
        '<td style="font-weight:600">#' + (o.order_number || o.id) + '</td>' +
        '<td>' + userName + '</td>' +
        '<td style="font-weight:600">EGP ' + parseFloat(o.total || 0).toLocaleString() + '</td>' +
        '<td>' + statusBadge + '</td>' +
        '<td>' + payBadge + '</td>' +
        '<td style="color:#888;font-size:12px">' + date + '</td>' +
        '<td><a href="/admin/orders/' + o.id + '" onclick="event.stopPropagation()" style="color:#c9a96e;font-weight:600;font-size:13px">View</a></td>' +
        '</tr>';
    }).join('');
  }

  function getStatusBadge(status) {
    var map = {
      'pending': '<span class="badge-status badge-pending">Pending</span>',
      'processing': '<span class="badge-status badge-processing">Processing</span>',
      'shipped': '<span class="badge-status badge-shipped">Shipped</span>',
      'delivered': '<span class="badge-status badge-delivered">Delivered</span>',
      'cancelled': '<span class="badge-status badge-cancelled">Cancelled</span>'
    };
    return map[status] || '<span class="badge-status">' + esc(status || '') + '</span>';
  }

  function getPaymentBadge(paymentStatus) {
    if (paymentStatus === 'paid') return '<span class="badge-status badge-paid">Paid</span>';
    return '<span class="badge-status badge-unpaid">Unpaid</span>';
  }

  function renderPagination(response) {
    var container = document.getElementById('paginationContainer');
    if (!response.links) { container.innerHTML = ''; return; }
    var total = response.total || 0;
    var perPage = response.per_page || 15;
    var lastPage = response.last_page || 1;
    var current = response.current_page || 1;
    if (lastPage <= 1) { container.innerHTML = ''; return; }

    var html = '';
    for (var p = 1; p <= lastPage; p++) {
      var active = p === current ? 'active' : '';
      html += '<button class="filter-btn ' + active + '" onclick="goToPage(' + p + ')">' + p + '</button>';
    }
    container.innerHTML = html;
  }

  window.goToPage = function(page) {
    currentPage = page;
    loadOrders();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };
})();
</script>
@endpush
