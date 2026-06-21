@extends('layouts.app')

@section('title', 'Pre-Order — DecoHomz')

@section('content')

<div class="preorder-page">
  <div class="preorder-header animate-fade-down">
    <div class="sec-label">{{ __('Custom Furniture') }}</div>
    <h1>{{ __('Design Your Dream Furniture') }}</h1>
    <p>{{ __('Found a design you love? Share it with us and we will craft it for you. Upload inspiration images from Pinterest, Instagram, or any website and tell us what you need.') }}</p>
  </div>

  <div class="preorder-grid">

    {{-- Left: Inspiration Sources --}}
    <div class="preorder-sources animate-fade-right">
      <h2>{{ __('Find Inspiration') }}</h2>
      <p>{{ __('Browse these platforms for furniture ideas, then upload your favorite designs.') }}</p>
      <div class="source-links">
        <a href="https://pinterest.com/search/pins/?q=furniture+design" target="_blank" rel="noopener" class="source-card">
          <svg viewBox="0 0 24 24" fill="currentColor" style="width:28px;height:28px;color:#E60023"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>
          <span>Pinterest</span>
        </a>
        <a href="https://www.instagram.com/explore/tags/furniture/" target="_blank" rel="noopener" class="source-card">
          <svg viewBox="0 0 24 24" fill="currentColor" style="width:28px;height:28px;color:#E4405F"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
          <span>Instagram</span>
        </a>
        <a href="https://www.houzz.com/photos/furniture" target="_blank" rel="noopener" class="source-card">
          <svg viewBox="0 0 24 24" fill="currentColor" style="width:28px;height:28px;color:#7CC5E3"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
          <span>Houzz</span>
        </a>
        <a href="https://www.dezeen.com/tag/furniture/" target="_blank" rel="noopener" class="source-card">
          <svg viewBox="0 0 24 24" fill="currentColor" style="width:28px;height:28px;color:#1A1A1A"><path d="M3 3h18v18H3V3zm2 2v14h14V5H5z"/></svg>
          <span>Dezeen</span>
        </a>
        <a href="https://www.archdaily.com/search/projects?q=furniture" target="_blank" rel="noopener" class="source-card">
          <svg viewBox="0 0 24 24" fill="currentColor" style="width:28px;height:28px;color:#333"><path d="M3 21V9l9-7 9 7v12h-6v-6h-6v6H3z"/></svg>
          <span>ArchDaily</span>
        </a>
      </div>
    </div>

    {{-- Right: Form --}}
    <div class="preorder-form-wrap animate-fade-left">
      <h2>{{ __('Submit Your Design') }}</h2>

      <form id="preorder-form" onsubmit="event.preventDefault(); submitPreOrder();">

        {{-- Image Upload --}}
        <div class="form-group">
          <label>{{ __('Reference Images') }} <span style="color:var(--color-error)">*</span></label>
          <p class="form-hint">{{ __('Upload up to 10 images (max 10MB each). Screenshots, Pinterest pins, or any furniture photo.') }}</p>
          <div class="upload-zone" id="upload-zone">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:40px;height:40px;color:var(--color-text-faint);margin-bottom:8px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <p style="font-size:14px;color:var(--color-text-muted);margin-bottom:4px">{{ __('Drag & drop images here or') }} <span style="color:var(--color-accent-dark);font-weight:600;cursor:pointer">{{ __('browse') }}</span></p>
            <p style="font-size:12px;color:var(--color-text-faint)">{{ __('JPEG, PNG, WebP — Max 10MB each') }}</p>
            <input type="file" id="preorder-images" multiple accept="image/jpeg,image/png,image/webp,image/gif" style="display:none">
          </div>
          <div id="upload-preview" class="upload-preview"></div>
        </div>

        {{-- Contact Details --}}
        <div class="form-row">
          <div class="form-group">
            <label for="preorder-name">{{ __('Full Name') }} <span style="color:var(--color-error)">*</span></label>
            <input type="text" id="preorder-name" required placeholder="{{ __('Ahmed Mohamed') }}">
          </div>
          <div class="form-group">
            <label for="preorder-phone">{{ __('Phone Number') }} <span style="color:var(--color-error)">*</span></label>
            <input type="tel" id="preorder-phone" required placeholder="010xxxxxxxxx">
          </div>
        </div>

        <div class="form-group">
          <label for="preorder-email">{{ __('Email') }} <span style="font-size:12px;color:var(--color-text-faint)">{{ __('(optional)') }}</span></label>
          <input type="email" id="preorder-email" placeholder="ahmed@example.com">
        </div>

        <div class="form-group">
          <label for="preorder-notes">{{ __('Tell Us What You Want') }} <span style="color:var(--color-error)">*</span></label>
          <textarea id="preorder-notes" rows="5" required placeholder="{{ __('Describe the furniture you want: dimensions, colors, material, style, number of pieces, budget range, or any other details...') }}"></textarea>
        </div>

        <button type="submit" id="btn-preorder" class="btn-dark" style="width:100%;padding:16px;font-size:15px">
          {{ __('Submit Pre-Order Request') }}
        </button>
      </form>
    </div>

  </div>
</div>

@endsection

@section('extra_js')
<script>
var selectedFiles = [];

// Auto-fill form with logged-in user data
(function() {
  var user = JSON.parse(localStorage.getItem('dh_user') || 'null');
  if (!user) return;
  if (user.name) {
    var nameEl = document.getElementById('preorder-name');
    if (nameEl && !nameEl.value) nameEl.value = user.name;
  }
  if (user.email) {
    var emailEl = document.getElementById('preorder-email');
    if (emailEl && !emailEl.value) emailEl.value = user.email;
  }
  if (user.phone) {
    var phoneEl = document.getElementById('preorder-phone');
    if (phoneEl && !phoneEl.value) phoneEl.value = user.phone;
  }
})();

var uploadZone = document.getElementById('upload-zone');
var fileInput = document.getElementById('preorder-images');
var previewContainer = document.getElementById('upload-preview');

uploadZone.addEventListener('click', function() { fileInput.click(); });

uploadZone.addEventListener('dragover', function(e) {
  e.preventDefault();
  uploadZone.style.borderColor = 'var(--color-accent)';
  uploadZone.style.background = 'var(--color-accent-glow)';
});

uploadZone.addEventListener('dragleave', function() {
  uploadZone.style.borderColor = '';
  uploadZone.style.background = '';
});

uploadZone.addEventListener('drop', function(e) {
  e.preventDefault();
  uploadZone.style.borderColor = '';
  uploadZone.style.background = '';
  handleFiles(e.dataTransfer.files);
});

fileInput.addEventListener('change', function() {
  handleFiles(fileInput.files);
  fileInput.value = '';
});

function handleFiles(files) {
  Array.from(files).forEach(function(file) {
    if (!file.type.startsWith('image/')) return;
    if (file.size > 10 * 1024 * 1024) {
      showToast('{{ __("File too large (max 10MB)") }}', 'error');
      return;
    }
    if (selectedFiles.length >= 10) {
      showToast('{{ __("Maximum 10 images allowed") }}', 'error');
      return;
    }
    selectedFiles.push(file);
  });
  renderPreviews();
}

function renderPreviews() {
  previewContainer.innerHTML = '';
  selectedFiles.forEach(function(file, i) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var div = document.createElement('div');
      div.className = 'preview-thumb';
      div.innerHTML = '<img src="' + e.target.result + '" alt="Preview">' +
        '<button type="button" class="preview-remove" onclick="removePreview(' + i + ')">&times;</button>';
      previewContainer.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
}

window.removePreview = function(index) {
  selectedFiles.splice(index, 1);
  renderPreviews();
};

window.submitPreOrder = function() {
  var name = document.getElementById('preorder-name').value.trim();
  var phone = document.getElementById('preorder-phone').value.trim();
  var email = document.getElementById('preorder-email').value.trim();
  var notes = document.getElementById('preorder-notes').value.trim();

  if (!selectedFiles.length) {
    showToast('{{ __("Please upload at least one image") }}', 'error');
    return;
  }
  if (!name || !phone || !notes) {
    showToast('{{ __("Please fill in all required fields") }}', 'error');
    return;
  }

  var btn = document.getElementById('btn-preorder');
  btn.disabled = true;
  btn.textContent = '{{ __("Submitting...") }}';

  var formData = new FormData();
  formData.append('name', name);
  formData.append('phone', phone);
  if (email) formData.append('email', email);
  formData.append('notes', notes);
  selectedFiles.forEach(function(file) {
    formData.append('images[]', file);
  });

  var fetchHeaders = {
    'Accept': 'application/json',
    'X-Session-ID': localStorage.getItem('dh_session_id') || ''
  };
  var authToken = localStorage.getItem('dh_token');
  if (authToken) fetchHeaders['Authorization'] = 'Bearer ' + authToken;

  fetch('/api/pre-orders', {
    method: 'POST',
    headers: fetchHeaders,
    body: formData
  }).then(function(res) {
    return res.json().then(function(data) {
      if (!res.ok) throw data;
      return data;
    });
  }).then(function(data) {
    window.location.href = '/pre-order/confirmed';
  }).catch(function(err) {
    var msg = (err && err.message) ? err.message : '{{ __("Something went wrong. Please try again.") }}';
    if (err && err.errors) {
      var first = Object.values(err.errors)[0];
      if (first && first[0]) msg = first[0];
    }
    showToast(msg, 'error');
  }).finally(function() {
    btn.disabled = false;
    btn.textContent = '{{ __("Submit Pre-Order Request") }}';
  });
};
</script>

<style>
.preorder-page { max-width: var(--max-width); margin: 40px auto 80px; padding: 0 40px; }
.preorder-header { text-align: center; margin-bottom: 48px; }
.preorder-header h1 { font-size: 36px; font-weight: 800; color: var(--color-primary); letter-spacing: -0.02em; margin-bottom: 12px; }
.preorder-header p { font-size: 16px; color: var(--color-text-muted); max-width: 600px; margin: 0 auto; line-height: 1.6; }

.preorder-grid { display: grid; grid-template-columns: 1fr 1.4fr; gap: 48px; align-items: start; }

.preorder-sources h2 { font-size: 20px; font-weight: 700; color: var(--color-primary); margin-bottom: 8px; }
.preorder-sources > p { font-size: 14px; color: var(--color-text-muted); margin-bottom: 20px; line-height: 1.5; }
.source-links { display: flex; flex-direction: column; gap: 10px; }
.source-card { display: flex; align-items: center; gap: 14px; padding: 14px 18px; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); text-decoration: none; color: var(--color-text); font-weight: 600; font-size: 14px; transition: all var(--duration-fast) ease; }
.source-card:hover { border-color: var(--color-accent); background: var(--color-accent-glow); transform: translateX(4px); }

.preorder-form-wrap { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 32px; }
.preorder-form-wrap h2 { font-size: 20px; font-weight: 700; color: var(--color-primary); margin-bottom: 24px; }

.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 6px; }
.form-hint { font-size: 12px; color: var(--color-text-faint); margin-bottom: 10px; }
.form-group input, .form-group textarea {
  width: 100%; padding: 12px 14px; border: 1.5px solid var(--color-border); border-radius: var(--radius-sm);
  font-size: 14px; font-family: var(--font-body); color: var(--color-text); background: var(--color-bg);
  transition: border-color var(--duration-fast) ease;
}
.form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--color-accent); box-shadow: 0 0 0 3px var(--color-accent-glow); }
.form-group textarea { resize: vertical; min-height: 100px; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.upload-zone {
  border: 2px dashed var(--color-border); border-radius: var(--radius-md); padding: 32px; text-align: center;
  cursor: pointer; transition: all var(--duration-fast) ease;
}
.upload-zone:hover { border-color: var(--color-accent); background: var(--color-accent-glow); }

.upload-preview { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
.preview-thumb { position: relative; width: 80px; height: 80px; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--color-border); }
.preview-thumb img { width: 100%; height: 100%; object-fit: cover; }
.preview-remove {
  position: absolute; top: 2px; right: 2px; width: 20px; height: 20px; border-radius: 50%;
  background: rgba(0,0,0,0.6); color: #fff; border: none; font-size: 14px; cursor: pointer;
  display: flex; align-items: center; justify-content: center; line-height: 1;
}

@media (max-width: 768px) {
  .preorder-page { padding: 0 20px; }
  .preorder-grid { grid-template-columns: 1fr; gap: 32px; }
  .form-row { grid-template-columns: 1fr; }
  .preorder-header h1 { font-size: 28px; }
}
</style>
@endsection
