@extends('admin.layouts.app')

@section('title', 'Enterprise Analytics')
@section('page_title', '')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --brand-primary: #6366f1;
    --brand-secondary: #8b5cf6;
    --brand-accent: #f43f5e;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
    
    --bg-color: #f3f4f6;
    --surface: rgba(255, 255, 255, 0.85);
    --surface-hover: rgba(255, 255, 255, 1);
    
    --text-main: #1e293b;
    --text-muted: #64748b;
    --border-color: rgba(226, 232, 240, 0.8);
    
    --shadow-sm: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
    --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.025);
    --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.05), 0 10px 10px -5px rgba(0,0,0,0.02);
    --radius-lg: 20px;
    --radius-md: 14px;
    --radius-sm: 8px;
  }

  body {
    background-color: var(--bg-color);
    background-image: 
      radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%),
      radial-gradient(at 100% 0%, rgba(244, 63, 94, 0.08) 0px, transparent 50%);
    background-attachment: fixed;
    font-family: 'Outfit', sans-serif;
  }

  .analytics-wrapper {
    padding: 10px;
    animation: fadeUpIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
  }

  @keyframes fadeUpIn {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
  }

  /* --- HEADER --- */
  .dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 32px;
    flex-wrap: wrap;
    gap: 20px;
  }

  .header-title {
    font-size: 36px;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: -1px;
    line-height: 1.1;
    margin-bottom: 6px;
    background: linear-gradient(135deg, #1e293b, #6366f1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .header-subtitle {
    font-size: 16px;
    color: var(--text-muted);
    font-weight: 500;
  }

  /* --- LIVE PULSE --- */
  .live-glass-panel {
    background: var(--surface);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 16px 28px;
    display: flex;
    gap: 32px;
    box-shadow: var(--shadow-md);
    align-items: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .live-glass-panel:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
  }
  .pulse-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1px;
    color: var(--success);
    background: rgba(16, 185, 129, 0.1);
    padding: 6px 12px;
    border-radius: 20px;
  }
  .pulse-dot {
    width: 8px; height: 8px;
    background: var(--success);
    border-radius: 50%;
    animation: livePulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  }
  @keyframes livePulse {
    0%, 100% { opacity: 1; transform: scale(1); box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
    50% { opacity: .5; transform: scale(1.1); box-shadow: 0 0 0 6px rgba(16,185,129,0); }
  }
  .live-stat {
    display: flex;
    flex-direction: column;
  }
  .live-stat-label { font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;}
  .live-stat-val { font-size: 22px; font-weight: 800; color: var(--text-main); }

  /* --- CONTROLS --- */
  .controls-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    background: var(--surface);
    backdrop-filter: blur(12px);
    padding: 8px 8px 8px 24px;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
  }

  .tabs {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    scrollbar-width: none;
  }
  .tabs::-webkit-scrollbar { display: none; }
  .tab-btn {
    padding: 10px 18px;
    border: none;
    background: transparent;
    border-radius: var(--radius-md);
    font-size: 15px;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
  }
  .tab-btn:hover { color: var(--brand-primary); background: rgba(99, 102, 241, 0.05); }
  .tab-btn.active {
    background: var(--brand-primary);
    color: white;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  }

  .period-export-group {
    display: flex;
    gap: 16px;
    align-items: center;
  }
  .period-selector {
    display: flex;
    background: rgba(226, 232, 240, 0.5);
    padding: 4px;
    border-radius: var(--radius-md);
  }
  .period-btn {
    padding: 8px 14px;
    border: none;
    background: transparent;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s;
  }
  .period-btn:hover { color: var(--text-main); }
  .period-btn.active { background: white; color: var(--brand-primary); box-shadow: var(--shadow-sm); }

  .export-btn {
    background: linear-gradient(135deg, #1e293b, #334155);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(30, 41, 59, 0.2);
  }
  .export-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(30, 41, 59, 0.3);
  }

  .tab-pane { display: none; opacity: 0; transition: opacity 0.4s ease; }
  .tab-pane.active { display: block; opacity: 1; animation: tabFadeIn 0.4s ease forwards; }
  @keyframes tabFadeIn { from { opacity:0; transform:scale(0.98); } to { opacity:1; transform:scale(1); } }

  /* --- KPI GRIDS --- */
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
  }
  .kpi-card {
    background: var(--surface);
    backdrop-filter: blur(12px);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: rgba(99, 102, 241, 0.3);
  }
  .kpi-card::before {
    content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px;
    background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary));
    opacity: 0; transition: opacity 0.3s;
  }
  .kpi-card:hover::before { opacity: 1; }
  
  .kpi-card.gradient-bg {
    background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
    color: white; border: none;
  }
  .kpi-card.gradient-bg .kpi-label, .kpi-card.gradient-bg .kpi-value { color: white; }
  
  .kpi-icon {
    position: absolute; right: 20px; top: 24px;
    width: 48px; height: 48px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: rgba(99, 102, 241, 0.1); color: var(--brand-primary);
    font-size: 24px;
  }
  .kpi-card.gradient-bg .kpi-icon { background: rgba(255,255,255,0.2); color: white; }

  .kpi-label { font-size: 14px; color: var(--text-muted); font-weight: 600; margin-bottom: 8px; }
  .kpi-value { font-size: 28px; font-weight: 800; color: var(--text-main); margin-bottom: 12px; letter-spacing:-0.5px;}
  
  .kpi-change {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 13px; font-weight: 700; padding: 4px 10px; border-radius: 20px;
  }
  .kpi-change.pos { background: rgba(16, 185, 129, 0.1); color: #047857; }
  .kpi-change.neg { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
  .kpi-change.neu { background: rgba(100, 116, 139, 0.1); color: #475569; }
  .kpi-card.gradient-bg .kpi-change { background: rgba(255,255,255,0.2); color: white; }

  /* --- CHARTS & TABLES --- */
  .bento-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 20px;
    margin-bottom: 24px;
  }
  .col-span-12 { grid-column: span 12; }
  .col-span-8 { grid-column: span 8; }
  .col-span-6 { grid-column: span 6; }
  .col-span-4 { grid-column: span 4; }

  @media (max-width: 1100px) {
    .col-span-8, .col-span-6, .col-span-4 { grid-column: span 12; }
  }

  .glass-card {
    background: var(--surface);
    backdrop-filter: blur(12px);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-sm);
    transition: box-shadow 0.3s ease;
    display: flex; flex-direction: column;
  }
  .glass-card:hover { box-shadow: var(--shadow-md); }
  
  .card-header {
    display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;
  }
  .card-title { font-size: 18px; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap:8px;}
  .card-subtitle { font-size: 13px; color: var(--text-muted); font-weight: 500; }
  
  .chart-container { position: relative; height: 320px; width: 100%; flex-grow: 1; }
  .chart-container.small { height: 260px; }

  /* --- TABLES --- */
  .table-responsive { overflow-x: auto; margin: -10px; padding: 10px; }
  table.modern-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
  table.modern-table th {
    text-align: left; padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border-color);
  }
  table.modern-table td {
    padding: 16px; font-size: 14px; color: var(--text-main); font-weight: 500;
    background: white; transition: transform 0.2s;
  }
  table.modern-table tr td:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
  table.modern-table tr td:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
  table.modern-table tr { box-shadow: 0 2px 4px rgba(0,0,0,0.02); cursor: default; }
  table.modern-table tr:hover td { transform: scale(1.01); background: #fdfefe; box-shadow: 0 4px 12px rgba(0,0,0,0.05); z-index:10; position:relative;}

  .progress-bg { width: 100px; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; display: inline-block; vertical-align: middle; margin-right: 8px; }
  .progress-fill { height: 100%; background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary)); border-radius: 3px; }

  .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing:0.5px;}
  .badge.critical { background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); }
  .badge.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); border: 1px solid rgba(245, 158, 11, 0.2); }
  .badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.2); }
  .badge.info { background: rgba(59, 130, 246, 0.1); color: var(--info); border: 1px solid rgba(59, 130, 246, 0.2); }

  /* --- LOADER --- */
  .loader-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(243, 244, 246, 0.8);
    z-index: 999; display: flex; flex-direction: column; align-items: center; justify-content: center;
    backdrop-filter: blur(8px); display: none; opacity:0; transition: opacity 0.3s;
  }
  .loader-overlay.show { display: flex; opacity:1; }
  
  .modern-spinner {
    width: 50px; height: 50px;
    border: 4px solid rgba(99, 102, 241, 0.2);
    border-top-color: var(--brand-primary);
    border-radius: 50%;
    animation: spin 1s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    margin-bottom: 20px;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  .loader-text { font-weight: 700; font-size: 16px; color: var(--text-main); letter-spacing: 0.5px; background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;}

</style>
@endpush

@section('content')

<div class="loader-overlay" id="loader">
  <div class="modern-spinner"></div>
  <div class="loader-text">Synthesizing Data...</div>
</div>

<div class="analytics-wrapper">

  <div class="dashboard-header">
    <div>
      <h2 class="header-title">Intelligence Hub</h2>
      <p class="header-subtitle">Real-time enterprise analytics and business performance.</p>
    </div>
    
    <div class="live-glass-panel">
      <div class="pulse-indicator"><div class="pulse-dot"></div> LIVE</div>
      <div class="live-stat"><span class="live-stat-label">Orders Today</span><span class="live-stat-val" id="live-orders">-</span></div>
      <div class="live-stat"><span class="live-stat-label">Revenue Today</span><span class="live-stat-val" id="live-revenue">-</span></div>
      <div class="live-stat"><span class="live-stat-label">Active Carts</span><span class="live-stat-val" id="live-carts">-</span></div>
    </div>
  </div>

  <div class="controls-bar">
    <div class="tabs">
      <button class="tab-btn active" data-target="overview">Overview</button>
      <button class="tab-btn" data-target="revenue">Financials</button>
      <button class="tab-btn" data-target="orders">Orders & Funnel</button>
      <button class="tab-btn" data-target="products">Inventory</button>
      <button class="tab-btn" data-target="customers">Customers</button>
      <button class="tab-btn" data-target="geographic">Geographic</button>
      <button class="tab-btn" data-target="vendors">Vendors</button>
      <button class="tab-btn" data-target="marketing">Marketing</button>
    </div>

    <div class="period-export-group">
      <div class="period-selector" id="period-selector">
        <button class="period-btn" data-period="7d">7D</button>
        <button class="period-btn active" data-period="30d">30D</button>
        <button class="period-btn" data-period="90d">90D</button>
        <button class="period-btn" data-period="12m">12M</button>
        <button class="period-btn" data-period="all">ALL</button>
      </div>
      <button class="export-btn" id="export-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export
      </button>
    </div>
  </div>

  <!-- TAB: OVERVIEW -->
  <div class="tab-pane active" id="tab-overview">
    <div class="kpi-grid" id="overview-kpis-top"></div>
    <div class="kpi-grid" id="overview-kpis-bottom"></div>
    
    <div class="bento-grid">
      <div class="glass-card col-span-8">
        <div class="card-header"><div class="card-title">✨ Revenue Waterfall</div><div class="card-subtitle">Gross to Net margin breakdown</div></div>
        <div class="chart-container"><canvas id="waterfallChart"></canvas></div>
      </div>
      <div class="glass-card col-span-4">
        <div class="card-header"><div class="card-title">🎯 Activity Matrix</div></div>
        <div class="chart-container"><canvas id="activityChart"></canvas></div>
      </div>
    </div>
  </div>

  <!-- TAB: REVENUE -->
  <div class="tab-pane" id="tab-revenue">
    <div class="glass-card col-span-12" style="margin-bottom:24px;">
      <div class="card-header"><div class="card-title">📈 Revenue & Growth Trend</div></div>
      <div class="chart-container"><canvas id="revenueTrendChart"></canvas></div>
    </div>
    
    <div class="bento-grid">
      <div class="glass-card col-span-8">
        <div class="card-header"><div class="card-title">📅 Daily Performance Heat</div></div>
        <div class="chart-container"><canvas id="revDayOfWeekChart"></canvas></div>
      </div>
      <div class="glass-card col-span-4">
        <div class="card-header"><div class="card-title">💳 Payment Methods</div></div>
        <div class="chart-container small"><canvas id="paymentMethodChart"></canvas></div>
      </div>
    </div>
  </div>

  <!-- TAB: ORDERS -->
  <div class="tab-pane" id="tab-orders">
    <div class="kpi-grid" id="orders-kpis"></div>
    
    <div class="bento-grid">
      <div class="glass-card col-span-8">
        <div class="card-header"><div class="card-title">🛒 Conversion Funnel</div></div>
        <div class="chart-container"><canvas id="funnelChart"></canvas></div>
      </div>
      <div class="glass-card col-span-4">
        <div class="card-header"><div class="card-title">📦 Status Distribution</div></div>
        <div class="chart-container"><canvas id="orderStatusChart"></canvas></div>
      </div>
    </div>
    
    <div class="bento-grid">
      <div class="glass-card col-span-6">
        <div class="card-header"><div class="card-title">⚡ Peak Action Hours</div></div>
        <div class="chart-container small"><canvas id="peakHoursChart"></canvas></div>
      </div>
      <div class="glass-card col-span-6">
        <div class="card-header"><div class="card-title">↩️ Refund Analytics</div></div>
        <div class="chart-container small"><canvas id="refundStatusChart"></canvas></div>
      </div>
    </div>
  </div>

  <!-- TAB: PRODUCTS -->
  <div class="tab-pane" id="tab-products">
    <div class="kpi-grid" id="inventory-kpis"></div>
    
    <div class="bento-grid">
      <div class="glass-card col-span-6 table-responsive">
        <div class="card-header"><div class="card-title">🔥 Top Selling Performers</div></div>
        <table class="modern-table" id="top-products-table">
          <thead><tr><th>Product</th><th>Sold</th><th>Revenue</th><th>Margin</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="glass-card col-span-6 table-responsive">
        <div class="card-header"><div class="card-title">🧊 Dead & Slow Moving</div></div>
        <table class="modern-table" id="worst-products-table">
          <thead><tr><th>Product</th><th>Stock Locked</th><th>Value</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
    
    <div class="glass-card col-span-12 table-responsive" style="margin-bottom:24px;">
      <div class="card-header"><div class="card-title">⚡ Stock Velocity & Reorder AI</div><div class="card-subtitle">Predictive stockout analysis based on current run rate</div></div>
      <table class="modern-table" id="stock-velocity-table">
        <thead><tr><th>Product</th><th>Current Stock</th><th>Daily Velocity</th><th>Days Until Out</th><th>Status</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <!-- TAB: CUSTOMERS -->
  <div class="tab-pane" id="tab-customers">
    <div class="kpi-grid" id="customer-kpis"></div>
    
    <div class="bento-grid">
      <div class="glass-card col-span-8">
        <div class="card-header"><div class="card-title">👥 User Acquisition</div></div>
        <div class="chart-container"><canvas id="customerGrowthChart"></canvas></div>
      </div>
      <div class="glass-card col-span-4">
        <div class="card-header"><div class="card-title">🔁 Retention Cohorts</div></div>
        <div class="chart-container"><canvas id="orderFreqChart"></canvas></div>
      </div>
    </div>
    
    <div class="glass-card col-span-12 table-responsive">
      <div class="card-header"><div class="card-title">👑 Top Spenders (CLV)</div></div>
      <table class="modern-table" id="top-customers-table">
        <thead><tr><th>Customer</th><th>Orders</th><th>Total Spent</th><th>Est. Annual CLV</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <!-- TAB: GEOGRAPHIC -->
  <div class="tab-pane" id="tab-geographic">
    <div class="kpi-grid" id="geo-kpis"></div>
    
    <div class="bento-grid">
      <div class="glass-card col-span-6">
        <div class="card-header"><div class="card-title">🗺️ Regional Distribution</div></div>
        <div class="chart-container"><canvas id="geoBarChart"></canvas></div>
      </div>
      <div class="glass-card col-span-6 table-responsive">
        <div class="card-header"><div class="card-title">📊 Governorate ROI</div></div>
        <table class="modern-table" id="geo-table">
          <thead><tr><th>Region</th><th>Orders</th><th>Revenue</th><th>AOV</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- TAB: VENDORS -->
  <div class="tab-pane" id="tab-vendors">
    <div class="kpi-grid" id="vendor-kpis"></div>
    
    <div class="glass-card col-span-12 table-responsive">
      <div class="card-header"><div class="card-title">🏭 Vendor Scorecards</div></div>
      <table class="modern-table" id="vendor-table">
        <thead><tr><th>Vendor</th><th>Status</th><th>Catalog</th><th>Sold</th><th>Revenue generated</th><th>Platform Cut</th><th>Rating</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <!-- TAB: MARKETING -->
  <div class="tab-pane" id="tab-marketing">
    <div class="bento-grid">
      <div class="glass-card col-span-6 table-responsive">
        <div class="card-header">
          <div class="card-title">🎟️ Coupon ROI</div>
          <div class="card-subtitle" id="coupon-stats"></div>
        </div>
        <table class="modern-table" id="top-coupons-table">
          <thead><tr><th>Code</th><th>Uses</th><th>Discount</th><th>Revenue</th><th>ROI</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="glass-card col-span-6 table-responsive">
        <div class="card-header">
          <div class="card-title">🤝 Affiliate Network</div>
          <div class="card-subtitle" id="affiliate-stats"></div>
        </div>
        <table class="modern-table" id="top-affiliates-table">
          <thead><tr><th>Affiliate</th><th>Referrals</th><th>Payout</th><th>Revenue</th><th>ROI</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
  // Global State
  let currentPeriod = '30d';
  let currentTab = 'overview';
  let chartInstances = {};
  
  // Formatters with aggressive Number parsing to fix aggregate string bugs
  const formatEGP = (val) => new Intl.NumberFormat('en-EG', { style: 'currency', currency: 'EGP', maximumFractionDigits: 0 }).format(Number(val) || 0);
  const formatNum = (val) => new Intl.NumberFormat('en-EG').format(Number(val) || 0);
  const formatPct = (val) => (val !== null && val !== undefined) ? Number(val).toFixed(1) + '%' : '-';
  
  const getChangeBadge = (pct, inverseColors = false) => {
    if (pct === null || pct === undefined) return `<span class="kpi-change neu">-</span>`;
    const num = Number(pct);
    const isPos = num > 0;
    let cls = isPos ? 'pos' : (num < 0 ? 'neg' : 'neu');
    if (inverseColors && num !== 0) cls = isPos ? 'neg' : 'pos';
    const sign = isPos ? '+' : '';
    return `<span class="kpi-change ${cls}">${sign}${num.toFixed(1)}%</span>`;
  };

  // Setup Premium Chart Defaults
  Chart.defaults.font.family = "'Outfit', sans-serif";
  Chart.defaults.color = '#64748b';
  Chart.defaults.scale.grid.color = 'rgba(226, 232, 240, 0.4)';
  Chart.defaults.plugins.legend.labels.usePointStyle = true;
  Chart.defaults.plugins.legend.labels.boxWidth = 8;
  Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.9)';
  Chart.defaults.plugins.tooltip.titleFont = { size: 14, family: "'Outfit', sans-serif", weight: '700' };
  Chart.defaults.plugins.tooltip.bodyFont = { size: 13, family: "'Outfit', sans-serif" };
  Chart.defaults.plugins.tooltip.padding = 12;
  Chart.defaults.plugins.tooltip.cornerRadius = 12;
  Chart.defaults.plugins.tooltip.displayColors = true;
  Chart.defaults.plugins.tooltip.boxPadding = 6;
  
  // Creates a beautiful gradient for charts
  const getGradient = (ctx, colorStart, colorEnd) => {
    if(!ctx) return colorStart;
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, colorStart);
    gradient.addColorStop(1, colorEnd);
    return gradient;
  };

  // Core loader
  const loadTab = async () => {
    const loader = document.getElementById('loader');
    loader.classList.add('show');
    try {
      if (currentTab === 'overview') await loadOverview();
      else if (currentTab === 'revenue') await loadRevenue();
      else if (currentTab === 'orders') await loadOrders();
      else if (currentTab === 'products') await loadProducts();
      else if (currentTab === 'customers') await loadCustomers();
      else if (currentTab === 'geographic') await loadGeographic();
      else if (currentTab === 'vendors') await loadVendors();
      else if (currentTab === 'marketing') await loadMarketing();
    } catch(e) {
      console.error(e);
    } finally {
      setTimeout(() => loader.classList.remove('show'), 300); // smooth fade
    }
  };

  const buildChart = (id, config) => {
    if (chartInstances[id]) chartInstances[id].destroy();
    chartInstances[id] = new Chart(document.getElementById(id), config);
  };

  // --- TAB LOADERS ---

  async function loadOverview() {
    const [overviewData, activityData, revData] = await Promise.all([
      API.get(`/admin/analytics/overview?period=${currentPeriod}`),
      API.get(`/admin/analytics/activity?period=${currentPeriod}`),
      API.get(`/admin/analytics/revenue?period=${currentPeriod}`)
    ]);
    
    const kpis = overviewData.kpis;
    
    document.getElementById('overview-kpis-top').innerHTML = `
      <div class="kpi-card gradient-bg">
        <div class="kpi-label">Gross Revenue</div>
        <div class="kpi-value">${formatEGP(kpis.gross_revenue.value)}</div>
        ${getChangeBadge(kpis.gross_revenue.change_pct)}
        <div class="kpi-icon">💰</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Net Revenue</div>
        <div class="kpi-value">${formatEGP(kpis.net_revenue.value)}</div>
        ${getChangeBadge(kpis.net_revenue.change_pct)}
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Profit Margin</div>
        <div class="kpi-value">${formatPct(kpis.profit_margin_pct.value)}</div>
        ${getChangeBadge(kpis.profit_margin_pct.change_pct)}
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Total Delivery Fees</div>
        <div class="kpi-value">${formatEGP(kpis.total_delivery_fees.value)}</div>
        ${getChangeBadge(kpis.total_delivery_fees.change_pct)}
      </div>
    `;
    
    document.getElementById('overview-kpis-bottom').innerHTML = `
      <div class="kpi-card">
        <div class="kpi-label">Repeat Cust. Rate</div>
        <div class="kpi-value">${formatPct(kpis.repeat_customer_rate.value)}</div>
        ${getChangeBadge(kpis.repeat_customer_rate.change_pct)}
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Refund Rate</div>
        <div class="kpi-value">${formatPct(kpis.refund_rate.value)}</div>
        ${getChangeBadge(kpis.refund_rate.change_pct, true)}
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Pre-Orders</div>
        <div class="kpi-value">${Number(kpis.pre_order_count.value)}</div>
        ${getChangeBadge(kpis.pre_order_count.change_pct)}
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Action Center</div>
        <div style="display:flex; gap:12px;">
          <span class="badge info">${Number(kpis.active_carts.value)} Carts</span>
          <span class="badge warning">${Number(kpis.pending_contacts.value)} Msgs</span>
        </div>
      </div>
    `;

    // Waterfall Chart
    const w = revData.waterfall || {};
    const ctxW = document.getElementById('waterfallChart').getContext('2d');
    buildChart('waterfallChart', {
      type: 'bar',
      data: {
        labels: ['Gross Sub', 'Discounts', 'Delivery', 'VAT', 'Net Total'],
        datasets: [{
          data: [Number(w.subtotal || 0), -(Number(w.discount || 0) + Number(w.affiliate_discount || 0)), Number(w.delivery_fee || 0), Number(w.vat || 0), Number(w.net_total || 0)],
          backgroundColor: [
            getGradient(ctxW, '#94a3b8', '#64748b'),
            getGradient(ctxW, '#f87171', '#ef4444'),
            getGradient(ctxW, '#fbbf24', '#f59e0b'),
            getGradient(ctxW, '#60a5fa', '#3b82f6'),
            getGradient(ctxW, '#34d399', '#10b981')
          ],
          borderRadius: 8,
          borderSkipped: false
        }]
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // Activity
    const actTypes = Object.keys(activityData.actions_by_type);
    const actCounts = Object.values(activityData.actions_by_type);
    buildChart('activityChart', {
      type: 'doughnut',
      data: {
        labels: actTypes,
        datasets: [{ data: actCounts, backgroundColor: ['#6366f1', '#8b5cf6', '#ec4899', '#14b8a6', '#f59e0b'], borderWidth: 0, hoverOffset: 4 }]
      },
      options: { responsive: true, maintainAspectRatio: false, cutout: '75%', layout: { padding: 10 } }
    });
  }

  async function loadRevenue() {
    const data = await API.get(`/admin/analytics/revenue?period=${currentPeriod}`);
    const ctxR = document.getElementById('revenueTrendChart').getContext('2d');
    
    buildChart('revenueTrendChart', {
      type: 'line',
      data: {
        labels: data.time_series.map(d => d.date),
        datasets: [
          {
            label: 'Net Revenue (EGP)',
            data: data.time_series.map(d => Number(d.revenue)),
            borderColor: '#6366f1',
            backgroundColor: getGradient(ctxR, 'rgba(99, 102, 241, 0.4)', 'rgba(99, 102, 241, 0.0)'),
            borderWidth: 3, pointRadius: 0, pointHoverRadius: 6, fill: true, tension: 0.4, yAxisID: 'y'
          },
          {
            label: 'Orders',
            data: data.time_series.map(d => Number(d.orders)),
            borderColor: '#f43f5e', backgroundColor: '#f43f5e',
            type: 'bar', yAxisID: 'y1', borderRadius: 6, barPercentage: 0.3
          }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: {
          y: { beginAtZero: true, grid: { borderDash: [4, 4] } },
          y1: { beginAtZero: true, position: 'right', grid: { display: false } }
        }
      }
    });

    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const ctxD = document.getElementById('revDayOfWeekChart').getContext('2d');
    buildChart('revDayOfWeekChart', {
      type: 'bar',
      data: {
        labels: data.day_of_week.map(d => days[d.day - 1]),
        datasets: [{
          label: 'Revenue',
          data: data.day_of_week.map(d => Number(d.revenue)),
          backgroundColor: getGradient(ctxD, '#8b5cf6', '#6366f1'), borderRadius: 8
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    });

    buildChart('paymentMethodChart', {
      type: 'doughnut',
      data: {
        labels: data.by_payment_method.map(d => d.method.toUpperCase()),
        datasets: [{
          data: data.by_payment_method.map(d => Number(d.revenue)),
          backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#8b5cf6'], borderWidth: 0, hoverOffset: 4
        }]
      },
      options: { responsive: true, maintainAspectRatio: false, cutout: '70%' }
    });
  }

  async function loadOrders() {
    const data = await API.get(`/admin/analytics/orders?period=${currentPeriod}`);
    
    document.getElementById('orders-kpis').innerHTML = `
      <div class="kpi-card gradient-bg">
        <div class="kpi-label">Avg Fulfillment Time</div>
        <div class="kpi-value">${Number(data.transition_times || 0).toFixed(1)} <span style="font-size:16px; font-weight:500; opacity:0.8;">hrs</span></div>
        <div class="kpi-icon">⏱️</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Cart Conversion Rate</div>
        <div class="kpi-value">${formatPct((Number(data.funnel.orders_placed) / Math.max(1, Number(data.funnel.total_carts))) * 100)}</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Refund Amount</div>
        <div class="kpi-value">${formatEGP(data.refund_stats.total_refunded_amount)}</div>
      </div>
    `;

    const ctxF = document.getElementById('funnelChart').getContext('2d');
    buildChart('funnelChart', {
      type: 'bar',
      data: {
        labels: ['All Carts', 'Carts w/ Items', 'Orders Placed', 'Delivered'],
        datasets: [{
          label: 'Count',
          data: [Number(data.funnel.total_carts), Number(data.funnel.carts_with_items), Number(data.funnel.orders_placed), Number(data.funnel.orders_delivered)],
          backgroundColor: [
            getGradient(ctxF, '#94a3b8', '#cbd5e1'), 
            getGradient(ctxF, '#60a5fa', '#93c5fd'), 
            getGradient(ctxF, '#8b5cf6', '#c4b5fd'), 
            getGradient(ctxF, '#10b981', '#6ee7b7')
          ],
          borderRadius: 8
        }]
      },
      options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
    });

    const sLabels = Object.keys(data.status_breakdown);
    const sData = Object.values(data.status_breakdown).map(v => Number(v));
    buildChart('orderStatusChart', {
      type: 'pie',
      data: {
        labels: sLabels.map(s => s.toUpperCase()),
        datasets: [{ data: sData, backgroundColor: ['#f59e0b', '#3b82f6', '#8b5cf6', '#10b981', '#ef4444'], borderWidth: 0, hoverOffset: 4 }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    });

    const ctxP = document.getElementById('peakHoursChart').getContext('2d');
    buildChart('peakHoursChart', {
      type: 'line',
      data: {
        labels: data.peak_hours.map(d => Number(d.hour) + ':00'),
        datasets: [{
          label: 'Orders',
          data: data.peak_hours.map(d => Number(d.count)),
          borderColor: '#f43f5e', backgroundColor: getGradient(ctxP, 'rgba(244, 63, 94, 0.4)', 'rgba(244, 63, 94, 0)'), fill: true, tension: 0.4, borderWidth:3, pointRadius:0, pointHoverRadius:6
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    });

    buildChart('refundStatusChart', {
      type: 'doughnut',
      data: {
        labels: ['Approved', 'Rejected', 'Pending'],
        datasets: [{ data: [Number(data.refund_stats.approved), Number(data.refund_stats.rejected), Number(data.refund_stats.pending)], backgroundColor: ['#10b981', '#ef4444', '#f59e0b'], borderWidth: 0 }]
      },
      options: { responsive: true, maintainAspectRatio: false, cutout: '75%' }
    });
  }

  async function loadProducts() {
    const [prodData, invData] = await Promise.all([
      API.get(`/admin/analytics/products?period=${currentPeriod}&limit=10`),
      API.get(`/admin/analytics/inventory`)
    ]);

    document.getElementById('inventory-kpis').innerHTML = `
      <div class="kpi-card gradient-bg">
        <div class="kpi-label">Total Stock Value</div>
        <div class="kpi-value">${formatEGP(invData.summary.total_stock_value)}</div>
        <div class="kpi-icon">📦</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Active SKUs</div>
        <div class="kpi-value">${Number(invData.summary.total_sku_count)}</div>
      </div>
      <div class="kpi-card" style="border: 1px solid rgba(239, 68, 68, 0.4); background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), transparent);">
        <div class="kpi-label" style="color:var(--danger)">Urgent Restock Needed</div>
        <div class="kpi-value" style="color:var(--danger)">${Number(invData.summary.products_needing_restock)} <span style="font-size:16px; font-weight:500;">items</span></div>
      </div>
    `;

    const getMedal = (idx) => idx === 0 ? '🥇' : (idx === 1 ? '🥈' : (idx === 2 ? '🥉' : '<span style="width:24px;display:inline-block"></span>'));
    
    document.querySelector('#top-products-table tbody').innerHTML = prodData.top_selling.map((p, i) => `
      <tr>
        <td><div style="display:flex;align-items:center;">${getMedal(i)}
          <img src="${p.image ? '/storage/'+p.image : '/images/placeholder.png'}" style="width:40px;height:40px;border-radius:8px;margin:0 12px;object-fit:cover;box-shadow:var(--shadow-sm)">
          <div style="font-weight:700; color:var(--text-main)">${p.name}</div></div>
        </td>
        <td>${Number(p.units_sold)}</td>
        <td style="font-weight:800;color:var(--brand-primary)">${formatEGP(p.revenue)}</td>
        <td><div class="progress-bg"><div class="progress-fill" style="width:${Number(p.margin_pct)}%"></div></div> <span style="font-weight:700">${Number(p.margin_pct).toFixed(1)}%</span></td>
      </tr>
    `).join('');

    document.querySelector('#worst-products-table tbody').innerHTML = prodData.dead_stock.map(p => `
      <tr>
        <td><div style="font-weight:700;">${p.name}</div></td>
        <td><span class="badge critical">${Number(p.stock)} units locked</span></td>
        <td style="font-weight:800;">${formatEGP(p.value)}</td>
      </tr>
    `).join('');

    document.querySelector('#stock-velocity-table tbody').innerHTML = prodData.stock_velocity.map(p => {
      let badgeCls = p.urgency === 'critical' ? 'critical' : (p.urgency === 'high' ? 'warning' : 'success');
      return `<tr>
        <td style="font-weight:700">${p.name}</td>
        <td>${Number(p.stock)}</td>
        <td style="color:var(--brand-secondary); font-weight:600;">${Number(p.velocity).toFixed(2)} / day</td>
        <td style="font-weight:800">${Number(p.days_of_stock).toFixed(1)} days</td>
        <td><span class="badge ${badgeCls}">${p.urgency}</span></td>
      </tr>`;
    }).join('');
  }

  async function loadCustomers() {
    const data = await API.get(`/admin/analytics/customers?period=${currentPeriod}`);

    document.getElementById('customer-kpis').innerHTML = `
      <div class="kpi-card gradient-bg">
        <div class="kpi-label">New Registrations</div>
        <div class="kpi-value">${Number(data.segmentation.total_registered)}</div>
        <div class="kpi-icon">✨</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Loyalty (Repeat Buyers)</div>
        <div class="kpi-value">${Number(data.segmentation.repeat_customers)}</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Registered Revenue</div>
        <div class="kpi-value">${formatEGP(data.revenue_split.registered)}</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Guest Revenue</div>
        <div class="kpi-value">${formatEGP(data.revenue_split.guest)}</div>
      </div>
    `;

    const ctxC = document.getElementById('customerGrowthChart').getContext('2d');
    buildChart('customerGrowthChart', {
      type: 'line',
      data: {
        labels: data.growth_time_series.map(d => d.date),
        datasets: [
          { label: 'New Users', data: data.growth_time_series.map(d => Number(d.new_users)), borderColor: '#f43f5e', type: 'bar', borderRadius:4, barPercentage:0.4 },
          { label: 'Cumulative Total', data: data.growth_time_series.map(d => Number(d.total_users)), borderColor: '#6366f1', backgroundColor: getGradient(ctxC, 'rgba(99, 102, 241, 0.2)', 'rgba(99, 102, 241, 0)'), fill: true, tension: 0.4, type: 'line', yAxisID: 'y1', borderWidth:3, pointRadius:0 }
        ]
      },
      options: { responsive: true, maintainAspectRatio: false, scales: { y1: { position: 'right', grid: {display:false} }, y: {grid:{borderDash:[4,4]}} } }
    });

    const fLabels = Object.keys(data.orders_per_customer_distribution).map(k => k.replace(/_/g, ' ').toUpperCase());
    const ctxF = document.getElementById('orderFreqChart').getContext('2d');
    buildChart('orderFreqChart', {
      type: 'bar',
      data: {
        labels: fLabels,
        datasets: [{ label: 'Customers', data: Object.values(data.orders_per_customer_distribution).map(v => Number(v)), backgroundColor: getGradient(ctxF, '#8b5cf6', '#6366f1'), borderRadius: 8 }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    });

    document.querySelector('#top-customers-table tbody').innerHTML = data.top_spenders.map(c => `
      <tr>
        <td><div style="font-weight:700; color:var(--text-main)">${c.name}</div><div style="font-size:12px;color:var(--text-muted)">${c.email}</div></td>
        <td><span class="badge info">${Number(c.order_count)} Orders</span></td>
        <td style="font-weight:800">${formatEGP(c.total_spent)}</td>
        <td style="font-weight:800;color:var(--success)">${formatEGP(c.clv_annualized)}</td>
      </tr>
    `).join('');
  }

  async function loadGeographic() {
    const data = await API.get(`/admin/analytics/geographic?period=${currentPeriod}`);
    
    document.getElementById('geo-kpis').innerHTML = `
      <div class="kpi-card gradient-bg">
        <div class="kpi-label">Total Delivery Fees</div>
        <div class="kpi-value">${formatEGP(data.delivery_fee_impact.total_fees_collected)}</div>
        <div class="kpi-icon">🚚</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Free Delivery Orders</div>
        <div class="kpi-value">${Number(data.delivery_fee_impact.orders_with_free_delivery)}</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Paid Delivery Orders</div>
        <div class="kpi-value">${Number(data.delivery_fee_impact.orders_with_paid_delivery)}</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Avg Fee (When Charged)</div>
        <div class="kpi-value">${formatEGP(data.delivery_fee_impact.avg_fee)}</div>
      </div>
    `;

    const ctxG = document.getElementById('geoBarChart').getContext('2d');
    buildChart('geoBarChart', {
      type: 'bar',
      data: {
        labels: data.by_governorate.map(g => g.governorate),
        datasets: [{ label: 'Orders', data: data.by_governorate.map(g => Number(g.orders)), backgroundColor: getGradient(ctxG, '#3b82f6', '#1e3a8a'), borderRadius: 6 }]
      },
      options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
    });

    document.querySelector('#geo-table tbody').innerHTML = data.by_governorate.map(g => `
      <tr>
        <td style="font-weight:700">${g.governorate}</td>
        <td>${Number(g.orders)} <span style="font-size:12px;color:var(--text-muted)">(${formatPct(g.pct_of_total_orders)})</span></td>
        <td style="font-weight:800">${formatEGP(g.revenue)}</td>
        <td style="font-weight:700; color:var(--brand-primary)">${formatEGP(g.avg_order_value)}</td>
      </tr>
    `).join('');
  }

  async function loadVendors() {
    const data = await API.get(`/admin/analytics/vendors?period=${currentPeriod}`);
    
    document.getElementById('vendor-kpis').innerHTML = `
      <div class="kpi-card gradient-bg">
        <div class="kpi-label">Platform Commission</div>
        <div class="kpi-value">${formatEGP(data.summary.platform_commission)}</div>
        <div class="kpi-icon">🏛️</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Total Vendor Revenue</div>
        <div class="kpi-value">${formatEGP(data.summary.total_vendor_revenue)}</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Total Payouts</div>
        <div class="kpi-value">${formatEGP(data.summary.total_payouts)}</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Active Vendors</div>
        <div class="kpi-value">${Number(data.summary.active_vendors)} / ${Number(data.summary.total_vendors)}</div>
      </div>
    `;

    document.querySelector('#vendor-table tbody').innerHTML = data.vendors.map(v => {
      const bCls = v.status === 'active' ? 'success' : 'critical';
      return `<tr>
        <td style="font-weight:700">${v.company_name}</td>
        <td><span class="badge ${bCls}">${v.status}</span></td>
        <td><span class="badge info">${Number(v.products_published)} / ${Number(v.products_listed)}</span></td>
        <td>${Number(v.total_units_sold)}</td>
        <td style="font-weight:800">${formatEGP(v.total_revenue)}</td>
        <td style="font-weight:800;color:var(--brand-primary)">${formatEGP(v.platform_cut)}</td>
        <td style="font-weight:700; color:var(--warning)">⭐ ${formatPct(v.avg_product_rating).replace('%','')}</td>
      </tr>`;
    }).join('');
  }

  async function loadMarketing() {
    const data = await API.get(`/admin/analytics/marketing?period=${currentPeriod}`);
    
    document.getElementById('coupon-stats').innerHTML = `<span class="badge info">${formatNum(data.coupons.total_usage)} uses</span> <span class="badge warning">${formatEGP(data.coupons.total_discount_given)} given</span>`;
    document.querySelector('#top-coupons-table tbody').innerHTML = data.coupons.top_coupons.map(c => `
      <tr>
        <td style="font-weight:800;font-family:monospace;font-size:16px;color:var(--brand-secondary)">${c.code}</td>
        <td>${Number(c.uses)}</td>
        <td style="color:var(--danger); font-weight:600;">-${formatEGP(c.discount_given)}</td>
        <td style="font-weight:800">${formatEGP(c.revenue_generated)}</td>
        <td><span class="badge success">${Number(c.roi).toFixed(2)}x</span></td>
      </tr>
    `).join('');

    document.getElementById('affiliate-stats').innerHTML = `<span class="badge info">${Number(data.affiliates.total_referral_orders)} orders</span> <span class="badge warning">${formatEGP(data.affiliates.total_commissions)} comm.</span>`;
    document.querySelector('#top-affiliates-table tbody').innerHTML = data.affiliates.top_affiliates.map(a => `
      <tr>
        <td style="font-weight:700">${a.user_name}</td>
        <td>${Number(a.referral_count)}</td>
        <td style="color:var(--danger); font-weight:600;">-${formatEGP(a.commission)}</td>
        <td style="font-weight:800">${formatEGP(a.revenue_generated)}</td>
        <td><span class="badge success">${Number(a.roi).toFixed(2)}x</span></td>
      </tr>
    `).join('');
  }

  // --- LIVE PULSE ---
  const updateLivePulse = async () => {
    try {
      const data = await API.get('/admin/analytics/live');
      document.getElementById('live-orders').innerText = Number(data.orders_today);
      document.getElementById('live-revenue').innerText = formatEGP(data.revenue_today);
      document.getElementById('live-carts').innerText = Number(data.active_carts);
    } catch(e) {}
  };

  // --- INIT ---
  document.addEventListener('DOMContentLoaded', () => {
    loadTab();
    updateLivePulse();
    setInterval(updateLivePulse, 60000);

    // Period clicks
    document.querySelectorAll('.period-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        e.target.classList.add('active');
        currentPeriod = e.target.dataset.period;
        loadTab();
      });
    });

    // Tab clicks
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        
        e.target.classList.add('active');
        currentTab = e.target.dataset.target;
        document.getElementById(`tab-${currentTab}`).classList.add('active');
        
        loadTab();
      });
    });

    // Export
    document.getElementById('export-btn').addEventListener('click', () => {
      window.location.href = `/api/admin/analytics/export?section=${currentTab}&period=${currentPeriod}`;
    });
  });
</script>
@endpush
