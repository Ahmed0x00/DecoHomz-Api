@extends('admin.layouts.app')
@section('title', 'Pre-Order #' . $id)

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
  <a href="/admin/pre-orders" style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:500;color:#6b7280;text-decoration:none;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    Back to Pre-Orders
  </a>
  <span style="background:#f3f4f6;padding:4px 12px;border-radius:99px;font-size:12px;font-weight:700;letter-spacing:0.5px;color:#1a1a1a;">PRE-ORDER #{{ $id }}</span>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:32px;">
  <div style="display:flex;flex-direction:column;gap:32px;">

    <!-- Images -->
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f1f1;overflow:hidden;">
      <div style="padding:20px 24px;border-bottom:1px solid #f1f1f1;display:flex;align-items:center;gap:12px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c9a96e" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        <h5 style="font-size:16px;font-weight:600;color:#2C1F14;margin:0;">Inspiration Images</h5>
      </div>
      <div id="imagesContainer" style="padding:24px;">
        <div style="text-align:center;color:#6b7280;padding:40px;">Loading images...</div>
      </div>
    </div>

    <!-- Description -->
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f1f1;overflow:hidden;">
      <div style="padding:20px 24px;border-bottom:1px solid #f1f1f1;display:flex;align-items:center;gap:12px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c9a96e" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <h5 style="font-size:16px;font-weight:600;color:#2C1F14;margin:0;">Description & Notes</h5>
      </div>
      <div id="descriptionContainer" style="padding:24px;">
        <div style="text-align:center;color:#6b7280;padding:20px;">Loading...</div>
      </div>
    </div>

    <!-- Admin Notes -->
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f1f1;overflow:hidden;">
      <div style="padding:20px 24px;border-bottom:1px solid #f1f1f1;display:flex;align-items:center;gap:12px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c9a96e" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        <h5 style="font-size:16px;font-weight:600;color:#2C1F14;margin:0;">Admin Notes</h5>
      </div>
      <div style="padding:24px;">
        <textarea id="adminNotes" style="width:100%;min-height:120px;padding:12px 14px;border:1.5px solid #f1f1f1;border-radius:8px;font-size:13px;font-family:inherit;resize:vertical;outline:none;transition:0.2s;box-sizing:border-box;" placeholder="Add internal notes about this pre-order..."></textarea>
        <div style="display:flex;justify-content:flex-end;margin-top:12px;">
          <button onclick="saveNotes()" style="padding:8px 20px;background:#2C1F14;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Save Notes</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div style="display:flex;flex-direction:column;gap:32px;">

    <!-- Status -->
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f1f1;overflow:hidden;">
      <div style="padding:20px 24px;border-bottom:1px solid #f1f1f1;display:flex;align-items:center;gap:12px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c9a96e" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <h5 style="font-size:16px;font-weight:600;color:#2C1F14;margin:0;">Status</h5>
      </div>
      <div style="padding:24px;">
        <div style="display:flex;gap:8px;align-items:center;">
          <select id="statusSelect" style="flex:1;padding:8px 12px;border:1.5px solid #f1f1f1;border-radius:8px;font-size:13px;color:#1a1a1a;outline:none;background:#fff;">
            <option value="pending">Pending</option>
            <option value="contacted">Contacted</option>
            <option value="confirmed">Confirmed</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <button onclick="updateStatus()" style="padding:8px 16px;background:#2C1F14;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Save</button>
        </div>
      </div>
    </div>

    <!-- Customer Info -->
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f1f1;overflow:hidden;">
      <div style="padding:20px 24px;border-bottom:1px solid #f1f1f1;display:flex;align-items:center;gap:12px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c9a96e" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/></svg>
        <h5 style="font-size:16px;font-weight:600;color:#2C1F14;margin:0;">Customer</h5>
      </div>
      <div id="customerInfo" style="padding:24px;">
        <div style="text-align:center;color:#6b7280;">Loading...</div>
      </div>
    </div>

    <!-- Inspiration Source -->
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f1f1;overflow:hidden;">
      <div style="padding:20px 24px;border-bottom:1px solid #f1f1f1;display:flex;align-items:center;gap:12px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c9a96e" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        <h5 style="font-size:16px;font-weight:600;color:#2C1F14;margin:0;">Inspiration Source</h5>
      </div>
      <div id="sourceInfo" style="padding:24px;">
        <div style="text-align:center;color:#6b7280;">Loading...</div>
      </div>
    </div>

    <!-- Meta -->
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f1f1;overflow:hidden;">
      <div style="padding:20px 24px;border-bottom:1px solid #f1f1f1;display:flex;align-items:center;gap:12px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c9a96e" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <h5 style="font-size:16px;font-weight:600;color:#2C1F14;margin:0;">Details</h5>
      </div>
      <div id="metaInfo" style="padding:24px;">
        <div style="text-align:center;color:#6b7280;">Loading...</div>
      </div>
    </div>

    <!-- Delete -->
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f1f1;overflow:hidden;">
      <div style="padding:24px;">
        <button onclick="deletePreOrder()" style="width:100%;padding:10px;background:#fef2f2;color:#991b1b;border:1px solid #fee2e2;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Delete Pre-Order</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  var preOrderId = '{{ $id }}';
  var preOrder = null;

  document.addEventListener('DOMContentLoaded', function() {
    loadPreOrder();
  });

  function loadPreOrder() {
    API.get('/admin/pre-orders/' + preOrderId).then(function(res) {
      preOrder = res.data || res;
      renderImages(preOrder.images || []);
      renderDescription(preOrder);
      renderCustomer(preOrder);
      renderSource(preOrder);
      renderMeta(preOrder);
      document.getElementById('adminNotes').value = preOrder.admin_notes || '';
      document.getElementById('statusSelect').value = preOrder.status || 'pending';
    }).catch(function() {
      document.getElementById('imagesContainer').innerHTML = '<div style="color:#ef4444;text-align:center;padding:40px;">Failed to load pre-order.</div>';
    });
  }

  function renderImages(images) {
    var container = document.getElementById('imagesContainer');
    if (!images.length) {
      container.innerHTML = '<div style="text-align:center;color:#6b7280;padding:40px;">No images uploaded.</div>';
      return;
    }
    container.innerHTML = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;">' +
      images.map(function(img) {
        var src = img.url || ('/storage/' + img.image);
        return '<div style="position:relative;border-radius:12px;overflow:hidden;border:1px solid #f1f1f1;background:#f9f9f9;">' +
          '<img src="' + src + '" style="width:100%;height:200px;object-fit:cover;display:block;" onerror="this.style.display=\'none\'">' +
        '</div>';
      }).join('') +
    '</div>';
  }

  function renderDescription(po) {
    var container = document.getElementById('descriptionContainer');
    var html = '<div style="display:flex;flex-direction:column;gap:16px;">';

    if (po.notes) {
      html += '<div><label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Description / Notes</label>' +
        '<div style="font-size:14px;line-height:1.6;color:#1a1a1a;background:#fafafa;padding:14px 16px;border-radius:8px;border:1px solid #f1f1f1;white-space:pre-wrap;">' + esc(po.notes) + '</div></div>';
    }

    if (po.notes) {
      html += '<div><label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Full Description</label>' +
        '<div style="font-size:14px;line-height:1.6;color:#1a1a1a;background:#fafafa;padding:14px 16px;border-radius:8px;border:1px solid #f1f1f1;white-space:pre-wrap;">' + esc(po.notes) + '</div></div>';
    }

    html += '</div>';
    container.innerHTML = html;
  }

  function renderCustomer(po) {
    var container = document.getElementById('customerInfo');
    container.innerHTML =
      '<div style="display:flex;flex-direction:column;gap:16px;">' +
        '<div><label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Full Name</label><div style="font-size:14px;font-weight:500;color:#1a1a1a;">' + esc(po.name || '—') + '</div></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Phone</label><div style="font-size:14px;font-weight:500;color:#1a1a1a;">' + esc(po.phone || '—') + '</div></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Email</label><div style="font-size:14px;font-weight:500;color:#1a1a1a;">' + esc(po.email || '—') + '</div></div>' +
        (po.governorate ? '<div><label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Governorate</label><div style="font-size:14px;font-weight:500;color:#1a1a1a;">' + esc(po.governorate) + '</div></div>' : '') +
      '</div>';
  }

  function renderSource(po) {
    var container = document.getElementById('sourceInfo');
    var source = po.inspiration_source || '';
    var custom = po.inspiration_custom || '';
    var links = po.inspiration_links || '';

    var html = '<div style="display:flex;flex-direction:column;gap:16px;">';

    if (source) {
      html += '<div><label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Source</label><div style="font-size:14px;font-weight:500;color:#1a1a1a;">' + esc(source) + '</div></div>';
    }

    if (custom) {
      html += '<div><label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Custom Source</label><div style="font-size:14px;font-weight:500;color:#1a1a1a;">' + esc(custom) + '</div></div>';
    }

    if (links) {
      var linkArr = Array.isArray(links) ? links : links.split('\n').filter(Boolean);
      html += '<div><label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Inspiration Links</label>' +
        linkArr.map(function(l) { return '<a href="' + esc(l.trim()) + '" target="_blank" style="display:block;font-size:13px;color:#1e40af;text-decoration:none;margin-bottom:4px;word-break:break-all;">' + esc(l.trim()) + '</a>'; }).join('') +
      '</div>';
    }

    if (!source && !custom && !links) {
      html += '<div style="color:#6b7280;font-size:13px;">No source information provided.</div>';
    }

    html += '</div>';
    container.innerHTML = html;
  }

  function renderMeta(po) {
    var container = document.getElementById('metaInfo');
    var created = po.created_at ? new Date(po.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
    var updated = po.updated_at ? new Date(po.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';

    container.innerHTML =
      '<div style="display:flex;flex-direction:column;gap:16px;">' +
        '<div><label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Pre-Order ID</label><div style="font-size:14px;font-weight:500;color:#1a1a1a;">#' + po.id + '</div></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Created</label><div style="font-size:14px;font-weight:500;color:#1a1a1a;">' + created + '</div></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Last Updated</label><div style="font-size:14px;font-weight:500;color:#1a1a1a;">' + updated + '</div></div>' +
      '</div>';
  }

  window.updateStatus = function() {
    var status = document.getElementById('statusSelect').value;
    API.patch('/admin/pre-orders/' + preOrderId + '/status', { status: status }).then(function() {
      showToast('Status updated!', 'success');
    }).catch(function() {
      showToast('Failed to update status.', 'error');
    });
  };

  window.saveNotes = function() {
    var notes = document.getElementById('adminNotes').value;
    API.patch('/admin/pre-orders/' + preOrderId + '/notes', { admin_notes: notes }).then(function() {
      showToast('Notes saved!', 'success');
    }).catch(function() {
      showToast('Failed to save notes.', 'error');
    });
  };

  window.deletePreOrder = function() {
    if (!confirm('Delete this pre-order permanently?')) return;
    API.delete('/admin/pre-orders/' + preOrderId).then(function() {
      showToast('Pre-order deleted.', 'success');
      window.location.href = '/admin/pre-orders';
    }).catch(function() {
      showToast('Failed to delete pre-order.', 'error');
    });
  };

  function esc(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
})();
</script>
@endsection
