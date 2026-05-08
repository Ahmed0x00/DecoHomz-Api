@extends('admin.layouts.app')

@section('title', 'Add User')
@section('page_title', 'Add User')

@section('content')
  <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
    <a href="/admin/users" style="color:#888;font-size:13px;text-decoration:none;">← Users</a>
    <span style="color:#e5e5e5;">|</span>
    <span style="font-size:13px;color:#666;">Add New User</span>
  </div>

  <div class="admin-card" style="max-width:800px;">
    <div class="admin-card-header">
      <div class="admin-card-title">User Information</div>
    </div>
    <div style="padding:24px;">
      <form id="user-form" onsubmit="createUser(event)">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
          <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Full Name *</label>
            <input type="text" id="field-name" required
              style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
          </div>
          <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Email Address
              *</label>
            <input type="email" id="field-email" required
              style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
          </div>
          <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Phone Number</label>
            <input type="text" id="field-phone"
              style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
          </div>
          <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Role</label>
            <select id="field-role"
              style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
              <option value="user">Customer</option>
              <option value="admin">Administrator</option>
              <option value="support">Support</option>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Password *</label>
            <input type="password" id="field-password" required
              style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
          </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;">
          <a href="/admin/users"
            style="padding:10px 20px;background:#f3f4f6;color:#666;border:1px solid #e5e5e5;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">Cancel</a>
          <button type="submit" id="save-btn"
            style="padding:10px 24px;background:#c9a96e;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Create
            User</button>
        </div>
      </form>
    </div>
  </div>

@endsection

@section('extra_js')
  <script>
    (function () {
      window.createUser = function (e) {
        e.preventDefault();
        var btn = document.getElementById('save-btn');
        btn.textContent = 'Creating...';
        btn.disabled = true;

        var payload = {
          name: document.getElementById('field-name').value,
          email: document.getElementById('field-email').value,
          phone: document.getElementById('field-phone').value || null,
          role: document.getElementById('field-role').value,
          password: document.getElementById('field-password').value
        };

        API.post('/admin/users', payload).then(function (res) {
          showToast('User created successfully.', 'success');
          setTimeout(function () { window.location.href = '/admin/users'; }, 800);
        }).catch(function (err) {
          showToast(err.data && err.data.message ? err.data.message : 'Failed to create user.', 'error');
          btn.textContent = 'Create User';
          btn.disabled = false;
        });
      };
    })();
  </script>
@endsection