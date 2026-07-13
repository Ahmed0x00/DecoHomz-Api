<!-- DASHBOARD TAB -->
<div id="tab-dashboard" class="portal-tab active">
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon bg-emerald-light text-emerald">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="stat-info">
        <div class="stat-label">Available Balance</div>
        <div class="stat-val" id="stat-balance">EGP 0</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon bg-amber-light text-amber">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div class="stat-info">
        <div class="stat-label">Pending Payouts</div>
        <div class="stat-val" id="stat-pending-balance">EGP 0</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon bg-rose-light text-rose">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div class="stat-info">
        <div class="stat-label">Violation Points</div>
        <div class="stat-val" id="stat-violations">0</div>
      </div>
    </div>
  </div>

  <div class="portal-card">
    <div class="card-header">
      <h2 class="card-title">Recent Transactions</h2>
      <button class="btn btn-secondary btn-sm" onclick="VendorPortal.switchTab('finances', document.querySelector('[data-tab=finances]'))">View All</button>
    </div>
    <div class="table-responsive">
      <table class="data-table" id="dashboard-tx-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="4" class="text-center text-muted">No transactions loaded.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
