@extends('layouts.app')

@section('title', 'My Account — DecoHomz')

@section('extra_css')
  <link rel="stylesheet" href="/css/account.css">
@endsection

@section('content')

  <div class="breadcrumb">Home › <span>My Account</span></div>

  <div class="account-layout">
    <!-- Sidebar -->
    <div class="acc-sidebar">
      <div class="acc-profile">
        <div class="avatar">
          <svg viewBox="0 0 24 24" stroke-width="1.5">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
        </div>
        <div class="acc-name" id="acc-name">—</div>
        <div class="acc-email" id="acc-email">—</div>
        <div class="acc-since" id="acc-since">Member</div>
      </div>
      <ul class="acc-menu">
        <li><a href="#" class="active" data-tab="overview" onclick="showTab('overview', this); return false;">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <rect x="3" y="3" width="7" height="7" />
              <rect x="14" y="3" width="7" height="7" />
              <rect x="14" y="14" width="7" height="7" />
              <rect x="3" y="14" width="7" height="7" />
            </svg>
            Overview
          </a></li>
        <li><a href="#" data-tab="orders" onclick="showTab('orders', this); return false;">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
              <line x1="3" y1="6" x2="21" y2="6" />
              <path d="M16 10a4 4 0 0 1-8 0" />
            </svg>
            My Orders
          </a></li>
        <li><a href="#" data-tab="profile" onclick="showTab('profile', this); return false;">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            Edit Profile
          </a></li>
        <li><a href="#" data-tab="addresses" onclick="showTab('addresses', this); return false;">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            Addresses
          </a></li>
        <li class="logout">
          <a href="#" id="btn-logout">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
              <polyline points="16 17 21 12 16 7" />
              <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
            Sign Out
          </a>
        </li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="acc-main">

      <!-- OVERVIEW TAB -->
      <div id="tab-overview">
        <div class="section-head">
          <div class="section-title">Account Overview</div>
        </div>
        <div class="stats-row">
          <div class="stat-card">
            <div class="stat-icon" style="background:#F5F0E8">
              <svg viewBox="0 0 24 24" stroke="#8B6A48" stroke-width="1.5">
                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                <line x1="3" y1="6" x2="21" y2="6" />
              </svg>
            </div>
            <div class="stat-num" id="stat-total">—</div>
            <div class="stat-label">Total Orders</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#F0F7EC">
              <svg viewBox="0 0 24 24" stroke="#4A7C3F" stroke-width="1.5">
                <polyline points="20 6 9 17 4 12" />
              </svg>
            </div>
            <div class="stat-num" id="stat-delivered">—</div>
            <div class="stat-label">Delivered</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#FFF8E6">
              <svg viewBox="0 0 24 24" stroke="#B8860B" stroke-width="1.5">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
            </div>
            <div class="stat-num" id="stat-pending">—</div>
            <div class="stat-label">Processing</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#F5F0E8">
              <svg viewBox="0 0 24 24" stroke="#8B6A48" stroke-width="1.5">
                <path
                  d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
              </svg>
            </div>
            <div class="stat-num" id="stat-wishlist">—</div>
            <div class="stat-label">Wishlist</div>
          </div>
        </div>

        <div class="section-head">
          <div class="section-title">Recent Orders</div>
          <button class="btn-edit" onclick="showTab('orders', document.querySelector('[data-tab=orders]'));">View
            All</button>
        </div>
        <div id="recent-orders-container" class="orders-list">
          <!-- Loaded dynamically -->
        </div>
      </div>

      <!-- ORDERS TAB -->
      <div id="tab-orders" style="display:none">
        <div class="section-head">
          <div class="section-title">My Orders</div>
        </div>
        <div id="orders-list" class="orders-list"></div>
      </div>

      <!-- PROFILE TAB -->
      <div id="tab-profile" style="display:none">
        <div class="section-head">
          <div class="section-title">Edit Profile</div>
        </div>
        <div class="profile-form">
          <div class="form-section-title">Personal Information</div>
          <div class="form-grid">
            <div class="field">
              <label>Name</label>
              <input type="text" name="name" id="profile-name">
            </div>
            <div class="field">
              <label>Email</label>
              <input type="email" name="email" id="profile-email" disabled>
            </div>
            <div class="field">
              <label>Phone</label>
              <input type="tel" name="phone" id="profile-phone">
            </div>
          </div>
          <button class="save-btn" id="btn-save-profile">Save Changes</button>
        </div>
        <div class="profile-form">
          <div class="form-section-title">Change Password</div>
          <div class="form-grid">
            <div class="field">
              <label>Current Password</label>
              <input type="password" name="current_password" id="profile-current-password" placeholder="••••••••">
            </div>
            <div class="field">
              <label>New Password</label>
              <input type="password" name="new_password" id="profile-new-password" placeholder="Min. 8 characters">
            </div>
          </div>
          <button class="save-btn" id="btn-save-password">Update Password</button>
        </div>
      </div>

      <!-- ADDRESSES TAB -->
      <div id="tab-addresses" style="display:none">
        <div class="section-head">
          <div class="section-title">Saved Addresses</div>
          <button class="btn-edit" id="btn-add-address">+ Add New</button>
        </div>
        <div id="addresses-container"></div>

        <!-- Add/Edit Address Modal -->
        <div id="address-modal"
          style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:1000; align-items:center; justify-content:center;">
          <div style="background:#fff; border-radius:10px; padding:28px; width:100%; max-width:460px; margin:20px;">
            <div style="font-size:15px; font-weight:700; color:#2C1F14; margin-bottom:20px" id="address-modal-title">Add
              Address</div>
            <input type="hidden" id="edit-address-id">
            <div class="form-grid">
              <div class="field full">
                <label>Label (e.g. Home, Office)</label>
                <input type="text" id="addr-label" placeholder="Home">
              </div>
              <div class="field">
                <label>First Name</label>
                <input type="text" id="addr-first-name" required>
              </div>
              <div class="field">
                <label>Last Name</label>
                <input type="text" id="addr-last-name" required>
              </div>
              <div class="field full">
                <label>Street Address</label>
                <input type="text" id="addr-line-1" placeholder="14 El Nasr Street, Apt 5" required>
              </div>
              <div class="field full">
                <label>Address Line 2 (optional)</label>
                <input type="text" id="addr-line-2" placeholder="Floor, building info...">
              </div>
              <div class="field">
                <label>City</label>
                <input type="text" id="addr-city" required>
              </div>
              <div class="field">
                <label>Governorate</label>
                <input type="text" id="addr-state" required>
              </div>
              <div class="field">
                <label>Postal Code</label>
                <input type="text" id="addr-postal" placeholder="11511">
              </div>
              <div class="field">
                <label>Phone</label>
                <input type="tel" id="addr-phone" required>
              </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px">
              <button class="save-btn" id="btn-save-address" style="flex:1">Save Address</button>
              <button class="btn-edit" id="btn-cancel-address" style="flex:1">Cancel</button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

@endsection

@section('extra_js')
  <script>
    (function () {
      Cart.updateBadge();

      // ── Auth guard ─────────────────────────────────────────────
      if (!Auth.token()) {
        location.href = '/auth';
        return;
      }

      // ── Load user & data ────────────────────────────────────────
      (async function init() {
        try {
          const res = await API.get('/auth/user');
          const user = res.data || res;
          renderUserInfo(user);
        } catch (e) {
          location.href = '/auth';
          return;
        }
        loadOverview();
      })();

      function renderUserInfo(user) {
        if (!user) return;
        const nameEl = document.getElementById('acc-name');
        const emailEl = document.getElementById('acc-email');
        const sinceEl = document.getElementById('acc-since');

        const fullName = user.name || [user.first_name, user.last_name].filter(Boolean).join(' ');
        if (nameEl) nameEl.textContent = fullName || 'User';
        if (emailEl) emailEl.textContent = user.email || '—';
        if (sinceEl && user.created_at) {
          sinceEl.textContent = 'Since ' + new Date(user.created_at).getFullYear();
        }

        // Pre-fill profile tab
        const nameParts = (fullName || '').split(' ');
        const firstName = document.getElementById('profile-first-name');
        const lastName = document.getElementById('profile-last-name');
        const emailInput = document.getElementById('profile-email');
        const phoneInput = document.getElementById('profile-phone');

        if (firstName) firstName.value = user.first_name || nameParts[0] || '';
        if (lastName) lastName.value = nameParts.slice(1).join(' ') || user.last_name || '';
        if (emailInput) emailInput.value = user.email || '';
        if (phoneInput) phoneInput.value = user.phone || '';
      }

      async function loadOverview() {
        try {
          const res = await API.get('/orders');
          const orders = res.data || res.orders || [];
          const total = orders.length;
          const delivered = orders.filter(o => ['delivered', 'completed'].includes((o.status || '').toLowerCase())).length;
          const processing = orders.filter(o => ['pending', 'processing'].includes((o.status || '').toLowerCase())).length;

          document.getElementById('stat-total').textContent = total;
          document.getElementById('stat-delivered').textContent = delivered;
          document.getElementById('stat-pending').textContent = processing;

          // Try wishlist count
          try {
            const wRes = await API.get('/wishlist');
            const wishlist = wRes.products || wRes.data || [];
            document.getElementById('stat-wishlist').textContent = wishlist.length;
          } catch (e) {
            document.getElementById('stat-wishlist').textContent = '0';
          }

          renderRecentOrders(orders);
        } catch (e) {
          document.getElementById('stat-total').textContent = '0';
          document.getElementById('stat-delivered').textContent = '0';
          document.getElementById('stat-pending').textContent = '0';
        }
      }

      function renderRecentOrders(orders) {
        const container = document.getElementById('recent-orders-container');
        const fullContainer = document.getElementById('orders-list');
        if (!container && !fullContainer) return;

        const html = orders.slice(0, 10).map(o => buildOrderCard(o)).join('');

        if (container) container.innerHTML = html || '<p style="color:#aaa;font-size:13px">No orders yet.</p>';
        if (fullContainer) fullContainer.innerHTML = html || '<p style="color:#aaa;font-size:13px">No orders yet.</p>';
      }

      function buildOrderCard(o) {
        const statusClass = (o.status || '').toLowerCase();
        const dateStr = o.created_at ? new Date(o.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';
        const items = o.items || [];
        const orderNum = o.order_number || o.id || '—';

        return `
          <div class="order-card">
            <div>
              <div class="order-top">
                <span class="order-id">#${orderNum}</span>
                <span class="order-status status-${statusClass}">${o.status || '—'}</span>
                <span class="order-date">${dateStr}</span>
              </div>
              <div class="order-items-preview">
                ${items.slice(0, 3).map(item => `
                  <div class="order-thumb" title="${item.name || ''}">
                    ${item.image
            ? `<img src="${item.image}" alt="${item.name || ''}" style="width:65%;height:65%;object-fit:contain" onerror="this.style.display='none'">`
            : `<svg viewBox="0 0 40 40" fill="none">
                          <rect x="3" y="15" width="34" height="18" rx="4" fill="#C4A882"/>
                          <rect x="6" y="10" width="8" height="12" rx="2" fill="#A07858"/>
                          <rect x="26" y="10" width="8" height="12" rx="2" fill="#A07858"/>
                        </svg>`
          }
                  </div>
                `).join('')}
                ${items.length > 3 ? `<div class="order-thumb-more">+${items.length - 3}</div>` : ''}
              </div>
            </div>
            <div>
              <div class="order-total">EGP ${(parseFloat(o.total) || 0).toLocaleString()}</div>
              <a class="order-action" href="/account/orders/${o.id}">Details →</a>
            </div>
          </div>
        `;
      }

      // ── Tab switching ───────────────────────────────────────────
      window.showTab = function (tabId, el) {
        document.querySelectorAll('.acc-main > div').forEach(tab => tab.style.display = 'none');
        const target = document.getElementById('tab-' + tabId);
        if (target) target.style.display = 'block';

        document.querySelectorAll('.acc-menu a').forEach(a => a.classList.remove('active'));
        if (el) el.classList.add('active');

        // Lazy-load tab content
        if (tabId === 'orders') loadOrdersTab();
        if (tabId === 'addresses') loadAddressesTab();
      };

      async function loadOrdersTab() {
        try {
          const res = await API.get('/orders');
          const orders = res.data || res.orders || [];
          const container = document.getElementById('orders-list');
          if (container) {
            container.innerHTML = orders.length
              ? orders.map(o => buildOrderCard(o)).join('')
              : '<p style="color:#aaa;font-size:13px">No orders yet.</p>';
          }
        } catch (e) { }
      }

      async function loadAddressesTab() {
        try {
          const res = await API.get('/addresses');
          const addresses = res.data?.addresses || res.addresses || [];
          renderAddresses(addresses);
        } catch (e) {
          renderAddresses([]);
        }
      }

      function renderAddresses(addresses) {
        const container = document.getElementById('addresses-container');
        if (!container) return;

        if (addresses.length === 0) {
          container.innerHTML = '<p style="color:#aaa;font-size:13px">No saved addresses.</p>';
          return;
        }

        container.innerHTML = addresses.map(addr => `
          <div class="address-card" style="background:#fff;border:1px solid #EDE8E2;border-radius:10px;padding:20px;margin-bottom:12px">
            <div style="font-size:13px;font-weight:600;color:#2C1F14;margin-bottom:4px">
              ${addr.label || 'Address'} ${addr.is_default ? '— Default' : ''}
            </div>
            <div style="font-size:12px;color:#888;line-height:1.7">
              ${addr.first_name || ''} ${addr.last_name || ''}<br>
              ${addr.address_line_1 || ''}${addr.address_line_2 ? ', ' + addr.address_line_2 : ''}<br>
              ${addr.city || ''}${addr.state ? ', ' + addr.state : ''} ${addr.postal_code || ''}<br>
              ${addr.phone || ''}
            </div>
            <div style="margin-top:12px;display:flex;gap:8px">
              <button class="btn-edit" onclick="editAddress('${addr.id}')">Edit</button>
              <button class="btn-edit" style="color:#c0392b" onclick="deleteAddress('${addr.id}')">Remove</button>
            </div>
          </div>
        `).join('');
      }

      // ── Profile save ───────────────────────────────────────────
      document.getElementById('btn-save-profile').addEventListener('click', async function () {
        const firstName = document.getElementById('profile-first-name').value.trim();
        const lastName = document.getElementById('profile-last-name').value.trim();
        const phone = document.getElementById('profile-phone').value.trim();

        if (!firstName || !lastName) {
          showToast('First and last name are required.');
          return;
        }

        this.disabled = true;
        this.textContent = 'Saving...';

        try {
          await API.put('/auth/profile', {
            first_name: firstName,
            last_name: lastName,
            phone: phone
          });
          showToast('Profile updated!');
          document.getElementById('acc-name').textContent = firstName + ' ' + lastName;
        } catch (e) {
          showToast(e.data?.message || 'Update failed.');
        } finally {
          this.disabled = false;
          this.textContent = 'Save Changes';
        }
      });

      document.getElementById('btn-save-password').addEventListener('click', async function () {
        const current = document.getElementById('profile-current-password').value;
        const newPass = document.getElementById('profile-new-password').value;

        if (!current || !newPass) {
          showToast('Please fill in both password fields.');
          return;
        }
        if (newPass.length < 8) {
          showToast('New password must be at least 8 characters.');
          return;
        }

        this.disabled = true;
        this.textContent = 'Updating...';

        try {
          await API.put('/auth/password', {
            current_password: current,
            password: newPass,
            password_confirmation: newPass
          });
          showToast('Password updated!');
          document.getElementById('profile-current-password').value = '';
          document.getElementById('profile-new-password').value = '';
        } catch (e) {
          showToast(e.data?.message || 'Password update failed.');
        } finally {
          this.disabled = false;
          this.textContent = 'Update Password';
        }
      });

      // ── Address management ─────────────────────────────────────
      document.getElementById('btn-add-address').addEventListener('click', function () {
        document.getElementById('address-modal-title').textContent = 'Add Address';
        document.getElementById('edit-address-id').value = '';
        clearAddressForm();
        document.getElementById('address-modal').style.display = 'flex';
      });

      document.getElementById('btn-cancel-address').addEventListener('click', function () {
        document.getElementById('address-modal').style.display = 'none';
      });

      document.getElementById('btn-save-address').addEventListener('click', async function () {
        const id = document.getElementById('edit-address-id').value;
        const payload = {
          label: document.getElementById('addr-label').value.trim(),
          first_name: document.getElementById('addr-first-name').value.trim(),
          last_name: document.getElementById('addr-last-name').value.trim(),
          address_line_1: document.getElementById('addr-line-1').value.trim(),
          address_line_2: document.getElementById('addr-line-2').value.trim(),
          city: document.getElementById('addr-city').value.trim(),
          state: document.getElementById('addr-state').value.trim(),
          postal_code: document.getElementById('addr-postal').value.trim(),
          phone: document.getElementById('addr-phone').value.trim(),
          country: 'Egypt'
        };

        if (!payload.first_name || !payload.address_line_1 || !payload.city || !payload.phone) {
          showToast('Please fill in all required fields.');
          return;
        }

        this.disabled = true;
        this.textContent = 'Saving...';

        try {
          if (id) {
            await API.put('/addresses/' + id, payload);
            showToast('Address updated!');
          } else {
            await API.post('/addresses', payload);
            showToast('Address added!');
          }
          document.getElementById('address-modal').style.display = 'none';
          loadAddressesTab();
        } catch (e) {
          showToast(e.data?.message || 'Failed to save address.');
        } finally {
          this.disabled = false;
          this.textContent = 'Save Address';
        }
      });

      window.editAddress = async function (id) {
        try {
          const res = await API.get('/addresses/' + id);
          const addr = res.data?.address || res.address;
          if (!addr) return;

          document.getElementById('address-modal-title').textContent = 'Edit Address';
          document.getElementById('edit-address-id').value = id;
          document.getElementById('addr-label').value = addr.label || '';
          document.getElementById('addr-first-name').value = addr.first_name || '';
          document.getElementById('addr-last-name').value = addr.last_name || '';
          document.getElementById('addr-line-1').value = addr.address_line_1 || '';
          document.getElementById('addr-line-2').value = addr.address_line_2 || '';
          document.getElementById('addr-city').value = addr.city || '';
          document.getElementById('addr-state').value = addr.state || '';
          document.getElementById('addr-postal').value = addr.postal_code || '';
          document.getElementById('addr-phone').value = addr.phone || '';
          document.getElementById('address-modal').style.display = 'flex';
        } catch (e) {
          showToast('Could not load address.');
        }
      };

      window.deleteAddress = async function (id) {
        if (!confirm('Remove this address?')) return;
        try {
          await API.del('/addresses/' + id);
          showToast('Address removed.');
          loadAddressesTab();
        } catch (e) {
          showToast(e.data?.message || 'Could not remove address.');
        }
      };

      function clearAddressForm() {
        ['addr-label', 'addr-first-name', 'addr-last-name', 'addr-line-1', 'addr-line-2',
          'addr-city', 'addr-state', 'addr-postal', 'addr-phone'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
          });
      }

      // ── Logout ─────────────────────────────────────────────────
      document.getElementById('btn-logout').addEventListener('click', function (e) {
        e.preventDefault();
        Auth.logout();
        location.href = '/';
      });
    })();
  </script>
@endsection