@extends('admin.layouts.app')

@section('title', 'Add Fake Review')
@section('page_title', 'Add Fake Review')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Add Fake Review</h1>
  <a href="/admin/reviews" style="padding:10px 16px;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:8px;font-weight:600;text-decoration:none;font-size:13px;">Back to Reviews</a>
</div>

<div class="admin-card" style="padding: 24px; width: 100%;">
  <form id="fake-review-form" onsubmit="submitFakeReview(event)" style="display:flex; gap: 32px; align-items: flex-start;">
    
    <!-- Left Column: Products -->
    <div style="width: 320px; flex-shrink: 0; position: sticky; top: 24px;">
      <h3 style="font-size:16px; font-weight:700; color:#1a1a1a; margin-bottom:12px;">1. Target Products</h3>
      <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:16px;">
        <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:8px;">Select Products *</label>
        <p style="font-size:12px; color:#6b7280; margin-top:0; margin-bottom:12px;">Select the single product you want to generate these reviews for.</p>
        <div id="products-list-container" style="width:100%; height:400px; overflow-y:auto; border:1px solid #d1d5db; border-radius:8px; background:#fff; padding:8px;">
          <div style="padding: 10px; color: #888; font-size:13px;">Loading products...</div>
        </div>
      </div>
    </div>

    <!-- Right Column: Reviews -->
    <div style="flex: 1; min-width: 0;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
        <h3 style="font-size:16px; font-weight:700; color:#1a1a1a;">2. Reviews to Generate</h3>
        <button type="button" onclick="addReviewBlock()" style="padding:8px 16px; background:#1a1a1a; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600; display:flex; align-items:center; gap:6px;">
          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          Add Review Block
        </button>
      </div>

      <div id="reviews-container">
        <!-- Initial Review Block -->
        <div class="review-block" style="padding:16px; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:12px; background:#fff; display:flex; gap:16px; align-items:flex-start; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
          
          <div style="flex:1;">
            <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:6px;">Reviewer Name</label>
            <input type="text" class="review-name" placeholder="Random if empty" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; background:#f9fafb;">
          </div>

          <div style="width:120px; flex-shrink:0;">
            <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:6px;">Rating *</label>
            <select class="review-rating" required style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; background:#f9fafb;">
              <option value="5">★★★★★ (5)</option>
              <option value="4">★★★★☆ (4)</option>
              <option value="3">★★★☆☆ (3)</option>
              <option value="2">★★☆☆☆ (2)</option>
              <option value="1">★☆☆☆☆ (1)</option>
            </select>
          </div>

          <div style="flex:2;">
            <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:6px;">Comment</label>
            <textarea class="review-comment" placeholder="Write review content..." rows="2" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; resize:vertical; font-family:inherit; background:#f9fafb;"></textarea>
          </div>

          <div style="width:180px; flex-shrink:0;">
            <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:6px;">Date <span style="font-weight:normal; color:#888;">(Random if empty)</span></label>
            <input type="datetime-local" class="review-date" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; background:#f9fafb;">
          </div>

          <div style="flex-shrink:0; padding-top:24px;">
            <button type="button" onclick="removeReviewBlock(this)" style="background:#fee2e2; border:none; color:#ef4444; cursor:pointer; font-weight:600; font-size:13px; padding:10px; border-radius:6px; display:flex; align-items:center; justify-content:center;" title="Remove this review">
              <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
          </div>
        </div>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:12px; margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
        <a href="/admin/reviews" style="padding:10px 20px; border:1px solid #d1d5db; background:#fff; color:#374151; border-radius:8px; text-decoration:none; font-size:14px; font-weight:500;">Cancel</a>
        <button type="submit" id="btn-submit-fake-review" style="padding:10px 24px; background:#c9a96e; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:14px; font-weight:600;">Save All Reviews</button>
      </div>
    </div>
  </form>
</div>
@endsection

@section('extra_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
  loadProductsForDropdown();
});

async function loadProductsForDropdown() {
  try {
    var container = document.getElementById('products-list-container');
    container.innerHTML = '<div style="padding: 10px; color: #888; font-size:13px;">Loading products...</div>';
    var res = await API.get('/admin/products', { params: { per_page: 1000 } });
    var products = res.products || res.data || res || [];
    if (!Array.isArray(products) && products.data) products = products.data;
    
    container.innerHTML = '';
    products.forEach(function(p) {
      var label = document.createElement('label');
      label.style.display = 'flex';
      label.style.alignItems = 'center';
      label.style.padding = '8px 12px';
      label.style.cursor = 'pointer';
      label.style.borderRadius = '6px';
      label.style.marginBottom = '4px';
      label.style.transition = 'background 0.2s';
      
      label.onmouseover = function() { this.style.background = '#f3f4f6'; };
      label.onmouseout = function() { this.style.background = 'transparent'; };

      var cb = document.createElement('input');
      cb.type = 'radio';
      cb.name = 'product_id';
      cb.value = p.id;
      cb.className = 'product-radio';
      cb.style.marginRight = '12px';
      cb.style.width = '16px';
      cb.style.height = '16px';
      cb.style.accentColor = '#c9a96e';
      cb.style.cursor = 'pointer';

      var span = document.createElement('span');
      span.textContent = p.name;
      span.style.fontSize = '13px';
      span.style.color = '#374151';

      label.appendChild(cb);
      label.appendChild(span);
      container.appendChild(label);
    });
  } catch (e) {
    document.getElementById('products-list-container').innerHTML = '<div style="padding: 10px; color: #ef4444; font-size:13px;">Failed to load products</div>';
  }
}

window.addReviewBlock = function() {
  const container = document.getElementById('reviews-container');
  const firstBlock = container.querySelector('.review-block');
  const clone = firstBlock.cloneNode(true);
  
  // Clear the values of the cloned inputs
  clone.querySelector('.review-name').value = '';
  clone.querySelector('.review-comment').value = '';
  clone.querySelector('.review-date').value = '';
  clone.querySelector('.review-rating').value = '5';
  
  container.appendChild(clone);
};

window.removeReviewBlock = function(btn) {
  const container = document.getElementById('reviews-container');
  if (container.querySelectorAll('.review-block').length > 1) {
    btn.parentElement.remove();
  } else {
    showToast('You must have at least one review block.', 'error');
  }
};

window.submitFakeReview = async function(e) {
  e.preventDefault();
  var btn = document.getElementById('btn-submit-fake-review');
  btn.disabled = true;
  btn.textContent = 'Adding...';

  var selectedProductIds = Array.from(document.querySelectorAll('.product-radio:checked')).map(cb => cb.value);

  if (selectedProductIds.length === 0) {
    showToast('Please select at least one product.', 'error');
    btn.disabled = false;
    btn.textContent = 'Add Reviews';
    return;
  }

  const blocks = document.querySelectorAll('.review-block');
  var reviewsPayload = [];
  
  blocks.forEach(block => {
    reviewsPayload.push({
      reviewer_name: block.querySelector('.review-name').value.trim() || null,
      comment: block.querySelector('.review-comment').value.trim() || null,
      rating: block.querySelector('.review-rating').value,
      created_at: block.querySelector('.review-date').value || null
    });
  });

  try {
    await API.post('/admin/reviews', {
      product_ids: selectedProductIds,
      reviews: reviewsPayload
    });

    showToast('Fake reviews added successfully.', 'success');
    setTimeout(function() {
      window.location.href = '/admin/reviews';
    }, 1000);
  } catch(err) {
    showToast('Failed to add fake reviews.', 'error');
    btn.disabled = false;
    btn.textContent = 'Add Reviews';
  }
};
</script>
@endsection
