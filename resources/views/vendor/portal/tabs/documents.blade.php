<!-- DOCUMENTS TAB -->
<div id="tab-documents" class="portal-tab" style="display:none;">
  <div class="documents-layout">
    <div class="portal-card">
      <div class="card-header">
        <h2 class="card-title">Uploaded Documents</h2>
      </div>
      <div class="card-body" id="documents-list-container">
        Loading documents...
      </div>
    </div>
    
    <div class="portal-card">
      <div class="card-header">
        <h2 class="card-title">Upload New Document</h2>
      </div>
      <div class="card-body">
        <form id="doc-upload-form" class="vertical-form">
          <div class="form-group">
            <label>Document Type *</label>
            <select id="doc-type" required>
              <option value="commercial_register">Commercial Register</option>
              <option value="tax_card">Tax Card</option>
              <option value="id_card">ID Card</option>
              <option value="bank_letter">Bank Letter</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label>Label / Name</label>
            <input type="text" id="doc-label" placeholder="e.g. Tax Card 2026">
          </div>
          <div class="form-group">
            <label>Document Reference Number</label>
            <input type="text" id="doc-number" placeholder="e.g. Reg-998811">
          </div>
          <div class="form-group">
            <label>File (PDF or Image, max 5MB) *</label>
            <input type="file" id="doc-file" required accept=".pdf,image/*">
          </div>
          <button type="submit" class="btn btn-primary mt-3 w-100">Upload Document</button>
        </form>
      </div>
    </div>
  </div>
</div>
