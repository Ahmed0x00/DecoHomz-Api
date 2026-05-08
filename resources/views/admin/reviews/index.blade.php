@extends('admin.layouts.app')

@section('title', 'Reviews')
@section('page_title', 'Reviews')

@section('content')

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;">Reviews</h1>
</div>

<!-- Stats Cards -->
<div class="stat-grid" id="stats-grid">
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#dbeafe">
      <svg viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
    </div>
    <div class="stat-card-num" id="stat-total">—</div>
    <div class="stat-card-label">Total Reviews</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#d1fae5">
      <svg viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    </div>
    <div class="stat-card-num" id="stat-approved">—</div>
    <div class="stat-card-label">Approved</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#fef3c7">
      <svg viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <div class="stat-card-num" id="stat-pending">—</div>
    <div class="stat-card-label">Pending</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#fee2e2">
      <svg viewBox="0 0 24 24" fill="none" stroke="#991b1b" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    </div>
    <div class="stat-card-num" id="stat-rejected">—</div>
    <div class="stat-card-label">Rejected</div>
  </div>
</div>

<!-- Filter Tabs -->
<div class="admin-card" style="margin-bottom:24px;">
  <div style="padding:0 24px;display:flex;gap:0;border-bottom:1px solid #eee;">
    <button class="filter-tab" data-filter="all" onclick="setFilter('all')" style="padding:14px 20px;background:none;border:none;font-size:13px;font-weight:600;color:#c9a96e;cursor:pointer;border-bottom:2px solid #c9a96e;margin-bottom:-1px;">All</button>
    <button class="filter-tab" data-filter="pending" onclick="setFilter('pending')" style="padding:14px 20px;background:none;border:none;font-size:13px;font-weight:600;color:#888;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;">Pending</button>
    <button class="filter-tab" data-filter="approved" onclick="setFilter('approved')" style="padding:14px 20px;background:none;border:none;font-size:13px;font-weight:600;color:#888;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;">Approved</button>
    <button class="filter-tab" data-filter="rejected" onclick="setFilter('rejected')" style="padding:14px 20px;background:none;border:none;font-size:13px;font-weight:600;color:#888;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;">Rejected</button>
  </div>
</div>

<!-- Reviews Table -->
<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Product</th>
        <th>User</th>
        <th>Rating</th>
        <th>Comment</th>
        <th>Status</th>
        <th>Date</th>
        <th style="width:160px;">Actions</th>
      </tr>
    </thead>
    <tbody id="reviews-tbody">
      <tr class="loading-row"><td colspan="7"></td></tr>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<div id="pagination" style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:24px;">
</div>

@endsection

@section('extra_js')
<style>
.filter-tab { transition: 0.2s; }
.filter-tab:hover { color: #1a1a1a; }
</style>
<script>
(function() {
  var currentPage = 1;
  var currentFilter = 'all';
  var allReviews = [];

  document.addEventListener('DOMContentLoaded', function() {
    loadReviews();
  });

  function setFilter(filter) {
    currentFilter = filter;
    document.querySelectorAll('.filter-tab').forEach(function(tab) {
      if (tab.dataset.filter === filter) {
        tab.style.color = '#c9a96e';
        tab.style.borderBottomColor = '#c9a96e';
      } else {
        tab.style.color = '#888';
        tab.style.borderBottomColor = 'transparent';
      }
    });
    renderFiltered();
  }
  window.setFilter = setFilter;

  async function loadReviews(page) {
    if (page) currentPage = page;

    renderTableLoading();
    try {
      var res = await API.get('/admin/reviews', { params: { per_page: 200 } });
      allReviews = res.data || res.reviews || res || [];
      if (!Array.isArray(allReviews) && allReviews.data) allReviews = allReviews.data;

      renderStats();
      renderFiltered();
    } catch(e) {
      document.getElementById('reviews-tbody').innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:30px">Failed to load reviews.</td></tr>';
    }
  }

  function renderStats() {
    var total = allReviews.length;
    var approved = 0, pending = 0, rejected = 0;

    allReviews.forEach(function(r) {
      var status = (r.status || '').toLowerCase();
      if (status === 'approved' || status === '1' || r.is_approved == 1) approved++;
      else if (status === 'rejected' || status === '2' || r.is_rejected == 1) rejected++;
      else pending++;
    });

    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-approved').textContent = approved;
    document.getElementById('stat-pending').textContent = pending;
    document.getElementById('stat-rejected').textContent = rejected;
  }

  function renderFiltered() {
    var filtered = allReviews;
    if (currentFilter !== 'all') {
      filtered = allReviews.filter(function(r) {
        var status = (r.status || '').toLowerCase();
        if (currentFilter === 'pending') {
          return status !== 'approved' && status !== 'rejected' && r.is_approved != 1 && r.is_rejected != 1;
        }
        return status === currentFilter || r.status == currentFilter || (currentFilter === 'rejected' && r.is_rejected == 1);
      });
    }
    renderTable(filtered);
  }

  function renderTableLoading() {
    document.getElementById('reviews-tbody').innerHTML = '<tr class="loading-row"><td colspan="7"></td></tr>';
  }

  function renderTable(reviews) {
    var tbody = document.getElementById('reviews-tbody');
    if (!reviews || reviews.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#aaa;padding:40px">No reviews found.</td></tr>';
      return;
    }
    tbody.innerHTML = reviews.map(function(r) {
      var productName = r.product ? r.product.name : (r.product_name || '—');
      var userName = r.user ? r.user.name : (r.user_name || 'Anonymous');
      var rating = r.rating || 0;
      var stars = '★'.repeat(rating) + '☆'.repeat(5 - rating);
      var comment = r.comment || r.content || '';
      var truncated = comment.length > 60 ? comment.substring(0, 60) + '...' : comment;

      var status = (r.status || '').toLowerCase();
      var isApproved = status === 'approved' || status === '1' || r.is_approved == 1;
      var isRejected = status === 'rejected' || status === '2' || r.is_rejected == 1;

      var statusClass, statusLabel;
      if (isApproved) {
        statusClass = 'badge-approved';
        statusLabel = 'Approved';
      } else if (isRejected) {
        statusClass = 'badge-rejected';
        statusLabel = 'Rejected';
      } else {
        statusClass = 'badge-pending';
        statusLabel = 'Pending';
      }

      var date = r.created_at
        ? new Date(r.created_at).toLocaleDateString('en-EG', { year: 'numeric', month: 'short', day: 'numeric' })
        : '—';

      var actions = '';
      if (!isApproved && !isRejected) {
        actions += '<div style="display:flex;gap:8px;">' +
          '<button onclick="approveReview(' + r.id + ')" style="flex:1;padding:6px;background:#d1fae5;color:#065f46;border:none;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M20 6L9 17l-5-5"/></svg> Approve</button>' +
          '<button onclick="rejectReview(' + r.id + ')" style="flex:1;padding:6px;background:#fee2e2;color:#991b1b;border:none;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M18 6L6 18M6 6l12 12"/></svg> Reject</button>' +
        '</div>';
      } else {
        actions = '<span style="color:#aaa;font-size:11px;font-style:italic;">No actions available</span>';
      }

      return '<tr>' +
        '<td style="font-weight:600;">' + productName + '</td>' +
        '<td>' + userName + '</td>' +
        '<td style="color:#f59e0b;letter-spacing:2px;">' + stars + '</td>' +
        '<td style="color:#666;font-size:12px;" title="' + comment.replace(/"/g, '&quot;') + '">' + truncated + '</td>' +
        '<td><span class="badge-status ' + statusClass + '">' + statusLabel + '</span></td>' +
        '<td>' + date + '</td>' +
        '<td>' + actions + '</td>' +
        '</tr>';
    }).join('');
  }

  window.approveReview = function(id) {
    API.patch('/admin/reviews/' + id + '/approve').then(function() {
      showToast('Review approved.', 'success');
      setTimeout(loadReviews, 500); // Small delay to ensure DB sync
    }).catch(function() {
      showToast('Failed to approve review.', 'error');
    });
  };

  window.rejectReview = function(id) {
    API.patch('/admin/reviews/' + id + '/reject').then(function() {
      showToast('Review rejected.', 'success');
      setTimeout(loadReviews, 500); // Small delay to ensure DB sync
    }).catch(function() {
      showToast('Failed to reject review.', 'error');
    });
  };
})();
</script>
@endsection
