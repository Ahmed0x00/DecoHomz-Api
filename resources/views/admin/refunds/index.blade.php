@extends('admin.layouts.app')
@section('title', 'Refunds — DecoHomz Admin')
@section('page_title', 'Refunds')

@section('content')
<div style="padding: 32px">

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <h1 style="font-size:20px;font-weight:700;color:#1a1a1a">Refund Requests</h1>
    <div style="display:flex;gap:8px;align-items:center;">
      <div style="position:relative;">
        <input type="text" id="tableSearchInput" placeholder="Search orders..." 
          style="padding:8px 12px 8px 32px;border:1px solid #e5e5e5;border-radius:50px;font-size:12px;width:180px;outline:none;"
          oninput="debounceTableSearch(this.value)">
        <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#aaa;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </div>
      <button class="filter-btn active" data-refund-status="">All</button>
      <button class="filter-btn" data-refund-status="pending">Pending</button>
      <button class="filter-btn" data-refund-status="approved">Approved</button>
      <button class="filter-btn" data-refund-status="rejected">Rejected</button>
    </div>
  </div>

  {{-- Create Refund Request for Guest --}}
  <div class="admin-card" style="margin-bottom:24px">
    <div class="admin-card-header">
      <h5 class="admin-card-title">Create Refund Request</h5>
    </div>
    <div style="padding:20px;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
      <div style="position:relative;min-width:280px;">
        <label style="display:block;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Select Order</label>
        <div id="orderSelectWrapper" style="position:relative;">
          <input type="text" id="orderSearchInput" placeholder="Search by order # or customer..." autocomplete="off"
            style="width:100%;padding:8px 36px 8px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;box-sizing:border-box;background:#fff;"
            onfocus="searchEligibleOrders('')" oninput="debounceOrderSearch(this.value)">
          <svg id="orderSearchIcon" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#aaa;pointer-events:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <div id="orderDropdown" style="display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #e5e5e5;border-radius:6px;margin-top:4px;max-height:240px;overflow-y:auto;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.08);"></div>
        </div>
        <div id="selectedOrderDisplay" style="margin-top:6px;font-size:12px;color:#555;display:none;">
          <span id="selectedOrderText"></span>
          <span id="clearOrderBtn" style="color:#991b1b;cursor:pointer;margin-left:8px;font-weight:600;" onclick="clearSelectedOrder()">✕</span>
        </div>
      </div>
      <div style="flex:1;min-width:240px">
        <label style="display:block;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Reason</label>
        <input type="text" id="guestRefundReason" placeholder="Reason for refund request..." style="width:100%;padding:8px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;box-sizing:border-box">
      </div>
      <button type="button" onclick="createGuestRefund()" style="padding:8px 18px;background:#2C1F14;color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap">Create Refund Request</button>
    </div>
    <div id="guestRefundMsg" style="padding:0 20px 16px;font-size:12px;"></div>
  </div>

  <div class="admin-card">
    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Order</th>
          <th>Customer</th>
          <th>Order Total</th>
          <th>Refund Amount</th>
          <th>Refund Reason</th>
          <th>Status</th>
          <th>Requested At</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="refundsTableBody">
        <tr><td colspan="9" style="text-align:center;color:#aaa;padding:40px">Loading...</td></tr>
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
  .reason-text { font-size: 12px; color: #555; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
  .refund-action-btn { padding: 5px 12px; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; margin-right: 4px; }
  .btn-approve { background: #d1fae5; color: #065f46; }
  .btn-approve:hover { background: #16a34a; color: #fff; }
  .btn-reject { background: #fee2e2; color: #991b1b; }
  .btn-reject:hover { background: #b91c1c; color: #fff; }
</style>
<script>
(function() {
  var currentStatus = '';
  var tableSearch = '';
  var currentPage = 1;
  var selectedOrderId = null;
  var orderSearchTimer = null;
  var tableSearchTimer = null;

  loadRefunds();

  document.querySelectorAll('.filter-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.filter-btn').forEach(function(b) { b.classList.remove('active'); });
      btn.classList.add('active');
      currentStatus = btn.dataset.refundStatus || '';
      currentPage = 1;
      loadRefunds();
    });
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    var wrapper = document.getElementById('orderSelectWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
      document.getElementById('orderDropdown').style.display = 'none';
    }
  });

  window.debounceTableSearch = function(query) {
    clearTimeout(tableSearchTimer);
    tableSearchTimer = setTimeout(function() {
      tableSearch = query;
      currentPage = 1;
      loadRefunds();
    }, 400);
  };

  window.debounceOrderSearch = function(query) {
    clearTimeout(orderSearchTimer);
    orderSearchTimer = setTimeout(function() {
      searchEligibleOrders(query);
    }, 250);
  };

  window.searchEligibleOrders = function(query) {
    API.get('/admin/refunds/search-eligible', { params: { q: query } }).then(function(orders) {
      renderOrderDropdown(orders);
    }).catch(function() {});
  };

  window.renderOrderDropdown = function(orders) {
    var dropdown = document.getElementById('orderDropdown');
    if (!orders || orders.length === 0) {
      dropdown.innerHTML = '<div style="padding:12px 14px;color:#aaa;font-size:13px;">No eligible orders found.</div>';
      dropdown.style.display = 'block';
      return;
    }
    dropdown.innerHTML = orders.map(function(o) {
      var customer = o.user ? o.user.name : (o.shipping_address ? (o.shipping_address.name || o.shipping_address.first_name || 'Guest') : 'Guest');
      var orderNum = o.order_number || o.id;
      var total = parseFloat(o.total || 0).toLocaleString();
      var refundAmt = o.payment_status === 'full_paid'
        ? parseFloat(o.total || 0)
        : parseFloat(o.deposit_amount || 0);
      var refundLabel = 'Refund: EGP ' + refundAmt.toLocaleString();
      var paymentLabel = o.payment_status === 'full_paid' ? 'Full Paid' : 'Paid Deposit';
      var statusNote = o.refund_status === 'rejected' ? ' (prev. rejected)' : '';
      return '<div onclick="selectOrder(' + o.id + ', \'' + orderNum.replace(/'/g, "\\'") + '\', \'' + customer.replace(/'/g, "\\'") + '\', \'' + total + '\', \'' + paymentLabel + '\', ' + refundAmt + ')" ' +
        'style="padding:10px 14px;border-bottom:1px solid #f0f0f0;cursor:pointer;font-size:13px;line-height:1.4;" ' +
        'onmouseover="this.style.background=\'#f9f7f5\'" onmouseout="this.style.background=\'\'">' +
        '<div style="font-weight:600;color:#2C1F14;">#' + orderNum + ' <span style="font-weight:400;color:#888;font-size:12px;">&mdash; ' + esc(customer) + '</span></div>' +
        '<div style="color:#555;font-size:12px;margin-top:2px;">EGP ' + total + ' &middot; ' + paymentLabel + ' &middot; <span style="color:#92400e;font-weight:600;">' + refundLabel + '</span><span style="color:#ef4444;">' + statusNote + '</span></div>' +
        '</div>';
    }).join('');
    dropdown.style.display = 'block';
  };

  window.selectOrder = function(id, orderNumber, customer, total, paymentLabel, refundAmt) {
    selectedOrderId = id;
    document.getElementById('orderSearchInput').value = '#' + orderNumber + ' — ' + customer + ' (EGP ' + total + ')';
    document.getElementById('orderSearchInput').style.borderColor = '#16a34a';
    document.getElementById('orderDropdown').style.display = 'none';
    var display = document.getElementById('selectedOrderDisplay');
    var refundText = refundAmt ? 'Refund: EGP ' + parseFloat(refundAmt).toLocaleString() : paymentLabel + ' · EGP ' + total;
    document.getElementById('selectedOrderText').textContent = refundText;
    display.style.display = 'block';
  };

  window.clearSelectedOrder = function() {
    selectedOrderId = null;
    document.getElementById('orderSearchInput').value = '';
    document.getElementById('orderSearchInput').style.borderColor = '#e5e5e5';
    document.getElementById('selectedOrderDisplay').style.display = 'none';
    document.getElementById('orderDropdown').style.display = 'none';
  };

  function loadRefunds() {
    var params = { page: currentPage };
    if (currentStatus) params.refund_status = currentStatus;
    if (tableSearch) params.search = tableSearch;

    document.getElementById('refundsTableBody').innerHTML = '<tr><td colspan="9" style="text-align:center;color:#aaa;padding:40px">Loading...</td></tr>';

    API.get('/admin/refunds', { params: params }).then(function(response) {
      var refunds = response.data || [];
      renderRefunds(refunds);
      renderPagination(response);
    }).catch(function() {
      document.getElementById('refundsTableBody').innerHTML = '<tr><td colspan="9" style="text-align:center;color:#ef4444;padding:30px">Failed to load refund requests.</td></tr>';
    });
  }

  function renderRefunds(orders) {
    var tbody = document.getElementById('refundsTableBody');
    if (!orders || orders.length === 0) {
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#aaa;padding:40px">No refund requests found.</td></tr>';
      return;
    }
    tbody.innerHTML = orders.map(function(o) {
      var statusBadge = getRefundBadge(o.refund_status);
      var customer = o.user ? o.user.name : (o.shipping_address ? (o.shipping_address.name || o.shipping_address.first_name || 'Guest') : 'Guest');
      var orderNum = o.order_number || o.id;
      var reason = o.refund_reason ? esc(o.refund_reason) : '—';
      var date = o.created_at ? new Date(o.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
      var refundAmt = o.payment_status === 'full_paid'
        ? parseFloat(o.total || 0)
        : parseFloat(o.deposit_amount || 0);
      var actions = '';
      if (o.refund_status === 'pending') {
        actions = '<button class="refund-action-btn btn-approve" onclick="handleRefund(' + o.id + ', \'approve\')">Approve</button>' +
                 '<button class="refund-action-btn btn-reject" onclick="handleRefund(' + o.id + ', \'reject\')">Reject</button>';
      } else if (o.refund_status === 'approved' || o.refund_status === 'rejected') {
        actions = '<a href="/admin/orders/' + o.id + '" style="color:#c9a96e;font-size:11px;font-weight:600;">View Order</a>';
      }
      return '<tr>' +
        '<td style="font-weight:600">#' + o.id + '</td>' +
        '<td><a href="/admin/orders/' + o.id + '" style="color:#2C1F14;font-weight:600;">#' + orderNum + '</a></td>' +
        '<td>' + esc(customer) + '</td>' +
        '<td style="font-weight:600">EGP ' + parseFloat(o.total || 0).toLocaleString() + '</td>' +
        '<td><span style="font-weight:700;color:#92400e;">EGP ' + refundAmt.toLocaleString() + '</span></td>' +
        '<td><span class="reason-text" title="' + reason + '">' + reason + '</span></td>' +
        '<td>' + statusBadge + '</td>' +
        '<td style="color:#888;font-size:12px">' + date + '</td>' +
        '<td>' + actions + '</td>' +
        '</tr>';
    }).join('');
  }

  function getRefundBadge(status) {
    if (status === 'pending') return '<span class="badge-status badge-pending">Pending</span>';
    if (status === 'approved') return '<span class="badge-status badge-paid">Approved</span>';
    if (status === 'rejected') return '<span class="badge-status badge-cancelled">Rejected</span>';
    return '<span class="badge-status">' + esc(status || '') + '</span>';
  }

  window.handleRefund = function(orderId, action) {
    var confirmed = confirm(action === 'approve'
      ? 'Approve this refund? Stock will be restored and payment marked as refunded.'
      : 'Reject this refund request?');
    if (!confirmed) return;

    var formData = new FormData();
    formData.append('action', action);

    fetch('/api/admin/refunds/' + orderId + '/handle', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Authorization': 'Bearer ' + (localStorage.getItem('dh_token') || '')
      },
      body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if (data.error) {
        alert(data.error);
      } else {
        alert(data.success);
        loadRefunds();
      }
    })
    .catch(function() { alert('Failed to process refund.'); });
  };

  window.createGuestRefund = function() {
    var orderId = selectedOrderId;
    var reason = document.getElementById('guestRefundReason').value.trim();
    var msgEl = document.getElementById('guestRefundMsg');

    if (!orderId) {
      msgEl.textContent = 'Please select an order from the list above.';
      msgEl.style.color = '#991b1b';
      return;
    }
    if (!reason) {
      msgEl.textContent = 'Please enter a reason for the refund.';
      msgEl.style.color = '#991b1b';
      return;
    }

    msgEl.textContent = 'Creating...';
    msgEl.style.color = '#888';

    var formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('reason', reason);

    fetch('/api/admin/refunds/create-for-guest', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Authorization': 'Bearer ' + (localStorage.getItem('dh_token') || '')
      },
      body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if (data.error) {
        msgEl.textContent = data.error;
        msgEl.style.color = '#991b1b';
      } else {
        msgEl.textContent = data.success;
        msgEl.style.color = '#16a34a';
        document.getElementById('guestRefundReason').value = '';
        clearSelectedOrder();
        loadRefunds();
      }
    })
    .catch(function() {
      msgEl.textContent = 'Failed to create refund request.';
      msgEl.style.color = '#991b1b';
    });
  };

  function renderPagination(response) {
    var container = document.getElementById('paginationContainer');
    if (!response.links) { container.innerHTML = ''; return; }
    var lastPage = response.last_page || 1;
    var current = response.current_page || 1;
    if (lastPage <= 1) { container.innerHTML = ''; return; }

    var html = '';
    for (var i = 1; i <= lastPage; i++) {
      html += '<button onclick="goToPage(' + i + ')" style="padding:6px 12px;border:1px solid ' + (i === current ? '#2C1F14' : '#e5e5e5') + ';background:' + (i === current ? '#2C1F14' : '#fff') + ';color:' + (i === current ? '#fff' : '#666') + ';border-radius:6px;font-size:12px;cursor:pointer;">' + i + '</button>';
    }
    container.innerHTML = html;
  }

  window.goToPage = function(page) {
    currentPage = page;
    loadRefunds();
  };
})();
</script>
@endpush
