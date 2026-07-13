<!-- PRODUCTS TAB -->
<div id="tab-products" class="portal-tab" style="display:none;">
  <div class="portal-card">
    <div class="card-header">
      <div>
        <h2 class="card-title">Your Products</h2>
        <p class="card-subtitle">Track review status, fix admin notes, and resubmit revised items.</p>
      </div>
      <button class="btn btn-primary" onclick="VendorPortal.openProductForm()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add New Product
      </button>
    </div>
    <div class="product-workbench">
      <div class="product-status-filters" id="product-status-filters"></div>
    </div>
    <div id="products-container" class="products-grid">
      <!-- Dynamic products list -->
    </div>
  </div>
</div>

