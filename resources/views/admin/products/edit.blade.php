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
                  <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Color</label>
                  <input type="text" name="color" id="field-color" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
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
            <div class="admin-card-header">
              <div class="admin-card-title">Product Images</div>
              <span id="images-count" style="font-size:12px;color:#888;"></span>
            </div>
            <div style="padding:24px;">
              <div id="image-gallery" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:12px;">
              </div>
              <p id="no-images" style="display:none;color:#aaa;font-size:13px;text-align:center;padding:20px;">No images uploaded yet.</p>
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
              <a href="/admin/products" style="display:block;width:100%;padding:12px;background:#f3f4f6;color:#666;border:1px solid #e5e5e5;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;text-align:center;text-decoration:none;text-align:center;">
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
    var pathParts = window.location.pathname.split('/');
    productId = pathParts[pathParts.length - 2];
    await Promise.all([loadProduct(), loadCategories()]);
  });

  async function loadProduct() {
    try {
      var res = await API.get('/admin/products/' + productId);
      productData = res.data || res;
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
      categories = res.data || res || [];
      var select = document.getElementById('field-category_id');
      categories.forEach(function(c) {
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
    document.getElementById('field-color').value = p.color || '';
    document.getElementById('field-dimensions').value = p.dimensions || '';
    document.getElementById('field-is_featured').checked = !!(p.is_featured == 1 || p.is_featured === true);
    document.getElementById('field-is_active').checked = !!(p.is_active == 1 || p.is_active === true);

    // Category is set by loadCategories() after options are populated

    renderImageGallery(p.images || []);
  }

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
      var isPrimary = img.is_primary == 1 || img.is_primary === true;
      return '<div style="position:relative;border:1px solid #e5e5e5;border-radius:8px;overflow:hidden;background:#fafafa;">' +
        '<img src="' + (img.url || img.image_url || '/img/placeholder.svg') + '" style="width:100%;height:100px;object-fit:cover;display:block;" onerror="this.src=\'/img/placeholder.svg\'">' +
        (isPrimary ? '<div style="position:absolute;top:4px;left:4px;background:#c9a96e;color:#fff;font-size:10px;font-weight:600;padding:2px 6px;border-radius:4px;">Primary</div>' : '') +
        '<div style="padding:6px;display:flex;gap:4px;">' +
          (isPrimary ? '' : '<button onclick="setPrimary(' + img.id + ')" style="flex:1;padding:4px;font-size:10px;border:1px solid #e5e5e5;background:#fff;border-radius:4px;cursor:pointer;color:#666;">Set Primary</button>') +
          '<button onclick="deleteImage(' + img.id + ')" style="flex:1;padding:4px;font-size:10px;border:1px solid #fee2e2;background:#fee2e2;color:#991b1b;border-radius:4px;cursor:pointer;">Delete</button>' +
        '</div>' +
      '</div>';
    }).join('');
  }

  window.setPrimary = function(imageId) {
    API.patch('/admin/products/' + productId + '/images/' + imageId + '/set-primary').then(function() {
      showToast('Primary image updated.', 'success');
      var images = productData.images || [];
      images.forEach(function(img) {
        img.is_primary = (img.id === imageId) ? 1 : 0;
      });
      productData.images = images;
      renderImageGallery(images);
    }).catch(function() {
      showToast('Failed to set primary image.', 'error');
    });
  };

  window.deleteImage = function(imageId) {
    if (!confirm('Delete this image?')) return;
    API.del('/admin/products/' + productId + '/images/' + imageId).then(function() {
      showToast('Image deleted.', 'success');
      var images = productData.images || [];
      images = images.filter(function(img) { return img.id !== imageId; });
      productData.images = images;
      renderImageGallery(images);
    }).catch(function() {
      showToast('Failed to delete image.', 'error');
    });
  };

  window.saveProduct = function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-btn');
    btn.textContent = 'Saving...';
    btn.disabled = true;

    var payload = {
      name: document.getElementById('field-name').value,
      description: document.getElementById('field-description').value,
      price: parseFloat(document.getElementById('field-price').value) || 0,
      old_price: parseFloat(document.getElementById('field-old_price').value) || null,
      stock: parseInt(document.getElementById('field-stock').value) || 0,
      category_id: parseInt(document.getElementById('field-category_id').value) || null,
      material: document.getElementById('field-material').value || null,
      color: document.getElementById('field-color').value || null,
      dimensions: document.getElementById('field-dimensions').value || null,
      is_featured: document.getElementById('field-is_featured').checked ? 1 : 0,
      is_active: document.getElementById('field-is_active').checked ? 1 : 0
    };

    API.put('/admin/products/' + productId, payload).then(function() {
      showToast('Product saved successfully.', 'success');
      setTimeout(function() { window.location.href = '/admin/products'; }, 800);
    }).catch(function() {
      showToast('Failed to save product.', 'error');
      btn.textContent = 'Save Changes';
      btn.disabled = false;
    });
  };
})();
</script>
@endsection
