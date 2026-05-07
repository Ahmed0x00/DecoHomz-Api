@extends('admin.layouts.app')

@section('title', 'Coupons')
@section('page_title', 'Coupons')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Coupons</h1>
  <button onclick="openModal()" style="background:#c9a96e;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">+ Add Coupon</button>
</div>

<!-- Stats Cards -->
<div class="stat-grid" id="stats-grid">
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#dbeafe">
      <svg viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="1.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
    </div>
    <div class="stat-card-num" id="stat-total">—</div>
    <div class="stat-card-label">Total Coupons</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#d1fae5">
      <svg viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    </div>
    <div class="stat-card-num" id="stat-active">—</div>
    <div class="stat-card-label">Active</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#fee2e2">
      <svg viewBox="0 0 24 24" fill="none" stroke="#991b1b" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    </div>
    <div class="stat-card-num" id="stat-expired">—</div>
    <div class="stat-card-label">Expired</div>
  </div>
</div>

<!-- Coupons Table -->
<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Code</th>
        <th>Type</th>
        <th>Value</th>
        <th>Min Order</th>
        <th>Uses</th>
        <th>Expires</th>
        <th>Status</th>
        <th style="width:160px;">Actions</th>
      </tr>
    </thead>
    <tbody id="coupons-tbody">
      <tr class="loading-row"><td colspan="8"></td></tr>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<div id="pagination" style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:24px;">
</div>

@endsection

@section('extra_js')
<!-- Modal -->
<style>
.admin-modal-overlay { display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center; }
.admin-modal-overlay.show { display:flex; }
.admin-modal-box { background:#fff;border-radius:12px;width:500px;max-width:90%;max-height:90vh;overflow-y:auto; }
.admin-modal-header { padding:20px 24px;border-bottom:1px solid #eee;display:flex;align-items:center;justify-content:space-between; }
.admin-modal-title { font-size:16px;font-weight:700;color:#1a1a1a; }
.admin-modal-close { background:none;border:none;font-size:20px;color:#aaa;cursor:pointer;padding:4px;line-height:1; }
.admin-modal-close:hover { color:#333; }
.admin-modal-body { padding:24px; }
.form-group { margin-bottom:16px; }
.form-label { display:block;font-size:12px;font-weight:600;color:#666;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px; }
.form-input { width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;box-sizing:border-box; }
.form-input:focus { outline:none;border-color:#c9a96e; }
.form-row { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
.form-select { width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;background:#fff; }
.form-toggle { display:flex;align-items:center;gap:10px; }
.toggle-switch { position:relative;width:44px;height:24px; }
.toggle-switch input { opacity:0;width:0;height:0; }
.toggle-slider { position:absolute;top:0;left:0;right:0;bottom:0;background:#ccc;border-radius:24px;cursor:pointer;transition:0.2s; }
.toggle-slider:before { position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:0.2s; }
input:checked + .toggle-slider { background:#c9a96e; }
input:checked + .toggle-slider:before { transform:translateX(20px); }
.admin-modal-footer { padding:16px 24px;border-top:1px solid #eee;display:flex;gap:12px;justify-content:flex-end; }
.form-btn-primary { background:#c9a96e;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer; }
.form-btn-secondary { background:#f3f4f6;color:#666;border:1px solid #e5e5e5;padding:10px 20px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer; }
</style>

<div class="admin-modal-overlay" id="couponModal">
  <div class="admin-modal-box">
    <div class="admin-modal-header">
      <div class="admin-modal-title" id="modalTitle">Add Coupon</div>
      <button class="admin-modal-close" onclick="closeModal()">&times;</button>
    </div>
    <div class="admin-modal-body">
      <input type="hidden" id="couponId">
      <div class="form-group">
        <label class="form-label">Code *</label>
        <input type="text" id="couponCode" class="form-input" placeholder="e.g. SUMMER20" style="text-transform:uppercase;">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Type</label>
          <select id="couponType" class="form-select">
            <option value="percentage">Percentage (%)</option>
            <option value="fixed">Fixed Amount</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Value *</label>
          <input type="number" id="couponValue" class="form-input" placeholder="0" step="0.01" min="0">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Min Order</label>
          <input type="number" id="couponMinOrder" class="form-input" placeholder="0" step="0.01" min="0">
        </div>
        <div class="form-group">
          <label class="form-label">Max Discount</label>
          <input type="number" id="couponMaxDiscount" class="form-input" placeholder="0" step="0.01" min="0">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Uses Limit</label>
        <input type="number" id="couponUsesLimit" class="form-input" placeholder="Unlimited" min="0">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Starts At</label>
          <input type="date" id="couponStarts" class="form-input">
        </div>
        <div class="form-group">
          <label class="form-label">Expires At</label>
          <input type="date" id="couponExpires" class="form-input">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Active</label>
        <div class="form-toggle">
          <label class="toggle-switch">
            <input type="checkbox" id="couponActive" checked>
            <span class="toggle-slider"></span>
          </label>
          <span style="font-size:13px;color:#666;" id="activeLabel">Active</span>
        </div>
      </div>
    </div>
    <div class="admin-modal-footer">
      <button class="form-btn-secondary" onclick="closeModal()">Cancel</button>
      <button class="form-btn-primary" onclick="saveCoupon()">Save Coupon</button>
    </div>
  </div>
</div>

<script>
(function() {
  var currentPage = 1;
  var allCoupons = [];
  var editingId = null;

  document.addEventListener('DOMContentLoaded', function() {
    loadCoupons();
  });

  async function loadCoupons(page) {
    if (page) currentPage = page;

    renderTableLoading();
    try {
      var res = await API.get('/admin/coupons', { params: { per_page: 200 } });
      allCoupons = res.data || res.coupons || res || [];
      if (!Array.isArray(allCoupons) && allCoupons.data) allCoupons = allCoupons.data;

      renderStats();
      renderTable(allCoupons);
    } catch(e) {
      document.getElementById('coupons-tbody').innerHTML = '<tr><td colspan="8" style="text-align:center;color:#ef4444;padding:30px">Failed to load coupons.</td></tr>';
    }
  }

  function renderStats() {
    var total = allCoupons.length;
    var active = 0, expired = 0;
    var now = new Date();

    allCoupons.forEach(function(c) {
      var isActive = c.is_active == 1 || c.is_active === true || c.active === true;
      var expDate = c.expires_at ? new Date(c.expires_at) : null;
      if (expDate && expDate < now) isActive = false;

      if (isActive) active++;
      else expired++;
    });

    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-active').textContent = active;
    document.getElementById('stat-expired').textContent = expired;
  }

  function renderTableLoading() {
    document.getElementById('coupons-tbody').innerHTML = '<tr class="loading-row"><td colspan="8"></td></tr>';
  }

  function renderTable(coupons) {
    var tbody = document.getElementById('coupons-tbody');
    if (!coupons || coupons.length === 0) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#aaa;padding:40px">No coupons found.</td></tr>';
      return;
    }
    var now = new Date();
    tbody.innerHTML = coupons.map(function(c) {
      var typeLabel = c.type === 'percentage' ? '%' : 'Fixed';
      var value = c.type === 'percentage' ? c.value + '%' : 'EGP ' + parseFloat(c.value || 0).toLocaleString();
      var minOrder = c.min_order ? 'EGP ' + parseFloat(c.min_order).toLocaleString() : '—';
      var uses = c.uses_count !== undefined ? c.uses_count + (c.uses_limit ? '/' + c.uses_limit : '') : (c.uses || 0);
      var expires = c.expires_at
        ? new Date(c.expires_at).toLocaleDateString('en-EG', { year: 'numeric', month: 'short', day: 'numeric' })
        : 'Never';

      var expDate = c.expires_at ? new Date(c.expires_at) : null;
      var isExpired = expDate && expDate < now;
      var isActive = (c.is_active == 1 || c.is_active === true || c.active === true) && !isExpired;

      var statusClass = isActive ? 'badge-active' : 'badge-inactive';
      var statusLabel = isExpired ? 'Expired' : (isActive ? 'Active' : 'Inactive');

      return '<tr>' +
        '<td style="font-weight:700;letter-spacing:1px;">' + (c.code || '—') + '</td>' +
        '<td>' + typeLabel + '</td>' +
        '<td style="font-weight:600;">' + value + '</td>' +
        '<td style="color:#666;">' + minOrder + '</td>' +
        '<td>' + uses + '</td>' +
        '<td>' + expires + '</td>' +
        '<td><span class="admin-badge ' + statusClass + '">' + statusLabel + '</span></td>' +
        '<td>' +
          '<button onclick="editCoupon(' + c.id + ')" style="padding:5px 10px;background:#fef3c7;color:#92400e;border:1px solid #fef3c7;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;margin-right:6px;">Edit</button>' +
          '<button onclick="deleteCoupon(' + c.id + ')" style="padding:5px 10px;background:#fee2e2;color:#991b1b;border:1px solid #fee2e2;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">Delete</button>' +
        '</td>' +
        '</tr>';
    }).join('');
  }

  window.openModal = function(coupon) {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Add Coupon';
    document.getElementById('couponId').value = '';
    document.getElementById('couponCode').value = '';
    document.getElementById('couponType').value = 'percentage';
    document.getElementById('couponValue').value = '';
    document.getElementById('couponMinOrder').value = '';
    document.getElementById('couponMaxDiscount').value = '';
    document.getElementById('couponUsesLimit').value = '';
    document.getElementById('couponStarts').value = '';
    document.getElementById('couponExpires').value = '';
    document.getElementById('couponActive').checked = true;
    document.getElementById('activeLabel').textContent = 'Active';
    document.getElementById('couponModal').classList.add('show');
  };

  window.closeModal = function() {
    document.getElementById('couponModal').classList.remove('show');
    editingId = null;
  };

  window.editCoupon = function(id) {
    var coupon = allCoupons.find(function(c) { return c.id === id; });
    if (!coupon) return;

    editingId = id;
    document.getElementById('modalTitle').textContent = 'Edit Coupon';
    document.getElementById('couponId').value = coupon.id;
    document.getElementById('couponCode').value = coupon.code || '';
    document.getElementById('couponType').value = coupon.type || 'percentage';
    document.getElementById('couponValue').value = coupon.value || '';
    document.getElementById('couponMinOrder').value = coupon.min_order || '';
    document.getElementById('couponMaxDiscount').value = coupon.max_discount || '';
    document.getElementById('couponUsesLimit').value = coupon.uses_limit || '';
    document.getElementById('couponStarts').value = coupon.starts_at ? coupon.starts_at.split('T')[0] : '';
    document.getElementById('couponExpires').value = coupon.expires_at ? coupon.expires_at.split('T')[0] : '';
    document.getElementById('couponActive').checked = coupon.is_active == 1 || coupon.is_active === true;
    document.getElementById('activeLabel').textContent = document.getElementById('couponActive').checked ? 'Active' : 'Inactive';
    document.getElementById('couponModal').classList.add('show');
  };

  window.saveCoupon = function() {
    var code = document.getElementById('couponCode').value.trim();
    var type = document.getElementById('couponType').value;
    var value = parseFloat(document.getElementById('couponValue').value);
    var minOrder = parseFloat(document.getElementById('couponMinOrder').value) || 0;
    var maxDiscount = parseFloat(document.getElementById('couponMaxDiscount').value) || 0;
    var usesLimit = parseInt(document.getElementById('couponUsesLimit').value) || 0;
    var startsAt = document.getElementById('couponStarts').value;
    var expiresAt = document.getElementById('couponExpires').value;
    var isActive = document.getElementById('couponActive').checked;

    if (!code) { showToast('Please enter a coupon code.', 'error'); return; }
    if (!value || value <= 0) { showToast('Please enter a valid value.', 'error'); return; }

    var payload = { code: code, type: type, value: value, min_order: minOrder, max_discount: maxDiscount, uses_limit: usesLimit, starts_at: startsAt, expires_at: expiresAt, is_active: isActive ? 1 : 0 };

    var promise;
    if (editingId) {
      promise = API.put('/admin/coupons/' + editingId, payload);
    } else {
      promise = API.post('/admin/coupons', payload);
    }

    promise.then(function() {
      showToast(editingId ? 'Coupon updated.' : 'Coupon created.', 'success');
      closeModal();
      loadCoupons();
    }).catch(function() {
      showToast('Failed to save coupon.', 'error');
    });
  };

  window.deleteCoupon = function(id) {
    if (!confirm('Delete this coupon?')) return;
    API.delete('/admin/coupons/' + id).then(function() {
      showToast('Coupon deleted.', 'success');
      loadCoupons();
    }).catch(function() {
      showToast('Failed to delete coupon.', 'error');
    });
  };

  // Toggle active label
  document.getElementById('couponActive').addEventListener('change', function() {
    document.getElementById('activeLabel').textContent = this.checked ? 'Active' : 'Inactive';
  });
})();
</script>
@endsection
