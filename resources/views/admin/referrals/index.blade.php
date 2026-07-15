@extends('admin.layouts.app')

@section('title', 'Referrals Dashboard')
@section('page_title', 'All Referrals')

@section('content')
<div style="padding: 0 16px;">

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;margin:0;">Referrals Dashboard</h1>
  <div style="display:flex;gap:12px;">
    <button onclick="updateReferralStatus('approved')" style="background:#fff;color:#1a1a1a;border:1px solid #e5e5e5;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:0.2s;">Approve Selected</button>
    <button onclick="updateReferralStatus('rejected')" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:0.2s;">Reject Selected</button>
    <select id="status-filter" class="form-input" style="width:160px;padding:8px 12px;border-radius:8px;border:1px solid #e5e5e5;background:#fff;" onchange="loadReferrals(1)">
      <option value="">All Statuses</option>
      <option value="pending">Pending</option>
      <option value="holding">Holding</option>
      <option value="approved">Approved</option>
      <option value="paid">Paid</option>
      <option value="rejected">Rejected</option>
      <option value="clawback">Clawback</option>
    </select>
  </div>
</div>

<!-- Referrals Table -->
<div class="admin-card" style="background:#fff;border-radius:16px;border:1px solid #eee;overflow:hidden;">
  <div style="overflow-x:auto;">
    <table class="admin-table" style="width:100%;border-collapse:collapse;text-align:left;">
      <thead>
        <tr style="background:#f9f9f9;border-bottom:1px solid #eee;">
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;width:40px;text-align:center;"><input type="checkbox" id="select-all" onclick="toggleAll(this)"></th>
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;">ID</th>
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;">Affiliate</th>
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;">Order #</th>
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;">Subtotal</th>
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;">Commission</th>
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;">Status</th>
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;">Fraud Flags</th>
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;">Date</th>
        </tr>
      </thead>
      <tbody id="referrals-tbody">
      <tr class="loading-row"><td colspan="8"></td></tr>
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
    loadReferrals();
  });

  async function loadReferrals(page) {
    if (page) currentPage = page;
    var status = document.getElementById('status-filter').value;
    var params = { page: currentPage, status: status };

    renderTableLoading();
    try {
      var res = await API.get('/admin/referrals', { params: params });
      var referrals = res.data || res.referrals || res || [];
      if (!Array.isArray(referrals) && referrals.data) referrals = referrals.data;

      renderTable(referrals);
      renderPagination(res);
    } catch(e) {
      document.getElementById('referrals-tbody').innerHTML = '<tr><td colspan="8" style="text-align:center;color:#ef4444;padding:30px">Failed to load referrals.</td></tr>';
    }
  }

  function renderTableLoading() {
    document.getElementById('referrals-tbody').innerHTML = '<tr class="loading-row"><td colspan="8"></td></tr>';
  }

  function renderTable(referrals) {
    var tbody = document.getElementById('referrals-tbody');
    if (!referrals || referrals.length === 0) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#aaa;padding:40px">No referrals found.</td></tr>';
      return;
    }
    tbody.innerHTML = referrals.map(function(r) {
      var affiliateName = (r.affiliate && r.affiliate.user) ? r.affiliate.user.name : 'Unknown';
      var orderNum = r.order ? r.order.order_number : ('ID: ' + r.order_id);
      
      var statusColor = '#9a3412';
      var statusBg = '#fff7ed';
      if (r.commission_status === 'approved' || r.commission_status === 'paid') {
          statusColor = '#166534';
          statusBg = '#f0fdf4';
      } else if (r.commission_status === 'revoked' || r.commission_status === 'clawback' || r.commission_status === 'rejected') {
          statusColor = '#991b1b';
          statusBg = '#fef2f2';
      } else if (r.commission_status === 'holding') {
          statusColor = '#1e40af';
          statusBg = '#eff6ff';
      }

      var statusBadge = '<span style="display:inline-block;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;background:' + statusBg + ';color:' + statusColor + ';text-transform:capitalize;">' + esc(r.commission_status) + '</span>';

      var flagsHtml = '<span style="color:#aaa;">None</span>';
      if (r.fraud_flags && typeof r.fraud_flags === 'object') {
          var flags = [];
          if (r.fraud_flags.self_referral) flags.push('Self Ref');
          if (r.fraud_flags.same_ip) flags.push('Same IP');
          if (flags.length > 0) {
              flagsHtml = '<span style="color:#ef4444;font-size:12px;font-weight:600;" title="Potential Fraud">⚠️ ' + flags.join(', ') + '</span>';
          }
      }

      var checkbox = '';
      if (r.commission_status !== 'paid') {
          checkbox = '<input type="checkbox" class="payout-check" value="' + r.id + '">';
      }

      return '<tr style="border-bottom:1px solid #eee;">' +
        '<td style="padding:16px;text-align:center;">' + checkbox + '</td>' +
        '<td style="padding:16px;"><a href="/admin/referrals/' + r.id + '" style="color:var(--text-main);font-weight:600;text-decoration:none;">#' + r.id + '</a></td>' +
        '<td style="padding:16px;"><a href="/admin/affiliates/' + (r.affiliate_id || '') + '" style="color:var(--primary);text-decoration:none;font-weight:600;">' + esc(affiliateName) + '</a></td>' +
        '<td style="padding:16px;"><a href="/admin/orders/' + r.order_id + '" style="color:var(--primary);text-decoration:none;font-weight:600;">' + esc(orderNum) + '</a></td>' +
        '<td style="padding:16px;">EGP ' + parseFloat(r.order_subtotal).toFixed(2) + '</td>' +
        '<td style="padding:16px;font-weight:600;color:var(--secondary);">EGP ' + parseFloat(r.commission_amount).toFixed(2) + '</td>' +
        '<td style="padding:16px;">' + statusBadge + '</td>' +
        '<td style="padding:16px;">' + flagsHtml + '</td>' +
        '<td style="padding:16px;font-size:12px;color:#666;">' + formatDate(r.created_at) + '</td>' +
        '</tr>';
    }).join('');
  }

  window.toggleAll = function(el) {
      var checks = document.querySelectorAll('.payout-check');
      checks.forEach(c => c.checked = el.checked);
  }

  window.updateReferralStatus = function(status) {
      var checks = document.querySelectorAll('.payout-check:checked');
      var ids = Array.from(checks).map(c => parseInt(c.value));
      
      if (ids.length === 0) {
          showToast('Please select at least one referral', 'error');
          return;
      }

      var actionWord = status === 'approved' ? 'approve' : 'reject';
      if (!confirm('Are you sure you want to ' + actionWord + ' ' + ids.length + ' referrals?')) return;

      var data = { referral_ids: ids, status: status };
      if (status === 'rejected') {
          var reason = prompt('Reason for rejection:');
          if (reason) data.reason = reason;
      }

      API.post('/admin/referrals/update-status', data).then(res => {
          showToast(res.message || 'Status updated', 'success');
          loadReferrals();
      }).catch(err => {
          showToast(err.response?.data?.message || err.message || 'Failed to update status', 'error');
      });
  }

  function renderPagination(res) {
    var container = document.getElementById('pagination');
    var total = res.total || 0;
    var perPage = res.per_page || 20;
    var current = res.current_page || 1;
    var last = Math.ceil(total / perPage);
    if (last <= 1) { container.innerHTML = ''; return; }

    var html = '';
    if (current > 1) html += '<button onclick="loadReferrals(' + (current - 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">Prev</button>';
    html += ' <span style="font-size:13px;margin:0 10px;">Page ' + current + ' of ' + last + '</span> ';
    if (current < last) html += '<button onclick="loadReferrals(' + (current + 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">Next</button>';
    container.innerHTML = html;
  }

  window.loadReferrals = loadReferrals;
})();
</script>
@endsection
