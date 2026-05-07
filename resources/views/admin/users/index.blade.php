@extends('admin.layouts.app')

@section('title', 'Users')
@section('page_title', 'Users')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Users</h1>
</div>

<!-- Stats Cards -->
<div class="stat-grid" id="stats-grid">
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#dbeafe">
      <svg viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div class="stat-card-num" id="stat-total">—</div>
    <div class="stat-card-label">Total Users</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#fef3c7">
      <svg viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>
    <div class="stat-card-num" id="stat-admins">—</div>
    <div class="stat-card-label">Admins</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#d1fae5">
      <svg viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </div>
    <div class="stat-card-num" id="stat-customers">—</div>
    <div class="stat-card-label">Customers</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#e0e7ff">
      <svg viewBox="0 0 24 24" fill="none" stroke="#4338ca" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
    </div>
    <div class="stat-card-num" id="stat-new">—</div>
    <div class="stat-card-label">New This Month</div>
  </div>
</div>

<!-- Users Table -->
<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Joined</th>
        <th style="width:100px;">Actions</th>
      </tr>
    </thead>
    <tbody id="users-tbody">
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
    loadUsers();
  });

  async function loadUsers(page) {
    if (page) currentPage = page;
    var params = { page: currentPage };

    renderTableLoading();
    try {
      var res = await API.get('/admin/users', { params: params });
      var users = res.data || res.users || res || [];
      if (!Array.isArray(users) && users.data) users = users.data;

      // Stats from meta or fetch separately
      renderTable(users);
      renderPagination(res);

      // Try to extract stats from response
      if (res.total !== undefined) {
        var total = res.total || users.length;
        document.getElementById('stat-total').textContent = total;
      }
    } catch(e) {
      document.getElementById('users-tbody').innerHTML = '<tr><td colspan="6" style="text-align:center;color:#ef4444;padding:30px">Failed to load users.</td></tr>';
    }

    // Load stats separately for accuracy
    loadStats();
  }

  async function loadStats() {
    try {
      var res = await API.get('/admin/users', { params: { per_page: 500 } });
      var users = res.data || res.users || res || [];
      if (!Array.isArray(users) && users.data) users = users.data;

      var total = users.length;
      var admins = 0, customers = 0, newThisMonth = 0;

      var now = new Date();
      var monthStart = new Date(now.getFullYear(), now.getMonth(), 1);

      users.forEach(function(u) {
        if (u.role === 'admin' || u.is_admin === 1 || u.isAdmin === true) {
          admins++;
        } else {
          customers++;
        }
        var joined = new Date(u.created_at || u.join_date);
        if (joined >= monthStart) newThisMonth++;
      });

      document.getElementById('stat-total').textContent = total;
      document.getElementById('stat-admins').textContent = admins;
      document.getElementById('stat-customers').textContent = customers;
      document.getElementById('stat-new').textContent = newThisMonth;
    } catch(e) {
      console.warn('Failed to load stats', e);
    }
  }

  function renderTableLoading() {
    document.getElementById('users-tbody').innerHTML = '<tr class="loading-row"><td colspan="6"></td></tr>';
  }

  function renderTable(users) {
    var tbody = document.getElementById('users-tbody');
    if (!users || users.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#aaa;padding:40px">No users found.</td></tr>';
      return;
    }
    tbody.innerHTML = users.map(function(u) {
      var isAdmin = u.role === 'admin' || u.is_admin === 1 || u.isAdmin === true;
      var roleBadge = isAdmin
        ? '<span class="badge-status badge-approved">Admin</span>'
        : '<span class="badge-status badge-inactive">Customer</span>';
      var joined = u.created_at
        ? new Date(u.created_at).toLocaleDateString('en-EG', { year: 'numeric', month: 'short', day: 'numeric' })
        : (u.join_date || '—');
      return '<tr>' +
        '<td>#' + u.id + '</td>' +
        '<td style="font-weight:600;">' + esc(u.name || '—') + '</td>' +
        '<td style="color:#666;">' + esc(u.email || '—') + '</td>' +
        '<td>' + roleBadge + '</td>' +
        '<td>' + joined + '</td>' +
        '<td><a href="/admin/users/' + u.id + '" style="color:#c9a96e;font-size:13px;font-weight:600;text-decoration:none;">View</a></td>' +
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
      html += '<button onclick="loadUsers(' + (current - 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">← Prev</button>';
    }
    for (var i = 1; i <= last; i++) {
      if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
        html += '<button onclick="loadUsers(' + i + ')" style="padding:6px 12px;border:1px solid ' + (i === current ? '#c9a96e' : '#e5e5e5') + ';background:' + (i === current ? '#c9a96e' : '#fff') + ';color:' + (i === current ? '#fff' : '#333') + ';border-radius:6px;cursor:pointer;font-size:13px;">' + i + '</button>';
      } else if (i === current - 2 || i === current + 2) {
        html += '<span style="color:#aaa;padding:0 4px;">...</span>';
      }
    }
    if (current < last) {
      html += '<button onclick="loadUsers(' + (current + 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">Next →</button>';
    }
    container.innerHTML = html;
  }

  window.loadUsers = loadUsers;
})();
</script>
@endsection
