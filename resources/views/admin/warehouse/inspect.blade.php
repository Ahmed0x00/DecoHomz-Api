@extends('admin.layouts.app')

@section('title', 'Log Warehouse Inspection')
@section('page_title', 'Warehouse Inspection')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <div>
    <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Log Inspection</h1>
    <p style="color:#666;font-size:14px;margin-top:4px;" id="product-name">Loading product details...</p>
  </div>
</div>

<div class="admin-card" style="max-width:600px;">
  <form id="inspection-form" style="padding:24px;">
    
    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:13px;font-weight:600;margin-bottom:8px;color:#333;">Expected Quantity</label>
      <input type="number" id="inp-expected" required min="1" style="width:100%;padding:10px 14px;border:1px solid #ccc;border-radius:6px;font-size:14px;" />
    </div>

    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:13px;font-weight:600;margin-bottom:8px;color:#333;">Received Quantity</label>
      <input type="number" id="inp-received" required min="0" style="width:100%;padding:10px 14px;border:1px solid #ccc;border-radius:6px;font-size:14px;" />
      <div style="font-size:12px;color:#666;margin-top:4px;">Total number of units physically received.</div>
    </div>

    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:13px;font-weight:600;margin-bottom:8px;color:#333;">Accepted Quantity (Passed QA)</label>
      <input type="number" id="inp-accepted" required min="0" style="width:100%;padding:10px 14px;border:1px solid #ccc;border-radius:6px;font-size:14px;" />
      <div style="font-size:12px;color:#666;margin-top:4px;">Units that passed quality control and are ready for sale.</div>
    </div>

    <div style="margin-bottom:24px;">
      <label style="display:block;font-size:13px;font-weight:600;margin-bottom:8px;color:#333;">Inspector Notes</label>
      <textarea id="inp-notes" rows="4" style="width:100%;padding:10px 14px;border:1px solid #ccc;border-radius:6px;font-size:14px;resize:vertical;"></textarea>
    </div>

    <div style="display:flex;justify-content:flex-end;">
      <button type="submit" id="btn-submit" style="background:#1a1a1a;color:#fff;border:none;padding:12px 24px;border-radius:8px;font-weight:600;cursor:pointer;font-size:14px;">Log Inspection</button>
    </div>
  </form>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  var productId = {{ $id }};

  document.addEventListener('DOMContentLoaded', async function() {
    try {
      var res = await API.get('/admin/vendor-products/' + productId);
      var product = res.data || res;
      document.getElementById('product-name').textContent = 'Product: ' + product.name + ' | Vendor: ' + (product.vendor ? product.vendor.company_name : 'Unknown');
    } catch(e) {
      document.getElementById('product-name').textContent = 'Product not found.';
      document.getElementById('btn-submit').disabled = true;
    }
  });

  document.getElementById('inspection-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    var btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    var payload = {
      product_id: productId,
      expected_quantity: document.getElementById('inp-expected').value,
      received_quantity: document.getElementById('inp-received').value,
      accepted_quantity: document.getElementById('inp-accepted').value,
      inspector_notes: document.getElementById('inp-notes').value,
    };

    try {
      await API.post('/admin/warehouse/inspections', payload);
      showToast('Inspection logged successfully', 'success');
      setTimeout(function() {
        location.href = '/admin/warehouse';
      }, 1000);
    } catch(err) {
      btn.disabled = false;
      btn.textContent = 'Log Inspection';
      showToast('Failed to log inspection: ' + (err.response?.data?.message || err.message), 'error');
    }
  });
})();
</script>
@endsection
