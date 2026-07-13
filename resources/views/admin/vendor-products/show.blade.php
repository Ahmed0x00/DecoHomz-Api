@extends('admin.layouts.app')

@section('title', 'Review Vendor Product')
@section('page_title', 'Review Vendor Product')

@push('styles')
<style>
  .vp-wrap {
    max-width: 1200px;
    margin: 0 auto;
  }

  /* Header */
  .vp-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
    margin-bottom: 28px;
  }

  .vp-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--color-text-muted);
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 12px;
    transition: color var(--duration-fast) ease;
  }
  .vp-back:hover { color: var(--color-primary); }
  .vp-back svg { width: 16px; height: 16px; stroke: currentColor; }

  .vp-title {
    font-size: 26px;
    font-weight: 700;
    color: var(--color-text);
    line-height: 1.25;
    letter-spacing: -0.02em;
    margin-bottom: 8px;
  }

  .vp-sub {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    color: var(--color-text-muted);
    font-size: 13px;
  }
  .vp-sub .dot { width: 4px; height: 4px; border-radius: 50%; background: var(--color-border); }

  .vp-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
  }

  /* Buttons */
  .vp-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border: none;
    padding: 11px 18px;
    border-radius: var(--radius-md);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--duration-normal) ease;
    white-space: nowrap;
  }
  .vp-btn svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; }
  .vp-btn:active { transform: scale(0.97); }

  .vp-btn-approve { background: var(--color-success); color: #fff; }
  .vp-btn-approve:hover { background: #246f3f; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(45,138,78,0.25); }

  .vp-btn-change { background: var(--color-warning); color: #fff; }
  .vp-btn-change:hover { background: #b9880f; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(212,160,23,0.25); }

  .vp-btn-reject { background: var(--color-error); color: #fff; }
  .vp-btn-reject:hover { background: #9e2e22; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(192,57,43,0.25); }

  .vp-btn-neutral { background: var(--color-primary); color: #fff; text-decoration: none; }
  .vp-btn-neutral:hover { background: var(--color-primary-light); transform: translateY(-1px); box-shadow: var(--shadow-md); }

  .vp-btn-danger { background: var(--color-error-bg); color: var(--color-error); }
  .vp-btn-danger:hover { background: var(--color-error); color: #fff; }

  /* Layout */
  .vp-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 24px;
    align-items: start;
  }

  .vp-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    overflow: hidden;
  }
  .vp-card + .vp-card { margin-top: 24px; }

  .vp-card-head {
    padding: 18px 22px;
    border-bottom: 1px solid var(--color-border-light);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }
  .vp-card-head h3 {
    font-size: 14px;
    font-weight: 700;
    color: var(--color-text);
    letter-spacing: 0.01em;
  }
  .vp-card-body { padding: 22px; }

  /* Image gallery */
  .vp-main-img {
    width: 100%;
    height: 380px;
    background: var(--color-bg-warm);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: var(--radius-md);
    border: 1px solid var(--color-border-light);
  }
  .vp-main-img img { width: 100%; height: 100%; object-fit: contain; }
  .vp-main-img .vp-empty { color: var(--color-text-faint); font-size: 13px; }

  .vp-thumbs {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(76px, 1fr));
    gap: 10px;
    margin-top: 14px;
  }
  .vp-thumb {
    aspect-ratio: 1;
    background: var(--color-bg-warm);
    border: 2px solid transparent;
    border-radius: var(--radius-sm);
    overflow: hidden;
    cursor: pointer;
    transition: border-color var(--duration-fast) ease;
    padding: 0;
  }
  .vp-thumb img { width: 100%; height: 100%; object-fit: cover; }
  .vp-thumb.active { border-color: var(--color-accent); }

  /* Description */
  .vp-desc {
    font-size: 14px;
    line-height: 1.75;
    color: var(--color-text-secondary);
  }

  /* Info rows */
  .vp-rows { display: flex; flex-direction: column; }
  .vp-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 12px 0;
    border-bottom: 1px solid var(--color-divider);
    font-size: 14px;
  }
  .vp-row:last-child { border-bottom: none; }
  .vp-row .k { color: var(--color-text-muted); font-weight: 500; flex-shrink: 0; }
  .vp-row .v { color: var(--color-text); font-weight: 600; text-align: right; }

  .vp-price { font-size: 18px; color: var(--color-accent-dark); font-weight: 800; }

  /* Spec grid */
  .vp-specs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px 24px;
  }
  .vp-spec {
    display: flex;
    flex-direction: column;
    gap: 3px;
  }
  .vp-spec .k {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--color-text-faint);
    font-weight: 600;
  }
  .vp-spec .v {
    font-size: 14px;
    color: var(--color-text);
    font-weight: 500;
  }
  .vp-empty-note { color: var(--color-text-faint); font-size: 13px; font-style: italic; }

  @media (max-width: 880px) {
    .vp-grid { grid-template-columns: 1fr; }
    .vp-specs { grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')

<div class="vp-wrap">

  <!-- Header -->
  <div class="vp-header">
    <div>
      <a href="/admin/vendor-products" class="vp-back">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back to Products
      </a>
      <h1 class="vp-title" id="product-name">Loading...</h1>
      <div class="vp-sub" id="product-sub">
        <span id="vp-vendor">—</span>
        <span class="dot"></span>
        <span id="vp-category">—</span>
      </div>
    </div>
    <div class="vp-actions" id="product-actions"></div>
  </div>

  <!-- Main grid -->
  <div class="vp-grid">

    <!-- Left: gallery + description -->
    <div>
      <div class="vp-card">
        <div class="vp-card-head"><h3>Images</h3></div>
        <div class="vp-card-body">
          <div class="vp-main-img" id="vp-main-img"><span class="vp-empty">No images provided.</span></div>
          <div class="vp-thumbs" id="vp-thumbs"></div>
        </div>
      </div>

      <div class="vp-card">
        <div class="vp-card-head"><h3>Description</h3></div>
        <div class="vp-card-body">
          <div class="vp-desc" id="vp-desc">Loading...</div>
        </div>
      </div>
    </div>

    <!-- Right: info + specs -->
    <div>
      <div class="vp-card">
        <div class="vp-card-head"><h3>Product Details</h3></div>
        <div class="vp-card-body">
          <div class="vp-rows" id="vp-info">Loading...</div>
        </div>
      </div>

      <div class="vp-card">
        <div class="vp-card-head"><h3>Specifications</h3></div>
        <div class="vp-card-body">
          <div class="vp-specs" id="vp-specs">Loading...</div>
        </div>
      </div>
    </div>

  </div>

</div>

@endsection

@section('extra_js')
<script>
(function() {
  var productId = {{ $id }};
  var currentProduct = null;
  var mainImgEl = null;

  document.addEventListener('DOMContentLoaded', function() {
    loadProduct();
  });

  async function loadProduct() {
    try {
      var res = await API.get('/admin/vendor-products/' + productId);
      currentProduct = res.data || res;

      document.getElementById('product-name').textContent = currentProduct.name;
      document.getElementById('vp-vendor').textContent = currentProduct.vendor ? currentProduct.vendor.company_name : 'Unknown Vendor';
      document.getElementById('vp-category').textContent = currentProduct.category ? currentProduct.category.name : 'Uncategorized';

      renderInfo(currentProduct);
      renderSpecs(currentProduct);
      renderDescription(currentProduct);
      renderImages(currentProduct);
      renderActions(currentProduct);
    } catch(e) {
      showToast('Failed to load product details', 'error');
    }
  }

  function getStatusBadge(status) {
    if (status === 'published') return '<span class="badge-status badge-active">Published</span>';
    if (status === 'approved') return '<span class="badge-status badge-pending" style="background:#dcfce7;color:#166534;">Approved for Warehouse</span>';
    if (status === 'submitted') return '<span class="badge-status badge-pending" style="background:#bae6fd;color:#0369a1;">Submitted</span>';
    if (status === 'under_review') return '<span class="badge-status badge-pending">Under Review</span>';
    if (status === 'changes_requested') return '<span class="badge-status badge-pending" style="background:#fef08a;color:#854d0e;">Changes Requested</span>';
    if (status === 'rejected') return '<span class="badge-status badge-rejected">Rejected</span>';
    return '<span class="badge-status badge-inactive">' + esc(status || 'Draft') + '</span>';
  }

  function renderInfo(p) {
    var price = parseFloat(p.vendor_price || p.price).toLocaleString();
    document.getElementById('vp-info').innerHTML = `
      <div class="vp-row"><span class="k">Status</span><span class="v">${getStatusBadge(p.vendor_status)}</span></div>
      <div class="vp-row"><span class="k">Vendor</span><span class="v">${esc(p.vendor ? p.vendor.company_name : 'Unknown')}</span></div>
      <div class="vp-row"><span class="k">Category</span><span class="v">${esc(p.category ? p.category.name : 'Unknown')}</span></div>
      <div class="vp-row"><span class="k">Price</span><span class="v vp-price">EGP ${price}</span></div>
    `;
  }

  function renderSpecs(p) {
    var spec = p.specification;
    var el = document.getElementById('vp-specs');
    if (!spec) {
      el.innerHTML = '<div class="vp-empty-note" style="grid-column:1/-1;">No specifications provided.</div>';
      return;
    }
    var dims = [spec.dimensions_length, spec.dimensions_width, spec.dimensions_height]
      .filter(function(v) { return v !== null && v !== undefined && v !== ''; })
      .join(' × ');
    if (dims) dims += ' cm';
    var colors = Array.isArray(spec.available_colors) ? spec.available_colors.join(', ') : (spec.available_colors || 'N/A');

    var rows = [
      ['Dimensions', dims || 'N/A'],
      ['Weight', spec.weight_kg ? spec.weight_kg + ' kg' : 'N/A'],
      ['Materials', spec.materials || 'N/A'],
      ['Colors', colors],
      ['Finishes', spec.finishes || 'N/A'],
      ['Production Time', (spec.production_time_days || 0) + ' days'],
      ['Warranty', spec.warranty_months ? spec.warranty_months + ' months' : 'N/A'],
      ['Packaging', spec.packaging_details || 'N/A'],
      ['Care Instructions', spec.care_instructions || 'N/A'],
      ['Notes', spec.additional_notes || 'N/A']
    ];

    el.innerHTML = rows.map(function(r) {
      return `<div class="vp-spec"><span class="k">${esc(r[0])}</span><span class="v">${esc(r[1])}</span></div>`;
    }).join('');
  }

  function renderDescription(p) {
    var el = document.getElementById('vp-desc');
    var desc = p.description || '';
    el.innerHTML = desc
      ? esc(desc).replace(/\n/g, '<br>')
      : '<span class="vp-empty-note">No description provided.</span>';
  }

  function renderImages(p) {
    var imgs = p.images || [];
    mainImgEl = document.getElementById('vp-main-img');
    var thumbsEl = document.getElementById('vp-thumbs');

    if (imgs.length === 0) {
      mainImgEl.innerHTML = '<span class="vp-empty">No images provided.</span>';
      thumbsEl.innerHTML = '';
      return;
    }

    mainImgEl.innerHTML = `<img src="${imgs[0].url}" alt="${esc(p.name || 'product')}" />`;
    thumbsEl.innerHTML = imgs.map(function(img, i) {
      return `<button class="vp-thumb ${i === 0 ? 'active' : ''}" data-url="${img.url}"><img src="${img.url}" alt="thumb" /></button>`;
    }).join('');

    thumbsEl.querySelectorAll('.vp-thumb').forEach(function(btn) {
      btn.addEventListener('click', function() {
        thumbsEl.querySelectorAll('.vp-thumb').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        mainImgEl.innerHTML = `<img src="${btn.dataset.url}" alt="${esc(p.name || 'product')}" />`;
      });
    });
  }

  function renderActions(p) {
    var html = '';
    if (['submitted', 'under_review', 'changes_requested'].includes(p.vendor_status)) {
      html += `<button class="vp-btn vp-btn-approve" onclick="reviewProduct('approved')">
        <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>Approve</button>`;
      html += `<button class="vp-btn vp-btn-change" onclick="reviewProduct('changes_requested')">
        <svg viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>Request Changes</button>`;
      html += `<button class="vp-btn vp-btn-reject" onclick="reviewProduct('rejected')">
        <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>Reject</button>`;
    } else if (p.vendor_status === 'approved') {
      html += `<a href="/admin/warehouse/inspect/${p.id}" class="vp-btn vp-btn-neutral">
        <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>Log Inspection</a>`;
      html += `<button class="vp-btn vp-btn-change" onclick="reviewProduct('changes_requested')">
        <svg viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>Request Changes</button>`;
      html += `<button class="vp-btn vp-btn-reject" onclick="reviewProduct('rejected')">
        <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>Reject</button>`;
    } else if (p.vendor_status === 'published') {
      html += `<button class="vp-btn vp-btn-danger" onclick="unpublishProduct()">
        <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>Unpublish</button>`;
    }
    document.getElementById('product-actions').innerHTML = html;
  }

  window.reviewProduct = async function(status) {
    var comment = '';
    if (status === 'changes_requested' || status === 'rejected') {
      comment = prompt('Please enter a comment/reason:');
      if (!comment) return;
    } else {
      if(!confirm('Approve this product? It will be expected at the warehouse next.')) return;
    }

    try {
      await API.patch('/admin/vendor-products/' + productId + '/review', { status: status, comment: comment });
      showToast('Product review updated', 'success');
      loadProduct();
    } catch(e) {
      showToast('Failed to review product', 'error');
    }
  };

  window.unpublishProduct = async function() {
    if(!confirm('Unpublish this product? It will be hidden from the store.')) return;
    try {
      await API.patch('/admin/vendor-products/' + productId + '/unpublish');
      showToast('Product unpublished', 'success');
      loadProduct();
    } catch(e) {
      showToast('Failed to unpublish product', 'error');
    }
  };

})();
</script>
@endsection
