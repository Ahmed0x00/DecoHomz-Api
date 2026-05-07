@extends('admin.layouts.app')

@section('title', 'Contact Messages')
@section('page_title', 'Contact Messages')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Contact Messages</h1>
</div>

<!-- Stats Cards -->
<div class="stat-grid" id="stats-grid">
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#dbeafe">
      <svg viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
    </div>
    <div class="stat-card-num" id="stat-total">—</div>
    <div class="stat-card-label">Total Messages</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#fee2e2">
      <svg viewBox="0 0 24 24" fill="none" stroke="#991b1b" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <div class="stat-card-num" id="stat-new">—</div>
    <div class="stat-card-label">New</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#fef3c7">
      <svg viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
    </div>
    <div class="stat-card-num" id="stat-read">—</div>
    <div class="stat-card-label">Read</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#d1fae5">
      <svg viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="1.5"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <div class="stat-card-num" id="stat-replied">—</div>
    <div class="stat-card-label">Replied</div>
  </div>
</div>

<!-- Contacts Table -->
<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Subject</th>
        <th>Message</th>
        <th>Status</th>
        <th>Date</th>
        <th style="width:180px;">Actions</th>
      </tr>
    </thead>
    <tbody id="contacts-tbody">
      <tr class="loading-row"><td colspan="7"></td></tr>
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
  var expandedId = null;
  var allContacts = [];

  document.addEventListener('DOMContentLoaded', function() {
    loadContacts();
  });

  async function loadContacts(page) {
    if (page) currentPage = page;

    renderTableLoading();
    try {
      var res = await API.get('/admin/contacts', { params: { per_page: 200 } });
      allContacts = res.data || res.contacts || res || [];
      if (!Array.isArray(allContacts) && allContacts.data) allContacts = allContacts.data;

      renderStats();
      renderTable(allContacts);
    } catch(e) {
      document.getElementById('contacts-tbody').innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:30px">Failed to load contacts.</td></tr>';
    }
  }

  function renderStats() {
    var total = allContacts.length;
    var newCount = 0, readCount = 0, repliedCount = 0;

    allContacts.forEach(function(c) {
      var status = (c.status || '').toLowerCase();
      if (status === 'new') newCount++;
      else if (status === 'read') readCount++;
      else if (status === 'replied') repliedCount++;
    });

    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-new').textContent = newCount;
    document.getElementById('stat-read').textContent = readCount;
    document.getElementById('stat-replied').textContent = repliedCount;
  }

  function renderTableLoading() {
    document.getElementById('contacts-tbody').innerHTML = '<tr class="loading-row"><td colspan="7"></td></tr>';
  }

  function renderTable(contacts) {
    var tbody = document.getElementById('contacts-tbody');
    if (!contacts || contacts.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#aaa;padding:40px">No messages found.</td></tr>';
      return;
    }
    tbody.innerHTML = contacts.map(function(c) {
      var name = c.name || '—';
      var email = c.email || '—';
      var subject = c.subject || '—';
      var message = c.message || c.content || '';
      var truncated = message.length > 50 ? message.substring(0, 50) + '...' : message;

      var status = (c.status || '').toLowerCase();
      var statusClass, statusLabel;
      if (status === 'replied') {
        statusClass = 'badge-approved';
        statusLabel = 'Replied';
      } else if (status === 'read') {
        statusClass = 'badge-inactive';
        statusLabel = 'Read';
      } else {
        statusClass = 'badge-pending';
        statusLabel = 'New';
      }

      var date = c.created_at
        ? new Date(c.created_at).toLocaleDateString('en-EG', { year: 'numeric', month: 'short', day: 'numeric' })
        : '—';

      var isExpanded = expandedId === c.id;
      var messageDisplay = isExpanded
        ? '<div style="margin-top:8px;padding:12px;background:#f9f9f9;border-radius:6px;font-size:12px;line-height:1.6;color:#333;white-space:pre-wrap;">' + message + '</div>'
        : '<span style="color:#666;font-size:12px;" title="' + message.replace(/"/g, '&quot;') + '">' + truncated + '</span>';

      var actions = '';
      if (status !== 'replied') {
        actions += '<button onclick="markReplied(' + c.id + ')" style="padding:5px 10px;background:#d1fae5;color:#065f46;border:1px solid #d1fae5;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;margin-right:6px;">Mark Replied</button>';
      }
      actions += '<button onclick="deleteContact(' + c.id + ')" style="padding:5px 10px;background:#fee2e2;color:#991b1b;border:1px solid #fee2e2;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">Delete</button>';

      return '<tr class="contact-row" data-id="' + c.id + '">' +
        '<td style="font-weight:600;">' + name + '</td>' +
        '<td style="color:#666;font-size:12px;">' + email + '</td>' +
        '<td>' + subject + '</td>' +
        '<td style="max-width:200px;">' + messageDisplay + '</td>' +
        '<td><span class="admin-badge ' + statusClass + '">' + statusLabel + '</span></td>' +
        '<td>' + date + '</td>' +
        '<td>' +
          '<button onclick="toggleExpand(' + c.id + ')" style="padding:5px 10px;background:#fef3c7;color:#92400e;border:1px solid #fef3c7;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;margin-right:6px;">' + (isExpanded ? 'Hide' : 'View') + '</button>' +
          actions +
        '</td>' +
        '</tr>';
    }).join('');
  }

  window.toggleExpand = function(id) {
    expandedId = expandedId === id ? null : id;
    renderTable(allContacts);
  };

  window.markReplied = function(id) {
    API.patch('/admin/contacts/' + id + '/replied').then(function() {
      showToast('Message marked as replied.', 'success');
      loadContacts();
    }).catch(function() {
      showToast('Failed to update message.', 'error');
    });
  };

  window.deleteContact = function(id) {
    if (!confirm('Delete this message?')) return;
    API.delete('/admin/contacts/' + id).then(function() {
      showToast('Message deleted.', 'success');
      loadContacts();
    }).catch(function() {
      showToast('Failed to delete message.', 'error');
    });
  };
})();
</script>
@endsection
