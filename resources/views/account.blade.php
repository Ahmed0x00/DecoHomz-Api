@extends('layouts.app')

@section('title', 'My Account — DecoHomz')

@section('extra_css')
  <link rel="stylesheet" href="{{ asset_v('/css/account.css') }}">
@endsection

@section('content')

  <div class="breadcrumb">{{ __('Home') }} › <span>{{ __('My Account') }}</span></div>

  <div class="account-page">
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
        <div class="acc-since" id="acc-since">{{ __('Member') }}</div>
      </div>
      <ul class="acc-menu">
        <li><a href="#" class="active" data-tab="overview" onclick="showTab('overview', this); return false;">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <rect x="3" y="3" width="7" height="7" />
              <rect x="14" y="3" width="7" height="7" />
              <rect x="14" y="14" width="7" height="7" />
              <rect x="3" y="14" width="7" height="7" />
            </svg>
            {{ __('Overview') }}
          </a></li>
        <li><a href="#" data-tab="orders" onclick="showTab('orders', this); return false;">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
              <line x1="3" y1="6" x2="21" y2="6" />
              <path d="M16 10a4 4 0 0 1-8 0" />
            </svg>
            {{ __('My Orders') }}
          </a></li>
        <li><a href="#" data-tab="profile" onclick="showTab('profile', this); return false;">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            {{ __('Edit Profile') }}
          </a></li>
        <li><a href="#" data-tab="addresses" onclick="showTab('addresses', this); return false;">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            {{ __('Addresses') }}
          </a></li>
        <li><a href="#" data-tab="preorders" onclick="showTab('preorders', this); return false;">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
              <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
              <path d="M12 11h4M12 16h4M8 11h.01M8 16h.01"/>
            </svg>
            {{ __('My Pre-Orders') }}
          </a></li>
        <li class="logout">
          <a href="#" id="btn-logout">
            <svg viewBox="0 0 24 24" stroke-width="1.5">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
              <polyline points="16 17 21 12 16 7" />
              <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
            {{ __('Sign Out') }}
          </a>
        </li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="acc-main">

      <!-- OVERVIEW TAB -->
      <div id="tab-overview">
        <div id="vendor-status-alert" style="display:none; margin-bottom:24px; padding:16px; border-radius:8px; border:1px solid #e0e0e0; background:#fafafa;">
          <div style="display:flex; justify-content:space-between; align-items:center; gap: 16px;">
            <div>
              <div style="font-weight:700; font-size:15px; color:#1a1a1a;">Vendor Application Status</div>
              <div id="vendor-status-text" style="font-size:13px; color:#666; margin-top:4px; line-height:1.4;"></div>
            </div>
            <div id="vendor-status-badge"></div>
          </div>
          <div id="vendor-portal-link" style="margin-top:12px; display:none;">
            <a href="/vendor/portal" style="display:inline-block; background:#8B6A48; color:#fff; text-decoration:none; padding:8px 16px; border-radius:4px; font-size:12px; font-weight:600;">Go to Vendor Portal</a>
          </div>
        </div>

        <div class="section-head">
          <div class="section-title">{{ __('Account Overview') }}</div>
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
            <div class="stat-label">{{ __('Total Orders') }}</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#F0F7EC">
              <svg viewBox="0 0 24 24" stroke="#4A7C3F" stroke-width="1.5">
                <polyline points="20 6 9 17 4 12" />
              </svg>
            </div>
            <div class="stat-num" id="stat-delivered">—</div>
            <div class="stat-label">{{ __('Delivered') }}</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#FFF8E6">
              <svg viewBox="0 0 24 24" stroke="#B8860B" stroke-width="1.5">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
            </div>
            <div class="stat-num" id="stat-pending">—</div>
            <div class="stat-label">{{ __('Processing') }}</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#F5F0E8">
              <svg viewBox="0 0 24 24" stroke="#8B6A48" stroke-width="1.5">
                <path
                  d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
              </svg>
            </div>
            <div class="stat-num" id="stat-wishlist">—</div>
            <div class="stat-label">{{ __('Wishlist') }}</div>
          </div>
        </div>

        <div class="section-head">
          <div class="section-title">{{ __('Recent Orders') }}</div>
          <button class="btn-edit" onclick="showTab('orders', document.querySelector('[data-tab=orders]'));">{{ __('View All') }}</button>
        </div>
        <div id="recent-orders-container" class="orders-list">
          <!-- Loaded dynamically -->
        </div>
      </div>

      <!-- ORDERS TAB -->
      <div id="tab-orders" style="display:none">
        <div class="section-head">
          <div class="section-title">{{ __('My Orders') }}</div>
        </div>
        <div id="orders-list" class="orders-list"></div>
      </div>

      <!-- PROFILE TAB -->
      <div id="tab-profile" style="display:none">
        <div class="section-head">
          <div class="section-title">{{ __('Edit Profile') }}</div>
        </div>
        <div class="profile-form">
          <div class="form-section-title">{{ __('Personal Information') }}</div>
          <div class="form-grid">
            <div class="field">
              <label>{{ __('Name') }}</label>
              <input type="text" name="name" id="profile-name">
            </div>
            <div class="field">
              <label>{{ __('Email Address') }}</label>
              <input type="email" name="email" id="profile-email" disabled>
            </div>
            <div class="field">
              <label>{{ __('Phone') }}</label>
              <input type="tel" name="phone" id="profile-phone">
            </div>
          </div>
          <button class="save-btn" id="btn-save-profile">{{ __('Save Changes') }}</button>
        </div>
        <div class="profile-form">
          <div class="form-section-title">{{ __('Change Password') }}</div>
          <div class="form-grid">
            <div class="field">
              <label>{{ __('Current Password') }}</label>
              <input type="password" name="current_password" id="profile-current-password" placeholder="••••••••">
            </div>
            <div class="field">
              <label>{{ __('New Password') }}</label>
              <input type="password" name="new_password" id="profile-new-password" placeholder="{{ __('Min. 8 characters') }}">
            </div>
          </div>
          <button class="save-btn" id="btn-save-password">{{ __('Update Password') }}</button>
        </div>
      </div>

      <!-- ADDRESSES TAB -->
      <div id="tab-addresses" style="display:none">
        <div class="section-head">
          <div class="section-title">{{ __('Saved Addresses') }}</div>
          <button class="btn-edit" id="btn-add-address">+ {{ __('Add New') }}</button>
        </div>
        <div id="addresses-container"></div>

        <!-- Add/Edit Address Modal -->
        <div id="address-modal" class="address-modal-overlay">
          <div class="address-modal">
            <div class="address-modal-title" id="address-modal-title">{{ __('Add Address') }}</div>
            <input type="hidden" id="edit-address-id">
            <div class="form-grid">
              <div class="field full">
                <label>{{ __('Label (e.g. Home, Office)') }}</label>
                <input type="text" id="addr-label" placeholder="{{ __('Home') }}">
              </div>
              <div class="field">
                <label>{{ __('First Name') }}</label>
                <input type="text" id="addr-first-name" required>
              </div>
              <div class="field">
                <label>{{ __('Last Name') }}</label>
                <input type="text" id="addr-last-name" required>
              </div>
              <div class="field full">
                <label>{{ __('Street Address') }}</label>
                <input type="text" id="addr-line-1" placeholder="{{ __('14 El Nasr Street, Apt 5') }}" required>
              </div>
              <div class="field full">
                <label>{{ __('Address Line 2 (optional)') }}</label>
                <input type="text" id="addr-line-2" placeholder="{{ __('Floor, building info...') }}">
              </div>
              <div class="field">
                <label>{{ __('City') }}</label>
                <input type="text" id="addr-city" required>
              </div>
              <div class="field">
                <label>{{ __('Governorate') }}</label>
                <select id="addr-state" required>
                  <option value="">{{ __('Select Governorate') }}</option>
                </select>
              </div>
              <div class="field">
                <label>{{ __('Postal Code') }}</label>
                <input type="text" id="addr-postal" placeholder="11511">
              </div>
              <div class="field">
                <label>{{ __('Phone') }}</label>
                <input type="tel" id="addr-phone" required>
              </div>
            </div>
            <div class="address-modal-actions">
              <button class="save-btn" id="btn-save-address">{{ __('Save Address') }}</button>
              <button class="btn-edit" id="btn-cancel-address">{{ __('Cancel') }}</button>
            </div>
          </div>
        </div>
      </div>

      <!-- PRE-ORDERS TAB -->
      <div id="tab-preorders" style="display:none">
        <div class="section-head">
          <div class="section-title">{{ __('My Pre-Orders') }}</div>
          <a href="/pre-order" class="btn-edit" style="text-decoration:none;">+ {{ __('New Request') }}</a>
        </div>
        <div id="preorders-list" class="orders-list"></div>
      </div>

    </div>
  </div>
  </div>

@endsection

@section('extra_js')
  <script>
    (function () {
      Cart.updateBadge();

      // Handle hash-based tab navigation (e.g. /account#preorders)
      var hash = window.location.hash.replace('#', '');
      if (hash && document.getElementById('tab-' + hash)) {
        setTimeout(function() {
          var tabLink = document.querySelector('[data-tab="' + hash + '"]');
          showTab(hash, tabLink);
        }, 100);
      }

      var isGuest = !Auth.token();

      // ── Load user & data ────────────────────────────────────────
      (async function init() {
        if (isGuest) {
          try {
            // Fetch guest orders to see if they have any
            const res = await API.get('/orders');
            // Support paginated or flat list response format
            const orders = res.data || res.orders || [];
            if (orders.length === 0) {
              // Guest has no orders to track, redirect to login
              location.href = '/auth';
              return;
            }

            // Set up guest UI: Hide profile, address, and logout actions
            var profileMenuItem = document.querySelector('[data-tab="profile"]');
            var addressesMenuItem = document.querySelector('[data-tab="addresses"]');
            var logoutMenuItem = document.querySelector('.acc-menu .logout');

            if (profileMenuItem) profileMenuItem.parentNode.style.display = 'none';
            if (addressesMenuItem) addressesMenuItem.parentNode.style.display = 'none';

            if (logoutMenuItem) {
              logoutMenuItem.innerHTML = `
                <a href="/auth">
                  <svg viewBox="0 0 24 24" stroke-width="1.5">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                    <polyline points="10 17 15 12 10 7" />
                    <line x1="15" y1="12" x2="3" y2="12" />
                  </svg>
                  {{ __('Sign In') }}
                </a>
              `;
            }

            // Show guest tracking info in sidebar
            var nameEl = document.getElementById('acc-name');
            var emailEl = document.getElementById('acc-email');
            var sinceEl = document.getElementById('acc-since');
            if (nameEl) nameEl.textContent = "{{ __('Guest Customer') }}";
            if (emailEl) emailEl.textContent = "{{ __('Guest Session') }}";
            if (sinceEl) sinceEl.textContent = "{{ __('Tracking guest orders') }}";

            // Render guest orders in overview
            renderRecentOrders(orders);

            // Compute and render stats manually for guest
            const total = orders.length;
            const delivered = orders.filter(o => ['delivered', 'completed'].includes((o.status || '').toLowerCase())).length;
            const processing = orders.filter(o => ['pending', 'processing'].includes((o.status || '').toLowerCase())).length;

            document.getElementById('stat-total').textContent = total;
            document.getElementById('stat-delivered').textContent = delivered;
            document.getElementById('stat-pending').textContent = processing;
            document.getElementById('stat-wishlist').textContent = '0';
          } catch (e) {
            location.href = '/auth';
            return;
          }
        } else {
          try {
            const res = await API.get('/auth/user');
            const user = res.data || res;
            if (user) {
              localStorage.setItem('dh_user', JSON.stringify(user));
            }
            renderUserInfo(user);
          } catch (e) {
            location.href = '/auth';
            return;
          }
          loadOverview();
        }
      })();

      function renderUserInfo(user) {
        if (!user) return;
        const nameEl = document.getElementById('acc-name');
        const emailEl = document.getElementById('acc-email');
        const sinceEl = document.getElementById('acc-since');

        const fullName = user.name || [user.first_name, user.last_name].filter(Boolean).join(' ');
        if (nameEl) nameEl.textContent = fullName || "{{ __('User') }}";
        if (emailEl) emailEl.textContent = user.email || '—';
        if (sinceEl && user.created_at) {
          sinceEl.textContent = "{{ __('Since') }}" + " " + new Date(user.created_at).getFullYear();
        }

        // Show vendor application status if it exists
        if (user.vendor) {
          const alertEl = document.getElementById('vendor-status-alert');
          const textEl = document.getElementById('vendor-status-text');
          const badgeEl = document.getElementById('vendor-status-badge');
          const portalLinkEl = document.getElementById('vendor-portal-link');

          if (alertEl) {
            alertEl.style.display = 'block';
            let status = user.vendor.status;
            let statusText = 'Your vendor application is ' + status + '.';
            let badgeHtml = '';
            
            if (status === 'pending') {
              badgeHtml = '<span style="background:#fef3c7; color:#d97706; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; white-space:nowrap;">Pending Review</span>';
              statusText = 'We are currently reviewing your shop details. We will notify you once approved.';
            } else if (status === 'active') {
              badgeHtml = '<span style="background:#d1fae5; color:#059669; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; white-space:nowrap;">Approved</span>';
              statusText = 'Your vendor account is active. You can now access your vendor portal to add products, check finances, and more.';
              if (portalLinkEl) portalLinkEl.style.display = 'block';
            } else if (status === 'rejected') {
              badgeHtml = '<span style="background:#fee2e2; color:#dc2626; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; white-space:nowrap;">Rejected</span>';
              statusText = 'Unfortunately, your application was not accepted at this time. Please contact support for more details.';
            } else if (status === 'suspended') {
              badgeHtml = '<span style="background:#fef08a; color:#854d0e; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; white-space:nowrap;">Suspended</span>';
              statusText = 'Your vendor account is temporarily suspended. Please check violations or contact admin support.';
              if (portalLinkEl) portalLinkEl.style.display = 'block';
            } else if (status === 'banned') {
              badgeHtml = '<span style="background:#fee2e2; color:#b91c1c; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; white-space:nowrap;">Banned</span>';
              statusText = 'Your vendor account has been permanently banned due to policy violations.';
            }

            textEl.textContent = statusText;
            badgeEl.innerHTML = badgeHtml;
          }
        }

        // Pre-fill profile tab
        const nameInput = document.getElementById('profile-name');
        const emailInput = document.getElementById('profile-email');
        const phoneInput = document.getElementById('profile-phone');

        if (nameInput) nameInput.value = fullName || '';
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

        if (container) container.innerHTML = html || '<p style="color:#aaa;font-size:13px">' + "{{ __('No orders yet.') }}" + '</p>';
        if (fullContainer) fullContainer.innerHTML = html || '<p style="color:#aaa;font-size:13px">' + "{{ __('No orders yet.') }}" + '</p>';
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
            <div class="order-meta">
              <div class="order-total">EGP ${(parseFloat(o.total) || 0).toLocaleString()}</div>
              <a class="order-action" href="/account/orders/${o.id}">${"{{ __('Details →') }}"}</a>
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
        if (tabId === 'preorders') loadPreordersTab();
      };

      async function loadOrdersTab() {
        try {
          const res = await API.get('/orders');
          const orders = res.data || res.orders || [];
          const container = document.getElementById('orders-list');
          if (container) {
            container.innerHTML = orders.length
              ? orders.map(o => buildOrderCard(o)).join('')
              : '<p style="color:#aaa;font-size:13px">' + "{{ __('No orders yet.') }}" + '</p>';
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

      async function loadPreordersTab() {
        const container = document.getElementById('preorders-list');
        if (!container) return;
        container.innerHTML = '<p style="color:#aaa;font-size:13px">{{ __("Loading...") }}</p>';
        try {
          const res = await API.get('/pre-orders');
          const preOrders = res.pre_orders || res.data || [];
          if (!preOrders.length) {
            container.innerHTML = '<p style="color:#aaa;font-size:13px">{{ __("No pre-order requests yet.") }}</p>';
            return;
          }
          container.innerHTML = preOrders.map(function(po) {
            var status = (po.status || 'pending');
            var statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
            var dateStr = po.created_at ? new Date(po.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';
            var imageCount = (po.images && po.images.length) || 0;
            var notes = (po.notes || '').substring(0, 80);
            if ((po.notes || '').length > 80) notes += '...';

            return '<div class="order-card">' +
              '<div>' +
                '<div class="order-top">' +
                  '<span class="order-id">#' + po.id + '</span>' +
                  '<span class="order-status status-' + status + '">' + statusLabel + '</span>' +
                  '<span class="order-date">' + dateStr + '</span>' +
                '</div>' +
                (notes ? '<div style="font-size:13px;color:#666;line-height:1.5;margin-top:6px;">' + escHtml(notes) + '</div>' : '') +
                '<div style="display:flex;align-items:center;gap:12px;font-size:12px;color:#999;margin-top:8px;">' +
                  '<span style="background:#f5f0e8;padding:3px 8px;border-radius:4px;font-weight:600;color:#8B6A48;">' + imageCount + ' {{ __("images") }}</span>' +
                  (po.governorate ? '<span>{{ __("Governate") }}: ' + escHtml(po.governorate) + '</span>' : '') +
                '</div>' +
              '</div>' +
              '<div class="order-meta">' +
                '<a class="order-action" href="/account/pre-orders/' + po.id + '">{{ __("Details →") }}</a>' +
              '</div>' +
            '</div>';
          }).join('');
        } catch (e) {
          container.innerHTML = '<p style="color:#aaa;font-size:13px">{{ __("Failed to load pre-orders.") }}</p>';
        }
      }

      function escHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
      }

      function renderAddresses(addresses) {
        const container = document.getElementById('addresses-container');
        if (!container) return;

        if (addresses.length === 0) {
          container.innerHTML = '<p style="color:#aaa;font-size:13px">' + "{{ __('No saved addresses.') }}" + '</p>';
          return;
        }

        container.innerHTML = addresses.map(addr => `
          <div class="address-card">
            <div class="address-card-title">
              ${addr.label || "{{ __('Address') }}"} ${addr.is_default ? (' — ' + "{{ __('Default') }}") : ''}
            </div>
            <div class="address-card-body">
              ${addr.first_name || ''} ${addr.last_name || ''}<br>
              ${addr.address_line_1 || ''}${addr.address_line_2 ? ', ' + addr.address_line_2 : ''}<br>
              ${addr.city || ''}${addr.state ? ', ' + addr.state : ''} ${addr.postal_code || ''}<br>
              ${addr.phone || ''}
            </div>
            <div class="address-card-actions">
              <button class="btn-edit" onclick="editAddress('${addr.id}')">${"{{ __('Edit') }}"}</button>
              <button class="btn-edit" style="color:#c0392b" onclick="deleteAddress('${addr.id}')">${"{{ __('Remove') }}"}</button>
            </div>
          </div>
        `).join('');
      }

      // ── Profile save ───────────────────────────────────────────
      document.getElementById('btn-save-profile').addEventListener('click', async function () {
        const name = document.getElementById('profile-name').value.trim();
        const phone = document.getElementById('profile-phone').value.trim();

        if (!name) {
          showToast("{{ __('Name is required.') }}");
          return;
        }

        this.disabled = true;
        this.textContent = "{{ __('Saving...') }}";

        try {
          await API.put('/auth/profile', {
            name: name,
            phone: phone
          });
          showToast("{{ __('Profile updated!') }}");
          document.getElementById('acc-name').textContent = name;
        } catch (e) {
          showToast(e.data?.message || "{{ __('Update failed.') }}");
        } finally {
          this.disabled = false;
          this.textContent = "{{ __('Save Changes') }}";
        }
      });

      document.getElementById('btn-save-password').addEventListener('click', async function () {
        const current = document.getElementById('profile-current-password').value;
        const newPass = document.getElementById('profile-new-password').value;

        if (!current || !newPass) {
          showToast("{{ __('Please fill in both password fields.') }}");
          return;
        }
        if (newPass.length < 8) {
          showToast("{{ __('New password must be at least 8 characters.') }}");
          return;
        }

        this.disabled = true;
        this.textContent = "{{ __('Updating...') }}";

        try {
          await API.put('/auth/password', {
            current_password: current,
            password: newPass,
            password_confirmation: newPass
          });
          showToast("{{ __('Password updated!') }}");
          document.getElementById('profile-current-password').value = '';
          document.getElementById('profile-new-password').value = '';
        } catch (e) {
          showToast(e.data?.message || "{{ __('Password update failed.') }}");
        } finally {
          this.disabled = false;
          this.textContent = "{{ __('Update Password') }}";
        }
      });

      // ── Address management ─────────────────────────────────────
      var accountGovernorates = [];

      async function loadGovernorateOptions() {
        try {
          const res = await API.get('/shipping/governorate-fees/active');
          accountGovernorates = res.fees || res.data || [];
          const select = document.getElementById('addr-state');
          if (!select) return;
          const current = select.value;
          select.innerHTML = '<option value="">' + "{{ __('Select Governorate') }}" + '</option>' +
            accountGovernorates.map(function(g) {
              return '<option value="' + esc(g.governorate_name) + '">' + esc(g.governorate_name) + '</option>';
            }).join('');
          if (current) select.value = current;
        } catch (e) {}
      }

      loadGovernorateOptions();

      document.getElementById('btn-add-address').addEventListener('click', function () {
        document.getElementById('address-modal-title').textContent = "{{ __('Add Address') }}";
        document.getElementById('edit-address-id').value = '';
        clearAddressForm();
        document.getElementById('address-modal').classList.add('open');
      });

      document.getElementById('btn-cancel-address').addEventListener('click', function () {
        document.getElementById('address-modal').classList.remove('open');
      });

      document.getElementById('address-modal').addEventListener('click', function (e) {
        if (e.target === this) this.classList.remove('open');
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

        if (!payload.first_name || !payload.address_line_1 || !payload.city || !payload.state || !payload.phone) {
          showToast("{{ __('Please fill in all required fields.') }}");
          return;
        }

        this.disabled = true;
        this.textContent = "{{ __('Saving...') }}";

        try {
          if (id) {
            await API.put('/addresses/' + id, payload);
            showToast("{{ __('Address updated!') }}");
          } else {
            await API.post('/addresses', payload);
            showToast("{{ __('Address added!') }}");
          }
          document.getElementById('address-modal').classList.remove('open');
          loadAddressesTab();
        } catch (e) {
          showToast(e.data?.message || "{{ __('Failed to save address.') }}");
        } finally {
          this.disabled = false;
          this.textContent = "{{ __('Save Address') }}";
        }
      });

      window.editAddress = async function (id) {
        try {
          const res = await API.get('/addresses/' + id);
          const addr = res.data?.address || res.address;
          if (!addr) return;

          document.getElementById('address-modal-title').textContent = "{{ __('Edit Address') }}";
          document.getElementById('edit-address-id').value = id;
          document.getElementById('addr-label').value = addr.label || '';
          document.getElementById('addr-first-name').value = addr.first_name || '';
          document.getElementById('addr-last-name').value = addr.last_name || '';
          document.getElementById('addr-line-1').value = addr.address_line_1 || '';
          document.getElementById('addr-line-2').value = addr.address_line_2 || '';
          document.getElementById('addr-city').value = addr.city || '';
          document.getElementById('addr-state').value = addr.state || addr.governorate || '';
          document.getElementById('addr-postal').value = addr.postal_code || '';
          document.getElementById('addr-phone').value = addr.phone || '';
          document.getElementById('address-modal').classList.add('open');
        } catch (e) {
          showToast("{{ __('Could not load address.') }}");
        }
      };

      window.deleteAddress = async function (id) {
        if (!confirm("{{ __('Remove this address?') }}")) return;
        try {
          await API.del('/addresses/' + id);
          showToast("{{ __('Address removed.') }}");
          loadAddressesTab();
        } catch (e) {
          showToast(e.data?.message || "{{ __('Could not remove address.') }}");
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