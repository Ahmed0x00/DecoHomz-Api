@extends('admin.layouts.app')

@section('title', 'Warehouse Inspections')
@section('page_title', 'Warehouse Inspections')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Warehouse QA Logs</h1>
</div>

<!-- Inspections Table -->
<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Product</th>
        <th>Vendor</th>
        <th>Result</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody id="inspections-tbody">
      <tr class="loading-row"><td colspan="5"></td></tr>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<div id="pagination" style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:24px;">
</div>

@endsection

@section('extra_js')
<script>
(function() {
  var currentPage = 1;

  document.addEventListener('DOMContentLoaded', function() {
    loadInspections();
  });

  async function loadInspections(page) {
    if (page) currentPage = page;
    renderTableLoading();
    try {
      var res = await API.get('/admin/warehouse/inspections', { params: { page: currentPage } });
      var inspections = res.data && res.data.data ? res.data.data : (res.data || []);
      renderTable(inspections);
      renderPagination(res.data || res);
    } catch(e) {
      document.getElementById('inspections-tbody').innerHTML = '<tr><td colspan="5" style="text-align:center;color:#ef4444;padding:30px">Failed to load inspections.</td></tr>';
    }
  }

  function renderTableLoading() {
    document.getElementById('inspections-tbody').innerHTML = '<tr class="loading-row"><td colspan="5"></td></tr>';
  }

  function renderTable(inspections) {
    var tbody = document.getElementById('inspections-tbody');
    if (!inspections || inspections.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#aaa;padding:40px">No inspections found.</td></tr>';
      return;
    }
    tbody.innerHTML = inspections.map(function(i) {
      var badge = '';
      if (i.inspection_result === 'passed') {
        badge = '<span class="badge-status badge-active">Passed</span>';
      } else if (i.inspection_result === 'partial_pass') {
        badge = '<span class="badge-status badge-pending" style="background:#fef08a;color:#854d0e;">Partial Pass</span>';
      } else {
        badge = '<span class="badge-status badge-rejected">Failed</span>';
      }
      
      var product = (i.product && i.product.name) ? i.product.name : 'Unknown Product';
      var vendor = (i.vendor && i.vendor.company_name) ? i.vendor.company_name : 'Unknown Vendor';
      var date = i.inspected_at ? new Date(i.inspected_at).toLocaleString() : '—';
      
      return '<tr>' +
        '<td>#' + i.id + '</td>' +
        '<td style="font-weight:600;"><a href="/admin/vendor-products/' + i.product_id + '" style="color:#1a1a1a;text-decoration:none;">' + esc(product) + '</a></td>' +
        '<td style="color:#666;"><a href="/admin/vendors/' + i.vendor_id + '" style="color:#666;text-decoration:none;">' + esc(vendor) + '</a></td>' +
        '<td>' + badge + '<div style="font-size:11px;color:#888;margin-top:4px;">' + i.accepted_quantity + '/' + i.expected_quantity + ' accepted</div></td>' +
        '<td>' + date + '</td>' +
        '</tr>';
    }).join('');
  }

  function renderPagination(res) {
    var container = document.getElementById('pagination');
    var total = res.total || 0;
    var perPage = res.per_page || 15;
    var current = res.current_page || 1;
    var last = Math.ceil(total / perPage);
    if (last <= 1) { container.innerHTML = ''; return; }

    var html = '';
    if (current > 1) {
      html += '<button onclick="loadInspections(' + (current - 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">← Prev</button>';
    }
    for (var i = 1; i <= last; i++) {
      if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
        html += '<button onclick="loadInspections(' + i + ')" style="padding:6px 12px;border:1px solid ' + (i === current ? '#c9a96e' : '#e5e5e5') + ';background:' + (i === current ? '#c9a96e' : '#fff') + ';color:' + (i === current ? '#fff' : '#333') + ';border-radius:6px;cursor:pointer;font-size:13px;">' + i + '</button>';
      } else if (i === current - 2 || i === current + 2) {
        html += '<span style="color:#aaa;padding:0 4px;">...</span>';
      }
    }
    if (current < last) {
      html += '<button onclick="loadInspections(' + (current + 1) + ')" style="padding:6px 12px;border:1px solid #e5e5e5;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;">Next →</button>';
    }
    container.innerHTML = html;
  }
  
  window.loadInspections = loadInspections;
})();
</script>
@endsection
