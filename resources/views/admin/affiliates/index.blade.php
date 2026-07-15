@extends('admin.layouts.app')

@section('title', 'Affiliates')
@section('page_title', 'Affiliate Management')

@section('content')
<div style="padding: 32px">

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Affiliates</h1>
  <a href="/admin/settings" style="background:#c9a96e;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;">Settings</a>
</div>

<!-- Affiliates Table -->
<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Affiliate Name</th>
        <th>Code</th>
        <th>Status</th>
        <th>Total Earnings</th>
        <th>Pending Referrals</th>
        <th style="width:100px;">Actions</th>
      </tr>
    </thead>
    <tbody id="affiliates-tbody">
      <tr class="loading-row"><td colspan="7"></td></tr>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<div id="pagination" style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:24px;"></div>

</div>
@endsection

@section('extra_js')
<script>
(function() {
  var currentPage = 1;

  document.addEventListener('DOMContentLoaded', function() {
    loadAffiliates();
  });

  async function loadAffiliates(page) {
    if (page) currentPage = page;
    var params = { page: currentPage };

    renderTableLoading();
    try {
      var res = await API.get('/admin/affiliates', { params: params });
      var affiliates = res.data || res.affiliates || res || [];
      if (!Array.isArray(affiliates) && affiliates.data) affiliates = affiliates.data;

      renderTable(affiliates);
      renderPagination(res);
    } catch(e) {
      document.getElementById('affiliates-tbody').innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:30px">Failed to load affiliates.</td></tr>';
    }
  }

  function renderTableLoading() {
    document.getElementById('affiliates-tbody').innerHTML = '<tr class="loading-row"><td colspan="7"></td></tr>';
  }

  function renderTable(affiliates) {
    var tbody = document.getElementById('affiliates-tbody');
    if (!affiliates || affiliates.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#aaa;padding:40px">No affiliates found.</td></tr>';
      return;
    }
    tbody.innerHTML = affiliates.map(function(a) {
      var statusBadge = a.is_active 
        ? '<span class="badge-status badge-approved">Active</span>' 
        : '<span class="badge-status badge-inactive">Inactive</span>';
        
      return '<tr onclick="location.href=\'/admin/affiliates/' + a.id + '\'" style="cursor:pointer;">' +
        '<td>#' + a.id + '</td>' +
        '<td style="font-weight:600;">' + esc(a.user ? a.user.name : 'Unknown') + '</td>' +
        '<td style="font-family:monospace;font-weight:bold;">' + esc(a.referral_code) + '</td>' +
        '<td>' + statusBadge + '</td>' +
        '<td>EGP ' + parseFloat(a.total_earnings).toFixed(2) + '</td>' +
        '<td>' + (a.pending_referrals || 0) + '</td>' +
        '<td onclick="event.stopPropagation()">' + 
          '<button onclick="toggleStatus(' + a.id + ')" style="background:none;border:none;color:#4338ca;font-size:13px;font-weight:600;cursor:pointer;padding:0;">Toggle Status</button>' +
        '</td>' +
        '</tr>';
    }).join('');
  }

  window.toggleStatus = function(id) {
    if (!confirm('Are you sure you want to toggle the status of this affiliate?')) return;
    API.patch('/admin/affiliates/' + id + '/toggle-status').then(function() {
      showToast('Status toggled successfully.', 'success');
      loadAffiliates();
    }).catch(function() {
      showToast('Failed to toggle status.', 'error');
    });
  };

  function renderPagination(res) {
    var container = document.getElementById('pagination');
    var total = res.total || 0;
    var perPage = res.per_page || 20;
    var current = res.current_page || 1;
    var last = Math.ceil(total / perPage);
    if (last <= 1) { container.innerHTML = ''; return; }

    var html = '';
    if (current > 1) html += '<button onclick="loadAffiliates(' + (current - 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">Prev</button>';
    html += ' <span style="font-size:13px;margin:0 10px;">Page ' + current + ' of ' + last + '</span> ';
    if (current < last) html += '<button onclick="loadAffiliates(' + (current + 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">Next</button>';
    container.innerHTML = html;
  }

  window.loadAffiliates = loadAffiliates;
})();
</script>
@endsection
