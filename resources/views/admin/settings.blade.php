@extends('admin.layouts.app')

@section('title', 'Settings')
@section('page_title', 'Settings')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Settings</h1>
</div>

<!-- Settings Form -->
<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title">Store Information</div>
  </div>
  <div style="padding:24px;">
    <form id="settingsForm" onsubmit="saveSettings(event)">
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:24px;">
        <div class="form-group">
          <label class="form-label">Store Name</label>
          <input type="text" id="storeName" class="form-input" placeholder="DecoHomz">
        </div>
        <div class="form-group">
          <label class="form-label">Store Email</label>
          <input type="email" id="storeEmail" class="form-input" placeholder="contact@decohomz.com">
        </div>
        <div class="form-group">
          <label class="form-label">Store Phone</label>
          <input type="text" id="storePhone" class="form-input" placeholder="+20 xxx xxx xxxx">
        </div>
        <div class="form-group">
          <label class="form-label">Store Address</label>
          <input type="text" id="storeAddress" class="form-input" placeholder="Cairo, Egypt">
        </div>
        <div class="form-group">
          <label class="form-label">Facebook URL</label>
          <input type="url" id="facebookUrl" class="form-input" placeholder="https://facebook.com/...">
        </div>
        <div class="form-group">
          <label class="form-label">Instagram URL</label>
          <input type="url" id="instagramUrl" class="form-input" placeholder="https://instagram.com/...">
        </div>
      </div>
      <div style="margin-top:24px;padding-top:24px;border-top:1px solid #eee;display:flex;align-items:center;gap:16px;">
        <button type="submit" id="saveBtn" style="background:#c9a96e;color:#fff;border:none;padding:12px 28px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">Save Settings</button>
        <span id="saveStatus" style="font-size:13px;"></span>
      </div>
    </form>
  </div>
</div>

<!-- Additional Settings Cards -->
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:24px;margin-top:24px;">
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Store Statistics</div>
    </div>
    <div style="padding:24px;">
      <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f5f5f5;">
        <span style="color:#666;font-size:13px;">Total Products</span>
        <span style="font-weight:700;color:#1a1a1a;" id="stat-products">—</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f5f5f5;">
        <span style="color:#666;font-size:13px;">Total Orders</span>
        <span style="font-weight:700;color:#1a1a1a;" id="stat-orders">—</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f5f5f5;">
        <span style="color:#666;font-size:13px;">Total Users</span>
        <span style="font-weight:700;color:#1a1a1a;" id="stat-users">—</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:12px 0;">
        <span style="color:#666;font-size:13px;">Total Reviews</span>
        <span style="font-weight:700;color:#1a1a1a;" id="stat-reviews">—</span>
      </div>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Quick Actions</div>
    </div>
    <div style="padding:24px;display:flex;flex-direction:column;gap:12px;">
      <a href="/admin/products/create" style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#fef3c7;border-radius:8px;text-decoration:none;transition:0.2s;" onmouseover="this.style.background='#fde9c3'" onmouseout="this.style.background='#fef3c7'">
        <svg viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="1.5" style="width:20px;height:20px;"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        <span style="font-size:13px;font-weight:600;color:#92400e;">Add New Product</span>
      </a>
      <a href="/admin/coupons" style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#dbeafe;border-radius:8px;text-decoration:none;transition:0.2s;" onmouseover="this.style.background='#c7d9fc'" onmouseout="this.style.background='#dbeafe'">
        <svg viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="1.5" style="width:20px;height:20px;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        <span style="font-size:13px;font-weight:600;color:#1e40af;">Manage Coupons</span>
      </a>
      <a href="/admin/contacts" style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#d1fae5;border-radius:8px;text-decoration:none;transition:0.2s;" onmouseover="this.style.background='#bbf0d4'" onmouseout="this.style.background='#d1fae5'">
        <svg viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="1.5" style="width:20px;height:20px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <span style="font-size:13px;font-weight:600;color:#065f46;">View Contact Messages</span>
      </a>
      <a href="/" target="_blank" style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#f3f4f6;border-radius:8px;text-decoration:none;transition:0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
        <svg viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="1.5" style="width:20px;height:20px;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        <span style="font-size:13px;font-weight:600;color:#6b7280;">View Live Store</span>
      </a>
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<style>
.form-label { display:block;font-size:12px;font-weight:600;color:#666;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px; }
.form-input { width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;box-sizing:border-box;transition:border-color 0.2s; }
.form-input:focus { outline:none;border-color:#c9a96e; }
</style>
<script>
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    loadQuickStats();
  });

  async function loadSettings() {
    try {
      var res = await API.get('/admin/settings');
      var settings = res.data || res.settings || res || {};

      document.getElementById('storeName').value = settings.store_name || settings.storeName || '';
      document.getElementById('storeEmail').value = settings.store_email || settings.storeEmail || '';
      document.getElementById('storePhone').value = settings.store_phone || settings.storePhone || '';
      document.getElementById('storeAddress').value = settings.store_address || settings.storeAddress || '';
      document.getElementById('facebookUrl').value = settings.facebook_url || settings.facebookUrl || '';
      document.getElementById('instagramUrl').value = settings.instagram_url || settings.instagramUrl || '';
    } catch(e) {
      console.warn('Failed to load settings', e);
    }
  }

  async function loadQuickStats() {
    try {
      // Products
      var productsRes = await API.get('/admin/products', { params: { per_page: 1 } });
      var productsTotal = productsRes.total || 0;
      document.getElementById('stat-products').textContent = productsTotal.toLocaleString();
    } catch(e) {
      document.getElementById('stat-products').textContent = '—';
    }

    try {
      // Orders
      var ordersRes = await API.get('/admin/orders', { params: { per_page: 1 } });
      var ordersTotal = ordersRes.total || 0;
      document.getElementById('stat-orders').textContent = ordersTotal.toLocaleString();
    } catch(e) {
      document.getElementById('stat-orders').textContent = '—';
    }

    try {
      // Users
      var usersRes = await API.get('/admin/users', { params: { per_page: 1 } });
      var usersTotal = usersRes.total || 0;
      document.getElementById('stat-users').textContent = usersTotal.toLocaleString();
    } catch(e) {
      document.getElementById('stat-users').textContent = '—';
    }

    try {
      // Reviews
      var reviewsRes = await API.get('/admin/reviews', { params: { per_page: 1 } });
      var reviewsTotal = reviewsRes.total || 0;
      document.getElementById('stat-reviews').textContent = reviewsTotal.toLocaleString();
    } catch(e) {
      document.getElementById('stat-reviews').textContent = '—';
    }
  }

  window.saveSettings = function(e) {
    e.preventDefault();

    var btn = document.getElementById('saveBtn');
    var status = document.getElementById('saveStatus');
    btn.disabled = true;
    btn.textContent = 'Saving...';
    status.textContent = '';
    status.style.color = '';

    var data = {
      store_name: document.getElementById('storeName').value,
      store_email: document.getElementById('storeEmail').value,
      store_phone: document.getElementById('storePhone').value,
      store_address: document.getElementById('storeAddress').value,
      facebook_url: document.getElementById('facebookUrl').value,
      instagram_url: document.getElementById('instagramUrl').value
    };

    API.put('/admin/settings', data).then(function() {
      status.textContent = '✓ Settings saved successfully!';
      status.style.color = '#065f46';
      showToast('Settings saved successfully.', 'success');
      setTimeout(function() {
        status.textContent = '';
      }, 3000);
    }).catch(function() {
      status.textContent = 'Failed to save settings.';
      status.style.color = '#991b1b';
      showToast('Failed to save settings.', 'error');
    }).finally(function() {
      btn.disabled = false;
      btn.textContent = 'Save Settings';
    });
  };
})();
</script>
@endsection
