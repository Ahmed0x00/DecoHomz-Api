@extends('admin.layouts.app')

@section('title', 'Deposit Rules')
@section('page_title', 'Deposit Rules')

@section('content')
<style>
.dr-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; }
.dr-header-left h1 { font-size:20px; font-weight:700; color:#1e293b; margin:0 0 4px; }
.dr-header-left p { font-size:13px; color:#94a3b8; margin:0; }
.dr-btn { padding:8px 16px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; border:none; transition:all 0.15s; display:inline-flex; align-items:center; gap:6px; }
.dr-btn-primary { background:#1e293b; color:#fff; }
.dr-btn-primary:hover { background:#334155; }
.dr-btn-outline { background:#fff; color:#475569; border:1px solid #e2e8f0; }
.dr-btn-outline:hover { background:#f8fafc; }
.dr-btn-delete { background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; padding:9px 12px; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; }
.dr-btn-delete:hover { background:#fecaca; }
.dr-table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.06); }
.dr-table th { text-align:left; padding:14px 18px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#94a3b8; background:#f8fafc; border-bottom:1px solid #f1f5f9; }
.dr-table td { padding:16px 18px; font-size:14px; color:#1e293b; border-bottom:1px solid #f8fafc; vertical-align:middle; }
.dr-table tr:last-child td { border-bottom:none; }
.dr-table tr:hover td { background:#fafafa; }
.dr-badge { display:inline-flex; align-items:center; padding:3px 10px; border-radius:20px; font-size:10px; font-weight:700; text-transform:uppercase; }
.dr-badge-active { background:#dcfce7; color:#15803d; }
.dr-badge-inactive { background:#f1f5f9; color:#64748b; }
.dr-toggle { position:relative; width:40px; height:22px; display:inline-block; }
.dr-toggle input { opacity:0; width:0; height:0; }
.dr-toggle-slider { position:absolute; cursor:pointer; inset:0; background:#d1d5db; border-radius:22px; transition:0.2s; }
.dr-toggle-slider:before { content:''; position:absolute; height:16px; width:16px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:0.2s; }
.dr-toggle input:checked + .dr-toggle-slider { background:#16a34a; }
.dr-toggle input:checked + .dr-toggle-slider:before { transform:translateX(18px); }
.dr-modal-overlay { display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; }
.dr-modal-overlay.open { display:flex; }
.dr-modal { background:#fff; border-radius:16px; padding:28px; width:420px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.dr-modal h3 { font-size:16px; font-weight:700; color:#1e293b; margin:0 0 20px; }
.dr-form-group { margin-bottom:14px; }
.dr-form-group label { display:block; font-size:12px; font-weight:600; color:#555; margin-bottom:6px; }
.dr-form-group input { width:100%; padding:10px 12px; border:1.5px solid #e5e7eb; border-radius:8px; font-size:14px; box-sizing:border-box; }
.dr-form-group input:focus { border-color:#c9a96e; outline:none; }
.dr-form-group .hint { font-size:11px; color:#94a3b8; margin-top:4px; }
.dr-modal-actions { display:flex; gap:10px; margin-top:20px; }
.dr-modal-actions .cancel { flex:1; padding:11px; background:#f3f4f6; color:#374151; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; }
.dr-modal-actions .save { flex:1; padding:11px; background:#1e293b; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; }
.dr-modal-actions .save:hover { background:#334155; }
.dr-info-box { background:#fef3c7; border:1px solid #fde68a; border-radius:10px; padding:14px 18px; margin-bottom:20px; font-size:13px; color:#92400e; display:flex; gap:10px; align-items:flex-start; }
</style>

<div class="dr-header">
  <div class="dr-header-left">
    <h1>Deposit Rules</h1>
    <p>Configure deposit percentage and minimum amounts for orders</p>
  </div>
  <button class="dr-btn dr-btn-primary" onclick="openAddModal()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
    Add Rule
  </button>
</div>

<!-- Info Box -->
<div class="dr-info-box">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  Only one rule can be active at a time. When you activate a new rule, the previous active rule is automatically deactivated.
</div>

<!-- Rules Table -->
<table class="dr-table">
  <thead>
    <tr>
      <th>Percentage</th>
      <th>Minimum Amount</th>
      <th>Status</th>
      <th>Created</th>
      <th style="text-align:right;">Actions</th>
    </tr>
  </thead>
  <tbody id="rules-tbody">
    <tr><td colspan="5" style="text-align:center;padding:40px;color:#aaa;">Loading...</td></tr>
  </tbody>
</table>

<!-- Add/Edit Modal -->
<div id="dr-modal" class="dr-modal-overlay" onclick="if(event.target===this)closeModal()">
  <div class="dr-modal">
    <h3 id="modal-title">Add Deposit Rule</h3>
    <div class="dr-form-group">
      <label>Deposit Percentage (%) *</label>
      <input type="number" id="modal-percentage" placeholder="e.g. 10" min="0" max="100" step="0.01">
      <div class="hint">Percentage of the order total to require as deposit</div>
    </div>
    <div class="dr-form-group">
      <label>Minimum Amount (EGP) *</label>
      <input type="number" id="modal-minimum" placeholder="e.g. 0" min="0" step="1">
      <div class="hint">Minimum deposit amount regardless of percentage (0 = no minimum)</div>
    </div>
    <div class="dr-form-group" style="display:flex;align-items:center;gap:10px;">
      <input type="checkbox" id="modal-active" style="width:16px;height:16px;accent-color:#16a34a;">
      <label for="modal-active" style="margin:0;font-size:13px;color:#374151;">Set as active rule</label>
    </div>
    <div class="dr-modal-actions">
      <button class="cancel" onclick="closeModal()">Cancel</button>
      <button class="save" onclick="saveModal()">Save Rule</button>
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  var rules = [];
  var editingId = null;

  loadRules();

  async function loadRules() {
    try {
      var res = await API.get('/admin/deposit-rules');
      rules = res.data || res;
      renderRules();
    } catch(e) {
      document.getElementById('rules-tbody').innerHTML =
        '<tr><td colspan="5" style="text-align:center;padding:40px;color:#ef4444;">Failed to load deposit rules.</td></tr>';
    }
  };

  function renderRules() {
    var tbody = document.getElementById('rules-tbody');
    if (!rules.length) {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:#aaa;">No deposit rules yet. Click "Add Rule" to create one.</td></tr>';
      return;
    }
    tbody.innerHTML = rules.map(function(r) {
      var badge = r.is_active
        ? '<span class="dr-badge dr-badge-active">● Active</span>'
        : '<span class="dr-badge dr-badge-inactive">● Inactive</span>';
      return '<tr>' +
        '<td style="font-weight:700;font-size:18px;color:#1e293b;">' + parseFloat(r.percentage).toFixed(2) + '%</td>' +
        '<td>' + parseFloat(r.minimum_amount || 0).toFixed(2) + ' EGP</td>' +
        '<td>' + badge + '</td>' +
        '<td style="color:#94a3b8;font-size:12px;">' + formatDate(r.created_at) + '</td>' +
        '<td style="text-align:right;">' +
          '<label class="dr-toggle" title="' + (r.is_active ? 'Deactivate' : 'Activate') + '">' +
            '<input type="checkbox" ' + (r.is_active ? 'checked' : '') + ' onchange="toggleRule(' + r.id + ', this.checked)">' +
            '<span class="dr-toggle-slider"></span>' +
          '</label> ' +
          '<button class="dr-btn-delete" onclick="deleteRule(' + r.id + ')" style="margin-left:8px;">Delete</button>' +
        '</td>' +
      '</tr>';
    }).join('');
  }

  window.toggleRule = function(id, isActive) {
    API.patch('/admin/deposit-rules/' + id + '/toggle').then(function() {
      loadRules();
      showToast(isActive ? 'Rule activated.' : 'Rule deactivated.', 'success');
    }).catch(function() {
      showToast('Failed to update rule.', 'error');
      loadRules();
    });
  };

  window.deleteRule = function(id) {
    if (!confirm('Delete this deposit rule?')) return;
    API.del('/admin/deposit-rules/' + id).then(function() {
      showToast('Rule deleted.', 'success');
      loadRules();
    }).catch(function() {
      showToast('Failed to delete rule.', 'error');
    });
  };

  window.openAddModal = function() {
    editingId = null;
    document.getElementById('modal-title').textContent = 'Add Deposit Rule';
    document.getElementById('modal-percentage').value = '';
    document.getElementById('modal-minimum').value = '';
    document.getElementById('modal-active').checked = true;
    document.getElementById('dr-modal').classList.add('open');
    document.getElementById('modal-percentage').focus();
  };

  window.closeModal = function() {
    document.getElementById('dr-modal').classList.remove('open');
    editingId = null;
  };

  window.saveModal = function() {
    var percentage = parseFloat(document.getElementById('modal-percentage').value);
    var minimum = parseFloat(document.getElementById('modal-minimum').value);
    var isActive = document.getElementById('modal-active').checked;

    if (isNaN(percentage) || percentage < 0 || percentage > 100) {
      showToast('Enter a valid percentage between 0 and 100.', 'error');
      return;
    }
    if (isNaN(minimum) || minimum < 0) {
      showToast('Enter a valid minimum amount.', 'error');
      return;
    }

    var payload = {
      percentage: percentage,
      minimum_amount: minimum,
      is_active: isActive ? true : false
    };

    var req = editingId
      ? API.put('/admin/deposit-rules/' + editingId, payload)
      : API.post('/admin/deposit-rules', payload);

    req.then(function() {
      closeModal();
      loadRules();
      showToast(editingId ? 'Rule updated!' : 'Rule added!', 'success');
    }).catch(function() {
      showToast('Failed to save rule.', 'error');
    });
  };

  function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
  }
})();
</script>
@endsection
