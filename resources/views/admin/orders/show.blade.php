@extends('admin.layouts.app')
@section('title', 'Order #' . $id)

@section('content')
    <style>
        :root {
            --primary: #c9a96e;
            --primary-dark: #b8985d;
            --primary-light: #fdfaf3;
            --secondary: #2C1F14;
            --text-main: #1a1a1a;
            --text-muted: #6b7280;
            --bg-card: #ffffff;
            --border: #f1f1f1;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .order-back-link {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            transition: 0.2s;
        }

        .order-back-link:hover {
            color: var(--primary);
        }

        .order-id-badge {
            background: var(--primary-light);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .order-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 32px;
        }

        .order-main {
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        .order-sidebar {
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        .premium-card {
            background: var(--bg-card);
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: 0.3s;
        }

        .premium-card:hover {
            box-shadow: var(--shadow);
        }

        .premium-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .premium-card-header svg {
            color: var(--primary);
        }

        .premium-card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--secondary);
            margin: 0;
        }

        .premium-card-body {
            padding: 24px;
        }

        .order-info-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .info-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-main);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending { background: #fff7ed; color: #9a3412; }
        .status-processing { background: #eff6ff; color: #1e40af; }
        .status-shipped { background: #fdf4ff; color: #86198f; }
        .status-delivered { background: #f0fdf4; color: #166534; }
        .status-cancelled { background: #fef2f2; color: #991b1b; }

        .item-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background: #f9f9f9;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 2px;
        }

        .item-meta {
            font-size: 12px;
            color: var(--text-muted);
        }

        .item-price-info {
            text-align: right;
        }

        .total-summary {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 2px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: flex-end;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            width: 240px;
            font-size: 14px;
        }

        .summary-row.grand-total {
            font-size: 18px;
            font-weight: 700;
            color: var(--secondary);
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--border);
        }

        .action-select {
            padding: 8px 12px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-main);
            outline: none;
            transition: 0.2s;
            background: #fff;
        }

        .action-select:focus {
            border-color: var(--primary);
        }

        .premium-btn {
            padding: 8px 16px;
            background: var(--secondary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .premium-btn:hover {
            background: #3d2c1c;
            transform: translateY(-1px);
        }

        .premium-btn-gold {
            background: var(--primary);
        }

        .premium-btn-gold:hover {
            background: var(--primary-dark);
        }

        .tracking-input-group {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }

        .premium-input {
            flex: 1;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            transition: 0.2s;
        }

        .premium-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        @media (max-width: 1024px) {
            .order-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="page-header">
        <a href="/admin/orders" class="order-back-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back to Orders
        </a>
        <div class="order-id-badge">ORDER #{{ $id }}</div>
    </div>

    <div class="order-grid">
        <!-- Main Content -->
        <div class="order-main">
            <!-- Order Summary Card -->
            <div class="premium-card">
                <div class="premium-card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h5 class="premium-card-title">Order Overview</h5>
                </div>
                <div id="orderDetails" class="premium-card-body">
                    <div style="text-align: center; color: var(--text-muted); padding: 40px;">Loading order details...</div>
                </div>
            </div>

            <!-- Order Items Card -->
            <div class="premium-card">
                <div class="premium-card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <h5 class="premium-card-title">Order Items</h5>
                </div>
                <div class="premium-card-body">
                    <div id="orderItems">
                        <div style="text-align: center; color: var(--text-muted); padding: 40px;">Loading items...</div>
                    </div>
                    
                    <div id="orderTotals" class="total-summary">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>

            <!-- Tracking Card -->
            <div class="premium-card">
                <div class="premium-card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                    <h5 class="premium-card-title">Shipping & Tracking</h5>
                </div>
                <div class="premium-card-body">
                    <label class="info-label">Tracking Number</label>
                    <div class="tracking-input-group">
                        <input type="text" id="trackingNumber" class="premium-input" placeholder="Enter tracking ID (e.g. ARMX-123456)">
                        <button type="button" class="premium-btn" onclick="updateTracking()">Update Status</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="order-sidebar">
            <!-- Customer Card -->
            <div class="premium-card">
                <div class="premium-card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <path d="M20 8v6M23 11h-6"/>
                    </svg>
                    <h5 class="premium-card-title">Customer</h5>
                </div>
                <div id="customerInfo" class="premium-card-body">
                    <div style="text-align: center; color: var(--text-muted);">Loading customer...</div>
                </div>
            </div>

            <!-- Shipping Card -->
            <div class="premium-card">
                <div class="premium-card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <h5 class="premium-card-title">Shipping Address</h5>
                </div>
                <div id="shippingAddress" class="premium-card-body">
                    <div style="text-align: center; color: var(--text-muted);">Loading address...</div>
                </div>
            </div>

            <!-- Refund Card -->
            <div class="premium-card">
                <div class="premium-card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h5 class="premium-card-title">Refund Status</h5>
                </div>
                <div id="refundSection" class="premium-card-body">
                    <div style="text-align: center; color: var(--text-muted);">Checking refunds...</div>
                </div>
            </div>

            <!-- Referral Card -->
            <div class="premium-card" id="referralCard" style="display:none;">
                <div class="premium-card-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h5 class="premium-card-title">Affiliate Referral</h5>
                </div>
                <div id="referralSection" class="premium-card-body">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const orderId = "{{ $id }}";

        function loadOrder() {
            API.get(`/admin/orders/${orderId}`)
                .then(res => {
                    const order = res.data || res;
                    renderOrderOverview(order);
                    renderOrderItems(order.items || []);
                    renderOrderTotals(order);
                    renderCustomer(order.user, order.shippingAddress || order.shipping_address);
                    renderAddress(order.shippingAddress || order.shipping_address);
                    renderRefund(order);
                    renderReferral(order);
                    document.getElementById('trackingNumber').value = order.tracking_number || '';
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('orderDetails').innerHTML = '<div style="color: #ef4444; text-align: center;">Error loading order data.</div>';
                });
        }

        function renderReferral(order) {
            if (!order.referral) return;
            const ref = order.referral;
            const affiliate = ref.affiliate || {};
            const affiliateUser = affiliate.user || {};
            
            document.getElementById('referralCard').style.display = 'block';
            
            let statusColor = '#9a3412';
            let statusBg = '#fff7ed';
            if (ref.commission_status === 'approved' || ref.commission_status === 'paid') {
                statusColor = '#166534';
                statusBg = '#f0fdf4';
            } else if (ref.commission_status === 'revoked' || ref.commission_status === 'clawback') {
                statusColor = '#991b1b';
                statusBg = '#fef2f2';
            }

            let fraudWarnings = '';
            if (ref.fraud_flags && typeof ref.fraud_flags === 'object') {
                const flags = [];
                if (ref.fraud_flags.self_referral) flags.push('Self Referral');
                if (ref.fraud_flags.same_ip) flags.push('Same IP Address');
                if (flags.length > 0) {
                    fraudWarnings = `<div style="margin-top:12px;padding:8px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;color:#991b1b;font-size:12px;font-weight:600;">⚠️ Flags: ${flags.join(', ')}</div>`;
                }
            }

            document.getElementById('referralSection').innerHTML = `
                <div class="info-group">
                    <label class="info-label">Affiliate</label>
                    <div class="info-value">
                        <a href="/admin/affiliates/${affiliate.id}" style="color:var(--primary);text-decoration:none;font-weight:600;">
                            ${esc(affiliateUser.name || 'Unknown Affiliate')}
                        </a>
                    </div>
                </div>
                <div class="info-group" style="margin-top:12px;">
                    <label class="info-label">Commission</label>
                    <div class="info-value" style="font-size:16px;font-weight:700;color:var(--secondary);">
                        EGP ${parseFloat(ref.commission_amount).toFixed(2)}
                    </div>
                </div>
                <div class="info-group" style="margin-top:12px;">
                    <label class="info-label">Status</label>
                    <div style="margin-top:4px;">
                        <span style="display:inline-block;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;background:${statusBg};color:${statusColor};text-transform:capitalize;">
                            ${esc(ref.commission_status)}
                        </span>
                    </div>
                </div>
                ${fraudWarnings}
            `;
        }

        function renderOrderOverview(order) {
            const statusOptions = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            const paymentOptions = ['unpaid', 'paid_deposit', 'full_paid', 'refunded'];

            document.getElementById('orderDetails').innerHTML = `
                <div class="order-info-row">
                    <div class="info-group">
                        <label class="info-label">Order Number</label>
                        <div class="info-value">#${order.order_number}</div>
                    </div>
                    <div class="info-group">
                        <label class="info-label">Created At</label>
                        <div class="info-value">${formatDate(order.created_at)}</div>
                    </div>
                    <div class="info-group">
                        <label class="info-label">Current Status</label>
                        <div style="display:flex; gap:8px; align-items:center; margin-top:4px;">
                            <select id="statusSelect" class="action-select">
                                ${statusOptions.map(opt => `<option value="${opt}" ${order.status === opt ? 'selected' : ''}>${opt.charAt(0).toUpperCase() + opt.slice(1)}</option>`).join('')}
                            </select>
                            <button onclick="updateStatus()" class="premium-btn">Save</button>
                        </div>
                    </div>
                    <div class="info-group">
                        <label class="info-label">Payment Status</label>
                        <div style="display:flex; gap:8px; align-items:center; margin-top:4px;">
                            <select id="paymentStatusSelect" class="action-select">
                                ${paymentOptions.map(opt => `<option value="${opt}" ${order.payment_status === opt ? 'selected' : ''}>${opt.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</option>`).join('')}
                            </select>
                            <button onclick="updatePaymentStatus()" class="premium-btn">Save</button>
                        </div>
                    </div>
                    <div class="info-group">
                        <label class="info-label">Payment Method</label>
                        <div class="info-value" style="text-transform: uppercase;">${order.payment_method || 'N/A'}</div>
                    </div>
                    ${order.notes ? `
                    <div class="info-group" style="grid-column: span 2; border-top: 1px solid var(--border); padding-top: 16px; margin-top: 8px;">
                        <label class="info-label">Order Notes / Special Instructions</label>
                        <div class="info-value" style="white-space: pre-line; font-weight: normal; color: var(--text-main); line-height: 1.6; background: #fafafa; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border);">
                            ${esc(order.notes)}
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
        }

        function renderOrderItems(items) {
            const container = document.getElementById('orderItems');
            if (!items.length) {
                container.innerHTML = '<div style="text-align: center; color: var(--text-muted);">No items in this order.</div>';
                return;
            }

            container.innerHTML = items.map(item => {
                const product = item.product || {};
                const image = product.image || product.product_image || '';
                const color = item.color || null;
                const variantName = item.variant || 'Standard';
                
                let colorHtml = '';
                if (color) {
                    colorHtml = `<span style="display:inline-flex;align-items:center;gap:6px;margin-top:4px;font-size:12px;color:var(--text-muted)"><span style="width:12px;height:12px;border-radius:50%;background:${esc(color.hex_code)};border:1px solid var(--border);flex-shrink:0"></span>${esc(color.name)}</span>`;
                } else {
                    colorHtml = `<span style="display:inline-flex;align-items:center;margin-top:4px;font-size:12px;color:var(--text-muted);font-weight:500;">Color: ${esc(variantName)}</span>`;
                }
                
                return `
                    <div class="item-row">
                        ${image ? `<img src="${image}" class="item-img">` : '<div class="item-img"></div>'}
                        <div class="item-details">
                            <div class="item-name">
                                <a href="/admin/products/${product.id}/edit" target="_blank" style="color:var(--primary);text-decoration:none;" title="View Product Details">${esc(item.name || product.name || 'Unknown Item')}</a>
                            </div>
                            <div class="item-meta">Quantity: ${item.quantity} × ${parseFloat(item.price).toFixed(2)} EGP</div>
                            ${colorHtml}
                        </div>
                        <div class="item-price-info">
                            <div class="info-value">${(item.quantity * item.price).toFixed(2)} EGP</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function renderOrderTotals(order) {
            let discountHtml = '';
            let discountVal = parseFloat(order.discount) || 0;
            let affDiscountVal = parseFloat(order.affiliate_discount) || 0;
            
            if (affDiscountVal > 0) {
                discountHtml = `<div class="summary-row" style="color:#ef4444;"><span>Affiliate Discount</span><span>-${affDiscountVal.toFixed(2)} EGP</span></div>`;
            } else if (discountVal > 0) {
                discountHtml = `<div class="summary-row" style="color:#ef4444;"><span>Discount</span><span>-${discountVal.toFixed(2)} EGP</span></div>`;
            } else {
                discountHtml = `<div class="summary-row" style="color:#ef4444;"><span>Discount</span><span>-0.00 EGP</span></div>`;
            }

            document.getElementById('orderTotals').innerHTML = `
                <div class="summary-row"><span>Subtotal</span><span>${parseFloat(order.subtotal).toFixed(2)} EGP</span></div>
                ${discountHtml}
                <div class="summary-row"><span>Delivery Fee</span><span>${parseFloat(order.delivery_fee).toFixed(2)} EGP</span></div>
                <div class="summary-row"><span>VAT (14%)</span><span>${parseFloat(order.vat_amount).toFixed(2)} EGP</span></div>
                <div class="summary-row grand-total"><span>Grand Total</span><span>${parseFloat(order.total).toFixed(2)} EGP</span></div>
                ${order.deposit_amount > 0 ? `<div class="summary-row" style="color:var(--primary); font-weight:600; font-size:12px; margin-top:4px;"><span>Deposit Amount</span><span>${parseFloat(order.deposit_amount).toFixed(2)} EGP</span></div>` : ''}
            `;
        }

        function renderCustomer(user, addr) {
            const container = document.getElementById('customerInfo');
            const name = user ? user.name : (addr ? `${addr.first_name} ${addr.last_name}` : 'Guest');
            const email = user ? user.email : (addr ? addr.email : 'N/A');
            const phone = user ? user.phone : (addr ? addr.phone : 'N/A');

            container.innerHTML = `
                <div style="display:flex; flex-direction:column; gap:16px;">
                    <div class="info-group"><label class="info-label">Full Name</label><div class="info-value">${esc(name)}</div></div>
                    <div class="info-group"><label class="info-label">Email</label><div class="info-value">${esc(email)}</div></div>
                    <div class="info-group"><label class="info-label">Phone</label><div class="info-value">${esc(phone)}</div></div>
                    <div class="info-group"><label class="info-label">Account Type</label><div class="info-value">${user ? 'Registered User' : 'Guest Checkout'}</div></div>
                </div>
            `;
        }

        function renderAddress(addr) {
            const container = document.getElementById('shippingAddress');
            if (!addr) {
                container.innerHTML = '<div style="color: var(--text-muted);">No address specified.</div>';
                return;
            }
            container.innerHTML = `
                <div style="display:flex; flex-direction:column; gap:16px;">
                    <div class="info-group"><label class="info-label">Street Address</label><div class="info-value">${esc(addr.address_line_1 || addr.address)}</div></div>
                    ${addr.address_line_2 ? `<div class="info-group"><label class="info-label">Building/Apt</label><div class="info-value">${esc(addr.address_line_2)}</div></div>` : ''}
                    <div class="info-group"><label class="info-label">City / State</label><div class="info-value">${esc(addr.city)}, ${esc(addr.state || addr.governorate)}</div></div>
                    <div class="info-group"><label class="info-label">Postal Code</label><div class="info-value">${esc(addr.postal_code || 'N/A')}</div></div>
                </div>
            `;
        }

        function renderRefund(order) {
            const container = document.getElementById('refundSection');
            if (!order.refund_status) {
                container.innerHTML = '<div style="color: var(--text-muted); font-size: 13px;">No active refund requests.</div>';
                return;
            }

            container.innerHTML = `
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div class="status-badge status-${order.refund_status}">${order.refund_status}</div>
                    <div class="info-group">
                        <label class="info-label">Reason</label>
                        <div style="font-size:13px; padding:12px; background:#f9f9f9; border-radius:8px; border:1px solid #eee;">
                            ${esc(order.refund_reason || 'No reason provided')}
                        </div>
                    </div>
                    ${order.refund_status === 'pending' ? `
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px;">
                            <button onclick="handleOrderRefund('${orderId}', 'approve')" class="premium-btn" style="background:#16a34a;">Approve</button>
                            <button onclick="handleOrderRefund('${orderId}', 'reject')" class="premium-btn" style="background:#dc2626;">Reject</button>
                        </div>
                    ` : ''}
                </div>
            `;
        }

        function handleOrderRefund(id, action) {
            var confirmed = confirm(action === 'approve'
                ? 'Approve this refund? Stock will be restored and payment marked as refunded.'
                : 'Reject this refund request?');
            if (!confirmed) return;

            API.post('/admin/refunds/' + id + '/handle', { action: action })
            .then(function(data) {
                if (data.error) {
                    alert(data.error);
                } else {
                    alert(data.success);
                    loadOrder();
                }
            })
            .catch(function(e) { alert(e.data?.error || 'Failed to process refund.'); });
        }

        function formatDate(str) {
            return new Date(str).toLocaleDateString('en-US', {
                year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });
        }

        function updateStatus() {
            const status = document.getElementById('statusSelect').value;
            API.patch(`/admin/orders/${orderId}/status`, { status }).then(() => {
                alert('Order status updated!');
                loadOrder();
            });
        }

        function updatePaymentStatus() {
            const payment_status = document.getElementById('paymentStatusSelect').value;
            API.patch(`/admin/orders/${orderId}/payment-status`, { payment_status }).then(() => {
                alert('Payment status updated!');
                loadOrder();
            });
        }

        function updateTracking() {
            const tracking_number = document.getElementById('trackingNumber').value;
            API.patch(`/admin/orders/${orderId}/tracking`, { tracking_number }).then(() => {
                alert('Tracking updated!');
                loadOrder();
            });
        }

        document.addEventListener('DOMContentLoaded', loadOrder);
    </script>
@endpush