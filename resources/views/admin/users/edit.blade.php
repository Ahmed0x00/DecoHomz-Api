@extends('admin.layouts.app')

@section('title', 'Edit User')
@section('page_title', 'Edit User')

@section('content')

<div id="edit-user-app">
  <!-- Loading State -->
  <div id="loading-state" style="text-align:center;padding:60px;color:#aaa;">
    Loading user data...
  </div>

  <!-- Error State -->
  <div id="error-state" style="display:none;text-align:center;padding:60px;">
    <p style="color:#ef4444;font-size:15px;margin-bottom:16px;">Failed to load user. User may not exist.</p>
    <a href="/admin/users" style="color:#c9a96e;font-size:13px;">← Back to Users</a>
  </div>

  <!-- Edit Form -->
  <div id="edit-form" style="display:none;">
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
      <a href="/admin/users" style="color:#888;font-size:13px;text-decoration:none;">← Users</a>
      <span style="color:#e5e5e5;">|</span>
      <span style="font-size:13px;color:#666;">Edit User</span>
    </div>

    <form id="user-form" onsubmit="saveUser(event)">
      <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;">

        <!-- Left Column: Main Fields -->
        <div>
          <div class="admin-card" style="margin-bottom:24px;">
            <div class="admin-card-header">
              <div class="admin-card-title">Basic Information</div>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;gap:20px;">
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Full Name *</label>
                <input type="text" id="field-name" required style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Email Address *</label>
                <input type="email" id="field-email" required style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                  <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Phone Number</label>
                  <input type="text" id="field-phone" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
                </div>
                <div>
                  <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Role</label>
                  <select id="field-role" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
                    <option value="user">Customer</option>
                    <option value="admin">Administrator</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="admin-card" style="margin-bottom:24px;">
            <div class="admin-card-header">
              <div class="admin-card-title">Security</div>
            </div>
            <div style="padding:24px;">
              <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">New Password (Optional)</label>
                <input type="password" id="field-password" placeholder="Leave blank to keep current password" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;font-size:13px;">
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Status & Actions -->
        <div>
          <div class="admin-card">
            <div style="padding:24px;display:flex;flex-direction:column;gap:12px;">
              <button type="submit" id="save-btn" style="width:100%;padding:12px;background:#c9a96e;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                Save Changes
              </button>
              <a href="/admin/users" style="display:block;width:100%;padding:12px;background:#f3f4f6;color:#666;border:1px solid #e5e5e5;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;text-align:center;text-decoration:none;">
                Cancel
              </a>
            </div>
          </div>

          <div id="meta-info" style="margin-top:20px;padding:0 8px;font-size:12px;color:#888;">
            <p id="info-joined" style="margin-bottom:8px;"></p>
            <p id="info-id"></p>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  var userId = null;
  var userData = null;

  document.addEventListener('DOMContentLoaded', async function() {
    var parts = window.location.pathname.split('/').filter(Boolean);
    userId = parts[parts.length - 2];
    loadUser();
  });

  async function loadUser() {
    try {
      var res = await API.get('/admin/users/' + userId);
      userData = res.data || res.user || res;
      if (!userData || !userData.id) throw new Error('Not found');
      
      populateForm(userData);
      
      document.getElementById('loading-state').style.display = 'none';
      document.getElementById('edit-form').style.display = 'block';
    } catch(e) {
      document.getElementById('loading-state').style.display = 'none';
      document.getElementById('error-state').style.display = 'block';
    }
  }

  function populateForm(u) {
    document.getElementById('field-name').value = u.name || '';
    document.getElementById('field-email').value = u.email || '';
    document.getElementById('field-phone').value = u.phone || '';
    document.getElementById('field-role').value = u.role || 'user';
    
    document.getElementById('info-id').textContent = 'User ID: #' + u.id;
    if (u.created_at) {
        var date = new Date(u.created_at).toLocaleDateString('en-EG', { year: 'numeric', month: 'long', day: 'numeric' });
        document.getElementById('info-joined').textContent = 'Joined: ' + date;
    }
  }

  window.saveUser = function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-btn');
    btn.textContent = 'Saving...';
    btn.disabled = true;

    var payload = {
      name: document.getElementById('field-name').value,
      email: document.getElementById('field-email').value,
      phone: document.getElementById('field-phone').value || null,
      role: document.getElementById('field-role').value
    };
    
    var password = document.getElementById('field-password').value;
    if (password) payload.password = password;

    API.put('/admin/users/' + userId, payload).then(function() {
      showToast('User updated successfully.', 'success');
      setTimeout(function() { window.location.href = '/admin/users'; }, 800);
    }).catch(function(err) {
      showToast(err.data && err.data.message ? err.data.message : 'Failed to update user.', 'error');
      btn.textContent = 'Save Changes';
      btn.disabled = false;
    });
  };
})();
</script>
@endsection
