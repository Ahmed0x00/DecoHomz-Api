@extends('admin.layouts.app')

@section('title', 'Vendors')
@section('page_title', 'Vendors')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Vendors</h1>
  <button onclick="openNotifyAllModal()" style="background:#3b82f6;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;">Notify All Vendors</button>
</div>

<!-- Vendors Table -->
<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Business Name</th>
        <th>Owner</th>
        <th>Status</th>
        <th>Joined</th>
        <th style="width:100px;">Actions</th>
      </tr>
    </thead>
    <tbody id="vendors-tbody">
      <tr class="loading-row"><td colspan="6"></td></tr>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<div id="pagination" style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:24px;">
</div>

<!-- Notify All Modal -->
<div id="notify-all-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000;">
  <div style="background:#fff; width:100%; max-width:400px; border-radius:8px; box-shadow:0 10px 15px rgba(0,0,0,0.1); padding:24px;">
    <h3 style="margin:0 0 16px 0; font-size:18px;">Notify All Vendors</h3>
    <div style="margin-bottom:12px;">
      <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Title</label>
      <input type="text" id="bulk-title" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
    </div>
    <div style="margin-bottom:12px;">
      <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Message</label>
      <textarea id="bulk-message" rows="3" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;"></textarea>
    </div>
    <div style="margin-bottom:16px;">
      <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Type</label>
      <select id="bulk-type" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
        <option value="info">Info</option>
        <option value="success">Success</option>
        <option value="warning">Warning</option>
        <option value="danger">Danger</option>
      </select>
    </div>
    <div style="display:flex; justify-content:flex-end; gap:8px;">
      <button onclick="closeNotifyAllModal()" style="padding:8px 16px; border:none; background:#eee; border-radius:4px; cursor:pointer;">Cancel</button>
      <button onclick="submitBulkNotification()" style="padding:8px 16px; border:none; background:#3b82f6; color:#fff; border-radius:4px; cursor:pointer; font-weight:600;">Send to All</button>
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  var currentPage = 1;

  document.addEventListener('DOMContentLoaded', function() {
    loadVendors();
  });

  async function loadVendors(page) {
    if (page) currentPage = page;
    renderTableLoading();
    try {
      var res = await API.get('/admin/vendors', { params: { page: currentPage } });
      var vendors = res.data && res.data.data ? res.data.data : (res.data || []);
      renderTable(vendors);
      renderPagination(res.data || res);
    } catch(e) {
      document.getElementById('vendors-tbody').innerHTML = '<tr><td colspan="6" style="text-align:center;color:#ef4444;padding:30px">Failed to load vendors.</td></tr>';
    }
  }

  function renderTableLoading() {
    document.getElementById('vendors-tbody').innerHTML = '<tr class="loading-row"><td colspan="6"></td></tr>';
  }

  function renderTable(vendors) {
    var tbody = document.getElementById('vendors-tbody');
    if (!vendors || vendors.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#aaa;padding:40px">No vendors found.</td></tr>';
      return;
    }
    tbody.innerHTML = vendors.map(function(v) {
      var statusBadge = '';
      if (v.status === 'active') {
        statusBadge = '<span class="badge-status badge-active">Active</span>';
      } else if (v.status === 'pending') {
        statusBadge = '<span class="badge-status badge-pending">Pending</span>';
      } else if (v.status === 'suspended') {
        statusBadge = '<span class="badge-status badge-pending" style="background:#fef08a;color:#854d0e;">Suspended</span>';
      } else if (v.status === 'banned' || v.status === 'rejected') {
        statusBadge = '<span class="badge-status badge-rejected">' + (v.status.charAt(0).toUpperCase() + v.status.slice(1)) + '</span>';
      }
      
      var joined = v.created_at ? new Date(v.created_at).toLocaleDateString() : '—';
      var owner = v.user ? v.user.name : '—';
      
      return '<tr onclick="location.href=\'/admin/vendors/' + v.id + '\'" style="cursor:pointer;">' +
        '<td>#' + v.id + '</td>' +
        '<td style="font-weight:600;">' + esc(v.company_name || '—') + '</td>' +
        '<td style="color:#666;">' + esc(owner) + '</td>' +
        '<td>' + statusBadge + '</td>' +
        '<td>' + joined + '</td>' +
        '<td onclick="event.stopPropagation()">' + 
          '<a href="/admin/vendors/' + v.id + '" style="color:#c9a96e;font-size:13px;font-weight:600;text-decoration:none;">Manage</a>' +
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
      html += '<button onclick="loadVendors(' + (current - 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">← Prev</button>';
    }
    for (var i = 1; i <= last; i++) {
      if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
        html += '<button onclick="loadVendors(' + i + ')" style="padding:6px 12px;border:1px solid ' + (i === current ? '#c9a96e' : '#e5e5e5') + ';background:' + (i === current ? '#c9a96e' : '#fff') + ';color:' + (i === current ? '#fff' : '#333') + ';border-radius:6px;cursor:pointer;font-size:13px;">' + i + '</button>';
      } else if (i === current - 2 || i === current + 2) {
        html += '<span style="color:#aaa;padding:0 4px;">...</span>';
      }
    }
    if (current < last) {
      html += '<button onclick="loadVendors(' + (current + 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">Next →</button>';
    }
    container.innerHTML = html;
  }
  
  window.loadVendors = loadVendors;

  window.openNotifyAllModal = function() {
    document.getElementById('bulk-title').value = '';
    document.getElementById('bulk-message').value = '';
    document.getElementById('bulk-type').value = 'info';
    document.getElementById('notify-all-modal').style.display = 'flex';
  }

  window.closeNotifyAllModal = function() {
    document.getElementById('notify-all-modal').style.display = 'none';
  }

  window.submitBulkNotification = async function() {
    var title = document.getElementById('bulk-title').value.trim();
    var message = document.getElementById('bulk-message').value.trim();
    var type = document.getElementById('bulk-type').value;

    if (!title || !message) {
      showToast('Title and message are required', 'error');
      return;
    }

    try {
      await API.post('/admin/vendors/notify-all', {
        title: title,
        message: message,
        type: type
      });
      showToast('Global notification sent successfully', 'success');
      closeNotifyAllModal();
    } catch (e) {
      showToast('Failed to send global notification', 'error');
    }
  }

})();
</script>
@endsection
