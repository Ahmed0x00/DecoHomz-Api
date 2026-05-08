@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

<!-- Stats Grid -->
<div class="stat-grid" id="stats-grid">
  <a href="/admin/orders" class="stat-card" style="text-decoration:none;color:inherit;display:block">
    <div class="stat-card-icon" style="background:#fef3c7">
      <svg viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="1.5"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
    </div>
    <div class="stat-card-num" id="stat-orders-total">—</div>
    <div class="stat-card-label">Total Orders</div>
    <div class="stat-card-change pos" id="stat-orders-month"></div>
  </a>
  <a href="/admin/dashboard" class="stat-card" style="text-decoration:none;color:inherit;display:block">
    <div class="stat-card-icon" style="background:#d1fae5">
      <svg viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="1.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
    </div>
    <div class="stat-card-num" id="stat-revenue-total">—</div>
    <div class="stat-card-label">Total Revenue</div>
    <div class="stat-card-change pos" id="stat-revenue-month"></div>
  </a>
  <a href="/admin/users" class="stat-card" style="text-decoration:none;color:inherit;display:block">
    <div class="stat-card-icon" style="background:#dbeafe">
      <svg viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div class="stat-card-num" id="stat-users-total">—</div>
    <div class="stat-card-label">Total Users</div>
    <div class="stat-card-change pos" id="stat-users-today"></div>
  </a>
  <a href="/admin/products" class="stat-card" style="text-decoration:none;color:inherit;display:block">
    <div class="stat-card-icon" style="background:#fee2e2">
      <svg viewBox="0 0 24 24" fill="none" stroke="#991b1b" stroke-width="1.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
    </div>
    <div class="stat-card-num" id="stat-products-total">—</div>
    <div class="stat-card-label">Products</div>
    <div class="stat-card-change" id="stat-products-alert"></div>
  </a>
</div>

<!-- Alerts row -->
<div id="alerts-row" style="display:flex;gap:16px;margin-bottom:24px;flex-wrap:wrap">
</div>

<!-- Recent Orders -->
<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title">Recent Orders</div>
    <a href="/admin/orders" class="admin-card-link">View All →</a>
  </div>
  <table class="admin-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Customer</th>
        <th>Total</th>
        <th>Status</th>
        <th>Payment</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody id="recent-orders-body">
      <tr class="loading-row"><td colspan="6"></td></tr>
    </tbody>
  </table>
</div>

<!-- Top Products -->
<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title">Top Selling Products</div>
    <a href="/admin/products" class="admin-card-link">Manage Products →</a>
  </div>
  <table class="admin-table">
    <thead>
      <tr>
        <th>Product</th>
        <th>Units Sold</th>
      </tr>
    </thead>
    <tbody id="top-products-body">
      <tr class="loading-row"><td colspan="2"></td></tr>
    </tbody>
  </table>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  loadDashboard();

  async function loadDashboard() {
    try {
      var res = await API.get('/admin/dashboard');
      var d = res.data || res;
      renderStats(d);
      renderAlerts(d);
      renderRecentOrders(d.recent_orders || []);
      renderTopProducts(d.top_products || []);
    } catch(e) {
      document.getElementById('stats-grid').innerHTML = '<p style="color:#ef4444;padding:20px">Failed to load dashboard data. Is the API running?</p>';
    }
  }

  function renderStats(d) {
    document.getElementById('stat-orders-total').textContent = d.orders ? d.orders.total.toLocaleString() : '—';
    document.getElementById('stat-orders-month').textContent = d.orders ? (d.orders.month + ' this month') : '';

    var rev = d.revenue ? d.revenue.total : 0;
    document.getElementById('stat-revenue-total').textContent = 'EGP ' + rev.toLocaleString();
    document.getElementById('stat-revenue-month').textContent = d.revenue ? ('EGP ' + d.revenue.month.toLocaleString() + ' this month') : '';

    document.getElementById('stat-users-total').textContent = d.users ? d.users.total.toLocaleString() : '—';
    document.getElementById('stat-users-today').textContent = d.users ? (d.users.today + ' new today') : '';

    var pt = d.products ? d.products.total : 0;
    document.getElementById('stat-products-total').textContent = pt.toLocaleString();
    var lowStock = d.products ? d.products.low_stock : 0;
    var outStock = d.products ? d.products.out_of_stock : 0;
    var alertEl = document.getElementById('stat-products-alert');
    if (lowStock > 0 || outStock > 0) {
      alertEl.className = 'stat-card-change neg';
      alertEl.textContent = lowStock + ' low, ' + outStock + ' out of stock';
    } else {
      alertEl.textContent = 'All in stock';
      alertEl.className = 'stat-card-change pos';
    }
  }

  function renderAlerts(d) {
    var alerts = [];
    if (d.pending_reviews > 0) alerts.push({ type: 'warning', msg: d.pending_reviews + ' pending review(s)', link: '/admin/reviews' });
    if (d.new_contacts > 0) alerts.push({ type: 'warning', msg: d.new_contacts + ' new contact message(s)', link: '/admin/contacts' });
    if (d.products && d.products.out_of_stock > 0) alerts.push({ type: 'error', msg: d.products.out_of_stock + ' product(s) out of stock', link: '/admin/products' });

    var el = document.getElementById('alerts-row');
    if (alerts.length === 0) {
      el.style.display = 'none';
      return;
    }
    el.style.display = 'flex';
    el.innerHTML = alerts.map(function(a) {
      var colors = { warning: '#fef3c7', error: '#fee2e2' };
      var textColors = { warning: '#92400e', error: '#991b1b' };
      return '<a href="' + a.link + '" style="background:' + colors[a.type] + ';color:' + textColors[a.type] + ';padding:12px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;display:block;cursor:pointer">' + a.msg + ' →</a>';
    }).join('');
  }

  function renderRecentOrders(orders) {
    var tbody = document.getElementById('recent-orders-body');
    if (!orders || orders.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#aaa;padding:30px">No orders yet.</td></tr>';
      return;
    }
    tbody.innerHTML = orders.map(function(o) {
      var statusClass = {
        'pending': 'badge-pending',
        'processing': 'badge-processing',
        'shipped': 'badge-shipped',
        'delivered': 'badge-delivered',
        'cancelled': 'badge-cancelled'
      }[o.status] || 'badge-pending';
      var payClass = {
        'full_paid': 'badge-paid',
        'paid_deposit': 'badge-paid-deposit',
        'refunded': 'badge-refunded',
        'unpaid': 'badge-unpaid'
      }[o.payment_status] || 'badge-unpaid';
      var payLabel = {
        'full_paid': 'Full Paid',
        'paid_deposit': 'Paid Deposit',
        'refunded': 'Refunded',
        'unpaid': 'Unpaid'
      }[o.payment_status] || o.payment_status || 'Unpaid';
      var date = new Date(o.created_at).toLocaleDateString('en-EG', { year: 'numeric', month: 'short', day: 'numeric' });
      var userName = o.user ? o.user.name : 'Guest';
      return '<tr onclick="location.href=\'/admin/orders/' + o.id + '\'" style="cursor:pointer">' +
        '<td>#' + (o.order_number || o.id) + '</td>' +
        '<td>' + esc(userName) + '</td>' +
        '<td>EGP ' + parseFloat(o.total || 0).toLocaleString() + '</td>' +
        '<td><span class="badge-status ' + statusClass + '">' + (o.status || 'pending') + '</span></td>' +
        '<td><span class="badge-status ' + payClass + '">' + payLabel + '</span></td>' +
        '<td>' + date + '</td>' +
        '</tr>';
    }).join('');
  }

  function renderTopProducts(products) {
    var tbody = document.getElementById('top-products-body');
    if (!products || products.length === 0) {
      tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;color:#aaa;padding:30px">No sales data yet.</td></tr>';
      return;
    }
    tbody.innerHTML = products.map(function(p) {
      var img = p.image ? '<img src="' + p.image + '" style="width:40px;height:40px;object-fit:cover;border-radius:6px;margin-right:12px;vertical-align:middle">' : '';
      return '<tr>' +
        '<td style="display:flex;align-items:center">' + img + esc(p.name) + '</td>' +
        '<td>' + p.total_sold + ' units</td>' +
        '</tr>';
    }).join('');
  }
})();
</script>
@endsection
