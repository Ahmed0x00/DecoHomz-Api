@extends('admin.layouts.app')

@section('title', 'Add Product')
@section('page_title', 'Add Product')

@section('content')

<div id="add-product-app">
  <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
    <a href="/admin/products" style="color:#888;font-size:13px;text-decoration:none;">← Products</a>
    <span style="color:#e5e5e5;">|</span>
    <span style="font-size:13px;color:#666;">Add New Product</span>
  </div>

  <form id="product-form" onsubmit="createProduct(event)">
    <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">
      <!-- Left Column -->
      <div>
        <!-- Basic Info -->
        <div class="admin-card" style="margin-bottom:24px;">
          <div class="admin-card-header"><div class="admin-card-title">Basic Information</div></div>
          <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
            <div>
              <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Product Name *</label>
              <input type="text" name="name" id="field-name" required style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;" placeholder="e.g. Modern Fabric Sofa">
            </div>
            <div>
              <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Description</label>
              <textarea name="description" id="field-description" rows="4" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;resize:vertical;" placeholder="Product description..."></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Price (EGP) *</label>
                <input type="number" name="price" id="field-price" required min="0" step="0.01" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;" placeholder="0.00">
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Old Price (EGP)</label>
                <input type="number" name="old_price" id="field-old_price" min="0" step="0.01" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;" placeholder="0.00">
              </div>
            </div>
          </div>
        </div>

        <!-- Category & Stock -->
        <div class="admin-card" style="margin-bottom:24px;">
          <div class="admin-card-header"><div class="admin-card-title">Category & Stock</div></div>
          <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
            <div>
              <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Category *</label>
              <select name="category_id" id="field-category_id" required style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
                <option value="">Select a category</option>
              </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Stock Quantity</label>
                <input type="number" name="stock" id="field-stock" min="0" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;" placeholder="0">
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Star Rating</label>
                <select name="stars" id="field-stars" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
                  <option value="">No rating</option>
                  <option value="1">★☆☆☆☆ (1)</option>
                  <option value="2">★★☆☆☆ (2)</option>
                  <option value="3">★★★☆☆ (3)</option>
                  <option value="4">★★★★☆ (4)</option>
                  <option value="5">★★★★★ (5)</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Details -->
        <div class="admin-card" style="margin-bottom:24px;">
          <div class="admin-card-header"><div class="admin-card-title">Product Details</div></div>
          <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Material</label>
                <input type="text" name="material" id="field-material" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Upholstery</label>
                <input type="text" name="upholstery" id="field-upholstery" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
              </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Dimensions</label>
                <input type="text" name="dimensions" id="field-dimensions" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;" placeholder="e.g. 200x90x85 cm">
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Weight</label>
                <input type="text" name="weight" id="field-weight" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;" placeholder="e.g. 45 kg">
              </div>
            </div>
          </div>
        </div>

        <!-- Images -->
        <div class="admin-card" style="margin-bottom:24px;">
          <div class="admin-card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div class="admin-card-title">Product Images</div>
            <span id="images-count" style="font-size:12px;color:#888;">0 images</span>
          </div>
          <div style="padding:24px;">
            <div id="image-drop-zone" style="border:2px dashed #e5e7eb;border-radius:10px;padding:32px;text-align:center;cursor:pointer;transition:all 0.2s;" onclick="document.getElementById('field-images').click()">
              <svg style="width:32px;height:32px;color:#cbd5e1;margin:0 auto 12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              <p style="font-size:13px;color:#94a3b8;margin:0 0 4px;">Click or drag images here</p>
              <p style="font-size:11px;color:#cbd5e1;margin:0;">JPEG, PNG, JPG, WEBP — max 2MB each</p>
            </div>
            <input type="file" name="images[]" id="field-images" multiple accept="image/*" style="display:none;" onchange="handleImageSelect(this)">
            <div id="image-preview" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:16px;"></div>
          </div>
        </div>

        <!-- Colors -->
        <div class="admin-card" style="margin-bottom:24px;">
          <div class="admin-card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div class="admin-card-title">Colors</div>
            <span id="colors-count" style="font-size:12px;color:#888;">0 colors</span>
          </div>
          <div style="padding:24px;">
            <div id="color-list" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:20px;"></div>
            <div style="border-top:1px solid #f3f4f6;padding-top:20px;">
              <p style="font-size:12px;font-weight:600;color:#888;margin:0 0 14px;text-transform:uppercase;letter-spacing:0.5px;">Add Color</p>
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:10px;align-items:end;">
                <div>
                  <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px;">Color Name *</label>
                  <input type="text" id="new-color-name" placeholder="e.g. Navy Blue" style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;">
                </div>
                <div>
                  <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px;">Hex Code *</label>
                  <div style="display:flex;gap:6px;align-items:center;">
                    <input type="color" id="new-color-preview" value="#1a365d" onchange="document.getElementById('new-color-hex').value=this.value" style="width:36px;height:36px;border:none;background:none;cursor:pointer;border-radius:6px;padding:0;">
                    <input type="text" id="new-color-hex" placeholder="#1a365d" value="#1a365d" style="flex:1;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;font-family:monospace;">
                  </div>
                </div>
                <div>
                  <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px;">Price Modifier (EGP)</label>
                  <input type="number" id="new-color-price" placeholder="0 = base price" step="1" style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;">
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
          <div class="admin-card-header"><div class="admin-card-title">Status</div></div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:12px;">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;">
              <input type="checkbox" id="field-is_active" checked style="width:16px;height:16px;accent-color:#1e293b;">
              <span>Active (visible in store)</span>
            </label>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;">
              <input type="checkbox" id="field-is_featured" style="width:16px;height:16px;accent-color:#1e293b;">
              <span>Featured product</span>
            </label>
          </div>
        </div>

        <!-- Badge -->
        <div class="admin-card" style="margin-bottom:24px;">
          <div class="admin-card-header"><div class="admin-card-title">Badge</div></div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:12px;">
            <div>
              <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Badge Label</label>
              <input type="text" name="badge" id="field-badge" maxlength="50" style="width:100%;padding:9px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;" placeholder="e.g. New Arrival">
            </div>
            <div>
              <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Badge Color</label>
              <select name="badge_color" id="field-badge_color" style="width:100%;padding:9px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
                <option value="">None</option>
                <option value="red">Red</option>
                <option value="orange">Orange</option>
                <option value="green">Green</option>
                <option value="blue">Blue</option>
                <option value="purple">Purple</option>
                <option value="pink">Pink</option>
              </select>
            </div>
          </div>
        </div>

        <button type="submit" id="submit-btn" style="width:100%;background:#1e293b;color:#fff;border:none;padding:14px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;">
          + Create Product
        </button>
        <a href="/admin/products" style="display:block;text-align:center;margin-top:10px;color:#888;font-size:13px;text-decoration:none;">Cancel</a>
      </div>
    </div>
  </form>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  var categories = [];
  var pendingColors = [];

  document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    setupDropZone();
  });

  async function loadCategories() {
    try {
      var res = await API.get('/admin/categories');
      categories = res.data || res.categories || res || [];
      if (!Array.isArray(categories)) categories = [];
      var select = document.getElementById('field-category_id');
      categories.forEach(function(c) {
        var opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.name;
        select.appendChild(opt);
      });
    } catch(e) {
      console.warn('Failed to load categories', e);
    }
  }

  // ─── Image handling ─────────────────────────────────────────
  function setupDropZone() {
    var dropZone = document.getElementById('image-drop-zone');
    var fileInput = document.getElementById('field-images');
    if (!dropZone || !fileInput) return;

    dropZone.addEventListener('dragover', function(e) {
      e.preventDefault();
      dropZone.style.borderColor = '#1e293b';
      dropZone.style.background = '#f8fafc';
    });
    dropZone.addEventListener('dragleave', function() {
      dropZone.style.borderColor = '#e5e7eb';
      dropZone.style.background = '';
    });
    dropZone.addEventListener('drop', function(e) {
      e.preventDefault();
      dropZone.style.borderColor = '#e5e7eb';
      dropZone.style.background = '';
      fileInput.files = e.dataTransfer.files;
      handleImageSelect(fileInput);
    });
  }

  var selectedImages = [];

  window.handleImageSelect = function(input) {
    if (!input.files) return;
    for (var i = 0; i < input.files.length; i++) {
      selectedImages.push(input.files[i]);
    }
    renderImagePreviews();
  };

  function renderImagePreviews() {
    var preview = document.getElementById('image-preview');
    var count = document.getElementById('images-count');
    preview.innerHTML = '';
    count.textContent = selectedImages.length + ' image' + (selectedImages.length !== 1 ? 's' : '');
    selectedImages.forEach(function(file, index) {
      var reader = new FileReader();
      reader.onload = function(e) {
        var div = document.createElement('div');
        div.style.position = 'relative';
        div.style.width = '90px';
        div.innerHTML = '<img src="' + e.target.result + '" style="width:90px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;">' +
          '<button type="button" onclick="removeImage(' + index + ')" style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;background:#ef4444;color:#fff;border:none;border-radius:50%;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;">×</button>';
        preview.appendChild(div);
      };
      reader.readAsDataURL(file);
    });
  }

  window.removeImage = function(index) {
    selectedImages.splice(index, 1);
    renderImagePreviews();
  };

  // ─── Color management ────────────────────────────────────────
  window.addNewColor = function() {
    var name = document.getElementById('new-color-name').value.trim();
    var hex = document.getElementById('new-color-hex').value.trim();
    var price = parseFloat(document.getElementById('new-color-price').value) || 0;
    var stock = parseInt(document.getElementById('new-color-stock').value) || 0;
    if (!name) { showToast('Color name is required.', 'error'); return; }
    if (!/^#[0-9A-Fa-f]{6}$/.test(hex)) { showToast('Enter a valid hex code like #1a365d', 'error'); return; }
    if (pendingColors.some(function(c) { return c.name.toLowerCase() === name.toLowerCase(); })) {
      showToast('This color already exists.', 'error'); return;
    }
    pendingColors.push({ name: name, hex_code: hex.toUpperCase(), price_modifier: price, stock: stock });
    document.getElementById('new-color-name').value = '';
    document.getElementById('new-color-hex').value = '#1a365d';
    document.getElementById('new-color-preview').value = '#1a365d';
    document.getElementById('new-color-price').value = '';
    document.getElementById('new-color-stock').value = '';
    renderColorList();
    showToast('Color added!', 'success');
  };

  window.removeColor = function(index) {
    pendingColors.splice(index, 1);
    renderColorList();
  };

  function renderColorList() {
    var list = document.getElementById('color-list');
    var countEl = document.getElementById('colors-count');
    if (!pendingColors.length) {
      list.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:20px;color:#aaa;font-size:13px;">No colors added yet.</div>';
      countEl.textContent = '0 colors';
      return;
    }
    countEl.textContent = pendingColors.length + ' color' + (pendingColors.length !== 1 ? 's' : '');
    list.innerHTML = pendingColors.map(function(c, i) {
      var priceText = c.price_modifier > 0 ? '+' + c.price_modifier + ' EGP' : (c.price_modifier < 0 ? c.price_modifier + ' EGP' : 'Base price');
      return '<div style="padding:10px 12px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;">' +
        '<div style="display:flex;align-items:center;gap:10px;">' +
          '<div style="width:26px;height:26px;border-radius:50%;background:' + escHtml(c.hex_code) + ';border:2px solid #e5e7eb;flex-shrink:0;"></div>' +
          '<div style="flex:1;min-width:0;">' +
            '<div style="font-size:13px;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escHtml(c.name) + '</div>' +
            '<div style="font-size:11px;color:#64748b;">' + priceText + ' · Stock: ' + c.stock + '</div>' +
          '</div>' +
          '<button type="button" onclick="removeColor(' + i + ')" style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;">Remove</button>' +
        '</div></div>';
    }).join('');
  }

  function escHtml(s) {
    if (s == null) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  // ─── Form submit ────────────────────────────────────────────
  window.createProduct = function(e) {
    e.preventDefault();
    var btn = document.getElementById('submit-btn');
    btn.textContent = 'Creating...';
    btn.disabled = true;

    var form = document.getElementById('product-form');
    var formData = new FormData(form);

    // Replace file list with current selected images
    var imageInput = document.getElementById('field-images');
    // Remove existing images[] entries and re-add
    for (var i = 0; i < selectedImages.length; i++) {
      formData.delete('images[]');
      formData.append('images[]', selectedImages[i]);
    }

    // Attach pending colors as JSON
    formData.set('colors_json', JSON.stringify(pendingColors));

    API.post('/admin/products', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }).then(function() {
      showToast('Product created successfully.', 'success');
      setTimeout(function() { window.location.href = '/admin/products'; }, 800);
    }).catch(function() {
      showToast('Failed to create product.', 'error');
      btn.textContent = '+ Create Product';
      btn.disabled = false;
    });
  };

  renderColorList();
})();
</script>
@endsection
