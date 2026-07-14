@extends('admin.layouts.app')

@section('title', 'Vendor Details')
@section('page_title', 'Vendor Details')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <div>
    <a href="/admin/vendors" style="color:#888;text-decoration:none;font-size:13px;display:inline-block;margin-bottom:8px;">← Back to Vendors</a>
    <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;" id="vendor-name">Loading...</h1>
  </div>
  <div id="vendor-actions" style="display:flex;gap:12px;"></div>
</div>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-card-label">Status</div>
    <div class="stat-card-num" id="v-status" style="font-size:20px;margin-top:8px;">—</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">Available Balance</div>
    <div class="stat-card-num" id="v-balance" style="font-size:20px;margin-top:8px;">—</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">Violation Points</div>
    <div class="stat-card-num" id="v-violations" style="font-size:20px;margin-top:8px;">—</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Business Information</div>
    </div>
    <div style="padding:24px;font-size:14px;line-height:1.6;" id="vendor-info">
      Loading details...
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Documents</div>
    </div>
    <div style="padding:24px;" id="vendor-docs">
      Loading documents...
    </div>
  </div>
</div>

<!-- Send Notification Modal -->
<div id="notification-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000;">
  <div style="background:#fff; width:100%; max-width:400px; border-radius:8px; box-shadow:0 10px 15px rgba(0,0,0,0.1); padding:24px;">
    <h3 style="margin:0 0 16px 0; font-size:18px;">Send Notification</h3>
    <div style="margin-bottom:12px;">
      <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Title</label>
      <input type="text" id="notif-title" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
    </div>
    <div style="margin-bottom:12px;">
      <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Message</label>
      <textarea id="notif-message" rows="3" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;"></textarea>
    </div>
    <div style="margin-bottom:16px;">
      <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Type</label>
      <select id="notif-type" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
        <option value="info">Info</option>
        <option value="success">Success</option>
        <option value="warning">Warning</option>
        <option value="danger">Danger</option>
      </select>
    </div>
    <div style="display:flex; justify-content:flex-end; gap:8px;">
      <button onclick="closeNotificationModal()" style="padding:8px 16px; border:none; background:#eee; border-radius:4px; cursor:pointer;">Cancel</button>
      <button onclick="submitNotification()" style="padding:8px 16px; border:none; background:#3b82f6; color:#fff; border-radius:4px; cursor:pointer; font-weight:600;">Send</button>
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  var vendorId = {{ $id }};
  var currentVendor = null;

  document.addEventListener('DOMContentLoaded', function() {
    loadVendor();
  });

  async function loadVendor() {
    try {
      var res = await API.get('/admin/vendors/' + vendorId);
      currentVendor = (res && res.data ? res.data.vendor : null) || res.vendor || res.data || res;
      var balances = (res && res.data ? res.data.balances : null) || res.balances || null;

      document.getElementById('vendor-name').textContent = currentVendor.company_name;
      document.getElementById('v-status').innerHTML = getStatusBadge(currentVendor.status);
      if (balances) {
        document.getElementById('v-balance').textContent = 'EGP ' + (balances.available || 0).toLocaleString();
      }
      document.getElementById('v-violations').textContent = currentVendor.severity_points || 0;
      
      renderInfo(currentVendor);
      renderDocs(currentVendor.documents || []);
      renderActions(currentVendor);

    } catch(e) {
      showToast('Failed to load vendor details', 'error');
    }
  }

  function getStatusBadge(status) {
    if (status === 'active') return '<span class="badge-status badge-active">Active</span>';
    if (status === 'pending') return '<span class="badge-status badge-pending">Pending</span>';
    if (status === 'suspended') return '<span class="badge-status badge-pending" style="background:#fef08a;color:#854d0e;">Suspended</span>';
    if (status === 'banned' || status === 'rejected') return '<span class="badge-status badge-rejected">' + (status.charAt(0).toUpperCase() + status.slice(1)) + '</span>';
    return status;
  }

  function renderInfo(v) {
    var owner = v.user ? v.user.name + ' (' + v.user.email + ')' : 'N/A';
    document.getElementById('vendor-info').innerHTML = `
      <div style="margin-bottom:12px;"><strong>Owner:</strong> ${esc(owner)}</div>
      <div style="margin-bottom:12px;"><strong>Contact Person:</strong> ${esc(v.contact_name || 'N/A')}</div>
      <div style="margin-bottom:12px;"><strong>Contact Email:</strong> ${esc(v.email || 'N/A')}</div>
      <div style="margin-bottom:12px;"><strong>Contact Phone:</strong> ${esc(v.phone || 'N/A')}</div>
      <div style="margin-bottom:12px;"><strong>Address:</strong> ${esc(v.address || 'N/A')}</div>
      <div style="margin-bottom:12px;"><strong>Workshop Address:</strong> ${esc(v.workshop_address || 'N/A')}</div>
      <div style="margin-bottom:12px;"><strong>Bank Account:</strong> ${esc(v.bank_account_number || 'N/A')}</div>
      <div style="margin-bottom:12px;"><strong>E-Wallet:</strong> ${esc(v.e_wallet_number || 'N/A')}</div>
    `;
  }

  function renderDocs(docs) {
    if (docs.length === 0) {
      document.getElementById('vendor-docs').innerHTML = '<div style="color:#aaa;">No documents uploaded.</div>';
      return;
    }
    document.getElementById('vendor-docs').innerHTML = docs.map(function(doc) {
      var typeLabel = doc.type ? doc.type.replace(/_/g, ' ') : 'document';
      return `
        <div style="padding:12px;border:1px solid #eee;border-radius:8px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;">
          <div>
            <div style="font-weight:600;font-size:13px;">${esc(typeLabel).toUpperCase()}</div>
            <div style="font-size:12px;color:#666;margin-top:4px;">Status: ${getStatusBadge(doc.status)}</div>
          </div>
          <div>
            <a href="${doc.file_url}" target="_blank" style="color:#c9a96e;font-size:12px;text-decoration:none;margin-right:12px;">View File</a>
            ${doc.status === 'pending' ? `
              <button onclick="verifyDoc(${doc.id})" style="background:#d1fae5;color:#065f46;border:none;padding:6px 12px;border-radius:4px;font-size:11px;cursor:pointer;margin-right:8px;">Verify</button>
              <button onclick="rejectDoc(${doc.id})" style="background:#fee2e2;color:#991b1b;border:none;padding:6px 12px;border-radius:4px;font-size:11px;cursor:pointer;">Reject</button>
            ` : ''}
          </div>
        </div>
      `;
    }).join('');
  }

  function renderActions(v) {
    var html = '';
    if (v.status === 'pending') {
      html += `<button onclick="updateVendorStatus('approve')" style="background:#22c55e;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;">Approve Application</button>`;
      html += `<button onclick="updateVendorStatus('reject')" style="background:#ef4444;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;">Reject Application</button>`;
    } else if (v.status === 'active') {
      html += `<button onclick="issueViolation()" style="background:#f59e0b;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;">Issue Violation</button>`;
      html += `<button onclick="updateVendorStatus('suspend')" style="background:#854d0e;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;">Suspend</button>`;
      html += `<button onclick="updateVendorStatus('ban')" style="background:#ef4444;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;">Ban</button>`;
    } else if (v.status === 'suspended' || v.status === 'banned') {
      html += `<button onclick="updateVendorStatus('reinstate')" style="background:#22c55e;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;">Reinstate</button>`;
    }
    html += `<button onclick="sendManualNotification()" style="background:#3b82f6;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;">Send Notification</button>`;
    document.getElementById('vendor-actions').innerHTML = html;
  }

  window.verifyDoc = async function(id) {
    if(!confirm('Verify this document?')) return;
    try {
      await API.patch('/admin/vendor-documents/' + id + '/verify');
      showToast('Document verified', 'success');
      loadVendor();
    } catch(e) {
      showToast('Failed to verify document', 'error');
    }
  }

  window.rejectDoc = async function(id) {
    var reason = prompt('Enter rejection reason:');
    if(!reason) return;
    try {
      await API.patch('/admin/vendor-documents/' + id + '/reject', { rejection_reason: reason });
      showToast('Document rejected', 'success');
      loadVendor();
    } catch(e) {
      showToast('Failed to reject document', 'error');
    }
  }

  window.updateVendorStatus = async function(action) {
    var url = '/admin/vendors/' + vendorId + '/' + action;
    var data = {};
    if (action === 'suspend') {
      var days = prompt('Enter suspension duration in days:');
      if(!days) return;
      data.days = parseInt(days);
    } else {
      if(!confirm('Are you sure you want to ' + action + ' this vendor?')) return;
    }
    
    try {
      await API.patch(url, data);
      showToast('Vendor ' + action + 'ed successfully', 'success');
      loadVendor();
    } catch(e) {
      showToast('Failed to update vendor status', 'error');
    }
  }
  
  window.issueViolation = async function() {
    var desc = prompt('Enter violation description:');
    if(!desc) return;
    var points = prompt('Enter severity points (1-10):');
    if(!points) return;
    try {
      await API.post('/admin/vendors/' + vendorId + '/violations', {
        violation_type: 'policy_breach',
        description: desc,
        severity_points: parseInt(points),
        action_taken: 'warning'
      });
      showToast('Violation issued', 'success');
      loadVendor();
    } catch(e) {
      showToast('Failed to issue violation', 'error');
    }
  }

  window.sendManualNotification = function() {
    document.getElementById('notif-title').value = '';
    document.getElementById('notif-message').value = '';
    document.getElementById('notif-type').value = 'info';
    document.getElementById('notification-modal').style.display = 'flex';
  }

  window.closeNotificationModal = function() {
    document.getElementById('notification-modal').style.display = 'none';
  }

  window.submitNotification = async function() {
    var title = document.getElementById('notif-title').value.trim();
    var message = document.getElementById('notif-message').value.trim();
    var type = document.getElementById('notif-type').value;

    if (!title || !message) {
      showToast('Title and message are required', 'error');
      return;
    }

    try {
      await API.post('/admin/vendors/' + vendorId + '/notifications', {
        title: title,
        message: message,
        type: type
      });
      showToast('Notification sent successfully', 'success');
      closeNotificationModal();
    } catch (e) {
      showToast('Failed to send notification', 'error');
    }
  }

  function esc(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
})();
</script>
@endsection
