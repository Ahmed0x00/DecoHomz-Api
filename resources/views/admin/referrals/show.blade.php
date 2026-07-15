@extends('admin.layouts.app')

@section('title', 'Referral Details')
@section('page_title', 'Referral Details')

@section('content')
<div style="padding: 0 16px;">

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <div style="display:flex;align-items:center;gap:16px;">
    <a href="/admin/referrals" style="color:var(--text-muted);text-decoration:none;display:flex;align-items:center;gap:4px;font-size:14px;font-weight:600;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      Back
    </a>
    <h1 style="font-size:24px;font-weight:700;color:#1a1a1a;margin:0;">Referral <span id="referral-id"></span></h1>
    <div id="referral-status-badge"></div>
  </div>
  <div style="display:flex;gap:12px;" id="action-buttons">
    <!-- Actions rendered via JS -->
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
  
  <!-- Main Info -->
  <div style="display:flex;flex-direction:column;gap:24px;">
    
    <!-- Referral Summary -->
    <div class="admin-card" style="background:#fff;padding:24px;border-radius:16px;border:1px solid #eee;">
      <h3 style="margin:0 0 16px 0;font-size:16px;font-weight:700;border-bottom:1px solid #eee;padding-bottom:12px;">Commission Details</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
        <div>
          <div style="color:#666;font-size:12px;margin-bottom:4px;font-weight:600;">Order Subtotal</div>
          <div style="font-size:20px;font-weight:700;color:#1a1a1a;" id="order-subtotal"></div>
        </div>
        <div>
          <div style="color:#666;font-size:12px;margin-bottom:4px;font-weight:600;">Affiliate Discount applied</div>
          <div style="font-size:20px;font-weight:700;color:#1a1a1a;" id="discount-amount"></div>
        </div>
        <div>
          <div style="color:#666;font-size:12px;margin-bottom:4px;font-weight:600;">Commission Amount</div>
          <div style="font-size:20px;font-weight:700;color:#16a34a;" id="commission-amount"></div>
        </div>
      </div>
    </div>

    <!-- Order Info -->
    <div class="admin-card" style="background:#fff;padding:24px;border-radius:16px;border:1px solid #eee;">
      <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #eee;padding-bottom:12px;margin-bottom:16px;">
        <h3 style="margin:0;font-size:16px;font-weight:700;">Order Information</h3>
        <a id="order-link" href="#" style="color:var(--primary);font-size:13px;font-weight:600;text-decoration:none;">View Order</a>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div>
          <div style="color:#666;font-size:12px;margin-bottom:4px;font-weight:600;">Order Number</div>
          <div style="font-weight:600;color:#1a1a1a;" id="order-number"></div>
        </div>
        <div>
          <div style="color:#666;font-size:12px;margin-bottom:4px;font-weight:600;">Order Total</div>
          <div style="font-weight:600;color:#1a1a1a;" id="order-total"></div>
        </div>
        <div>
          <div style="color:#666;font-size:12px;margin-bottom:4px;font-weight:600;">Payment Status</div>
          <div style="font-weight:600;color:#1a1a1a;text-transform:capitalize;" id="payment-status"></div>
        </div>
        <div>
          <div style="color:#666;font-size:12px;margin-bottom:4px;font-weight:600;">Order Status</div>
          <div style="font-weight:600;color:#1a1a1a;text-transform:capitalize;" id="order-status"></div>
        </div>
      </div>
    </div>
    
  </div>

  <!-- Sidebar -->
  <div style="display:flex;flex-direction:column;gap:24px;">
    
    <!-- Affiliate Info -->
    <div class="admin-card" style="background:#fff;padding:24px;border-radius:16px;border:1px solid #eee;">
      <h3 style="margin:0 0 16px 0;font-size:14px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:0.5px;">Affiliate Info</h3>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <div style="width:40px;height:40px;border-radius:20px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;" id="affiliate-avatar">A</div>
        <div>
          <a id="affiliate-name" href="#" style="font-weight:600;color:#1a1a1a;text-decoration:none;font-size:15px;display:block;">Unknown</a>
          <div style="font-size:13px;color:#666;" id="affiliate-email"></div>
        </div>
      </div>
      <div style="background:#f9f9f9;padding:12px;border-radius:8px;font-size:13px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
          <span style="color:#666;font-weight:600;">Referral Code</span>
          <span style="font-weight:700;color:#1a1a1a;" id="affiliate-code"></span>
        </div>
      </div>
    </div>

    <!-- Referred User Info -->
    <div class="admin-card" style="background:#fff;padding:24px;border-radius:16px;border:1px solid #eee;">
      <h3 style="margin:0 0 16px 0;font-size:14px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:0.5px;">Referred Customer</h3>
      <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:40px;height:40px;border-radius:20px;background:#f3f4f6;color:#6b7280;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;" id="customer-avatar">C</div>
        <div>
          <div style="font-weight:600;color:#1a1a1a;font-size:15px;" id="customer-name">Unknown</div>
          <div style="font-size:13px;color:#666;" id="customer-email"></div>
        </div>
      </div>
      <div style="margin-top:16px;padding-top:16px;border-top:1px solid #eee;font-size:13px;" id="fraud-flags-container">
        <!-- Fraud flags -->
      </div>
    </div>

    <!-- Timeline -->
    <div class="admin-card" style="background:#fff;padding:24px;border-radius:16px;border:1px solid #eee;">
      <h3 style="margin:0 0 16px 0;font-size:14px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:0.5px;">Timeline</h3>
      <div style="display:flex;flex-direction:column;gap:12px;font-size:13px;">
        <div style="display:flex;justify-content:space-between;">
          <span style="color:#666;font-weight:600;">Created</span>
          <span style="color:#1a1a1a;font-weight:500;" id="date-created"></span>
        </div>
        <div style="display:flex;justify-content:space-between;display:none;" id="date-hold-row">
          <span style="color:#666;font-weight:600;">Hold Expires</span>
          <span style="color:#1a1a1a;font-weight:500;" id="date-hold"></span>
        </div>
        <div style="display:flex;justify-content:space-between;display:none;" id="date-approved-row">
          <span style="color:#666;font-weight:600;">Approved</span>
          <span style="color:#1a1a1a;font-weight:500;" id="date-approved"></span>
        </div>
        <div style="display:flex;justify-content:space-between;display:none;" id="date-paid-row">
          <span style="color:#666;font-weight:600;">Paid</span>
          <span style="color:#1a1a1a;font-weight:500;" id="date-paid"></span>
        </div>
      </div>
    </div>

  </div>

</div>

</div>
@endsection

@section('extra_js')
<script>
(function() {
  var referralId = window.location.pathname.split('/').pop();

  document.addEventListener('DOMContentLoaded', function() {
    loadReferral();
  });

  function loadReferral() {
    API.get('/admin/referrals/' + referralId).then(res => {
      var r = res.data || res;
      
      document.getElementById('referral-id').textContent = '#' + r.id;
      
      // Status Badge
      var statusColor = '#9a3412';
      var statusBg = '#fff7ed';
      if (r.commission_status === 'approved' || r.commission_status === 'paid') {
          statusColor = '#166534';
          statusBg = '#f0fdf4';
      } else if (r.commission_status === 'revoked' || r.commission_status === 'clawback' || r.commission_status === 'rejected') {
          statusColor = '#991b1b';
          statusBg = '#fef2f2';
      } else if (r.commission_status === 'holding') {
          statusColor = '#1e40af';
          statusBg = '#eff6ff';
      }
      document.getElementById('referral-status-badge').innerHTML = '<span style="display:inline-block;padding:6px 12px;border-radius:99px;font-size:13px;font-weight:700;background:' + statusBg + ';color:' + statusColor + ';text-transform:capitalize;">' + esc(r.commission_status) + '</span>';

      // Actions
      var actionsHtml = '';
      if (r.commission_status !== 'paid' && r.commission_status !== 'clawback' && r.commission_status !== 'rejected') {
          if (r.commission_status !== 'approved') {
              actionsHtml += '<button onclick="updateReferralStatus(\'approved\')" style="background:#fff;color:#16a34a;border:1px solid #bbf7d0;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:0.2s;">Approve Manually</button>';
              actionsHtml += '<button onclick="updateReferralStatus(\'rejected\')" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:0.2s;">Reject</button>';
          } else {
              actionsHtml += '<button onclick="processPayoutSingle()" style="background:#1a1a1a;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:0.2s;">Process Payout</button>';
              actionsHtml += '<button onclick="updateReferralStatus(\'rejected\')" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:0.2s;">Revoke / Reject</button>';
          }
      }
      if (r.commission_status === 'paid') {
          actionsHtml += '<button onclick="updateReferralStatus(\'clawback\')" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:0.2s;">Mark Clawback (Refunded)</button>';
      }
      document.getElementById('action-buttons').innerHTML = actionsHtml;

      // Amounts
      document.getElementById('order-subtotal').textContent = 'EGP ' + parseFloat(r.order_subtotal).toLocaleString();
      document.getElementById('discount-amount').textContent = 'EGP ' + parseFloat(r.discount_amount).toLocaleString();
      document.getElementById('commission-amount').textContent = 'EGP ' + parseFloat(r.commission_amount).toLocaleString();

      // Order
      if (r.order) {
          document.getElementById('order-number').textContent = r.order.order_number;
          document.getElementById('order-total').textContent = 'EGP ' + parseFloat(r.order.total).toLocaleString();
          document.getElementById('payment-status').textContent = r.order.payment_status;
          document.getElementById('order-status').textContent = r.order.status;
          document.getElementById('order-link').href = '/admin/orders/' + r.order.id;
      }

      // Affiliate
      if (r.affiliate && r.affiliate.user) {
          document.getElementById('affiliate-name').textContent = r.affiliate.user.name;
          document.getElementById('affiliate-email').textContent = r.affiliate.user.email;
          document.getElementById('affiliate-avatar').textContent = r.affiliate.user.name.charAt(0).toUpperCase();
          document.getElementById('affiliate-name').href = '/admin/affiliates/' + r.affiliate.id;
          document.getElementById('affiliate-code').textContent = r.affiliate.referral_code;
      }

      // Customer
      if (r.referred_user) {
          document.getElementById('customer-name').textContent = r.referred_user.name;
          document.getElementById('customer-email').textContent = r.referred_user.email;
          document.getElementById('customer-avatar').textContent = r.referred_user.name.charAt(0).toUpperCase();
      } else if (r.order && r.order.shipping_address) {
          document.getElementById('customer-name').textContent = r.order.shipping_address.first_name + ' ' + r.order.shipping_address.last_name;
          document.getElementById('customer-email').textContent = r.order.shipping_address.email || r.order.shipping_address.phone;
          document.getElementById('customer-avatar').textContent = r.order.shipping_address.first_name.charAt(0).toUpperCase();
      }

      // Fraud flags
      if (r.fraud_flags && typeof r.fraud_flags === 'object') {
          var flags = [];
          if (r.fraud_flags.self_referral) flags.push('<span style="color:#ef4444;font-weight:600;">Self Referral</span>');
          if (r.fraud_flags.same_ip) flags.push('<span style="color:#ef4444;font-weight:600;">Same IP Address (' + esc(r.buyer_ip_address) + ')</span>');
          if (flags.length > 0) {
              document.getElementById('fraud-flags-container').innerHTML = '<div style="font-weight:600;color:#666;margin-bottom:8px;">Fraud Flags Detected:</div>' + flags.join('<br>');
          } else {
              document.getElementById('fraud-flags-container').innerHTML = '<div style="color:#16a34a;font-weight:600;">✓ No fraud flags detected</div>';
          }
      }

      // Timeline
      document.getElementById('date-created').textContent = formatDate(r.created_at);
      if (r.hold_expires_at) {
          document.getElementById('date-hold-row').style.display = 'flex';
          document.getElementById('date-hold').textContent = formatDate(r.hold_expires_at);
      }
      if (r.approved_at) {
          document.getElementById('date-approved-row').style.display = 'flex';
          document.getElementById('date-approved').textContent = formatDate(r.approved_at);
      }
      if (r.paid_at) {
          document.getElementById('date-paid-row').style.display = 'flex';
          document.getElementById('date-paid').textContent = formatDate(r.paid_at);
          document.getElementById('date-paid-row').insertAdjacentHTML('afterend', '<div style="display:flex;justify-content:space-between;margin-top:4px;"><span style="color:#666;font-weight:600;">Ref</span><span style="color:#1a1a1a;font-weight:500;">' + esc(r.payout_reference) + '</span></div>');
      }

    }).catch(err => {
      showToast('Failed to load referral details', 'error');
    });
  }

  window.updateReferralStatus = function(status) {
      var actionWord = status === 'approved' ? 'approve' : (status === 'rejected' ? 'reject' : 'mark as ' + status);
      if (!confirm('Are you sure you want to ' + actionWord + ' this referral?')) return;

      var data = { referral_ids: [referralId], status: status };
      if (status === 'rejected' || status === 'clawback') {
          var reason = prompt('Reason for ' + status + ':');
          if (reason) data.reason = reason;
      }

      API.post('/admin/referrals/update-status', data).then(res => {
          showToast(res.message || 'Status updated', 'success');
          loadReferral();
      }).catch(err => {
          showToast(err.response?.data?.message || err.message || 'Failed to update status', 'error');
      });
  }

  window.processPayoutSingle = function() {
      var ref = prompt('Enter payout reference (e.g. Bank Transfer ID):');
      if (!ref) return;

      // We need affiliate_id for this
      API.get('/admin/referrals/' + referralId).then(res => {
          var r = res.data || res;
          API.post('/admin/referrals/process-payouts', {
              affiliate_id: r.affiliate_id,
              referral_ids: [r.id],
              payout_reference: ref
          }).then(pRes => {
              showToast(pRes.message || 'Payout processed', 'success');
              loadReferral();
          }).catch(err => {
              showToast(err.response?.data?.message || err.message || 'Failed to process payout', 'error');
          });
      });
  }

})();
</script>
@endsection
