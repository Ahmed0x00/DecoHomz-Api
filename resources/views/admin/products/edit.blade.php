@extends('admin.layouts.app')

@section('title', 'Edit Product')
@section('page_title', 'Edit Product')

@section('content')

<div id="edit-product-app">
  <!-- Loading State -->
  <div id="loading-state" style="text-align:center;padding:60px;color:#aaa;">
    Loading product data...
  </div>

  <!-- Error State -->
  <div id="error-state" style="display:none;text-align:center;padding:60px;">
    <p style="color:#ef4444;font-size:15px;margin-bottom:16px;">Failed to load product. Product may not exist.</p>
    <a href="/admin/products" style="color:#c9a96e;font-size:13px;">← Back to Products</a>
  </div>

  <!-- Edit Form -->
  <div id="edit-form" style="display:none;">
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
      <a href="/admin/products" style="color:#888;font-size:13px;text-decoration:none;">← Products</a>
      <span style="color:#e5e5e5;">|</span>
      <span style="font-size:13px;color:#666;">Edit Product</span>
    </div>

    <form id="product-form" onsubmit="saveProduct(event)">
      <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;">

        <!-- Left Column: Main Fields -->
        <div>
          <div class="admin-card" style="margin-bottom:24px;">
            <div class="admin-card-header">
              <div class="admin-card-title">Basic Information</div>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;gap:20px;">
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Product Name *</label>
                <input type="text" name="name" id="field-name" required style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Description</label>
                <textarea name="description" id="field-description" rows="5" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;resize:vertical;font-family:inherit;"></textarea>
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                  <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Price (EGP) *</label>
                  <input type="number" name="price" id="field-price" step="0.01" min="0" required style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
                </div>
                <div>
                  <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Old Price (EGP)</label>
                  <input type="number" name="old_price" id="field-old_price" step="0.01" min="0" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
                </div>
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Category *</label>
                <select name="category_id" id="field-category_id" required style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
                  <option value="">Select Category</option>
                </select>
              </div>
            </div>
          </div>

          <div class="admin-card" style="margin-bottom:24px;">
            <div class="admin-card-header">
              <div class="admin-card-title">Additional Details</div>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;gap:20px;">
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div>
                  <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Material</label>
                  <input type="text" name="material" id="field-material" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
                </div>
                <div>
                  <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Dimensions</label>
                  <input type="text" name="dimensions" id="field-dimensions" placeholder="e.g. 120x60x45 cm" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
                </div>
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                  <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Stock Quantity *</label>
                  <input type="number" name="stock" id="field-stock" min="0" required style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
                </div>
              </div>
            </div>
          </div>

          <!-- Image Gallery -->
          <div class="admin-card" style="margin-bottom:24px;">
            <div class="admin-card-header" style="display:flex;justify-content:space-between;align-items:center;">
              <div class="admin-card-title">Product Images</div>
              <span id="images-count" style="font-size:12px;color:#888;"></span>
            </div>
            <div style="padding:24px;">
              <div id="image-gallery" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:16px;margin-bottom:24px;">
                <!-- Existing images here -->
              </div>
              
              <div style="border-top:1px solid #f3f4f6;padding-top:24px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:12px;">Upload New Images</label>
                <div id="new-image-previews" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(80px, 1fr));gap:12px;margin-bottom:12px;"></div>
                <div style="position:relative;border:2px dashed #e5e5e5;border-radius:10px;padding:24px;text-align:center;transition:border-color 0.2s;" id="drop-zone">
                  <input type="file" id="field-images" multiple accept="image/*" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;" onchange="handleNewImageSelect(event)">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="1.5" style="margin-bottom:8px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                  <div style="font-size:12px;color:#666;font-weight:500;">Add more images</div>
                </div>
              </div>
              
              <p id="no-images" style="display:none;color:#aaa;font-size:13px;text-align:center;padding:20px;">No images uploaded yet.</p>
            </div>
          </div>

        <!-- Colors -->
        <div class="admin-card" style="margin-bottom:24px;" id="colors-card">
          <div class="admin-card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div class="admin-card-title">Colors</div>
            <span id="colors-count" style="font-size:12px;color:#888;"></span>
          </div>
          <div style="padding:24px;">
            <div id="color-list" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-bottom:20px;"></div>
            <div style="border-top:1px solid #f3f4f6;padding-top:20px;">
              <p style="font-size:12px;font-weight:600;color:#888;margin:0 0 14px;text-transform:uppercase;letter-spacing:0.5px;">Add New Color</p>
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:10px;align-items:end;">
                <div>
                  <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px;">Color Name *</label>
                  <input type="text" id="new-color-name" placeholder="e.g. Navy Blue" style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;">
                </div>
                <div>
                  <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px;">Hex Code *</label>
                  <div style="display:flex;gap:6px;align-items:center;">
                    <input type="color" id="new-color-preview" value="#1a365d" onchange="document.getElementById('new-color-hex').value=this.value" style="width:36px;height:36px;border:none;background:none;cursor:pointer;border-radius:6px;">
                    <input type="text" id="new-color-hex" placeholder="#1a365d" value="#1a365d" style="flex:1;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;font-family:monospace;">
                  </div>
                </div>
                <div>
                  <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px;">Price Modifier (EGP)</label>
                  <input type="number" id="new-color-price" placeholder="0 = same as base" step="1" style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;">
                </div>
                <div>
                  <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px;">Stock</label>
                  <input type="number" id="new-color-stock" placeholder="0" min="0" style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;">
                </div>
                <button type="button" onclick="addNewColor()" style="background:#1e293b;color:#fff;border:none;padding:9px 16px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;">+ Add</button>
              </div>
            </div>
          </div>
        </div>
      </div>

        <!-- Right Column: Status & Actions -->
        <div>
          <div class="admin-card" style="margin-bottom:24px;">
            <div class="admin-card-header">
              <div class="admin-card-title">Status</div>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="is_featured" id="field-is_featured" style="width:16px;height:16px;cursor:pointer;">
                <span style="font-size:13px;font-weight:500;">Featured Product</span>
              </label>
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="is_active" id="field-is_active" checked style="width:16px;height:16px;cursor:pointer;">
                <span style="font-size:13px;font-weight:500;">Active (Visible in Store)</span>
              </label>
            </div>
          </div>

          <!-- Save Actions -->
          <div class="admin-card">
            <div style="padding:24px;display:flex;flex-direction:column;gap:12px;">
              <button type="submit" id="save-btn" style="width:100%;padding:12px;background:#c9a96e;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                Save Changes
              </button>
              <a href="/admin/products" style="display:block;width:100%;padding:12px;background:#f3f4f6;color:#666;border:1px solid #e5e5e5;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;text-align:center;text-decoration:none;">
                Cancel
              </a>
            </div>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  var productId = null;
  var productData = null;
  var categories = [];

  document.addEventListener('DOMContentLoaded', async function() {
    var pathParts = window.location.pathname.split('/').filter(Boolean);
    // URL: /admin/products/{id}/edit
    // Parts: ['admin', 'products', 'id', 'edit']
    productId = pathParts[pathParts.length - 2];
    if (!productId || isNaN(productId)) {
        // Try fallback if URL is /admin/products/edit/{id} or similar
        productId = pathParts[pathParts.length - 1];
    }
    await Promise.all([loadProduct(), loadCategories()]);
  });

  async function loadProduct() {
    try {
      var res = await API.get('/admin/products/' + productId);
      productData = res.data || res.product || res;
      if (!productData || !productData.id) throw new Error('Not found');
      populateForm(productData);
      document.getElementById('loading-state').style.display = 'none';
      document.getElementById('edit-form').style.display = 'block';
    } catch(e) {
      document.getElementById('loading-state').style.display = 'none';
      document.getElementById('error-state').style.display = 'block';
    }
  }

  async function loadCategories() {
    try {
      var res = await API.get('/admin/categories');
      var cats = res.data || res.categories || res || [];
      if (!Array.isArray(cats)) cats = [];
      var select = document.getElementById('field-category_id');
      cats.forEach(function(c) {
        var opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.name;
        select.appendChild(opt);
      });
      if (productData && productData.category_id) {
        select.value = productData.category_id;
      }
    } catch(e) {
      console.warn('Failed to load categories', e);
    }
  }

  function populateForm(p) {
    document.getElementById('field-name').value = p.name || '';
    document.getElementById('field-description').value = p.description || '';
    document.getElementById('field-price').value = p.price || '';
    document.getElementById('field-old_price').value = p.old_price || '';
    document.getElementById('field-stock').value = p.stock || 0;
    document.getElementById('field-material').value = p.material || '';
    document.getElementById('field-dimensions').value = p.dimensions || '';
    document.getElementById('field-is_featured').checked = !!(p.is_featured == 1 || p.is_featured === true);
    document.getElementById('field-is_active').checked = !!(p.is_active == 1 || p.is_active === true);

    // Category is set by loadCategories() after options are populated

    renderImageGallery(p.images || []);
  }

  var removedImageIds = [];
  var primaryImageId = null;

  function renderImageGallery(images) {
    var gallery = document.getElementById('image-gallery');
    var noImages = document.getElementById('no-images');
    var count = document.getElementById('images-count');

    if (!images || images.length === 0) {
      gallery.innerHTML = '';
      noImages.style.display = 'block';
      count.textContent = '0 images';
      return;
    }

    noImages.style.display = 'none';
    count.textContent = images.length + ' image' + (images.length !== 1 ? 's' : '');

    gallery.innerHTML = images.map(function(img) {
      var isPrimary = (primaryImageId ? primaryImageId == img.id : (img.is_primary == 1 || img.is_primary === true));
      if (isPrimary && !primaryImageId) primaryImageId = img.id;
      
      return '<div id="img-card-' + img.id + '" style="position:relative;border:1px solid #e5e5e5;border-radius:8px;overflow:hidden;background:#fafafa;">' +
        '<img src="' + (img.image_url || img.url || '/img/placeholder.svg') + '" style="width:100%;height:100px;object-fit:cover;display:block;" onerror="this.src=\'/img/placeholder.svg\'">' +
        '<div id="primary-badge-' + img.id + '" style="position:absolute;top:4px;left:4px;background:#c9a96e;color:#fff;font-size:10px;font-weight:600;padding:2px 6px;border-radius:4px;' + (isPrimary ? '' : 'display:none;') + '">Primary</div>' +
        '<div style="padding:6px;display:flex;gap:4px;">' +
          '<button type="button" onclick="markAsPrimary(' + img.id + ')" id="primary-btn-' + img.id + '" style="flex:1;padding:4px;font-size:10px;border:1px solid #e5e5e5;background:#fff;border-radius:4px;cursor:pointer;color:#666;' + (isPrimary ? 'display:none;' : '') + '">Set Primary</button>' +
          '<button type="button" onclick="queueDeleteImage(' + img.id + ')" style="flex:1;padding:4px;font-size:10px;border:1px solid #fee2e2;background:#fee2e2;color:#991b1b;border-radius:4px;cursor:pointer;">Delete</button>' +
        '</div>' +
      '</div>';
    }).join('');
  }

  window.markAsPrimary = function(imageId) {
    primaryImageId = imageId;
    // Update UI badges
    productData.images.forEach(img => {
      var badge = document.getElementById('primary-badge-' + img.id);
      var btn = document.getElementById('primary-btn-' + img.id);
      if (badge) badge.style.display = (img.id == imageId ? 'block' : 'none');
      if (btn) btn.style.display = (img.id == imageId ? 'none' : 'block');
    });
  };

  window.queueDeleteImage = function(imageId) {
    if (!confirm('Are you sure you want to remove this image? It will be deleted when you save.')) return;
    removedImageIds.push(imageId);
    document.getElementById('img-card-' + imageId).style.display = 'none';
    
    // If we deleted the primary image, pick another one
    if (primaryImageId == imageId) {
      var remaining = productData.images.filter(img => !removedImageIds.includes(img.id));
      if (remaining.length > 0) markAsPrimary(remaining[0].id);
      else primaryImageId = null;
    }
    
    var visibleCount = productData.images.length - removedImageIds.length;
    document.getElementById('images-count').textContent = visibleCount + ' image' + (visibleCount !== 1 ? 's' : '');
  };

  window.handleNewImageSelect = function(e) {
    var files = e.target.files;
    var previewContainer = document.getElementById('new-image-previews');
    previewContainer.innerHTML = '';
    
    if (files.length > 0) {
      Array.from(files).forEach((file) => {
        var reader = new FileReader();
        reader.onload = function(e) {
          var div = document.createElement('div');
          div.style = 'position:relative;aspect-ratio:1;border-radius:6px;overflow:hidden;border:1px solid #e5e5e5;';
          div.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
          previewContainer.appendChild(div);
        };
        reader.readAsDataURL(file);
      });
    }
  };

  window.saveProduct = function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-btn');
    btn.textContent = 'Saving...';
    btn.disabled = true;

    var formData = new FormData();
    formData.append('_method', 'PUT'); // Trick for Laravel to handle multipart PUT
    formData.append('name', document.getElementById('field-name').value);
    formData.append('description', document.getElementById('field-description').value);
    formData.append('price', document.getElementById('field-price').value);
    formData.append('old_price', document.getElementById('field-old_price').value || '');
    formData.append('stock', document.getElementById('field-stock').value);
    formData.append('category_id', document.getElementById('field-category_id').value);
    formData.append('material', document.getElementById('field-material').value);
    formData.append('dimensions', document.getElementById('field-dimensions').value);
    formData.append('is_featured', document.getElementById('field-is_featured').checked ? 1 : 0);
    formData.append('is_active', document.getElementById('field-is_active').checked ? 1 : 0);
    
    if (primaryImageId) {
      formData.append('primary_image_id', primaryImageId);
    }
    
    removedImageIds.forEach(id => {
      formData.append('remove_images[]', id);
    });
    
    var imageInput = document.getElementById('field-images');
    if (imageInput.files.length > 0) {
      for (var i = 0; i < imageInput.files.length; i++) {
        formData.append('images[]', imageInput.files[i]);
      }
    }

    // Use POST with _method PUT because browsers/servers often struggle with multipart/form-data on real PUT
    API.post('/admin/products/' + productId, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }).then(function() {
      showToast('Product updated successfully.', 'success');
      setTimeout(function() { window.location.href = '/admin/products'; }, 800);
    }).catch(function() {
      showToast('Failed to save product.', 'error');
      btn.textContent = 'Save Changes';
      btn.disabled = false;
    });
  };

  // ─── Color Management ───────────────────────────────────────
  var productColors = [];

  async function loadColors() {
    try {
      var res = await API.get('/admin/products/' + productId + '/colors');
      productColors = res.colors || [];
      renderColorList();
    } catch(e) {
      console.warn('Failed to load colors', e);
    }
  }

  function renderColorList() {
    var list = document.getElementById('color-list');
    var countEl = document.getElementById('colors-count');
    var card = document.getElementById('colors-card');
    if (!card) return;
    card.style.display = 'block';

    if (!productColors.length) {
      list.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:#aaa;font-size:13px;">No colors yet. Add your first color below.</div>';
      countEl.textContent = '0 colors';
      return;
    }

    countEl.textContent = productColors.length + ' color' + (productColors.length !== 1 ? 's' : '');
    list.innerHTML = productColors.map(function(c) {
      var isActive = c.is_active !== false && c.is_active !== 0;
      var priceText = c.price_modifier > 0 ? '+' + c.price_modifier + ' EGP' : (c.price_modifier < 0 ? c.price_modifier + ' EGP' : 'Base price');
      var cardBg = isActive ? '#f8fafc' : '#f3f4f6';
      var badgeHtml = isActive
        ? '<span style="padding:2px 8px;background:#d1fae5;color:#065f46;font-size:10px;font-weight:700;border-radius:10px;text-transform:uppercase;">Active</span>'
        : '<span style="padding:2px 8px;background:#f3f4f6;color:#6b7280;font-size:10px;font-weight:700;border-radius:10px;text-transform:uppercase;">Inactive</span>';

      return '<div style="padding:12px;background:' + cardBg + ';border:1px solid #e5e7eb;border-radius:10px;">' +
        '<div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">' +
          '<div style="width:30px;height:30px;border-radius:50%;background:' + escHtml(c.hex_code) + ';border:2px solid #e5e7eb;flex-shrink:0;" title="' + escHtml(c.hex_code) + '"></div>' +
          '<div style="flex:1;min-width:0;">' +
            '<div style="font-size:13px;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escHtml(c.name) + '</div>' +
            '<div style="font-size:11px;color:#64748b;">' + priceText + ' · Stock: ' + c.stock + '</div>' +
          '</div>' +
          badgeHtml +
        '</div>' +
        '<div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:6px;align-items:end;">' +
          '<div>' +
            '<input type="color" value="' + escHtml(c.hex_code) + '" onchange="updateColorField(' + c.id + ', \'hex_code\', this.value); document.getElementById(\'hex-display-\' + ' + c.id + ').value=this.value" style="width:100%;height:28px;border:none;background:none;cursor:pointer;border-radius:4px;">' +
          '</div>' +
          '<div>' +
            '<input type="number" id="price-' + c.id + '" value="' + c.price_modifier + '" onchange="updateColorField(' + c.id + ', \'price_modifier\', parseFloat(this.value))" placeholder="Price mod." step="1" style="width:100%;padding:4px 6px;border:1px solid #e5e7eb;border-radius:4px;font-size:11px;">' +
          '</div>' +
          '<div>' +
            '<input type="number" id="stock-' + c.id + '" value="' + c.stock + '" onchange="updateColorField(' + c.id + ', \'stock\', parseInt(this.value))" placeholder="Stock" min="0" style="width:100%;padding:4px 6px;border:1px solid #e5e7eb;border-radius:4px;font-size:11px;">' +
          '</div>' +
          '<div style="display:flex;gap:4px;">' +
            '<button onclick="toggleColorActive(' + c.id + ')" style="padding:4px 8px;font-size:11px;font-weight:600;border-radius:4px;cursor:pointer;border:1px solid ' + (isActive ? '#fde68a' : '#86efac') + ';background:' + (isActive ? '#fef3c7' : '#dcfce7') + ';color:' + (isActive ? '#a16207' : '#065f46') + ';">' + (isActive ? 'Off' : 'On') + '</button>' +
            '<button onclick="deleteColor(' + c.id + ')" style="padding:4px 8px;font-size:11px;font-weight:600;border-radius:4px;cursor:pointer;background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;">Del</button>' +
          '</div>' +
        '</div>' +
      '</div>';
    }).join('');
  }

  window.updateColorField = function(id, field, value) {
    var color = productColors.find(function(c) { return c.id == id; });
    if (!color) return;
    var payload = {};
    payload[field] = value;
    API.put('/admin/products/' + productId + '/colors/' + id, payload).then(function() {
      color[field] = value;
      renderColorList();
      showToast('Color updated.', 'success');
    }).catch(function() {
      showToast('Failed to update color.', 'error');
    });
  };

  window.toggleColorActive = function(id) {
    API.patch('/admin/products/' + productId + '/colors/' + id + '/toggle').then(function(res) {
      var color = productColors.find(function(c) { return c.id == id; });
      if (color) color.is_active = res.is_active;
      renderColorList();
      showToast('Color status updated.', 'success');
    }).catch(function() {
      showToast('Failed to toggle color.', 'error');
    });
  };

  window.deleteColor = function(id) {
    if (!confirm('Delete this color?')) return;
    API.del('/admin/products/' + productId + '/colors/' + id).then(function() {
      productColors = productColors.filter(function(c) { return c.id != id; });
      renderColorList();
      showToast('Color deleted.', 'success');
    }).catch(function() {
      showToast('Failed to delete color.', 'error');
    });
  };

  window.addNewColor = function() {
    var name = document.getElementById('new-color-name').value.trim();
    var hex = document.getElementById('new-color-hex').value.trim();
    var price = parseFloat(document.getElementById('new-color-price').value) || 0;
    var stock = parseInt(document.getElementById('new-color-stock').value) || 0;

    if (!name) { showToast('Color name is required.', 'error'); return; }
    if (!/^#[0-9A-Fa-f]{6}$/.test(hex)) { showToast('Enter a valid hex code like #1a365d', 'error'); return; }

    API.post('/admin/products/' + productId + '/colors', {
      name: name,
      hex_code: hex,
      price_modifier: price,
      stock: stock
    }).then(function(color) {
      productColors.push(color);
      document.getElementById('new-color-name').value = '';
      document.getElementById('new-color-hex').value = '#1a365d';
      document.getElementById('new-color-preview').value = '#1a365d';
      document.getElementById('new-color-price').value = '';
      document.getElementById('new-color-stock').value = '';
      renderColorList();
      showToast('Color added!', 'success');
    }).catch(function() {
      showToast('Failed to add color.', 'error');
    });
  };

  function escHtml(s) {
    if (s == null) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // Load colors after product loads
  var _origPopulate = populateForm;
  populateForm = function(p) {
    _origPopulate(p);
    loadColors();
  };
})();
</script>
@endsection
