@extends('layouts.app')

@section('title', 'Vendor Dashboard — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="{{ asset('css/vendor-portal.css') }}?v={{ filemtime(public_path('css/vendor-portal.css')) }}">
@endsection

@section('content')
<div class="vendor-portal">
  <div class="portal-layout">
    
    <!-- Sidebar Navigation -->
    <aside class="portal-sidebar">
      <div class="portal-brand">Vendor<span>Portal</span></div>
      
      <div class="sidebar-section">MAIN</div>
      <ul class="portal-menu">
        <li>
          <a href="#" class="active" data-tab="dashboard" onclick="VendorPortal.switchTab('dashboard', this); return false;">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Overview
          </a>
        </li>
        <li>
          <a href="#" data-tab="products" onclick="VendorPortal.switchTab('products', this); return false;">
            <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Products
          </a>
        </li>
        <li>
          <a href="#" data-tab="finances" onclick="VendorPortal.switchTab('finances', this); return false;">
            <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="10" x2="12" y2="10"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            Finances
          </a>
        </li>
      </ul>

      <div class="sidebar-section">COMPLIANCE</div>
      <ul class="portal-menu">
        <li>
          <a href="#" data-tab="documents" onclick="VendorPortal.switchTab('documents', this); return false;">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Documents
          </a>
        </li>
        <li>
          <a href="#" data-tab="violations" onclick="VendorPortal.switchTab('violations', this); return false;">
            <svg viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Violations
          </a>
        </li>
        <li>
          <a href="#" data-tab="policy" onclick="VendorPortal.switchTab('policy', this); return false;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            Our Policy
          </a>
        </li>
      </ul>
    </aside>

    <!-- Main Content Area -->
    <main class="portal-main">
      <header class="portal-header">
        <div>
          <h1 class="portal-title" id="page-title">Dashboard Overview</h1>
          <p class="portal-subtitle" id="page-subtitle">Manage your furniture catalog and track earnings.</p>
        </div>
        <div class="header-actions" id="header-action">
          <!-- Dynamic action buttons injected here -->
        </div>
      </header>

      <div class="portal-content">
        @include('vendor.portal.tabs.dashboard')
        @include('vendor.portal.tabs.products')
        @include('vendor.portal.tabs.product-form')
        @include('vendor.portal.tabs.finances')
        @include('vendor.portal.tabs.documents')
        @include('vendor.portal.tabs.violations')
        @include('vendor.portal.tabs.policy')
      </div>
    </main>

  </div>
</div>
@endsection

@section('extra_js')
<script src="{{ asset('js/vendor-portal.js') }}?v={{ filemtime(public_path('js/vendor-portal.js')) }}"></script>
@endsection
