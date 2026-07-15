@extends('admin.layouts.app')

@section('title', 'Activity Logs')
@section('page_title', 'Activity Logs')

@section('content')
<style>
    /* ── Section badge colors ── */
    .section-Auth { background: #4f46e5; color: #fff; }
    .section-Orders { background: #0891b2; color: #fff; }
    .section-Users { background: #7c3aed; color: #fff; }
    .section-Products { background: #059669; color: #fff; }
    .section-Categories { background: #d97706; color: #fff; }
    .section-Reviews { background: #db2777; color: #fff; }
    .section-Coupons { background: #ea580c; color: #fff; }
    .section-Cart { background: #2563eb; color: #fff; }
    .section-Addresses { background: #65a30d; color: #fff; }
    .section-Wishlist { background: #0d9488; color: #fff; }
    .section-General { background: #475569; color: #fff; }
    .section-Contacts { background: #9333ea; color: #fff; }

    /* ── Result badge colors ── */
    .result-success { background: #dcfce7; color: #15803d; }
    .result-failure { background: #fee2e2; color: #b91c1c; }
    .result-deletion { background: #fce7f3; color: #9d174d; }
    .result-warning { background: #fef3c7; color: #a16207; }
    .result-read { background: #e0f2fe; color: #0369a1; }
    .result-info { background: #f3f4f6; color: #374151; }

    .log-row:hover { background-color: #f9fafb !important; cursor: pointer; }
    .diff-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px; }
    .diff-table th { text-align: left; padding: 8px; background: #f3f4f6; border-bottom: 1px solid #e5e5e5; }
    .diff-table td { padding: 8px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
    .diff-old { color: #b91c1c; background: #fee2e2; text-decoration: line-through; padding: 2px 4px; border-radius: 4px; font-size: 11px; }
    .diff-new { color: #15803d; background: #dcfce7; padding: 2px 4px; border-radius: 4px; font-size: 11px; }

    .filter-bar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .filter-bar select, .filter-bar input { padding: 7px 12px; border: 1px solid #e5e5e5; border-radius: 8px; font-size: 13px; outline: none; background: #fff; }
    .filter-bar select:focus, .filter-bar input:focus { border-color: #c9a96e; }
    .filter-bar label { font-size: 12px; color: #888; font-weight: 500; }

    /* ── Section chips ── */
    .section-chips { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
    .chip {
        padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
        cursor: pointer; border: 1.5px solid transparent; transition: all 0.15s;
        background: #f1f5f9; color: #475569; border-color: #e2e8f0;
    }
    .chip:hover { border-color: #c9a96e; color: #c9a96e; }
    .chip.active { background: #1e293b; color: #fff; border-color: #1e293b; }
    .chip .chip-count { opacity: 0.7; margin-left: 3px; }

    /* ── Result chips ── */
    .result-chip { background: #f8fafc; }
    .result-chip.success.active { background: #15803d; border-color: #15803d; color: #fff; }
    .result-chip.failure.active { background: #b91c1c; border-color: #b91c1c; color: #fff; }
    .result-chip.deletion.active { background: #9d174d; border-color: #9d174d; color: #fff; }
    .result-chip.read.active { background: #0369a1; border-color: #0369a1; color: #fff; }
    .result-chip.warning.active { background: #a16207; border-color: #a16207; color: #fff; }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
  <div>
    <h1 style="font-size:22px;font-weight:700;color:#1a1a1a;margin:0;">Activity Logs</h1>
    <p style="font-size:12px;color:#888;margin:4px 0 0;">Track all actions across your store</p>
  </div>
  <button onclick="clearLogs()" style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Clear All Logs</button>
</div>

<!-- Section chips -->
<div class="section-chips" id="sectionChips">
  <span class="chip active" data-section="" onclick="setSection('')">All<span class="chip-count" id="cnt-all"></span></span>
  <span class="chip" data-section="Auth" onclick="setSection('Auth')">Auth<span class="chip-count" id="cnt-Auth"></span></span>
  <span class="chip" data-section="Orders" onclick="setSection('Orders')">Orders<span class="chip-count" id="cnt-Orders"></span></span>
  <span class="chip" data-section="Users" onclick="setSection('Users')">Users<span class="chip-count" id="cnt-Users"></span></span>
  <span class="chip" data-section="Products" onclick="setSection('Products')">Products<span class="chip-count" id="cnt-Products"></span></span>
  <span class="chip" data-section="Categories" onclick="setSection('Categories')">Categories<span class="chip-count" id="cnt-Categories"></span></span>
  <span class="chip" data-section="Reviews" onclick="setSection('Reviews')">Reviews<span class="chip-count" id="cnt-Reviews"></span></span>
  <span class="chip" data-section="Coupons" onclick="setSection('Coupons')">Coupons<span class="chip-count" id="cnt-Coupons"></span></span>
  <span class="chip" data-section="Cart" onclick="setSection('Cart')">Cart<span class="chip-count" id="cnt-Cart"></span></span>
  <span class="chip" data-section="Addresses" onclick="setSection('Addresses')">Addresses<span class="chip-count" id="cnt-Addresses"></span></span>
  <span class="chip" data-section="Contacts" onclick="setSection('Contacts')">Contacts<span class="chip-count" id="cnt-Contacts"></span></span>
  <span class="chip" data-section="General" onclick="setSection('General')">General<span class="chip-count" id="cnt-General"></span></span>
</div>

<!-- Filter bar -->
<div class="filter-bar" style="margin-bottom:16px;">
  <label>Result:</label>
  <select id="resultFilter" onchange="loadLogs()">
    <option value="">All Results</option>
    <option value="success">✓ Success</option>
    <option value="failure">✗ Failure</option>
    <option value="deletion">🗑 Deletion</option>
    <option value="warning">⚠ Warning</option>
    <option value="read">👁 Read</option>
    <option value="info">ℹ Info</option>
  </select>
  <label>From:</label>
  <input type="date" id="dateFrom" onchange="loadLogs()" style="width:140px;">
  <label>To:</label>
  <input type="date" id="dateTo" onchange="loadLogs()" style="width:140px;">
  <input type="text" id="logSearch" placeholder="Search actions, descriptions, users..." oninput="debounceLoadLogs()" style="width:240px;">
  <button onclick="loadLogs()" style="padding:7px 14px;background:#1e293b;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Search</button>
  <button onclick="resetFilters()" style="padding:7px 14px;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;cursor:pointer;">Reset</button>
</div>

<!-- Logs table -->
<div class="admin-card" style="padding:0;">
  <table class="admin-table" style="margin:0;">
    <thead>
      <tr style="background:#f8fafc;">
        <th style="padding:12px 16px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e5e7eb;">Date & Time</th>
        <th style="padding:12px 16px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e5e7eb;">Action</th>
        <th style="padding:12px 16px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e5e7eb;">User</th>
        <th style="padding:12px 16px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e5e7eb;">Section</th>
        <th style="padding:12px 16px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e5e7eb;">Result</th>
        <th style="padding:12px 16px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e5e7eb;">Description</th>
        <th style="padding:12px 16px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e5e7eb;">IP</th>
        <th style="padding:12px 16px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e5e7eb;width:40px;"></th>
      </tr>
    </thead>
    <tbody id="logsTable">
      <tr><td colspan="8" style="text-align:center;padding:60px;color:#aaa;font-size:14px;">Loading logs...</td></tr>
    </tbody>
  </table>
  <div id="pagination" style="padding:14px 16px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f1f5f9;"></div>
</div>

<!-- Log Details Modal -->
<div id="logModal" class="modal-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;width:95%;max-width:900px;border-radius:16px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);max-height:90vh;display:flex;flex-direction:column;">
        <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
            <h3 style="margin:0;font-size:17px;font-weight:700;color:#1e293b;">Activity Details</h3>
            <div style="display:flex;gap:10px;align-items:center;">
                <span id="modalSectionBadge" style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;"></span>
                <span id="modalResultBadge" style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;"></span>
                <button onclick="closeModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#94a3b8;line-height:1;">&times;</button>
            </div>
        </div>
        <div id="logModalBody" style="padding:24px;overflow-y:auto;flex:1;"></div>
    </div>
</div>
@endsection

@section('extra_js')
<script>
(function() {
  var currentPage = 1;
  var activeSection = '';
  var debounceTimer;

  // ── Load stats chips ──────────────────────────────────────
  async function loadStats() {
    try {
      var counts = {};
      var sections = ['Auth','Orders','Users','Products','Categories','Reviews','Coupons','Cart','Addresses','Contacts','General'];
      for (var s of sections) {
        var res = await API.get('/admin/logs?section=' + s + '&per_page=1');
        counts[s] = res.total || 0;
      }
      var allRes = await API.get('/admin/logs?per_page=1');
      counts['all'] = allRes.total || 0;
      for (var s of sections) {
        var el = document.getElementById('cnt-' + s);
        if (el) el.textContent = ' (' + counts[s] + ')';
      }
      var allEl = document.getElementById('cnt-all');
      if (allEl) allEl.textContent = ' (' + counts['all'] + ')';
    } catch(e) {}
  }

  // ── Section chip handler ───────────────────────────────────
  window.setSection = function(section) {
    activeSection = section;
    document.querySelectorAll('.chip[data-section]').forEach(function(c) {
      c.classList.toggle('active', c.dataset.section === section);
    });
    loadLogs(1);
  };

  window.resetFilters = function() {
    activeSection = '';
    document.getElementById('resultFilter').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('logSearch').value = '';
    document.querySelectorAll('.chip[data-section]').forEach(function(c) {
      c.classList.toggle('active', c.dataset.section === '');
    });
    loadLogs(1);
  };

  window.debounceLoadLogs = function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(loadLogs, 400);
  };

  // ── Main load ──────────────────────────────────────────────
  window.loadLogs = async function(page) {
    currentPage = page || 1;
    var table = document.getElementById('logsTable');
    var result = document.getElementById('resultFilter').value;
    var dateFrom = document.getElementById('dateFrom').value;
    var dateTo = document.getElementById('dateTo').value;
    var search = document.getElementById('logSearch').value;

    table.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:60px;color:#aaa;font-size:14px;">Loading...</td></tr>';

    try {
      var params = { page: currentPage, per_page: 30 };
      if (activeSection) params.section = activeSection;
      if (result) params.result = result;
      if (dateFrom) params.from = dateFrom;
      if (dateTo) params.to = dateTo;
      if (search) params.search = search;

      var qs = new URLSearchParams(params).toString();
      var res = await API.get('/admin/logs' + (qs ? ('?' + qs) : ''));
      renderLogs(res.data || []);
      renderPagination(res);
    } catch(e) {
      table.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:60px;color:#ef4444;font-size:14px;">Failed to load logs.</td></tr>';
    }
  };

  function renderLogs(logs) {
    var table = document.getElementById('logsTable');
    if (!logs.length) {
      table.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:60px;color:#aaa;font-size:14px;">No logs found. Try adjusting your filters.</td></tr>';
      return;
    }

    table.innerHTML = logs.map(function(log) {
      var date = new Date(log.created_at).toLocaleString('en-EG', {
          day: '2-digit', month: 'short', year: 'numeric',
          hour: '2-digit', minute: '2-digit'
      });
      var legacyAction = log.properties && log.properties.legacy_action;
      var legacyResult = log.properties && log.properties.legacy_result;
      
      var actionDisplay = legacyAction || log.event || 'Action';
      var resultDisplay = legacyResult || (log.event === 'created' ? 'success' : (log.event === 'deleted' ? 'deletion' : (log.event === 'updated' ? 'warning' : 'info')));
      var resultClass = 'result-' + (legacyResult ? legacyResult : (log.event === 'created' ? 'success' : (log.event === 'deleted' ? 'deletion' : (log.event === 'updated' ? 'warning' : 'info'))));
      var sectionClass = 'section-' + (log.log_name || 'General');
      var userName = log.causer ? esc(log.causer.name) : '<span style="color:#cbd5e1;font-style:italic;">System</span>';
      var description = log.description ? esc(log.description) : '';

      return '<tr class="log-row" onclick="viewLog(' + log.id + ')">' +
        '<td style="padding:11px 16px;font-size:12px;color:#64748b;white-space:nowrap;border-bottom:1px solid #f8fafc;">' + date + '</td>' +
        '<td style="padding:11px 16px;font-weight:600;font-size:13px;color:#1e293b;border-bottom:1px solid #f8fafc;text-transform:capitalize;">' + esc(actionDisplay) + '</td>' +
        '<td style="padding:11px 16px;font-size:13px;color:#334155;border-bottom:1px solid #f8fafc;">' + userName + '</td>' +
        '<td style="padding:11px 16px;border-bottom:1px solid #f8fafc;"><span class="' + sectionClass + '" style="padding:3px 9px;border-radius:12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;">' + (log.log_name || 'General') + '</span></td>' +
        '<td style="padding:11px 16px;border-bottom:1px solid #f8fafc;"><span class="' + resultClass + '" style="padding:3px 9px;border-radius:12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;">' + resultDisplay + '</span></td>' +
        '<td style="padding:11px 16px;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:#64748b;border-bottom:1px solid #f8fafc;" title="' + description + '">' + description + '</td>' +
        '<td style="padding:11px 16px;font-size:11px;color:#94a3b8;font-family:monospace;border-bottom:1px solid #f8fafc;white-space:nowrap;">' + (log.properties && log.properties.ip ? log.properties.ip : '—') + '</td>' +
        '<td style="padding:11px 16px;border-bottom:1px solid #f8fafc;"><button onclick="event.stopPropagation(); deleteLog(' + log.id + ')" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:16px;opacity:0.5;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.5">&times;</button></td>' +
        '</tr>';
    }).join('');
  }

  window.viewLog = async function(id) {
    try {
        var res = await API.get('/admin/logs/' + id);
        var log = res.log || res;
        var body = document.getElementById('logModalBody');

        var date = new Date(log.created_at).toLocaleString();

        // Database diffs (Spatie native)
        var attributesObj = log.attribute_changes && log.attribute_changes.attributes ? log.attribute_changes.attributes : (log.properties && log.properties.attributes && !log.properties.legacy_action ? log.properties.attributes : null);
        var oldObj = log.attribute_changes && log.attribute_changes.old ? log.attribute_changes.old : (log.properties && log.properties.old ? log.properties.old : null);
        
        // HTTP payload and response
        // Legacy standalone logs stored request in 'attributes', new enriched logs use 'request_payload'
        var requestPayloadObj = log.properties && log.properties.request_payload ? log.properties.request_payload : (log.properties && log.properties.legacy_action && log.properties.attributes ? log.properties.attributes : null);
        var responseDataObj = log.properties && log.properties.response_data ? log.properties.response_data : null;
        
        var attributesJson = (attributesObj && Object.keys(attributesObj).length) ? JSON.stringify(attributesObj, null, 2) : null;
        var oldJson = (oldObj && Object.keys(oldObj).length) ? JSON.stringify(oldObj, null, 2) : null;
        var requestJson = (requestPayloadObj && Object.keys(requestPayloadObj).length) ? JSON.stringify(requestPayloadObj, null, 2) : null;
        var responseJson = (responseDataObj && Object.keys(responseDataObj).length) ? JSON.stringify(responseDataObj, null, 2) : null;
        
        var legacyAction = log.properties && log.properties.legacy_action;
        var legacyResult = log.properties && log.properties.legacy_result;

        var actionDisplay = legacyAction || log.event || 'Action';
        var resultDisplay = legacyResult || (log.event === 'created' ? 'success' : (log.event === 'deleted' ? 'deletion' : (log.event === 'updated' ? 'warning' : 'info')));
        var resultClass = 'result-' + (legacyResult ? legacyResult : (log.event === 'created' ? 'success' : (log.event === 'deleted' ? 'deletion' : (log.event === 'updated' ? 'warning' : 'info'))));
        var sectionClass = 'section-' + (log.log_name || 'General');

        var diffHtml = '';
        if (oldObj && attributesObj && log.event === 'updated') {
            diffHtml = buildDiff(oldObj, attributesObj);
        }

        body.innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
                <div style="padding:18px;background:#f8fafc;border-radius:12px;">
                    <div style="font-weight:700;color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:14px;">Context</div>
                    <div style="display:grid;grid-template-columns:90px 1fr;gap:10px;font-size:13px;">
                        <span style="color:#64748b;">Action:</span> <span style="font-weight:700;color:#1e293b;text-transform:capitalize;">${esc(actionDisplay)}</span>
                        <span style="color:#64748b;">Log Name:</span> <span class="${sectionClass}" style="padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;text-transform:uppercase;">${log.log_name || 'General'}</span>
                        <span style="color:#64748b;">Resource:</span> <span style="color:#334155;">${log.subject_type ? log.subject_type.split('\\\\').pop() : '—'} ${log.subject_id ? '#' + log.subject_id : ''}</span>
                        <span style="color:#64748b;">User:</span> <span style="color:#334155;">${log.causer ? esc(log.causer.name) : '<i>System</i>'}</span>
                        <span style="color:#64748b;">Time:</span> <span style="color:#64748b;font-size:12px;">${date}</span>
                    </div>
                </div>
                <div style="padding:18px;background:#f8fafc;border-radius:12px;">
                    <div style="font-weight:700;color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:14px;">Outcome</div>
                    <div style="display:grid;grid-template-columns:90px 1fr;gap:10px;font-size:13px;">
                        <span style="color:#64748b;">Result:</span> <span class="${resultClass}" style="padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;text-transform:uppercase;">${resultDisplay}</span>
                        <span style="color:#64748b;">IP Address:</span> <span style="font-family:monospace;color:#64748b;">${(log.properties && log.properties.ip) || '—'}</span>
                        <span style="color:#64748b;">Browser:</span> <span style="font-size:11px;color:#94a3b8;">${((log.properties && log.properties.user_agent) || '—').substring(0, 60)}</span>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:24px;">
                <div style="font-weight:700;color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:8px;">Description</div>
                <div style="padding:16px 18px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;line-height:1.6;">${esc(log.description || '')}</div>
            </div>

            ${(requestJson || responseJson) ? `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
                <div>
                    <div style="font-weight:700;color:#64748b;font-size:10px;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:8px;">Request Payload</div>
                    <pre style="padding:14px;background:#f8fafc;color:#0f172a;border:1px solid #e2e8f0;border-radius:12px;font-size:12px;max-height:280px;overflow-y:auto;margin:0;line-height:1.5;">${requestJson || 'No payload'}</pre>
                </div>
                <div>
                    <div style="font-weight:700;color:#64748b;font-size:10px;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:8px;">Server Response</div>
                    <pre style="padding:14px;background:#f8fafc;color:#0f172a;border:1px solid #e2e8f0;border-radius:12px;font-size:12px;max-height:280px;overflow-y:auto;margin:0;line-height:1.5;">${responseJson || 'No response data'}</pre>
                </div>
            </div>
            ` : ''}

            ${(attributesJson || oldJson || diffHtml) ? `
            <div style="border-top:1px dashed #cbd5e1;padding-top:24px;">
                <div style="font-weight:700;color:#94a3b8;font-size:11px;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:14px;">Database Diffs (Background Data)</div>
                ${diffHtml ? `
                <div style="margin-bottom:16px;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    ${diffHtml}
                </div>
                ` : ''}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <div style="font-weight:700;color:#64748b;font-size:10px;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:8px;">New Data (Attributes)</div>
                        <pre style="padding:14px;background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;border-radius:12px;font-size:11px;max-height:200px;overflow-y:auto;margin:0;line-height:1.5;">${attributesJson || 'No new data'}</pre>
                    </div>
                    <div>
                        <div style="font-weight:700;color:#64748b;font-size:10px;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:8px;">Old Data</div>
                        <pre style="padding:14px;background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;border-radius:12px;font-size:11px;max-height:200px;overflow-y:auto;margin:0;line-height:1.5;">${oldJson || 'No old data'}</pre>
                    </div>
                </div>
            </div>
            ` : ''}
        `;

        var secBadge = document.getElementById('modalSectionBadge');
        secBadge.className = sectionClass;
        secBadge.textContent = log.log_name || 'General';
        var resBadge = document.getElementById('modalResultBadge');
        resBadge.className = resultClass;
        resBadge.textContent = resultDisplay;

        document.getElementById('logModal').style.display = 'flex';
    } catch(e) {
        showToast('Failed to load log details.', 'error');
    }
  };

  function buildDiff(oldVals, newVals) {
      var html = '<table class="diff-table"><thead><tr><th>Field</th><th>Old Value</th><th>New Value</th></tr></thead><tbody>';
      var hasChanges = false;
      for (var key in newVals) {
          if (oldVals.hasOwnProperty(key)) {
              var oldStr = String(oldVals[key] === null ? '' : oldVals[key]);
              var newStr = String(newVals[key] === null ? '' : newVals[key]);
              if (oldStr !== newStr && key !== 'updated_at' && key !== 'id') {
                  hasChanges = true;
                  html += '<tr><td style="font-weight:600;color:#334155;">' + key + '</td><td><span class="diff-old">' + esc(oldStr) + '</span></td><td><span class="diff-new">' + esc(newStr) + '</span></td></tr>';
              }
          }
      }
      if (!hasChanges) return '';
      return html + '</tbody></table>';
  }

  window.closeModal = function() {
    document.getElementById('logModal').style.display = 'none';
  };

  window.deleteLog = function(id) {
    if (!confirm('Delete this log entry?')) return;
    API.del('/admin/logs/' + id).then(function() {
      showToast('Log deleted.', 'success');
      loadLogs(currentPage);
      loadStats();
    });
  };

  window.clearLogs = function() {
    if (!confirm('CRITICAL: Delete ALL logs? This cannot be undone.')) return;
    API.del('/admin/logs-clear').then(function() {
      showToast('All logs cleared.', 'success');
      loadLogs(1);
      loadStats();
    });
  };

  function renderPagination(res) {
    var container = document.getElementById('pagination');
    var current = res.current_page;
    var last = res.last_page;
    var total = res.total || 0;
    var from = res.from || 0;
    var to = res.to || 0;

    var info = total > 0
        ? '<span style="font-size:12px;color:#94a3b8;">Showing ' + from + '–' + to + ' of ' + total + ' logs</span>'
        : '';

    if (last <= 1) {
        container.innerHTML = info;
        return;
    }

    var buttons = '';
    var maxButtons = 7;
    var start = Math.max(1, current - 3);
    var end = Math.min(last, start + maxButtons - 1);
    if (end - start < maxButtons - 1) start = Math.max(1, end - maxButtons + 1);

    if (start > 1) {
        buttons += '<button onclick="loadLogs(1)" style="padding:6px 12px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;background:#fff;color:#334155;border:1px solid #e2e8f0;">1</button>';
        if (start > 2) buttons += '<span style="color:#cbd5e1;padding:0 4px;">…</span>';
    }
    for (var i = start; i <= end; i++) {
        var style = i === current
            ? 'background:#1e293b;color:#fff;border-color:#1e293b;'
            : 'background:#fff;color:#334155;border-color:#e2e8f0;';
        buttons += '<button onclick="loadLogs(' + i + ')" style="padding:6px 12px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;' + style + '">' + i + '</button>';
    }
    if (end < last) {
        if (end < last - 1) buttons += '<span style="color:#cbd5e1;padding:0 4px;">…</span>';
        buttons += '<button onclick="loadLogs(' + last + ')" style="padding:6px 12px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;background:#fff;color:#334155;border:1px solid #e2e8f0;">' + last + '</button>';
    }

    container.innerHTML = '<div>' + info + '</div><div style="display:flex;gap:4px;">' + buttons + '</div>';
  }

  function esc(str) {
    if (str == null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadLogs();
  });

  // Close modal on backdrop click
  document.getElementById('logModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
  });
})();
</script>
@endsection
