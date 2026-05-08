<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin') — DecoHomz</title>
  <link rel="stylesheet" href="/css/shared.css">
  <script src="/js/api.js"></script>
  <script>
    // Block render until auth is verified
    (function () {
      var token = localStorage.getItem('dh_token');
      var user = JSON.parse(localStorage.getItem('dh_user') || 'null');
      var allowedRoles = ['admin', 'support'];
      if (!token || !user || allowedRoles.indexOf(user.role) === -1) {
        document.documentElement.style.display = 'none';
        location.href = '/auth';
        return;
      }
      
      // Redirect support user from non-allowed paths
      if (user.role === 'support') {
        var allowedPaths = ['/admin/orders', '/admin/refunds', '/admin/contacts'];
        var isAllowed = false;
        for (var i = 0; i < allowedPaths.length; i++) {
          if (location.pathname === allowedPaths[i] || location.pathname.startsWith(allowedPaths[i] + '/')) {
            isAllowed = true;
            break;
          }
        }
        if (!isAllowed) {
          document.documentElement.style.display = 'none';
          location.href = '/admin/orders';
          return;
        }
      }
      // Render user info in sidebar
      document.addEventListener('DOMContentLoaded', function () {
        var av = document.querySelector('.sidebar-avatar');
        var nm = document.querySelector('.sidebar-user-name');
        var rl = document.querySelector('.sidebar-user-role');
        if (av && user.name) av.textContent = user.name.charAt(0).toUpperCase();
        if (nm && user.name) nm.textContent = user.name;
        if (rl && user.role) rl.textContent = user.role === 'support' ? 'Support' : 'Administrator';

        // Show/hide nav items based on role
        if (user.role === 'support') {
          document.querySelectorAll('.nav-admin-only').forEach(function(el) { el.style.display = 'none'; });
        } else {
          document.querySelectorAll('.nav-support-only').forEach(function(el) { el.style.display = 'none'; });
        }
      });
    })();
  </script>
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      display: flex;
      min-height: 100vh;
      background: #f5f5f5;
      font-family: 'Segoe UI', system-ui, sans-serif;
    }

    /* Sidebar */
    .admin-sidebar {
      width: 240px;
      min-height: 100vh;
      background: #1a1a1a;
      color: #fff;
      display: flex;
      flex-direction: column;
      position: fixed;
      left: 0;
      top: 0;
      bottom: 0;
      z-index: 100;
    }

    .sidebar-brand {
      padding: 24px 20px 20px;
      border-bottom: 1px solid #333;
      font-size: 18px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .sidebar-brand span {
      color: #c9a96e;
    }

    .sidebar-brand-sub {
      font-size: 10px;
      color: #666;
      font-weight: 400;
      letter-spacing: 1px;
      text-transform: uppercase;
      margin-top: 2px;
    }

    .sidebar-nav {
      flex: 1;
      padding: 16px 0;
      overflow-y: auto;
    }

    .sidebar-nav a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 11px 20px;
      color: #aaa;
      text-decoration: none;
      font-size: 13px;
      transition: 0.2s;
      border-left: 3px solid transparent;
    }

    .sidebar-nav a:hover {
      color: #fff;
      background: #2a2a2a;
    }

    .sidebar-nav a.active {
      color: #c9a96e;
      background: #2a2a2a;
      border-left-color: #c9a96e;
    }

    .sidebar-nav a svg {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
    }

    .sidebar-footer {
      padding: 16px 20px;
      border-top: 1px solid #333;
    }

    .sidebar-user {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 12px;
    }

    .sidebar-avatar {
      width: 32px;
      height: 32px;
      background: #c9a96e;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 13px;
      color: #1a1a1a;
      flex-shrink: 0;
    }

    .sidebar-user-info {
      font-size: 12px;
      color: #ccc;
      line-height: 1.3;
    }

    .sidebar-user-name {
      font-weight: 600;
      color: #fff;
      font-size: 13px;
    }

    .sidebar-logout {
      display: block;
      width: 100%;
      padding: 8px;
      background: none;
      border: 1px solid #444;
      color: #aaa;
      font-size: 12px;
      border-radius: 6px;
      cursor: pointer;
      text-align: center;
      transition: 0.2s;
    }

    .sidebar-logout:hover {
      background: #2a2a2a;
      color: #fff;
      border-color: #666;
    }

    /* Main content */
    .admin-main {
      margin-left: 240px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .admin-topbar {
      background: #fff;
      border-bottom: 1px solid #e5e5e5;
      padding: 0 32px;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 50;
    }

    .topbar-title {
      font-size: 16px;
      font-weight: 600;
      color: #333;
    }

    .topbar-actions {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .topbar-link {
      font-size: 13px;
      color: #c9a96e;
      text-decoration: none;
    }

    .topbar-link:hover {
      text-decoration: underline;
    }

    .admin-content {
      padding: 32px;
      flex: 1;
    }

    /* Cards */
    .stat-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
      margin-bottom: 32px;
    }

    .stat-card {
      background: #fff;
      border-radius: 12px;
      padding: 24px;
      border: 1px solid #eee;
    }

    .stat-card-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
    }

    .stat-card-icon svg {
      width: 20px;
      height: 20px;
    }

    .stat-card-num {
      font-size: 28px;
      font-weight: 700;
      color: #1a1a1a;
      margin-bottom: 4px;
    }

    .stat-card-label {
      font-size: 12px;
      color: #888;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .stat-card-change {
      font-size: 11px;
      margin-top: 8px;
    }

    .stat-card-change.pos {
      color: #22c55e;
    }

    .stat-card-change.neg {
      color: #ef4444;
    }

    /* Table */
    .admin-card {
      background: #fff;
      border-radius: 12px;
      border: 1px solid #eee;
      margin-bottom: 24px;
    }

    .admin-card-header {
      padding: 20px 24px;
      border-bottom: 1px solid #eee;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .admin-card-title {
      font-size: 15px;
      font-weight: 600;
      color: #1a1a1a;
    }

    .admin-card-link {
      font-size: 12px;
      color: #c9a96e;
      text-decoration: none;
    }

    .admin-card-link:hover {
      text-decoration: underline;
    }

    .admin-table {
      width: 100%;
      border-collapse: collapse;
    }

    .admin-table th {
      padding: 12px 24px;
      text-align: left;
      font-size: 11px;
      font-weight: 600;
      color: #888;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 1px solid #eee;
      background: #fafafa;
    }

    .admin-table td {
      padding: 14px 24px;
      font-size: 13px;
      color: #333;
      border-bottom: 1px solid #f5f5f5;
    }

    .admin-table tr:last-child td {
      border-bottom: none;
    }

    .admin-table tr:hover td {
      background: #fafafa;
    }

    /* Status badges */
    .badge-status {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 50px;
      font-size: 11px;
      font-weight: 600;
    }

    .badge-pending {
      background: #fef3c7;
      color: #92400e;
    }

    .badge-processing {
      background: #dbeafe;
      color: #1e40af;
    }

    .badge-shipped {
      background: #e0e7ff;
      color: #3730a3;
    }

    .badge-delivered {
      background: #d1fae5;
      color: #065f46;
    }

    .badge-cancelled {
      background: #fee2e2;
      color: #991b1b;
    }

    .badge-paid {
      background: #d1fae5;
      color: #065f46;
    }

    .badge-paid-deposit {
      background: #fef3c7;
      color: #92400e;
    }

    .badge-unpaid {
      background: #fee2e2;
      color: #991b1b;
    }

    .badge-refunded {
      background: #f3e8ff;
      color: #6b21a8;
    }

    .badge-approved {
      background: #d1fae5;
      color: #065f46;
    }

    .badge-rejected {
      background: #fee2e2;
      color: #991b1b;
    }

    .badge-active {
      background: #d1fae5;
      color: #065f46;
    }

    .badge-inactive {
      background: #f3f4f6;
      color: #6b7280;
    }

    /* Loading */
    .loading-row td {
      text-align: center;
      padding: 40px;
      color: #aaa;
    }

    .loading-row td::before {
      content: 'Loading...';
    }

    @media (max-width: 1024px) {
      .stat-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .admin-sidebar {
        transform: translateX(-100%);
      }

      .admin-main {
        margin-left: 0;
      }

      .stat-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="sidebar-brand">
      Deco<span>Homz</span>
      <div class="sidebar-brand-sub">Admin Panel</div>
    </div>

    <nav class="sidebar-nav">
      <a href="/admin/dashboard" class="{{ request()->is('admin/dashboard') ? 'active' : '' }} nav-admin-only">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="3" y="3" width="7" height="7" />
          <rect x="14" y="3" width="7" height="7" />
          <rect x="14" y="14" width="7" height="7" />
          <rect x="3" y="14" width="7" height="7" />
        </svg>
        Dashboard
      </a>
      <a href="/admin/products" class="{{ request()->is('admin/products*') ? 'active' : '' }} nav-admin-only">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
          <line x1="3" y1="6" x2="21" y2="6" />
          <path d="M16 10a4 4 0 0 1-8 0" />
        </svg>
        Products
      </a>
      <a href="/admin/orders" class="{{ request()->is('admin/orders*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="2" y="4" width="20" height="16" rx="2" />
          <path d="M2 10h20" />
        </svg>
        Orders
      </a>
      <a href="/admin/refunds" class="{{ request()->is('admin/refunds*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
          <path d="M3 3v5h5" />
        </svg>
        Refunds
      </a>
      <a href="/admin/categories" class="{{ request()->is('admin/categories*') ? 'active' : '' }} nav-admin-only">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
        </svg>
        Categories
      </a>
      <a href="/admin/users" class="{{ request()->is('admin/users*') ? 'active' : '' }} nav-admin-only">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
          <circle cx="9" cy="7" r="4" />
          <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
          <path d="M16 3.13a4 4 0 0 1 0 7.75" />
        </svg>
        Users
      </a>
      <a href="/admin/reviews" class="{{ request()->is('admin/reviews*') ? 'active' : '' }} nav-admin-only">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <polygon
            points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
        </svg>
        Reviews
      </a>
      <a href="/admin/coupons" class="{{ request()->is('admin/coupons*') ? 'active' : '' }} nav-admin-only">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
          <line x1="7" y1="7" x2="7.01" y2="7" />
        </svg>
        Coupons
      </a>
      <a href="/admin/delivery-fees" class="{{ request()->is('admin/delivery-fees*') ? 'active' : '' }} nav-admin-only">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="1" y="3" width="15" height="13" rx="1" />
          <path d="M16 8h4l3 5v3h-7V8z" />
          <circle cx="5.5" cy="18.5" r="2.5" />
          <circle cx="18.5" cy="18.5" r="2.5" />
        </svg>
        Delivery Fees
      </a>
      <a href="/admin/deposit-rules" class="{{ request()->is('admin/deposit-rules*') ? 'active' : '' }} nav-admin-only">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
        </svg>
        Deposit Rules
      </a>
      <a href="/admin/contacts" class="{{ request()->is('admin/contacts*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
          <polyline points="22,6 12,13 2,6" />
        </svg>
        Contacts
      </a>
      <a href="/admin/logs" class="{{ request()->is('admin/logs*') ? 'active' : '' }} nav-admin-only">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z" />
          <polyline points="13 2 13 9 20 9" />
        </svg>
        Activity Logs
      </a>
      <a href="/admin/settings" class="{{ request()->is('admin/settings') ? 'active' : '' }} nav-admin-only">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="12" cy="12" r="3" />
          <path
            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
        </svg>
        Settings
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-avatar" id="sidebar-avatar">{{ substr(auth()->user()?->name ?? 'A', 0, 1) }}</div>
        <div class="sidebar-user-info">
          <div class="sidebar-user-name" id="sidebar-user-name">{{ auth()->user()?->name ?? 'Admin' }}</div>
          <div class="sidebar-user-role" id="sidebar-user-role">{{ auth()->user()?->isSupport() ? 'Support' : 'Administrator' }}</div>
        </div>
      </div>
      <button class="sidebar-logout" onclick="Auth.logout()">Sign Out</button>
    </div>
  </aside>

  <!-- Main -->
  <main class="admin-main">
    <header class="admin-topbar">
      <div class="topbar-title">@yield('page_title', 'Dashboard')</div>
      <div class="topbar-actions">
        <a href="/" class="topbar-link" target="_blank">View Store</a>
        <a href="/admin/dashboard" class="topbar-link">Refresh</a>
      </div>
    </header>

    <div class="admin-content">
      @yield('content')
    </div>
  </main>


  <script src="/js/shared.js"></script>
  @yield('extra_js')
  @stack('scripts')
</body>

</html>