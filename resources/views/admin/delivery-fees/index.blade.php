@extends('admin.layouts.app')

@section('title', 'Delivery Fees')
@section('page_title', 'Delivery Fees')

@section('content')
<style>
  .df-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; }
  .df-header-left h1 { font-size:20px; font-weight:700; color:#1e293b; margin:0 0 4px; }
  .df-header-left p { font-size:13px; color:#94a3b8; margin:0; }
  .df-stats { display:flex; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
  .df-stat { flex:1; min-width:140px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px 20px; }
  .df-stat-num { font-size:26px; font-weight:800; color:#1e293b; line-height:1; }
  .df-stat-num.green { color:#059669; }
  .df-stat-label { font-size:12px; color:#94a3b8; margin-top:6px; font-weight:500; }
  .df-controls { display:flex; gap:12px; margin-bottom:20px; align-items:center; flex-wrap:wrap; }
  .df-controls select, .df-controls input {
    padding:9px 14px; border:1.5px solid #e5e7eb; border-radius:8px;
    font-size:13px; outline:none; background:#fff; color:#374151;
  }
  .df-controls select:focus, .df-controls input:focus { border-color:#c9a96e; }
  .df-controls input[type="text"] { width:200px; }
  .df-bulk { display:flex; gap:10px; align-items:center; padding:14px 18px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; margin-bottom:20px; flex-wrap:wrap; }
  .df-bulk-label { font-size:12px; font-weight:700; color:#166534; display:flex; align-items:center; gap:6px; white-space:nowrap; }
  .df-bulk input { padding:7px 12px; border:1.5px solid #86efac; border-radius:8px; font-size:13px; width:120px; background:#fff; }
  .df-bulk input:focus { border-color:#16a34a; outline:none; }
  .df-btn { padding:8px 16px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; border:none; transition:all 0.15s; }
  .df-btn-green { background:#16a34a; color:#fff; }
  .df-btn-green:hover { background:#15803d; }
  .df-btn-outline { background:#fff; color:#475569; border:1px solid #e2e8f0; }
  .df-btn-outline:hover { background:#f8fafc; }
  .df-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
  .df-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; overflow:hidden; transition:box-shadow 0.2s; }
  .df-card:hover { box-shadow:0 4px 12px rgba(0,0,0,0.08); }
  .df-card.inactive { opacity:0.65; }
  .df-card-top { padding:16px 18px 14px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; }
  .df-card-name { font-size:14px; font-weight:700; color:#1e293b; }
  .df-card-toggle { position:relative; width:40px; height:22px; }
  .df-card-toggle input { opacity:0; width:0; height:0; }
  .df-toggle-slider { position:absolute; cursor:pointer; inset:0; background:#d1d5db; border-radius:22px; transition:0.2s; }
  .df-toggle-slider:before { content:''; position:absolute; height:16px; width:16px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:0.2s; }
  .df-card-toggle input:checked + .df-toggle-slider { background:#16a34a; }
  .df-card-toggle input:checked + .df-toggle-slider:before { transform:translateX(18px); }
  .df-card-body { padding:16px 18px; }
  .df-fee-row { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
  .df-fee-label { font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:0.5px; width:60px; flex-shrink:0; }
  .df-fee-input { flex:1; padding:8px 12px; border:1.5px solid #e5e7eb; border-radius:8px; font-size:15px; font-weight:700; color:#1e293b; background:#f8fafc; width:100%; box-sizing:border-box; }
  .df-fee-input:focus { border-color:#c9a96e; background:#fff; outline:none; }
  .df-fee-suffix { font-size:12px; color:#64748b; font-weight:500; flex-shrink:0; }
  .df-card-actions { display:flex; gap:8px; padding:12px 18px; background:#fafafa; border-top:1px solid #f1f5f9; }
  .df-btn-save { flex:1; background:#1e293b; color:#fff; border:none; padding:9px; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; transition:background 0.15s; }
  .df-btn-save:hover { background:#334155; }
  .df-btn-delete { background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; padding:9px 12px; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; }
  .df-btn-delete:hover { background:#fecaca; }
  .df-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.3px; }
  .df-badge-active { background:#dcfce7; color:#15803d; }
  .df-badge-inactive { background:#f1f5f9; color:#64748b; }
  .df-badge-free { background:#fef3c7; color:#a16207; margin-left:6px; }
  .df-check { position:absolute; top:14px; right:14px; }
  .df-card-inner { position:relative; }
</style>

<div class="df-header">
  <div class="df-header-left">
    <h1>Delivery Fees</h1>
    <p>Set delivery cost per governorate for orders</p>
  </div>
  <div style="display:flex;gap:10px;align-items:center;">
    <span id="selected-count" style="font-size:12px;color:#64748b;display:none;">0 selected</span>
    <button class="df-btn df-btn-outline" onclick="openAddModal()" style="display:flex;align-items:center;gap:6px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
      Add Governorate
    </button>
  </div>
</div>

<!-- Stats -->
<div class="df-stats">
  <div class="df-stat">
    <div class="df-stat-num" id="total-count">—</div>
    <div class="df-stat-label">Total Governorates</div>
  </div>
  <div class="df-stat">
    <div class="df-stat-num green" id="active-count">—</div>
    <div class="df-stat-label">Active Fees</div>
  </div>
  <div class="df-stat">
    <div class="df-stat-num" id="avg-fee">—</div>
    <div class="df-stat-label">Average Fee (EGP)</div>
  </div>
  <div class="df-stat">
    <div class="df-stat-num" id="free-count">—</div>
    <div class="df-stat-label">With Free Delivery</div>
  </div>
</div>

<!-- Bulk Update -->
<div class="df-bulk">
  <span class="df-bulk-label">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
    Bulk Update
  </span>
  <input type="number" id="bulk-fee" placeholder="Fee (EGP)" min="0" step="1">
  <input type="number" id="bulk-threshold" placeholder="Free above (EGP)" min="0" step="1">
  <button class="df-btn df-btn-green" onclick="applyBulk()">Apply to Selected</button>
  <button class="df-btn df-btn-outline" onclick="resetBulk()">Reset</button>
  <span id="bulk-status" style="font-size:12px;color:#16a34a;margin-left:auto;display:none;font-weight:600;">✓ Updated!</span>
</div>

<!-- Filters -->
<div class="df-controls">
  <select id="statusFilter" onchange="loadFees()">
    <option value="">All Status</option>
    <option value="1">Active Only</option>
    <option value="0">Inactive Only</option>
  </select>
  <input type="text" id="searchFilter" placeholder="Search governorate..." oninput="debounceLoad()">
  <span id="result-count" style="font-size:12px;color:#94a3b8;margin-left:auto;"></span>
</div>

<!-- Cards Grid -->
<div class="df-grid" id="fees-grid">
  <div style="text-align:center;padding:60px;color:#aaa;grid-column:1/-1;">Loading...</div>
</div>

<!-- Add/Edit Modal -->
<div id="fee-modal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;" onclick="if(event.target===this)closeModal()">
  <div style="background:#fff;border-radius:16px;padding:28px;width:420px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
      <h3 style="font-size:16px;font-weight:700;color:#1e293b;margin:0;" id="modal-title">Add Governorate</h3>
      <button onclick="closeModal()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;padding:0;line-height:1;">×</button>
    </div>
    <div style="display:flex;flex-direction:column;gap:14px;">
      <div>
        <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Governorate Name *</label>
        <input type="text" id="modal-name" placeholder="e.g. Cairo" style="width:100%;padding:10px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box;">
      </div>
      <div>
        <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Delivery Fee (EGP) *</label>
        <input type="number" id="modal-fee" placeholder="0" min="0" step="1" style="width:100%;padding:10px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box;">
      </div>
      <div>
        <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Free Delivery Above (EGP)</label>
        <input type="number" id="modal-threshold" placeholder="0 = no free delivery" min="0" step="1" style="width:100%;padding:10px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box;">
      </div>
      <div style="display:flex;align-items:center;gap:10px;">
        <input type="checkbox" id="modal-active" checked style="width:16px;height:16px;accent-color:#16a34a;">
        <label for="modal-active" style="font-size:13px;color:#374151;">Active immediately</label>
      </div>
    </div>
    <div style="display:flex;gap:10px;margin-top:20px;">
      <button onclick="closeModal()" style="flex:1;padding:11px;background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Cancel</button>
      <button onclick="saveModal()" id="modal-save-btn" style="flex:1;padding:11px;background:#1e293b;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Save</button>
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  var allFees = [];
  var selectedIds = new Set();
  var editingId = null;
  var debounceTimer;

  document.addEventListener('DOMContentLoaded', function() { window.loadFees(); });

  window.loadFees = async function() {
    var status = document.getElementById('statusFilter').value;
    var search = document.getElementById('searchFilter').value;
    var params = {};
    if (status !== '') params.is_active = status;
    if (search) params.search = search;
    var qs = new URLSearchParams(params).toString();
    try {
      var res = await API.get('/admin/governorate-fees' + (qs ? '?' + qs : ''));
      allFees = res.data || res;
      renderFees();
      updateSummary();
    } catch(e) {
      document.getElementById('fees-grid').innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px;color:#ef4444;font-size:14px;">Failed to load delivery fees.</div>';
    }
  };

  window.debounceLoad = function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(loadFees, 350);
  };

  function updateSummary() {
    var total = allFees.length;
    var active = allFees.filter(function(f) { return f.is_active; }).length;
    var avg = total > 0 ? Math.round(allFees.reduce(function(s, f) { return s + parseFloat(f.delivery_fee || 0); }, 0) / total) : 0;
    var free = allFees.filter(function(f) { return f.min_free_delivery_order > 0; }).length;
    document.getElementById('total-count').textContent = total;
    document.getElementById('active-count').textContent = active;
    document.getElementById('avg-fee').textContent = avg;
    document.getElementById('free-count').textContent = free;
    document.getElementById('result-count').textContent = total + ' governorate' + (total !== 1 ? 's' : '');
  }

  function renderFees() {
    var grid = document.getElementById('fees-grid');
    if (!allFees.length) {
      grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px;color:#aaa;font-size:14px;">No governorates found. Click "Add Governorate" to create one.</div>';
      return;
    }
    grid.innerHTML = allFees.map(function(f) {
      var badge = f.is_active
        ? '<span class="df-badge df-badge-active">● Active</span>'
        : '<span class="df-badge df-badge-inactive">● Inactive</span>';
      var freeBadge = f.min_free_delivery_order > 0
        ? '<span class="df-badge df-badge-free">Free @ ' + f.min_free_delivery_order + ' EGP+</span>'
        : '';
      var cardClass = f.is_active ? '' : 'inactive';
      var checked = f.is_active ? 'checked' : '';
      return '<div class="df-card-inner">' +
        (selectedIds.has(f.id) ? '<div class="df-check"><svg width="18" height="18" viewBox="0 0 24 24" fill="#16a34a"><circle cx="12" cy="12" r="12" fill="#dcfce7"/><path d="M9 12l2 2 4-4" stroke="#16a34a" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg></div>' : '') +
        '<div class="df-card ' + cardClass + '" onclick="if(event.target.tagName!==\'INPUT\')toggleSelect(' + f.id + ')">' +
          '<div class="df-card-top">' +
            '<div>' +
              '<div class="df-card-name">' + escHtml(f.governorate_name) + '</div>' +
              badge + freeBadge +
            '</div>' +
            '<label class="df-card-toggle" title="' + (f.is_active ? 'Deactivate' : 'Activate') + '">' +
              '<input type="checkbox" ' + checked + ' onchange="toggleFee(' + f.id + ', this.checked)" onclick="event.stopPropagation()">' +
              '<span class="df-toggle-slider"></span>' +
            '</label>' +
          '</div>' +
          '<div class="df-card-body">' +
            '<div class="df-fee-row">' +
              '<span class="df-fee-label">Fee</span>' +
              '<input type="number" class="df-fee-input" id="fee-' + f.id + '" value="' + f.delivery_fee + '" min="0" step="1" onclick="event.stopPropagation()">' +
              '<span class="df-fee-suffix">EGP</span>' +
            '</div>' +
          '</div>' +
          '<div class="df-card-actions" onclick="event.stopPropagation()">' +
            '<button class="df-btn-save" onclick="updateFee(' + f.id + ')">Save</button>' +
            '<button class="df-btn-delete" onclick="deleteFee(' + f.id + ')">Delete</button>' +
          '</div>' +
        '</div></div>';
    }).join('');
  }

  window.toggleSelect = function(id) {
    if (selectedIds.has(id)) {
      selectedIds.delete(id);
    } else {
      selectedIds.add(id);
    }
    var countEl = document.getElementById('selected-count');
    if (selectedIds.size > 0) {
      countEl.style.display = 'inline';
      countEl.textContent = selectedIds.size + ' selected';
    } else {
      countEl.style.display = 'none';
    }
    renderFees();
  };

  window.toggleFee = function(id, isActive) {
    API.patch('/admin/governorate-fees/' + id + '/toggle').then(function() {
      loadFees();
      showToast(isActive ? 'Governorate activated.' : 'Governorate deactivated.', 'success');
    }).catch(function() {
      showToast('Failed to update.', 'error');
      loadFees();
    });
  };

  window.updateFee = function(id) {
    var feeInput = document.getElementById('fee-' + id);
    var fee = parseFloat(feeInput.value);
    if (isNaN(fee) || fee < 0) { showToast('Enter a valid fee.', 'error'); return; }
    API.put('/admin/governorate-fees/' + id, { delivery_fee: fee }).then(function() {
      showToast('Fee updated.', 'success');
      loadFees();
    }).catch(function() {
      showToast('Failed to update fee.', 'error');
    });
  };

  window.deleteFee = function(id) {
    if (!confirm('Delete this governorate delivery fee?')) return;
    API.del('/admin/governorate-fees/' + id).then(function() {
      showToast('Deleted.', 'success');
      selectedIds.delete(id);
      loadFees();
    }).catch(function() {
      showToast('Failed to delete.', 'error');
    });
  };

  window.applyBulk = function() {
    if (selectedIds.size === 0) { showToast('Select governorates first.', 'error'); return; }
    var fee = document.getElementById('bulk-fee').value;
    var threshold = document.getElementById('bulk-threshold').value;
    var fees = [];
    selectedIds.forEach(function(id) {
      var f = {};
      f.id = id;
      if (fee !== '') f.delivery_fee = parseFloat(fee);
      if (threshold !== '') f.min_free_delivery_order = parseFloat(threshold);
      fees.push(f);
    });
    API.post('/admin/governorate-fees/bulk', { fees: fees }).then(function() {
      document.getElementById('bulk-status').style.display = 'inline';
      setTimeout(function() { document.getElementById('bulk-status').style.display = 'none'; }, 2000);
      document.getElementById('bulk-fee').value = '';
      document.getElementById('bulk-threshold').value = '';
      selectedIds.clear();
      document.getElementById('selected-count').style.display = 'none';
      loadFees();
      showToast('Bulk updated!', 'success');
    }).catch(function() {
      showToast('Bulk update failed.', 'error');
    });
  };

  window.resetBulk = function() {
    document.getElementById('bulk-fee').value = '';
    document.getElementById('bulk-threshold').value = '';
  };

  window.openAddModal = function() {
    editingId = null;
    document.getElementById('modal-title').textContent = 'Add Governorate';
    document.getElementById('modal-name').value = '';
    document.getElementById('modal-fee').value = '';
    document.getElementById('modal-threshold').value = '';
    document.getElementById('modal-active').checked = true;
    document.getElementById('fee-modal').style.display = 'flex';
    document.getElementById('modal-name').focus();
  };

  window.closeModal = function() {
    document.getElementById('fee-modal').style.display = 'none';
    editingId = null;
  };

  window.saveModal = function() {
    var name = document.getElementById('modal-name').value.trim();
    var fee = document.getElementById('modal-fee').value;
    var threshold = document.getElementById('modal-threshold').value;
    var isActive = document.getElementById('modal-active').checked;
    if (!name) { showToast('Governorate name is required.', 'error'); return; }
    if (fee === '' || isNaN(parseFloat(fee)) || parseFloat(fee) < 0) { showToast('Enter a valid delivery fee.', 'error'); return; }
    var payload = {
      governorate_name: name,
      delivery_fee: parseFloat(fee),
      min_free_delivery_order: threshold === '' ? 0 : parseFloat(threshold),
      is_active: isActive
    };
    API.post('/admin/governorate-fees', payload).then(function() {
      closeModal();
      loadFees();
      showToast('Governorate added!', 'success');
    }).catch(function() {
      showToast('Failed to add governorate.', 'error');
    });
  };

  function escHtml(s) {
    if (s == null) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
</script>
@endsection
