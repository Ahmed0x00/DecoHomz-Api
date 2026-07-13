<!-- CREATE/EDIT PRODUCT TAB -->
<div id="tab-product-form" class="portal-tab" style="display:none;">
  <div class="portal-card">
    <div class="card-header">
      <h2 class="card-title" id="product-form-title">Add New Product</h2>
      <button type="button" class="btn-close" onclick="VendorPortal.switchTab('products')">&times;</button>
    </div>
    <form id="product-form">
      <input type="hidden" id="form-product-id">
      <div id="product-review-feedback" style="display:none;margin:0 24px 16px;padding:12px 14px;border-radius:8px;border:1px solid #f59e0b;background:#fffbeb;color:#92400e;font-size:13px;line-height:1.5;"></div>
      
      <div class="card-body" style="padding:0 24px 24px;">
        <div class="form-section">
          <h3 class="section-title">Basic Information</h3>
          <div class="form-grid">
            <div class="form-group">
              <label>Product Name *</label>
              <input type="text" id="p-name" required placeholder="e.g. Classic Wooden Sofa">
            </div>
            <div class="form-group">
              <label>Category *</label>
              <select id="p-category" required>
                <option value="">Select Category</option>
              </select>
            </div>
            <div class="form-group full-width">
              <label>Description *</label>
              <textarea id="p-description" required rows="4" placeholder="Detailed product specifications, materials used, comfort level, etc."></textarea>
            </div>
            <div class="form-group">
              <label>Wholesale / Vendor Price (EGP) *</label>
              <input type="number" id="p-price" required min="0" placeholder="1000">
            </div>
            <div class="form-group">
              <label>Total Stock Quantity</label>
              <input type="number" id="p-stock" min="0" placeholder="0">
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">Additional Info</h3>
          <div class="form-grid">
            <div class="form-group">
              <label>Production Lead Time (Days)</label>
              <input type="number" id="p-lead" placeholder="14">
            </div>
            <div class="form-group">
              <label>Warranty (Months)</label>
              <input type="number" id="p-warranty" placeholder="12">
            </div>
            <div class="form-group full-width">
              <label>Care Instructions</label>
              <textarea id="p-care" rows="2" placeholder="e.g. Dry clean only, avoid direct sunlight"></textarea>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 class="section-title" style="margin:0;">Custom Specifications</h3>
          </div>
          <div style="display:flex;flex-direction:column;gap:20px;">
            <div>
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;border-bottom:1.5px solid #f1f5f9;padding-bottom:6px;">
                <span style="font-size:13px;font-weight:700;color:#1e293b;">Dimensions Specs</span>
                <button type="button" onclick="VendorPortal.addSpec('dimensions')" style="background:#1e293b;color:#fff;border:none;padding:5px 12px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;">+ Add Dimension</button>
              </div>
              <div id="dimensions-specs-container"></div>
            </div>
            <div style="margin-top:10px;">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;border-bottom:1.5px solid #f1f5f9;padding-bottom:6px;">
                <span style="font-size:13px;font-weight:700;color:#1e293b;">Materials Specs</span>
                <button type="button" onclick="VendorPortal.addSpec('materials')" style="background:#1e293b;color:#fff;border:none;padding:5px 12px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;">+ Add Material</button>
              </div>
              <div id="materials-specs-container"></div>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 class="section-title" style="margin:0;">Colors & Variations</h3>
            <span id="colors-count" style="font-size:12px;color:#888;">0 colors</span>
          </div>
          <div id="color-list" style="display:flex;flex-direction:column;gap:16px;margin-bottom:20px;"></div>
          
          <div style="border-top:1px solid #f3f4f6;padding-top:20px;">
            <p style="font-size:12px;font-weight:600;color:#888;margin:0 0 14px;text-transform:uppercase;letter-spacing:0.5px;">Add New Color</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));gap:12px;align-items:end;">
              <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px;">Color Name *</label>
                <input type="text" id="new-color-name" placeholder="e.g. Navy Blue" style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;">
              </div>
              <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px;">Hex Code *</label>
                <div style="display:flex;gap:6px;align-items:center;">
                  <input type="color" id="new-color-preview" value="#1a365d" onchange="document.getElementById('new-color-hex').value=this.value" style="width:36px;height:36px;border:none;background:none;cursor:pointer;border-radius:6px;padding:0;flex-shrink:0;">
                  <input type="text" id="new-color-hex" placeholder="#1a365d" value="#1a365d" style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;font-family:monospace;">
                </div>
              </div>
              <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px;">Additional Price (EGP)</label>
                <input type="number" id="new-color-price" placeholder="0 = base price" step="1" style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;">
              </div>
              <div>
                <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px;">Color Stock</label>
                <input type="number" id="new-color-stock" placeholder="0" min="0" style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;">
              </div>
              <button type="button" onclick="VendorPortal.addNewColor()" style="background:#1e293b;color:#fff;border:none;padding:9px 16px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;height:36px;transition:opacity 0.2s;">+ Add Color</button>
            </div>
          </div>
        </div>
        
        <div class="form-section" id="image-upload-section">
          <h3 class="section-title">Product Images</h3>
          <div class="image-gallery-container">
            <div id="product-images-preview" class="image-gallery"></div>
            
            <div class="image-upload-box">
              <input type="file" id="p-new-image" accept="image/jpeg,image/png,image/webp" multiple onchange="VendorPortal.handleImageSelection(this)">
              <div class="upload-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                <span>Click or Drag to select images</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      </div>
      
      <div class="card-footer" style="padding:16px 24px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:12px;">
        <button type="button" class="btn" style="background:#ef4444;color:#fff;border:none;margin-right:auto;display:none;" id="btn-delete-product" onclick="VendorPortal.deleteProduct()">Delete Product</button>
        <button type="button" class="btn btn-outline" onclick="VendorPortal.switchTab('products')">Cancel</button>
        <button type="submit" class="btn btn-secondary" id="btn-save-draft">Save Draft</button>
        <button type="button" class="btn btn-primary" id="btn-submit-review" onclick="VendorPortal.submitProductForReview()">Submit for Review</button>
      </div>
    </form>
  </div>
</div>
