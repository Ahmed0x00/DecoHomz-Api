@extends('admin.layouts.app')

@section('title', 'User Details')
@section('page_title', 'User Details')

@section('content')
<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title">User</div>
    <a href="/admin/users" class="admin-card-link">← Back</a>
  </div>
  <div style="padding:24px" id="user-box">
    <p style="color:#aaa">Loading...</p>
  </div>
</div>
@endsection

@section('extra_js')
<script>
(function() {
  var userId = null;

  document.addEventListener('DOMContentLoaded', function() {
    var parts = window.location.pathname.split('/').filter(Boolean);
    userId = parts[parts.length - 1];
    loadUser();
  });

  async function loadUser() {
    var box = document.getElementById('user-box');
    try {
      var res = await API.get('/admin/users/' + userId);
      var u = res.user || res.data || res;
      if (!u || !u.id) throw new Error('not found');

      box.innerHTML = '' +
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">' +
          row('ID', u.id) +
          row('Name', esc(u.name || '—')) +
          row('Email', esc(u.email || '—')) +
          row('Phone', esc(u.phone || '—')) +
          row('Role', esc(u.role || 'user')) +
          row('Joined', u.created_at ? new Date(u.created_at).toLocaleString() : '—') +
        '</div>';
    } catch(e) {
      box.innerHTML = '<p style="color:#ef4444">Failed to load user.</p>';
    }
  }

  function row(label, value) {
    return '<div style="padding:12px;border:1px solid #eee;border-radius:10px;background:#fafafa">' +
      '<div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.5px;font-weight:700;margin-bottom:6px">' + label + '</div>' +
      '<div style="font-size:13px;color:#111;font-weight:600">' + value + '</div>' +
    '</div>';
  }
})();
</script>
@endsection
