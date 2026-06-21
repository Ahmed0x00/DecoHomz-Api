@extends('admin.layouts.app')

@section('title', 'Pre-Orders')
@section('page_title', 'Pre-Orders')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Pre-Orders</h1>
</div>

<!-- Stats -->
<div class="stat-grid" id="stats-grid">
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#dbeafe">
      <svg viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="1.5"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
    </div>
    <div class="stat-card-num" id="stat-total">—</div>
    <div class="stat-card-label">Total</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#fef3c7">
      <svg viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <div class="stat-card-num" id="stat-pending">—</div>
    <div class="stat-card-label">Pending</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#dbeafe">
      <svg viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
    </div>
    <div class="stat-card-num" id="stat-contacted">—</div>
    <div class="stat-card-label">Contacted</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#d1fae5">
      <svg viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="1.5"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <div class="stat-card-num" id="stat-confirmed">—</div>
    <div class="stat-card-label">Confirmed</div>
  </div>
</div>

<!-- Filters -->
<div class="admin-card" style="margin-bottom:24px;">
  <div style="padding:16px 24px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
    <input type="text" id="filter-search" placeholder="Search name, phone, email..." style="flex:1;min-width:200px;padding:8px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;" oninput="debounceLoad()">
    <select id="filter-status" style="padding:8px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;min-width:140px;" onchange="loadPreOrders()">
      <option value="">All Status</option>
      <option value="pending">Pending</option>
      <option value="contacted">Contacted</option>
      <option value="confirmed">Confirmed</option>
      <option value="cancelled">Cancelled</option>
    </select>
  </div>
</div>

<!-- Table -->
<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Images</th>
        <th>Notes</th>
        <th>Status</th>
        <th>Date</th>
        <th style="width:120px;">Actions</th>
      </tr>
    </thead>
    <tbody id="preorders-tbody">
      <tr class="loading-row"><td colspan="8"></td></tr>
    </tbody>
  </table>
</div>

<div id="pagination" style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:24px;"></div>

@endsection

@section('extra_js')
<script>
(function() {
  var currentPage = 1;
  var _searchTimer;

  window.debounceLoad = function() {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(loadPreOrders, 350);
  };

  document.addEventListener('DOMContentLoaded', function() {
    loadPreOrders();
  });

  window.loadPreOrders = function(page) {
    if (page) currentPage = page;

    var search = document.getElementById('filter-search').value;
    var status = document.getElementById('filter-status').value;

    var params = { page: currentPage };
    if (search) params.search = search;
    if (status) params.status = status;

    document.getElementById('preorders-tbody').innerHTML = '<tr class="loading-row"><td colspan="8"></td></tr>';

    API.get('/admin/pre-orders', { params: params }).then(function(res) {
      var items = res.data || [];
      renderTable(items);
      renderPagination(res);
      loadStats();
    }).catch(function() {
      document.getElementById('preorders-tbody').innerHTML = '<tr><td colspan="8" style="text-align:center;color:#ef4444;padding:30px">Failed to load pre-orders.</td></tr>';
    });
  };

  function loadStats() {
    API.get('/admin/pre-orders', { params: { per_page: 200 } }).then(function(res) {
      var items = res.data || [];
      var total = items.length;
      var pending = 0, contacted = 0, confirmed = 0;
      items.forEach(function(p) {
        if (p.status === 'pending') pending++;
        else if (p.status === 'contacted') contacted++;
        else if (p.status === 'confirmed') confirmed++;
      });
      document.getElementById('stat-total').textContent = total;
      document.getElementById('stat-pending').textContent = pending;
      document.getElementById('stat-contacted').textContent = contacted;
      document.getElementById('stat-confirmed').textContent = confirmed;
    });
  }

  function renderTable(items) {
    var tbody = document.getElementById('preorders-tbody');
    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#aaa;padding:40px">No pre-orders found.</td></tr>';
      return;
    }
    tbody.innerHTML = items.map(function(p) {
      var imageCount = (p.images && p.images.length) || 0;
      var notes = (p.notes || '').substring(0, 60);
      if ((p.notes || '').length > 60) notes += '...';

      var statusClass, statusLabel;
      switch (p.status) {
        case 'contacted': statusClass = 'badge-inactive'; statusLabel = 'Contacted'; break;
        case 'confirmed': statusClass = 'badge-approved'; statusLabel = 'Confirmed'; break;
        case 'cancelled': statusClass = 'badge-pending'; statusLabel = 'Cancelled'; break;
        default: statusClass = 'badge-pending'; statusLabel = 'Pending';
      }

      var date = p.created_at ? new Date(p.created_at).toLocaleDateString('en-EG', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';

      return '<tr>' +
        '<td style="font-weight:600;">' + (p.name || '—') + '</td>' +
        '<td style="font-size:12px;color:#666;">' + (p.phone || '—') + '</td>' +
        '<td style="font-size:12px;color:#666;">' + (p.email || '—') + '</td>' +
        '<td><span style="background:#f3f4f6;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:600;">' + imageCount + ' images</span></td>' +
        '<td style="max-width:180px;font-size:12px;color:#666;" title="' + (p.notes || '').replace(/"/g, '&quot;') + '">' + notes + '</td>' +
        '<td><span class="admin-badge ' + statusClass + '">' + statusLabel + '</span></td>' +
        '<td style="font-size:12px;">' + date + '</td>' +
        '<td>' +
          '<a href="/admin/pre-orders/' + p.id + '" style="padding:5px 10px;background:#dbeafe;color:#1e40af;border:1px solid #dbeafe;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;">View</a>' +
        '</td>' +
      '</tr>';
    }).join('');
  }

  function renderPagination(res) {
    var container = document.getElementById('pagination');
    var lastPage = res.last_page || 1;
    if (lastPage <= 1) { container.innerHTML = ''; return; }
    var html = '';
    for (var i = 1; i <= lastPage; i++) {
      html += '<button onclick="loadPreOrders(' + i + ')" style="padding:6px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;cursor:pointer;background:' + (i === currentPage ? '#1a1a1a' : '#fff') + ';color:' + (i === currentPage ? '#fff' : '#333') + ';">' + i + '</button>';
    }
    container.innerHTML = html;
  }
})();
</script>
@endsection
