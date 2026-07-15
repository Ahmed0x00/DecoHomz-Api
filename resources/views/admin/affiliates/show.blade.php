@extends('admin.layouts.app')

@section('title', 'Affiliate Details')
@section('page_title', 'Affiliate Details')

@section('content')
<div style="padding: 0 16px;">

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;margin:0;">Affiliate: <span id="affiliate-name"></span></h1>
  <div style="display:flex;gap:12px;">
    <button onclick="updateReferralStatus('approved')" style="background:#fff;color:#1a1a1a;border:1px solid #e5e5e5;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:0.2s;">Approve Selected</button>
    <button onclick="processPayout()" style="background:#1a1a1a;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:0.2s;">Process Payout</button>
  </div>
</div>

<!-- Stats -->
<div class="stat-grid" id="stats-grid" style="margin-bottom:24px;display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:24px;">
  <div class="stat-card" style="background:#fff;padding:24px;border-radius:16px;border:1px solid #eee;">
    <div class="stat-card-label" style="color:#666;font-size:13px;font-weight:600;margin-bottom:8px;">Pending Earnings</div>
    <div class="stat-card-num" id="stat-pending" style="font-size:24px;font-weight:700;color:#1a1a1a;">—</div>
  </div>
  <div class="stat-card" style="background:#fff;padding:24px;border-radius:16px;border:1px solid #eee;">
    <div class="stat-card-label" style="color:#666;font-size:13px;font-weight:600;margin-bottom:8px;">Approved (Ready for Payout)</div>
    <div class="stat-card-num" id="stat-approved" style="font-size:24px;font-weight:700;color:#16a34a;">—</div>
  </div>
  <div class="stat-card" style="background:#fff;padding:24px;border-radius:16px;border:1px solid #eee;">
    <div class="stat-card-label" style="color:#666;font-size:13px;font-weight:600;margin-bottom:8px;">Total Paid</div>
    <div class="stat-card-num" id="stat-paid" style="font-size:24px;font-weight:700;color:#1a1a1a;">—</div>
  </div>
</div>

<div class="admin-card" style="margin-bottom:24px;background:#fff;padding:24px;border-radius:16px;border:1px solid #eee;">
  <h3 style="margin: 0 0 16px 0; border-bottom: 1px solid #eee; padding-bottom: 12px; font-size: 16px; font-weight: 700;">Bank Details</h3>
  <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;">
    <div>
      <div style="color:#666;font-size:12px;margin-bottom:4px;">Bank Name</div>
      <div style="font-weight:600;color:#1a1a1a;" id="bank-name"></div>
    </div>
    <div>
      <div style="color:#666;font-size:12px;margin-bottom:4px;">Account Name</div>
      <div style="font-weight:600;color:#1a1a1a;" id="account-name"></div>
    </div>
    <div>
      <div style="color:#666;font-size:12px;margin-bottom:4px;">Account Number</div>
      <div style="font-weight:600;color:#1a1a1a;" id="account-number"></div>
    </div>
  </div>
</div>

<!-- Referrals Table -->
<div class="admin-card" style="background:#fff;border-radius:16px;border:1px solid #eee;overflow:hidden;">
  <div style="padding:24px;border-bottom:1px solid #eee;">
    <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Referrals</h3>
  </div>
  <div style="overflow-x:auto;">
    <table class="admin-table" style="width:100%;border-collapse:collapse;text-align:left;">
      <thead>
        <tr style="background:#f9f9f9;border-bottom:1px solid #eee;">
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;width:40px;text-align:center;"><input type="checkbox" id="select-all" onclick="toggleAll(this)"></th>
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;">ID</th>
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;">Order #</th>
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;">Commission</th>
          <th style="padding:16px;font-size:13px;color:#666;font-weight:600;">Status</th>
        </tr>
      </thead>
      <tbody id="referrals-tbody">
        <tr class="loading-row"><td colspan="5" style="padding:16px;"></td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Pagination -->
<div id="pagination" style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:24px;"></div>

</div>
@endsection

@section('extra_js')
<script>
(function() {
  var affiliateId = window.location.pathname.split('/').pop();
  var currentPage = 1;
  var approvedIds = [];

  document.addEventListener('DOMContentLoaded', function() {
    loadAffiliate();
  });

  async function loadAffiliate(page) {
    if (page) currentPage = page;
    var params = { page: currentPage };

    try {
      var res = await API.get('/admin/affiliates/' + affiliateId, { params: params });
      
      var data = res.data || res;
      var affiliate = data.affiliate;
      var balances = data.balances;
      var referrals = data.referrals.data || data.referrals;

      document.getElementById('affiliate-name').textContent = affiliate.user ? affiliate.user.name : 'Unknown';
      
      document.getElementById('stat-pending').textContent = 'EGP ' + (balances.pending || 0).toLocaleString();
      document.getElementById('stat-approved').textContent = 'EGP ' + (balances.approved || 0).toLocaleString();
      document.getElementById('stat-paid').textContent = 'EGP ' + (balances.paid || 0).toLocaleString();

      document.getElementById('bank-name').textContent = affiliate.bank_name || 'N/A';
      document.getElementById('account-name').textContent = affiliate.bank_account_name || 'N/A';
      document.getElementById('account-number').textContent = affiliate.bank_account_number || 'N/A';

      renderTable(referrals);
      renderPagination(data.referrals);
    } catch(e) {
      showToast('Failed to load affiliate details', 'error');
    }
  }

  function renderTable(referrals) {
    var tbody = document.getElementById('referrals-tbody');
    if (!referrals || referrals.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#aaa;padding:40px">No referrals found.</td></tr>';
      return;
    }
    tbody.innerHTML = referrals.map(function(r) {
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
      
      var checkbox = '';
      if (r.commission_status !== 'paid') {
          checkbox = '<input type="checkbox" class="payout-check" value="' + r.id + '">';
      }
        
      return '<tr style="border-bottom:1px solid #eee;">' +
        '<td style="padding:16px;text-align:center;">' + checkbox + '</td>' +
        '<td style="padding:16px;"><a href="/admin/referrals/' + r.id + '" style="color:var(--text-main);font-weight:600;text-decoration:none;">#' + r.id + '</a></td>' +
        '<td style="padding:16px;"><a href="/admin/orders/' + r.order_id + '" style="color:var(--primary);text-decoration:none;font-weight:600;">' + esc(r.order ? r.order.order_number : r.order_id) + '</a></td>' +
        '<td style="padding:16px;font-weight:600;">EGP ' + parseFloat(r.commission_amount).toFixed(2) + '</td>' +
        '<td style="padding:16px;">' + statusBadge + '</td>' +
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

      if (!confirm('Are you sure you want to change the status of ' + ids.length + ' referrals to ' + status + '?')) return;

      API.post('/admin/referrals/update-status', {
          referral_ids: ids,
          status: status
      }).then(res => {
          showToast(res.message || 'Status updated', 'success');
          loadAffiliate();
      }).catch(err => {
          showToast(err.response?.data?.message || err.message || 'Failed to update status', 'error');
      });
  }

  window.processPayout = function() {
      var checks = document.querySelectorAll('.payout-check:checked');
      var ids = Array.from(checks).map(c => parseInt(c.value));
      
      if (ids.length === 0) {
          showToast('Please select at least one approved referral', 'error');
          return;
      }
      
      var ref = prompt('Enter payout reference (e.g. Bank Transfer ID):');
      if (!ref) return;

      API.post('/admin/referrals/process-payouts', {
          affiliate_id: affiliateId,
          referral_ids: ids,
          payout_reference: ref
      }).then(res => {
          showToast(res.message || res.success || 'Payout processed', 'success');
          loadAffiliate();
      }).catch(err => {
          showToast(err.response?.data?.message || err.message || 'Failed to process payout', 'error');
      });
  }

  function renderPagination(res) {
    if(!res) return;
    var container = document.getElementById('pagination');
    var total = res.total || 0;
    var perPage = res.per_page || 20;
    var current = res.current_page || 1;
    var last = Math.ceil(total / perPage);
    if (last <= 1) { container.innerHTML = ''; return; }

    var html = '';
    if (current > 1) html += '<button onclick="loadAffiliate(' + (current - 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">Prev</button>';
    html += ' <span style="font-size:13px;margin:0 10px;">Page ' + current + ' of ' + last + '</span> ';
    if (current < last) html += '<button onclick="loadAffiliate(' + (current + 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">Next</button>';
    container.innerHTML = html;
  }

  window.loadAffiliate = loadAffiliate;
})();
</script>
@endsection
