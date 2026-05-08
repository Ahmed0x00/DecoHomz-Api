@extends('admin.layouts.app')

@section('title', 'Categories')
@section('page_title', 'Categories')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Categories</h1>
  <button onclick="openModal()" style="background:#c9a96e;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">+ Add Category</button>
</div>

<!-- Stats Cards -->
<div class="stat-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;margin-bottom:24px;">
  <div class="stat-card" style="background:#fff;padding:24px;border-radius:12px;border:1px solid #e5e5e5;display:flex;align-items:center;gap:16px;">
    <div style="width:48px;height:48px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
    </div>
    <div>
      <div id="stat-total" style="font-size:20px;font-weight:700;color:#1a1a1a;">—</div>
      <div style="font-size:13px;color:#666;">Total Categories</div>
    </div>
  </div>
  <div class="stat-card" style="background:#fff;padding:24px;border-radius:12px;border:1px solid #e5e5e5;display:flex;align-items:center;gap:16px;">
    <div style="width:48px;height:48px;background:#d1fae5;border-radius:10px;display:flex;align-items:center;justify-content:center;">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    </div>
    <div>
      <div id="stat-active" style="font-size:20px;font-weight:700;color:#1a1a1a;">—</div>
      <div style="font-size:13px;color:#666;">Active</div>
    </div>
  </div>
</div>

<!-- Categories Table -->
<div class="admin-card" style="background:#fff;border-radius:12px;border:1px solid #e5e5e5;overflow:hidden;">
  <table class="admin-table" style="width:100%;border-collapse:collapse;text-align:left;">
    <thead>
      <tr style="background:#f9fafb;border-bottom:1px solid #e5e5e5;">
        <th style="padding:16px 24px;font-size:12px;font-weight:600;color:#666;text-transform:uppercase;">Name</th>
        <th style="padding:16px 24px;font-size:12px;font-weight:600;color:#666;text-transform:uppercase;">Slug</th>
        <th style="padding:16px 24px;font-size:12px;font-weight:600;color:#666;text-transform:uppercase;">Products</th>
        <th style="padding:16px 24px;font-size:12px;font-weight:600;color:#666;text-transform:uppercase;">Order</th>
        <th style="padding:16px 24px;font-size:12px;font-weight:600;color:#666;text-transform:uppercase;">Status</th>
        <th style="padding:16px 24px;font-size:12px;font-weight:600;color:#666;text-transform:uppercase;width:100px;">Actions</th>
      </tr>
    </thead>
    <tbody id="categories-tbody">
      <tr class="loading-row"><td colspan="6" style="padding:40px;text-align:center;color:#aaa;">Loading...</td></tr>
    </tbody>
  </table>
</div>

<!-- Modal -->
<div id="category-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:#fff;width:100%;max-width:500px;border-radius:12px;overflow:hidden;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);">
    <div style="padding:20px 24px;border-bottom:1px solid #e5e5e5;display:flex;justify-content:between;align-items:center;">
      <h3 id="modal-title" style="font-size:18px;font-weight:700;color:#1a1a1a;">Add Category</h3>
      <button onclick="closeModal()" style="background:none;border:none;color:#666;cursor:pointer;font-size:20px;">&times;</button>
    </div>
    <form id="category-form" onsubmit="saveCategory(event)" style="padding:24px;display:flex;flex-direction:column;gap:20px;">
      <input type="hidden" id="category-id">
      <div>
        <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Category Name *</label>
        <input type="text" id="field-name" required style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
      </div>
      <div>
        <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Description</label>
        <textarea id="field-description" rows="3" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;resize:vertical;"></textarea>
      </div>
      <div>
        <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Category Image</label>
        <div id="image-preview" style="margin-bottom:10px;display:none;">
          <img src="" id="preview-img" style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #e5e5e5;">
        </div>
        <input type="file" id="field-image" accept="image/*" style="width:100%;padding:8px 0;font-size:12px;" onchange="previewImage(event)">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Sort Order</label>
          <input type="number" id="field-sort_order" value="0" min="0" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
        </div>
        <div style="display:flex;align-items:flex-end;">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:10px;">
            <input type="checkbox" id="field-is_active" checked style="width:16px;height:16px;">
            <span style="font-size:13px;font-weight:500;">Active</span>
          </label>
        </div>
      </div>
      <div style="margin-top:12px;display:flex;gap:12px;">
        <button type="submit" id="save-btn" style="flex:1;padding:12px;background:#c9a96e;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
          Save Category
        </button>
        <button type="button" onclick="closeModal()" style="flex:1;padding:12px;background:#f3f4f6;color:#666;border:1px solid #e5e5e5;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
  });

  async function loadCategories() {
    try {
      var res = await API.get('/admin/categories');
      var categories = res.data || res || [];
      renderTable(categories);
      renderStats(categories);
    } catch(e) {
      document.getElementById('categories-tbody').innerHTML = '<tr><td colspan="6" style="padding:40px;text-align:center;color:#ef4444;">Failed to load categories.</td></tr>';
    }
  }

  function renderStats(categories) {
    var active = categories.filter(c => c.is_active).length;
    document.getElementById('stat-total').textContent = categories.length;
    document.getElementById('stat-active').textContent = active;
  }

  function renderTable(categories) {
    var tbody = document.getElementById('categories-tbody');
    if (categories.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="padding:40px;text-align:center;color:#aaa;">No categories found.</td></tr>';
      return;
    }
    tbody.innerHTML = categories.map(c => `
      <tr style="border-bottom:1px solid #f3f4f6;transition:background 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
        <td style="padding:16px 24px;">
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;background:#f3f4f6;border-radius:6px;overflow:hidden;border:1px solid #e5e5e5;">
              ${c.url ? `<img src="${c.url}" style="width:100%;height:100%;object-fit:cover;">` : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:10px;">No Img</div>`}
            </div>
            <div style="font-weight:600;color:#1a1a1a;">${esc(c.name)}</div>
          </div>
        </td>
        <td style="padding:16px 24px;color:#666;font-size:13px;">${esc(c.slug)}</td>
        <td style="padding:16px 24px;color:#666;font-size:13px;">${c.products_count || 0} Products</td>
        <td style="padding:16px 24px;color:#666;font-size:13px;">${c.sort_order || 0}</td>
        <td style="padding:16px 24px;">
          <button onclick="toggleActive(${c.id}, ${!c.is_active})" style="padding:4px 10px;border-radius:50px;font-size:11px;font-weight:600;cursor:pointer;border:${c.is_active ? '1px solid #d1fae5;background:#d1fae5;color:#065f46;' : '1px solid #fee2e2;background:#fee2e2;color:#991b1b;'}">
            ${c.is_active ? 'Active' : 'Inactive'}
          </button>
        </td>
        <td style="padding:16px 24px;">
          <button onclick="editCategory(${JSON.stringify(c).replace(/"/g, '&quot;')})" style="color:#c9a96e;background:none;border:none;font-size:13px;font-weight:600;cursor:pointer;margin-right:12px;padding:0;">Edit</button>
          <button onclick="deleteCategory(${c.id})" style="color:#ef4444;background:none;border:none;font-size:13px;font-weight:600;cursor:pointer;padding:0;">Delete</button>
        </td>
      </tr>
    `).join('');
  }

  window.openModal = function() {
    document.getElementById('modal-title').textContent = 'Add Category';
    document.getElementById('category-id').value = '';
    document.getElementById('category-form').reset();
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('category-modal').style.display = 'flex';
  };

  window.closeModal = function() {
    document.getElementById('category-modal').style.display = 'none';
  };

  window.previewImage = function(e) {
    var file = e.target.files[0];
    var preview = document.getElementById('image-preview');
    var img = document.getElementById('preview-img');
    if (file) {
      var reader = new FileReader();
      reader.onload = function(e) {
        img.src = e.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    } else {
      preview.style.display = 'none';
    }
  };

  window.editCategory = function(c) {
    document.getElementById('modal-title').textContent = 'Edit Category';
    document.getElementById('category-id').value = c.id;
    document.getElementById('field-name').value = c.name;
    document.getElementById('field-description').value = c.description || '';
    document.getElementById('field-sort_order').value = c.sort_order || 0;
    document.getElementById('field-is_active').checked = !!c.is_active;
    
    var preview = document.getElementById('image-preview');
    var img = document.getElementById('preview-img');
    if (c.image) {
      img.src = '/storage/' + c.image;
      preview.style.display = 'block';
    } else {
      preview.style.display = 'none';
    }
    
    document.getElementById('category-modal').style.display = 'flex';
  };

  window.saveCategory = async function(e) {
    e.preventDefault();
    var id = document.getElementById('category-id').value;
    var btn = document.getElementById('save-btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    var formData = new FormData();
    formData.append('name', document.getElementById('field-name').value);
    formData.append('description', document.getElementById('field-description').value);
    formData.append('sort_order', document.getElementById('field-sort_order').value);
    formData.append('is_active', document.getElementById('field-is_active').checked ? 1 : 0);
    
    var imageFile = document.getElementById('field-image').files[0];
    if (imageFile) {
      formData.append('image', imageFile);
    }

    try {
      if (id) {
        formData.append('_method', 'PUT');
        await API.post('/admin/categories/' + id, formData);
        showToast('Category updated successfully.', 'success');
      } else {
        await API.post('/admin/categories', formData);
        showToast('Category created successfully.', 'success');
      }
      closeModal();
      loadCategories();
    } catch(e) {
      showToast(e.data?.message || 'Failed to save category.', 'error');
    } finally {
      btn.disabled = false;
      btn.textContent = 'Save Category';
    }
  };

  window.deleteCategory = async function(id) {
    if (!confirm('Are you sure? This will fail if the category has products.')) return;
    try {
      await API.del('/admin/categories/' + id);
      showToast('Category deleted.', 'success');
      loadCategories();
    } catch(e) {
      showToast('Failed to delete. Check if category is empty.', 'error');
    }
  };

  window.toggleActive = async function(id, val) {
    try {
      await API.patch('/admin/categories/' + id + '/toggle-active', { is_active: val });
      loadCategories();
    } catch(e) {
      showToast('Failed to update status.', 'error');
    }
  };
})();
</script>
@endsection
