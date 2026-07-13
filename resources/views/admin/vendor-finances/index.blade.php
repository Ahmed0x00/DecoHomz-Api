@extends('admin.layouts.app')

@section('title', 'Vendor Finances')
@section('page_title', 'Vendor Finances')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Vendor Finances</h1>
</div>

<!-- Vendors Table -->
<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Vendor</th>
        <th>Pending Balance</th>
        <th>Available (Cleared)</th>
        <th>Total Paid</th>
        <th style="width:150px;">Actions</th>
      </tr>
    </thead>
    <tbody id="finances-tbody">
      <tr class="loading-row"><td colspan="5"></td></tr>
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
    loadFinances();
  });

  async function loadFinances(page) {
    if (page) currentPage = page;
    renderTableLoading();
    try {
      var res = await API.get('/admin/vendor-finances', { params: { page: currentPage } });
      var vendors = res.data && res.data.data ? res.data.data : (res.data || []);
      renderTable(vendors);
      renderPagination(res.data || res);
    } catch(e) {
      document.getElementById('finances-tbody').innerHTML = '<tr><td colspan="5" style="text-align:center;color:#ef4444;padding:30px">Failed to load finances.</td></tr>';
    }
  }

  function renderTableLoading() {
    document.getElementById('finances-tbody').innerHTML = '<tr class="loading-row"><td colspan="5"></td></tr>';
  }

  function renderTable(vendors) {
    var tbody = document.getElementById('finances-tbody');
    if (!vendors || vendors.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#aaa;padding:40px">No active vendors found.</td></tr>';
      return;
    }
    tbody.innerHTML = vendors.map(function(v) {
      var bal = v.balances || { pending: 0, available: 0, paid: 0 };
      var pending = Number(bal.pending || bal.pending_clearance || 0);
      var available = Number(bal.available || bal.available_balance || 0);
      var paid = Number(bal.paid || bal.total_paid || 0);
      var hasAvailable = available > 0;
      
      return '<tr>' +
        '<td style="font-weight:600;"><a href="/admin/vendors/' + v.id + '" style="color:#1a1a1a;text-decoration:none;">' + esc(v.company_name || '—') + '</a></td>' +
        '<td style="color:#888;">EGP ' + pending.toLocaleString() + '</td>' +
        '<td style="font-weight:700;color:' + (hasAvailable ? '#065f46' : '#333') + '">EGP ' + available.toLocaleString() + '</td>' +
        '<td style="color:#666;">EGP ' + paid.toLocaleString() + '</td>' +
        '<td>' + 
          (hasAvailable ? `<button onclick="processPayout(${v.id}, ${available})" style="background:#c9a96e;color:#fff;border:none;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">Process Payout</button>` : `<span style="color:#aaa;font-size:12px;">No funds</span>`) +
        '</td>' +
        '</tr>';
    }).join('');
  }

  window.processPayout = async function(vendorId, maxAmount) {
    var amount = prompt('Enter payout amount (Max: EGP ' + maxAmount + '):', maxAmount);
    if (!amount) return;
    
    var ref = prompt('Enter Bank Transfer Reference / Receipt ID:');
    if (!ref) {
      alert('Reference is required.');
      return;
    }

    try {
      await API.post('/admin/vendor-finances/process-payouts', {
        vendor_id: vendorId,
        amount: parseFloat(amount),
        reference: ref
      });
      showToast('Payout processed successfully', 'success');
      loadFinances(currentPage);
    } catch(err) {
      showToast('Failed to process payout: ' + (err.response?.data?.message || err.message), 'error');
    }
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
      html += '<button onclick="loadFinances(' + (current - 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">← Prev</button>';
    }
    for (var i = 1; i <= last; i++) {
      if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
        html += '<button onclick="loadFinances(' + i + ')" style="padding:6px 12px;border:1px solid ' + (i === current ? '#c9a96e' : '#e5e5e5') + ';background:' + (i === current ? '#c9a96e' : '#fff') + ';color:' + (i === current ? '#fff' : '#333') + ';border-radius:6px;cursor:pointer;font-size:13px;">' + i + '</button>';
      } else if (i === current - 2 || i === current + 2) {
        html += '<span style="color:#aaa;padding:0 4px;">...</span>';
      }
    }
    if (current < last) {
      html += '<button onclick="loadFinances(' + (current + 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">Next →</button>';
    }
    container.innerHTML = html;
  }
})();
</script>
@endsection
