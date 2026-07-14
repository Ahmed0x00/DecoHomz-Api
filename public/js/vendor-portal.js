/**
 * Vendor Portal JavaScript Logic
 */
const VendorPortal = (function () {

  let categories = [];
  let activeVendor = null;
  let pendingImages = [];
  let productsCache = [];
  let activeProductFilter = 'all';
  let dimensionsList = [];
  let materialsList = [];
  let productColors = [];

  async function init() {
    // Check authentication and vendor status
    if (!Auth.token()) {
      window.location.href = '/auth';
      return;
    }

    try {
      const userRes = await API.get('/auth/user');
      const user = userRes.data || userRes;

      if (user) {
        localStorage.setItem('dh_user', JSON.stringify(user));
      }

      if (!user || user.role !== 'vendor' || !user.vendor || (user.vendor.status !== 'active' && user.vendor.status !== 'suspended')) {
        window.location.href = '/';
        return;
      }
      activeVendor = user.vendor;
    } catch (e) {
      window.location.href = '/auth';
      return;
    }

    // Load initial data
    loadDashboard();
    loadCategories();
    setupEventListeners();
  }

  function setupEventListeners() {
    const docForm = document.getElementById('doc-upload-form');
    if (docForm) {
      docForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        await uploadDocument();
      });
    }

    const prodForm = document.getElementById('product-form');
    if (prodForm) {
      prodForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        await saveProduct();
      });
    }
  }

  function switchTab(tabId, el) {
    document.querySelectorAll('.portal-tab').forEach(div => {
      div.style.display = 'none';
      div.classList.remove('active');
    });

    const targetTab = document.getElementById('tab-' + tabId);
    if (targetTab) {
      targetTab.style.display = 'block';
      // tiny delay for animation
      setTimeout(() => targetTab.classList.add('active'), 10);
    }

    document.querySelectorAll('.portal-menu a').forEach(a => a.classList.remove('active'));
    if (el) el.classList.add('active');

    // Update titles
    const titles = {
      dashboard: { title: 'Dashboard Overview', sub: 'Manage your furniture catalog and track earnings.' },
      products: { title: 'Products Catalog', sub: 'Add new items or request updates for published furniture.' },
      finances: { title: 'Finances & Ledger', sub: 'Review sales, balance breakdowns, and payouts.' },
      documents: { title: 'Shop Documents', sub: 'Verify your business credibility to maintain active status.' },
      violations: { title: 'Policy Violations', sub: 'Ensure your products and services comply with our marketplace standards.' },
      policy: { title: 'DecoHomz Policies', sub: 'Marketplace operational, financial, and quality assurance rules.' }
    };

    if (titles[tabId]) {
      document.getElementById('page-title').textContent = titles[tabId].title;
      document.getElementById('page-subtitle').textContent = titles[tabId].sub;
    }

    // Lazy load tab data
    if (tabId === 'dashboard') loadDashboard();
    if (tabId === 'products') loadProducts();
    if (tabId === 'finances') loadFinances();
    if (tabId === 'documents') loadDocuments();
    if (tabId === 'violations') loadViolations();
  }

  async function loadDashboard() {
    try {
      const res = await API.get('/vendor/finances');
      const balances = res.balances || {};
      document.getElementById('stat-balance').textContent = formatMoney(balances.available_balance ?? balances.available ?? 0);
      document.getElementById('stat-pending-balance').textContent = formatMoney(balances.pending_clearance ?? balances.pending ?? 0);

      const txs = res.transactions?.data || res.transactions || [];
      const tbody = document.querySelector('#dashboard-tx-table tbody');
      if (!txs.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No recent transactions.</td></tr>';
      } else {
        tbody.innerHTML = txs.slice(0, 5).map(tx => `
          <tr>
            <td>${new Date(tx.created_at).toLocaleDateString()}</td>
            <td>${esc(tx.description)}</td>
            <td style="font-weight:600; color: ${Number(tx.amount) >= 0 ? '#059669' : '#dc2626'}">
              ${formatSignedMoney(tx.amount)}
            </td>
            <td><span class="badge badge-${financeStatusColor(tx.status)}">${esc(formatLabel(tx.status))}</span></td>
          </tr>
        `).join('');
      }

      // violations
      const vRes = await API.get('/vendor/violations');
      const vList = vRes.data || vRes || [];
      const totalPts = vList.reduce((sum, v) => sum + (v.points || 0), 0);
      document.getElementById('stat-violations').textContent = totalPts;

    } catch (e) { }
  }

  async function loadCategories() {
    try {
      const res = await API.get('/categories');
      categories = res.categories || res.data || res || [];
      const select = document.getElementById('p-category');
      if (select) {
        select.innerHTML = '<option value="">Select Category</option>' +
          categories.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');
      }
    } catch (e) { }
  }

  async function loadProducts() {
    try {
      const res = await API.get('/vendor/products');
      productsCache = getPaginatedItems(res, 'products');
      renderProductFilters();
      renderProductGrid();
    } catch (e) {
      showToast('Failed to load products', 'error');
    }
  }

  function renderProductFilters() {
    const filters = document.getElementById('product-status-filters');
    if (!filters) return;

    const counts = productsCache.reduce((acc, product) => {
      const status = product.vendor_status || 'draft';
      acc[status] = (acc[status] || 0) + 1;
      if (status === 'changes_requested' || status === 'rejected') {
        acc.needs_action = (acc.needs_action || 0) + 1;
      }
      return acc;
    }, { all: productsCache.length, needs_action: 0 });

    const filterItems = [
      ['all', 'All'],
      ['needs_action', 'Needs Action'],
      ['draft', 'Drafts'],
      ['submitted', 'Submitted'],
      ['approved', 'Warehouse'],
      ['published', 'Published'],
      ['rejected', 'Rejected'],
    ];

    filters.innerHTML = filterItems.map(([key, label]) => `
      <button type="button" class="status-filter ${activeProductFilter === key ? 'active' : ''}" onclick="VendorPortal.filterProducts('${key}')">
        <span>${esc(label)}</span>
        <strong>${counts[key] || 0}</strong>
      </button>
    `).join('');
  }

  function filterProducts(filter) {
    activeProductFilter = filter;
    renderProductFilters();
    renderProductGrid();
  }

  function renderProductGrid() {
    const container = document.getElementById('products-container');
    if (!container) return;

    const products = productsCache.filter(product => {
      const status = product.vendor_status || 'draft';
      if (activeProductFilter === 'all') return true;
      if (activeProductFilter === 'needs_action') return status === 'changes_requested' || status === 'rejected';
      return status === activeProductFilter;
    });

    if (!productsCache.length) {
      container.innerHTML = '<div class="product-empty-state">No products yet. Add your first product as a draft, then submit it for review.</div>';
      return;
    }

    if (!products.length) {
      container.innerHTML = '<div class="product-empty-state">No products in this status.</div>';
      return;
    }

    container.innerHTML = products.map(renderProductCard).join('');
  }

  function renderProductCard(p) {
    const pImg = p.images && p.images.length ? p.images[0].url : '';
    const status = p.vendor_status || 'draft';
    const color = productStatusColor(status);
    const feedback = getProductFeedback(p);
    const needsAttention = status === 'changes_requested' || status === 'rejected';
    const actionText = needsAttention ? 'Revise and resubmit' : productActionText(status);

    return `
      <button type="button" class="vendor-product-item vendor-product-${esc(status)}" onclick="VendorPortal.openProductForm(${p.id})">
        <div class="vendor-product-media">
            ${pImg ? `<img src="${esc(pImg)}" alt="">` : `<span>No image</span>`}
        </div>
        <div class="vendor-product-body">
          <div class="vendor-product-topline">
            <div>
              <div class="product-title">${esc(p.name)}</div>
              <div class="product-subline">Updated ${formatDate(p.updated_at || p.created_at)}</div>
            </div>
            <span class="badge badge-${color}">${esc(formatLabel(status))}</span>
          </div>
          ${needsAttention
        ? `<div class="product-feedback ${status === 'rejected' ? 'danger' : 'warning'}">
                <strong>${status === 'rejected' ? 'Rejected' : 'Changes requested'}</strong>
                <span>${esc(feedback || 'Revise the product and submit it again.')}</span>
              </div>`
        : `<div class="product-muted">${esc(status === 'submitted' ? 'Waiting for admin review' : actionText)}</div>`
      }
        </div>
        <div class="vendor-product-side">
          <div class="product-price-cell">
            <span>Vendor price</span>
            <strong>${formatMoney(p.vendor_price)}</strong>
          </div>
          <span class="product-action-pill">${esc(actionText)}</span>
        </div>
      </button>
    `;
  }

  function openProductForm(productId = null) {
    document.getElementById('form-product-id').value = productId || '';
    document.getElementById('product-form').reset();
    document.getElementById('product-form-title').textContent = productId ? 'Edit Product Details' : 'Add New Product';
    document.getElementById('btn-delete-product').style.display = 'none';

    document.getElementById('product-review-feedback').style.display = 'none';
    document.getElementById('product-images-preview').innerHTML = '';

    pendingImages = [];
    dimensionsList = [];
    materialsList = [];
    productColors = [];
    renderSpecsUI();
    renderColorList();
    renderProductFeedback(null);
    configureProductModalActions(null);

    if (productId) {
      loadProductForEditing(productId);
    }
    VendorPortal.switchTab('product-form');
  }

  async function loadProductForEditing(id) {
    try {
      const res = await API.get('/vendor/products/' + id);
      const p = res.product || res;
      renderProductFeedback(p);
      configureProductModalActions(p);

      const deleteBtn = document.getElementById('btn-delete-product');
      if (p.vendor_status !== 'approved' && p.vendor_status !== 'active' && p.vendor_status !== 'published') {
        deleteBtn.style.display = 'block';
      } else {
        deleteBtn.style.display = 'none';
      }

      document.getElementById('form-product-id').value = p.id;
      document.getElementById('p-name').value = p.name;
      document.getElementById('p-category').value = p.category_id;
      document.getElementById('p-description').value = p.description;
      document.getElementById('p-price').value = p.vendor_price || p.price || '';
      document.getElementById('p-stock').value = p.stock || 0;
      document.getElementById('p-lead').value = p.specification?.production_time_days || '';
      document.getElementById('p-warranty').value = p.specification?.warranty_months || '';
      document.getElementById('p-care').value = p.specification?.care_instructions || '';

      dimensionsList = [];
      materialsList = [];
      if (p.specifications) {
        const dims = p.specifications.Dimensions || {};
        for (let k in dims) dimensionsList.push({ key: k, value: dims[k] });
        const mats = p.specifications.Materials || {};
        for (let k in mats) materialsList.push({ key: k, value: mats[k] });
      }
      renderSpecsUI();

      productColors = (p.colors || []).map(c => ({
        name: c.name, hex_code: c.hex_code, price_modifier: c.price_modifier, stock: c.stock
      }));
      renderColorList();

      const preview = document.getElementById('product-images-preview');
      preview.innerHTML = '';
      if (p.images && p.images.length) {
        // Find general images vs color images
        const generalImages = p.images.filter(img => !img.product_color_id);
        preview.innerHTML = generalImages.map(img => `
          <div class="img-preview-item" style="position:relative;">
            <img src="${escHtml(img.url)}" alt="Product Image">
            ${img.is_primary
            ? '<div style="position:absolute;bottom:4px;left:4px;background:#10b981;color:#fff;font-size:10px;padding:2px 6px;border-radius:4px;font-weight:bold;">Primary</div>'
            : `<button type="button" onclick="VendorPortal.setPrimaryImage(${p.id}, ${img.id})" style="position:absolute;bottom:4px;left:4px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:4px;font-size:10px;padding:2px 6px;cursor:pointer;">Set Primary</button>`
          }
            <button type="button" class="img-delete-btn" onclick="VendorPortal.deleteProductImage(${p.id}, ${img.id})">&times;</button>
          </div>
        `).join('');

        // Put color images in their respective containers
        p.images.forEach(img => {
          if (!img.product_color_id) return;
          const color = (p.colors || []).find(c => c.id === img.product_color_id);
          if (!color) return;
          const safeName = color.name.replace(/\s+/g, '-');
          const container = document.getElementById('color-preview-' + safeName);
          if (container) {
            const div = document.createElement('div');
            div.style = 'position:relative;width:60px;height:60px;border-radius:6px;overflow:hidden;border:1px solid #e5e5e5;box-shadow:0 1px 2px rgba(0,0,0,0.05);';
            div.innerHTML = `<img src="${escHtml(img.url)}" style="width:100%;height:100%;object-fit:cover;">
              ${img.is_primary
                ? '<div style="position:absolute;bottom:2px;left:2px;background:#10b981;color:#fff;font-size:8px;padding:1px 4px;border-radius:3px;font-weight:bold;">Primary</div>'
                : `<button type="button" onclick="VendorPortal.setPrimaryImage(${p.id}, ${img.id})" style="position:absolute;bottom:2px;left:2px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:3px;font-size:8px;padding:1px 4px;cursor:pointer;">Primary</button>`
              }
              <button type="button" onclick="VendorPortal.deleteProductImage(${p.id}, ${img.id})" style="position:absolute;top:2px;right:2px;width:16px;height:16px;background:#ef4444;color:#fff;border:none;border-radius:50%;font-size:10px;line-height:1;cursor:pointer;">×</button>`;
            container.insertBefore(div, container.lastElementChild); // Insert before the Add button
          }
        });
      }

    } catch (e) {
      showToast('Error loading product details', 'error');
    }
  }

  async function handleImageSelection(input, colorName) {
    const id = document.getElementById('form-product-id').value;
    if (!input.files || !input.files.length) return;

    if (id) {
      // Editing product: upload images immediately
      for (const file of input.files) {
        const formData = new FormData();
        formData.append('image', file);
        if (colorName) formData.append('color_name', colorName);
        try {
          showToast('Uploading image...', 'info');
          await API.post('/vendor/products/' + id + '/images', formData);
          showToast('Image uploaded successfully', 'success');
        } catch (err) {
          showToast(err.data?.message || 'Failed to upload image', 'error');
        }
      }
      loadProductForEditing(id);
      loadProducts();
    } else {
      // New product: store in pendingImages array
      for (const file of input.files) {
        pendingImages.push({
          file: file,
          colorName: colorName || null,
          url: URL.createObjectURL(file),
          isPrimary: pendingImages.length === 0 // Make first image primary by default
        });
      }
      renderPendingImages();
    }
    input.value = '';
  }

  function renderPendingImages() {
    const generalPreview = document.getElementById('product-images-preview');
    if (generalPreview) generalPreview.innerHTML = '';

    // Clear color preview containers of pending images (keep existing)
    productColors.forEach(c => {
      const safeName = c.name.replace(/\s+/g, '-');
      const container = document.getElementById('color-preview-' + safeName);
      if (container) {
        const pendingNodes = container.querySelectorAll('.pending-img-node');
        pendingNodes.forEach(n => n.remove());
      }
    });

    pendingImages.forEach((img, idx) => {
      const url = img.url;
      if (img.colorName) {
        const safeName = img.colorName.replace(/\s+/g, '-');
        const container = document.getElementById('color-preview-' + safeName);
        if (container) {
          const div = document.createElement('div');
          div.className = 'pending-img-node';
          div.style = 'position:relative;width:60px;height:60px;border-radius:6px;overflow:hidden;border:1px solid #e5e5e5;opacity:0.8;';
          div.innerHTML = `<img src="${url}" alt="Pending Image" style="width:100%;height:100%;object-fit:cover;">
            ${img.isPrimary
              ? '<div style="position:absolute;bottom:2px;left:2px;background:#10b981;color:#fff;font-size:8px;padding:1px 4px;border-radius:3px;font-weight:bold;">Primary</div>'
              : `<button type="button" onclick="VendorPortal.setPendingPrimary(${idx})" style="position:absolute;bottom:2px;left:2px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:3px;font-size:8px;padding:1px 4px;cursor:pointer;">Primary</button>`
            }
            <button type="button" class="img-delete-btn" onclick="VendorPortal.removePendingImage(${idx})" style="position:absolute;top:4px;right:4px;width:16px;height:16px;font-size:12px;line-height:1;">&times;</button>`;
          container.insertBefore(div, container.lastElementChild);
        }
      } else {
        if (generalPreview) {
          const div = document.createElement('div');
          div.className = 'img-preview-item';
          div.style = "position:relative;";
          div.innerHTML = `<img src="${url}" alt="Pending Image">
            ${img.isPrimary
              ? '<div style="position:absolute;bottom:4px;left:4px;background:#10b981;color:#fff;font-size:10px;padding:2px 6px;border-radius:4px;font-weight:bold;">Primary</div>'
              : `<button type="button" onclick="VendorPortal.setPendingPrimary(${idx})" style="position:absolute;bottom:4px;left:4px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:4px;font-size:10px;padding:2px 6px;cursor:pointer;">Set Primary</button>`
            }
            <button type="button" class="img-delete-btn" onclick="VendorPortal.removePendingImage(${idx})">&times;</button>`;
          generalPreview.appendChild(div);
        }
      }
    });
  }

  function setPendingPrimary(idx) {
    pendingImages.forEach((p, i) => p.isPrimary = (i === idx));
    renderPendingImages();
  }

  function removePendingImage(idx) {
    pendingImages.splice(idx, 1);
    renderPendingImages();
  }

  async function deleteProductImage(productId, imageId) {
    if (!confirm('Are you sure you want to delete this image?')) return;
    try {
      await API.delete(`/vendor/products/${productId}/images/${imageId}`);
      showToast('Image deleted', 'success');
      loadProductForEditing(productId);
    } catch (err) {
      showToast('Failed to delete image', 'error');
    }
  }

  async function setPrimaryImage(productId, imageId) {
    try {
      showToast('Setting primary image...', 'info');
      await API.patch(`/vendor/products/${productId}/images/${imageId}/set-primary`);
      showToast('Primary image updated', 'success');
      loadProductForEditing(productId);
    } catch (err) {
      showToast(err.data?.message || 'Failed to update primary image', 'error');
    }
  }

  function getProductPayload() {
    return {
      name: document.getElementById('p-name').value,
      category_id: document.getElementById('p-category').value,
      description: document.getElementById('p-description').value,
      vendor_price: document.getElementById('p-price').value,
      stock: document.getElementById('p-stock').value || 0,
      colors_json: JSON.stringify(productColors),
      specifications_json: getSpecsJson(),
      production_time_days: document.getElementById('p-lead').value,
      warranty_months: document.getElementById('p-warranty').value,
      care_instructions: document.getElementById('p-care').value,
    };
  }

  function getSpecsJson() {
    const specs = { Dimensions: {}, Materials: {} };
    dimensionsList.forEach(item => {
      if (item.key.trim()) specs.Dimensions[item.key.trim()] = item.value.trim();
    });
    materialsList.forEach(item => {
      if (item.key.trim()) specs.Materials[item.key.trim()] = item.value.trim();
    });
    return JSON.stringify(specs);
  }

  async function uploadPendingImages(pId) {
    if (pendingImages.length === 0) return;

    showToast(`Uploading ${pendingImages.length} image(s)...`, 'info');
    for (let i = 0; i < pendingImages.length; i++) {
      const formData = new FormData();
      formData.append('image', pendingImages[i].file);
      if (pendingImages[i].colorName) formData.append('color_name', pendingImages[i].colorName);
      if (pendingImages[i].isPrimary) formData.append('is_primary', '1');
      try {
        await API.post('/vendor/products/' + pId + '/images', formData);
      } catch (err) {
        showToast('Failed to upload some images', 'error');
      }
    }
    pendingImages = [];
  }

  async function saveProduct(options = {}) {
    const id = document.getElementById('form-product-id').value;
    const payload = getProductPayload();
    const saveBtn = document.getElementById('btn-save-draft');
    const submitBtn = document.getElementById('btn-submit-review');
    const originalSaveText = saveBtn.textContent;
    const originalSubmitText = submitBtn.textContent;

    try {
      saveBtn.disabled = true;
      submitBtn.disabled = true;
      saveBtn.textContent = options.submitAfterSave ? 'Saving...' : 'Saving...';
      if (options.submitAfterSave) submitBtn.textContent = 'Submitting...';

      let productId = id;
      if (id) {
        await API.put('/vendor/products/' + id, payload);
      } else {
        const res = await API.post('/vendor/products', {
          ...payload,
          submit: !!options.submitAfterSave
        });
        const p = res.product || res;
        productId = p.id;
        document.getElementById('form-product-id').value = productId;
      }

      await uploadPendingImages(productId);

      if (options.submitAfterSave && id) {
        await API.post('/vendor/products/' + productId + '/submit', {});
      }

      saveBtn.disabled = false;
      submitBtn.disabled = false;
      saveBtn.textContent = originalSaveText;
      submitBtn.textContent = originalSubmitText;

      VendorPortal.switchTab('products');
      showToast(options.submitAfterSave ? 'Product submitted for review!' : 'Product saved successfully', 'success');
      loadProducts();
      return productId;
    } catch (err) {
      saveBtn.disabled = false;
      submitBtn.disabled = false;
      saveBtn.textContent = originalSaveText;
      submitBtn.textContent = originalSubmitText;
      showToast(err.data?.message || (options.submitAfterSave ? 'Failed to submit product' : 'Failed to save product'), 'error');
      return null;
    }
  }

  async function submitProductForReview() {
    await saveProduct({ submitAfterSave: true });
  }

  async function deleteProduct() {
    const id = document.getElementById('form-product-id').value;
    if (!id) return;

    if (!confirm('Are you sure you want to completely delete this product? This action cannot be undone.')) {
      return;
    }

    try {
      showToast('Deleting product...', 'info');
      await API.delete('/vendor/products/' + id);
      showToast('Product deleted successfully', 'success');
      VendorPortal.switchTab('products');
      loadProducts();
    } catch (err) {
      showToast(err.data?.message || 'Failed to delete product', 'error');
    }
  }

  async function loadFinances() {
    try {
      const finances = await API.get('/vendor/finances');
      const txs = finances.transactions?.data || finances.transactions || [];
      const tbody = document.querySelector('#finances-tx-table tbody');

      if (!txs.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No transactions found.</td></tr>';
      } else {
        tbody.innerHTML = txs.map(tx => `
          <tr>
            <td>${new Date(tx.created_at).toLocaleDateString()}</td>
            <td><span class="text-muted" style="font-size:12px;">${esc(tx.reference || (tx.order_item_id ? 'Order item #' + tx.order_item_id : '-'))}</span></td>
            <td><span class="badge badge-${financeTypeColor(tx.type, tx.amount)}">${esc(formatLabel(tx.type))}</span></td>
            <td>${esc(tx.description)}</td>
            <td style="font-weight:600; color:${Number(tx.amount) >= 0 ? '#065f46' : '#b91c1c'};">${formatSignedMoney(tx.amount)}</td>
          </tr>
        `).join('');
      }
    } catch (e) { }
  }

  async function loadDocuments() {
    try {
      const docs = await API.get('/vendor/documents');
      const container = document.getElementById('documents-list-container');

      if (!docs || !docs.length) {
        container.innerHTML = '<div class="text-muted text-center py-4">No documents uploaded yet.</div>';
        return;
      }

      container.innerHTML = '<div style="display:flex; flex-direction:column; gap:16px;">' + docs.map(doc => {
        const typeMap = {
          commercial_register: 'Commercial Register',
          tax_card: 'Tax Card',
          id_card: 'ID Card',
          bank_letter: 'Bank Letter',
          other: 'Other'
        };
        const statusColors = {
          pending: 'warning',
          verified: 'success',
          rejected: 'danger'
        };
        const color = statusColors[doc.status] || 'neutral';
        const typeLabel = typeMap[doc.type] || doc.type;

        return `
          <div style="padding:16px; border:1px solid #e2e8f0; border-radius:8px; display:flex; justify-content:space-between; align-items:center; background:#fafafa;">
            <div>
              <div style="font-weight:600; color:#0f172a; margin-bottom:4px;">${esc(doc.label || typeLabel)}</div>
              <div style="font-size:12px; color:#64748b;">Type: ${typeLabel} ${doc.document_number ? '| Ref: ' + esc(doc.document_number) : ''}</div>
              ${doc.rejection_reason ? `<div style="font-size:12px; color:#dc2626; margin-top:6px; font-weight:500;">Rejection Reason: ${esc(doc.rejection_reason)}</div>` : ''}
            </div>
            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
              <span class="badge badge-${color}">${esc(doc.status)}</span>
              <a href="${doc.file_url || '/storage/' + doc.file_path}" target="_blank" class="btn btn-outline btn-sm">View File</a>
            </div>
          </div>
        `;
      }).join('') + '</div>';
    } catch (e) { }
  }

  async function uploadDocument() {
    const type = document.getElementById('doc-type').value;
    const label = document.getElementById('doc-label').value.trim();
    const docNum = document.getElementById('doc-number').value.trim();
    const fileInput = document.getElementById('doc-file');

    if (!fileInput.files.length) {
      showToast('Please select a file to upload.', 'error');
      return;
    }

    const formData = new FormData();
    formData.append('type', type);
    formData.append('file', fileInput.files[0]);
    if (label) formData.append('label', label);
    if (docNum) formData.append('document_number', docNum);

    try {
      await API.post('/vendor/documents', formData);
      showToast('Document uploaded successfully.', 'success');
      fileInput.value = '';
      document.getElementById('doc-label').value = '';
      document.getElementById('doc-number').value = '';
      loadDocuments();
    } catch (err) {
      showToast(err.data?.message || 'Failed to upload document', 'error');
    }
  }

  async function loadViolations() {
    try {
      const res = await API.get('/vendor/violations');
      const list = res.data || res || [];
      const tbody = document.querySelector('#violations-table tbody');

      if (!list.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No policy violations. Keep up the good work!</td></tr>';
      } else {
        tbody.innerHTML = list.map(v => `
          <tr>
            <td>${new Date(v.created_at).toLocaleDateString()}</td>
            <td style="font-weight:500;">${esc(v.violation_type.replace(/_/g, ' '))}</td>
            <td>${esc(v.description)}</td>
            <td style="color:#dc2626; font-weight:700;">${v.severity_points}</td>
            <td><span class="badge badge-danger">${esc(v.action_taken || 'Warning')}</span></td>
          </tr>
        `).join('');
      }
    } catch (e) { }
  }

  function esc(str) {
    if (!str) return '';
    return String(str).replace(/[&<>'"]/g, match => {
      const escape = {
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
      };
      return escape[match];
    });
  }

  function getPaginatedItems(res, collectionKey) {
    if (Array.isArray(res)) return res;
    if (Array.isArray(res?.data)) return res.data;
    if (Array.isArray(res?.data?.data)) return res.data.data;
    if (Array.isArray(res?.[collectionKey])) return res[collectionKey];
    if (Array.isArray(res?.[collectionKey]?.data)) return res[collectionKey].data;
    return [];
  }

  // ─── SPECS AND COLORS LOGIC ───────────────────────────────────────────────

  function renderSpecsUI() {
    const dimContainer = document.getElementById('dimensions-specs-container');
    const matContainer = document.getElementById('materials-specs-container');
    if (!dimContainer || !matContainer) return;

    dimContainer.innerHTML = dimensionsList.map((item, idx) => `
      <div style="display:flex;gap:12px;margin-bottom:8px;align-items:center;">
        <input type="text" value="\${escHtml(item.key)}" oninput="VendorPortal.updateSpec('dimensions', \${idx}, 'key', this.value)" placeholder="Spec Key" style="flex:1;padding:8px 10px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
        <input type="text" value="\${escHtml(item.value)}" oninput="VendorPortal.updateSpec('dimensions', \${idx}, 'value', this.value)" placeholder="Spec Value" style="flex:1;padding:8px 10px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
        <button type="button" onclick="VendorPortal.deleteSpec('dimensions', \${idx})" style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;padding:8px 12px;border-radius:6px;font-size:13px;cursor:pointer;">&times;</button>
      </div>
    `).join('');

    matContainer.innerHTML = materialsList.map((item, idx) => `
      <div style="display:flex;gap:12px;margin-bottom:8px;align-items:center;">
        <input type="text" value="\${escHtml(item.key)}" oninput="VendorPortal.updateSpec('materials', \${idx}, 'key', this.value)" placeholder="Spec Key" style="flex:1;padding:8px 10px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
        <input type="text" value="\${escHtml(item.value)}" oninput="VendorPortal.updateSpec('materials', \${idx}, 'value', this.value)" placeholder="Spec Value" style="flex:1;padding:8px 10px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
        <button type="button" onclick="VendorPortal.deleteSpec('materials', \${idx})" style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;padding:8px 12px;border-radius:6px;font-size:13px;cursor:pointer;">&times;</button>
      </div>
    `).join('');
  }

  function addSpec(type) {
    if (type === 'dimensions') dimensionsList.push({ key: '', value: '' });
    else materialsList.push({ key: '', value: '' });
    renderSpecsUI();
  }

  function updateSpec(type, idx, field, value) {
    if (type === 'dimensions') dimensionsList[idx][field] = value;
    else materialsList[idx][field] = value;
  }

  function deleteSpec(type, idx) {
    if (type === 'dimensions') dimensionsList.splice(idx, 1);
    else materialsList.splice(idx, 1);
    renderSpecsUI();
  }

  function addNewColor() {
    const name = document.getElementById('new-color-name').value.trim();
    const hex = document.getElementById('new-color-hex').value.trim();
    const price = parseFloat(document.getElementById('new-color-price').value) || 0;
    const stock = parseInt(document.getElementById('new-color-stock').value) || 0;

    if (!name) { showToast('Color name is required.', 'error'); return; }
    if (!/^#[0-9A-Fa-f]{6}$/.test(hex)) { showToast('Enter a valid hex code', 'error'); return; }
    if (productColors.some(c => c.name.toLowerCase() === name.toLowerCase())) {
      showToast('This color already exists.', 'error'); return;
    }

    productColors.push({ name, hex_code: hex.toUpperCase(), price_modifier: price, stock });

    document.getElementById('new-color-name').value = '';
    document.getElementById('new-color-hex').value = '#1a365d';
    document.getElementById('new-color-preview').value = '#1a365d';
    document.getElementById('new-color-price').value = '';
    document.getElementById('new-color-stock').value = '';

    renderColorList();
    renderPendingImages();
    showToast('Color added!', 'success');
  }

  function removeColor(index) {
    productColors.splice(index, 1);
    renderColorList();
    renderPendingImages();
  }

  function renderColorList() {
    const list = document.getElementById('color-list');
    const countEl = document.getElementById('colors-count');
    if (!list) return;

    if (!productColors.length) {
      list.innerHTML = '<div style="color:#aaa;font-size:13px;">No colors added yet.</div>';
      if (countEl) countEl.textContent = '0 colors';
      return;
    }
    if (countEl) countEl.textContent = productColors.length + ' color' + (productColors.length !== 1 ? 's' : '');

    list.innerHTML = productColors.map((c, i) => {
      const priceText = c.price_modifier > 0 ? '+' + c.price_modifier + ' EGP' : (c.price_modifier < 0 ? c.price_modifier + ' EGP' : 'Base price');
      const safeName = c.name.replace(/\\s+/g, '-');
      const jsSafeName = escHtml(c.name).replace(/'/g, "\\\\'");

      return `<div style="padding:16px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
          <div style="width:26px;height:26px;border-radius:50%;background:\${escHtml(c.hex_code)};border:2px solid #e5e7eb;flex-shrink:0;"></div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:600;color:#1e293b;">\${escHtml(c.name)}</div>
            <div style="font-size:11px;color:#64748b;">\${priceText} · Stock: \${c.stock}</div>
          </div>
          <button type="button" onclick="VendorPortal.removeColor(\${i})" style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;">Remove</button>
        </div>
        <div style="border-top:1px dashed #cbd5e1;padding-top:12px;">
          <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:8px;">Images for \${escHtml(c.name)}</div>
          <div style="display:flex;flex-wrap:wrap;gap:8px;" id="color-preview-\${safeName}">
            <div class="add-btn" onclick="document.getElementById('field-images-\${safeName}').click()" style="width:60px;height:60px;border:1px dashed #cbd5e1;border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#fff;flex-shrink:0;">
              <svg style="width:20px;height:20px;color:#cbd5e1;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
            </div>
          </div>
          <input type="file" id="field-images-\${safeName}" multiple accept="image/*" style="display:none;" onchange="VendorPortal.handleImageSelection(this, '\${jsSafeName}')">
        </div>
      </div>`;
    }).join('');
  }

  function escHtml(s) {
    if (s == null) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function getProductFeedback(product) {
    const review = product?.latest_review_history || product?.latestReviewHistory;
    if (!review) return '';
    return review.comment || '';
  }

  function renderProductFeedback(product) {
    const box = document.getElementById('product-review-feedback');
    if (!box) return;

    if (!product || !['changes_requested', 'rejected'].includes(product.vendor_status)) {
      box.style.display = 'none';
      box.innerHTML = '';
      return;
    }

    const isRejected = product.vendor_status === 'rejected';
    const feedback = getProductFeedback(product) || 'Please revise this product and contact support if you need more detail.';
    box.style.display = 'block';
    box.style.borderColor = isRejected ? '#fecdd3' : '#fde68a';
    box.style.background = isRejected ? '#fff1f2' : '#fffbeb';
    box.style.color = isRejected ? '#be123c' : '#92400e';
    box.innerHTML = '<strong>' + (isRejected ? 'Rejected' : 'Changes requested') + '</strong><br>' + esc(feedback);
  }

  function configureProductModalActions(product) {
    const saveBtn = document.getElementById('btn-save-draft');
    const submitBtn = document.getElementById('btn-submit-review');
    if (!saveBtn || !submitBtn) return;

    const status = product?.vendor_status || 'draft';
    const canEdit = !product || ['draft', 'rejected', 'changes_requested', 'published'].includes(status);
    const canSubmit = !product || ['draft', 'rejected', 'changes_requested'].includes(status);

    saveBtn.style.display = canEdit ? 'inline-flex' : 'none';
    submitBtn.style.display = canSubmit ? 'inline-flex' : 'none';
    submitBtn.textContent = status === 'rejected' || status === 'changes_requested' ? 'Resubmit for Review' : 'Submit for Review';
  }

  function productStatusColor(status) {
    const colors = {
      draft: 'neutral',
      submitted: 'info',
      approved: 'info',
      under_review: 'warning',
      changes_requested: 'warning',
      published: 'success',
      rejected: 'danger'
    };
    return colors[status] || 'neutral';
  }

  function productActionText(status) {
    const actions = {
      draft: 'Finish draft',
      submitted: 'Awaiting review',
      approved: 'Awaiting warehouse',
      under_review: 'Under review',
      published: 'Live in store',
      rejected: 'Revise and resubmit',
      changes_requested: 'Revise and resubmit'
    };
    return actions[status] || 'View details';
  }

  function formatMoney(value) {
    return 'EGP ' + Number(value || 0).toLocaleString();
  }

  function formatSignedMoney(value) {
    const amount = Number(value || 0);
    const sign = amount > 0 ? '+' : amount < 0 ? '-' : '';
    return sign + ' ' + formatMoney(Math.abs(amount));
  }

  function formatLabel(value) {
    return String(value || '').replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
  }

  function formatDate(value) {
    if (!value) return '-';
    return new Date(value).toLocaleDateString();
  }

  function financeStatusColor(status) {
    if (status === 'available' || status === 'paid') return 'success';
    if (status === 'pending') return 'warning';
    return 'neutral';
  }

  function financeTypeColor(type, amount) {
    if (type === 'payout' || Number(amount) < 0) return 'danger';
    if (type === 'sale_credit') return 'success';
    return 'neutral';
  }

  // Init on DOMContentLoaded
  document.addEventListener('DOMContentLoaded', init);

  return {
    switchTab,
    filterProducts,
    openProductForm,
    setPrimaryImage,
    setPendingPrimary,
    deleteProduct,
    handleImageSelection,
    removePendingImage,
    deleteProductImage,
    saveProduct,
    submitProductForReview,
    addSpec,
    updateSpec,
    deleteSpec,
    addNewColor,
    removeColor
  };

})();
