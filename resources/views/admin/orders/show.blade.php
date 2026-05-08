@extends('admin.layouts.app')
@section('title', 'Order #' . $id)

@section('content')
    <style>
        .order-back-link {
            display: inline-block;
            margin-bottom: 24px;
            font-size: 13px;
            color: #c9a96e;
            text-decoration: none;
        }

        .order-back-link:hover {
            text-decoration: underline;
        }

        .order-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
        }

        .order-main {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .order-sidebar {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .order-detail-item {
            margin-bottom: 16px;
        }

        .order-detail-item:last-child {
            margin-bottom: 0;
        }

        .order-detail-label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .order-detail-value {
            font-size: 14px;
            color: #1a1a1a;
        }

        .order-status-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
        }

        .order-select {
            padding: 6px 12px;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            font-size: 13px;
            color: #333;
            background: #fff;
            min-width: 130px;
        }

        .order-btn {
            padding: 6px 14px;
            background: #c9a96e;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: 0.2s;
        }

        .order-btn:hover {
            background: #b8985d;
        }

        .order-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            font-size: 13px;
            color: #333;
            box-sizing: border-box;
        }

        .order-input:focus {
            outline: none;
            border-color: #c9a96e;
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
    </style>

    <a href="/admin/orders" class="order-back-link">&larr; Back to Orders</a>

    <div class="order-grid">
        <!-- Main Content -->
        <div class="order-main">
            <!-- Order Header -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Order Details</h5>
                </div>
                <div id="orderDetails" style="padding: 24px;">
                    <div style="text-align: center; color: #888;">Loading...</div>
                </div>
            </div>

            <!-- Tracking Number -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Tracking Information</h5>
                </div>
                <div style="padding: 24px; display: flex; gap: 12px;">
                    <input type="text" id="trackingNumber" class="order-input" placeholder="Enter tracking number"
                        style="flex: 1;">
                    <button type="button" class="order-btn" onclick="updateTracking()">Update Tracking</button>
                </div>
            </div>

            <!-- Refund Management -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Refund Management</h5>
                </div>
                <div id="refundSection" style="padding: 24px;">
                    <div style="text-align: center; color: #888;">Loading...</div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Order Items</h5>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="orderItems">
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #aaa;">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="order-sidebar">
            <!-- Customer Info -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Customer</h5>
                </div>
                <div id="customerInfo" style="padding: 24px;">
                    <div style="text-align: center; color: #888;">Loading...</div>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Shipping Address</h5>
                </div>
                <div id="shippingAddress" style="padding: 24px;">
                    <div style="text-align: center; color: #888;">Loading...</div>
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
                    renderOrderDetails(order);
                    renderOrderItems(order.items || []);
                    renderCustomerInfo(order.user, order.shippingAddress || order.shipping_address);
                    renderShippingAddress(order.shippingAddress || order.shipping_address);
                    renderRefundSection(order);
                    document.getElementById('trackingNumber').value = order.tracking_number || '';
                })
                .catch(error => {
                    console.error('Error loading order:', error);
                    document.getElementById('orderDetails').innerHTML =
                        '<div style="text-align: center; color: #ef4444;">Failed to load order details</div>';
                });
        }

        function renderOrderDetails(order) {
            const statusOptions = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            const paymentOptions = ['unpaid', 'paid_deposit', 'full_paid', 'refunded'];

            document.getElementById('orderDetails').innerHTML = `
                                <div class="order-info-grid">
                                    <div class="order-detail-item">
                                        <div class="order-detail-label">Order Number</div>
                                        <div class="order-detail-value">${order.order_number}</div>
                                    </div>
                                    <div class="order-detail-item">
                                        <div class="order-detail-label">Date</div>
                                        <div class="order-detail-value">${formatDate(order.created_at)}</div>
                                    </div>
                                    <div class="order-detail-item">
                                        <div class="order-detail-label">Subtotal</div>
                                        <div class="order-detail-value">${(parseFloat(order.subtotal) || 0).toFixed(2)} EGP</div>
                                    </div>
                                    <div class="order-detail-item">
                                        <div class="order-detail-label">Discount</div>
                                        <div class="order-detail-value">-${(parseFloat(order.discount) || 0).toFixed(2)} EGP</div>
                                    </div>
                                    <div class="order-detail-item">
                                        <div class="order-detail-label">Delivery Fee</div>
                                        <div class="order-detail-value">${(parseFloat(order.delivery_fee) || 0).toFixed(2)} EGP</div>
                                    </div>
                                    <div class="order-detail-item">
                                        <div class="order-detail-label">VAT (14%)</div>
                                        <div class="order-detail-value">${(parseFloat(order.vat_amount) || 0).toFixed(2)} EGP</div>
                                    </div>
                                    <div class="order-detail-item">
                                        <div class="order-detail-label">Deposit Required</div>
                                        <div class="order-detail-value">${(parseFloat(order.deposit_amount) || 0).toFixed(2)} EGP</div>
                                    </div>
                                    <div class="order-detail-item">
                                        <div class="order-detail-label">Total</div>
                                        <div class="order-detail-value"><strong>${(parseFloat(order.total) || 0).toFixed(2)} EGP</strong></div>
                                    </div>
                                    <div class="order-detail-item">
                                        <div class="order-detail-label">Payment Method</div>
                                        <div class="order-detail-value">${order.payment_method || 'N/A'}</div>
                                    </div>
                                    <div class="order-detail-item">
                                        <div class="order-detail-label">Refunded Amount</div>
                                        <div class="order-detail-value">
                                            ${(order.refund_status === 'approved' || order.refund_status === 'rejected')
                                                ? ((order.payment_status === 'full_paid' || order.payment_status === 'refunded')
                                                    ? (parseFloat(order.total) || 0).toFixed(2) + ' EGP'
                                                    : (parseFloat(order.deposit_amount) || 0).toFixed(2) + ' EGP')
                                                : '0.00 EGP'}
                                        </div>
                                    </div>
                                    <div class="order-detail-item">
                                        <div class="order-detail-label">Status</div>
                                        <div class="order-status-row">
                                            <select id="statusSelect" class="order-select">
                                                ${statusOptions.map(opt => `<option value="${opt}" ${order.status === opt ? 'selected' : ''}>${opt.charAt(0).toUpperCase() + opt.slice(1)}</option>`).join('')}
                                            </select>
                                            <button type="button" class="order-btn" onclick="updateStatus()">Update</button>
                                        </div>
                                    </div>
                                    <div class="order-detail-item">
                                        <div class="order-detail-label">Payment</div>
                                        <div class="order-status-row">
                                            <select id="paymentStatusSelect" class="order-select">
                                                ${paymentOptions.map(opt => `<option value="${opt}" ${order.payment_status === opt ? 'selected' : ''}>${opt.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</option>`).join('')}
                                            </select>
                                            <button type="button" class="order-btn" onclick="updatePaymentStatus()">Update</button>
                                        </div>
                                    </div>
                                </div>
                            `;
        }

        function renderOrderItems(items) {
            const tbody = document.getElementById('orderItems');

            if (!items || items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px; color: #aaa;">No items found</td></tr>';
                return;
            }

            tbody.innerHTML = items.map(item => {
                const product = item.product || {};
                const productImage = product.image || product.product_image || null;
                const productName = product.name || product.product_name || item.name || 'Unknown Product';
                const unitPrice = parseFloat(item.price || product.price || 0);
                const qty = parseInt(item.quantity || 1);
                return `
                                <tr>
                                    <td>
                                        ${productImage
                        ? `<img src="${productImage}" alt="${esc(productName)}" style="width: 50px; height: 50px; object-fit: cover;">`
                        : '<div style="width: 50px; height: 50px; background: #e5e5e5;"></div>'
                    }
                                    </td>
                                    <td>${esc(productName)}${product.sku ? `<br><small style="color:#888">SKU: ${esc(product.sku)}</small>` : ''}</td>
                                    <td>${qty}</td>
                                    <td>${unitPrice.toFixed(2)} EGP</td>
                                    <td>${(unitPrice * qty).toFixed(2)} EGP</td>
                                </tr>
                            `}).join('');
        }

        function renderCustomerInfo(user, shippingAddress) {
            const infoDiv = document.getElementById('customerInfo');

            if (user) {
                infoDiv.innerHTML = `
                                    <p class="order-detail-item"><span class="order-detail-label">Name</span><span class="order-detail-value">${esc(user.name || 'N/A')}</span></p>
                                    <p class="order-detail-item"><span class="order-detail-label">Email</span><span class="order-detail-value">${esc(user.email || 'N/A')}</span></p>
                                    <p class="order-detail-item"><span class="order-detail-label">Phone</span><span class="order-detail-value">${esc(user.phone || 'N/A')}</span></p>
                                `;
                return;
            }

            // Guest — pull name/email/phone from shipping address
            if (shippingAddress) {
                const name = [shippingAddress.first_name, shippingAddress.last_name].filter(Boolean).join(' ') || 'Guest';
                infoDiv.innerHTML = `
                                    <p class="order-detail-item"><span class="order-detail-label">Name</span><span class="order-detail-value">${esc(name)}</span></p>
                                    <p class="order-detail-item"><span class="order-detail-label">Email</span><span class="order-detail-value">${esc(shippingAddress.email || 'N/A')}</span></p>
                                    <p class="order-detail-item"><span class="order-detail-label">Phone</span><span class="order-detail-value">${esc(shippingAddress.phone || 'N/A')}</span></p>
                                    <p class="order-detail-item"><span class="order-detail-label">Type</span><span class="order-detail-value">Guest</span></p>
                                `;
                return;
            }

            infoDiv.innerHTML = '<p style="color: #888; font-style: italic;">No customer information available</p>';
        }

        function renderShippingAddress(address) {
            if (!address) {
                document.getElementById('shippingAddress').innerHTML = '<p style="color: #888; font-style: italic;">No shipping address available</p>';
                return;
            }

            document.getElementById('shippingAddress').innerHTML = `
                                <p class="order-detail-item"><span class="order-detail-label">Address</span><span class="order-detail-value">${esc(address.address || 'N/A')}</span></p>
                                <p class="order-detail-item"><span class="order-detail-label">City</span><span class="order-detail-value">${esc(address.city || 'N/A')}</span></p>
                                <p class="order-detail-item"><span class="order-detail-label">Postal Code</span><span class="order-detail-value">${esc(address.postal_code || 'N/A')}</span></p>
                                <p class="order-detail-item"><span class="order-detail-label">Country</span><span class="order-detail-value">${esc(address.country || 'N/A')}</span></p>
                            `;
        }

        function renderRefundSection(order) {
            const container = document.getElementById('refundSection');
            const refundStatus = order.refund_status;
            const refundReason = order.refund_reason || '';
            const refundHandledAt = order.refund_handled_at;
            const paymentStatus = order.payment_status;
            const isEligible = paymentStatus === 'paid_deposit' || paymentStatus === 'full_paid';

            if (!refundStatus) {
                if (isEligible) {
                    container.innerHTML = `
                                        <div style="margin-bottom:12px;font-size:13px;color:#555;">No refund request yet for this order.</div>
                                        <div style="margin-bottom:8px;">
                                            <textarea id="adminRefundReason" placeholder="Enter reason for creating this refund request..." style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:12px;font-family:inherit;resize:vertical;min-height:60px;box-sizing:border-box;"></textarea>
                                        </div>
                                        <button type="button" onclick="createRefundForOrder()" style="padding:7px 16px;background:#2C1F14;color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">Create Refund Request</button>
                                        <div id="refundActionMsg" style="margin-top:8px;font-size:12px;"></div>`;
                } else {
                    container.innerHTML = `
                                        <div style="display:flex;align-items:center;gap:10px;color:#888;font-size:13px;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                            No refund request for this order.
                                        </div>`;
                }
                return;
            }

            const statusClass = {
                'pending': 'badge-status badge-pending',
                'approved': 'badge-status badge-paid',
                'rejected': 'badge-status badge-cancelled'
            }[refundStatus] || 'badge-status';

            const statusLabel = {
                'pending': 'Pending',
                'approved': 'Approved',
                'rejected': 'Rejected'
            }[refundStatus] || refundStatus;

            let actionsHtml = '';
            if (refundStatus === 'pending') {
                actionsHtml = `
                                    <div style="display:flex;gap:10px;margin-top:14px;">
                                        <button type="button" onclick="handleRefund('approve')" style="flex:1;padding:8px;background:#16a34a;color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">Approve Refund</button>
                                        <button type="button" onclick="handleRefund('reject')" style="flex:1;padding:8px;background:#991b1b;color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">Reject Refund</button>
                                    </div>
                                    <div id="refundActionMsg" style="margin-top:10px;font-size:12px;"></div>`;
            }

            let handledHtml = '';
            if (refundHandledAt) {
                const handledDate = new Date(refundHandledAt).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                handledHtml = `<div style="margin-top:8px;font-size:12px;color:#888;">Processed on: ${handledDate}</div>`;
            }

            container.innerHTML = `
                                <div style="margin-bottom:12px;">
                                    <span class="${statusClass}">${statusLabel}</span>
                                </div>
                                ${refundReason ? `<div style="margin-bottom:8px;"><div class="order-detail-label">Customer Reason</div><div style="font-size:13px;color:#333;background:#f9f9f9;padding:10px;border-radius:6px;line-height:1.5;">${esc(refundReason)}</div></div>` : ''}
                                ${handledHtml}
                                ${actionsHtml}
                            `;
        }

        window.handleRefund = function (action) {
            const confirmed = confirm(action === 'approve'
                ? 'Approve this refund? Stock will be restored and payment marked as refunded.'
                : 'Reject this refund request?');
            if (!confirmed) return;

            const msg = document.getElementById('refundActionMsg');
            if (msg) { msg.textContent = 'Processing...'; msg.style.color = '#888'; }

            const formData = new FormData();
            formData.append('action', action);

            fetch(`/api/admin/refunds/${orderId}/handle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                        ? document.querySelector('meta[name="csrf-token"]').content
                        : '',
                    'Authorization': 'Bearer ' + (localStorage.getItem('dh_token') || '')
                },
                body: formData
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    const m = document.getElementById('refundActionMsg');
                    if (data.error) {
                        if (m) { m.textContent = data.error; m.style.color = '#991b1b'; }
                        alert(data.error);
                    } else {
                        if (m) { m.textContent = data.success; m.style.color = '#16a34a'; }
                        setTimeout(function () { loadOrder(); }, 1200);
                    }
                })
                .catch(function () {
                    const m = document.getElementById('refundActionMsg');
                    if (m) { m.textContent = 'Failed to process refund.'; m.style.color = '#991b1b'; }
                });
        };

        window.createRefundForOrder = function () {
            const reason = document.getElementById('adminRefundReason').value.trim();
            const msg = document.getElementById('refundActionMsg');
            if (!reason) {
                if (msg) { msg.textContent = 'Please enter a reason.'; msg.style.color = '#991b1b'; }
                return;
            }
            if (msg) { msg.textContent = 'Creating...'; msg.style.color = '#888'; }

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('reason', reason);

            fetch('/api/admin/refunds/create-for-guest', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                        ? document.querySelector('meta[name="csrf-token"]').content
                        : '',
                    'Authorization': 'Bearer ' + (localStorage.getItem('dh_token') || '')
                },
                body: formData
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    const m = document.getElementById('refundActionMsg');
                    if (data.error) {
                        if (m) { m.textContent = data.error; m.style.color = '#991b1b'; }
                    } else {
                        if (m) { m.textContent = data.success; m.style.color = '#16a34a'; }
                        setTimeout(function () { loadOrder(); }, 1200);
                    }
                })
                .catch(function () {
                    const m = document.getElementById('refundActionMsg');
                    if (m) { m.textContent = 'Failed to create refund request.'; m.style.color = '#991b1b'; }
                });
        };

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }

        function updateStatus() {
            const status = document.getElementById('statusSelect').value;
            API.patch(`/admin/orders/${orderId}/status`, { status })
                .then(response => {
                    alert('Status updated successfully');
                    loadOrder();
                })
                .catch(error => {
                    console.error('Error updating status:', error);
                    alert('Failed to update status');
                });
        }

        function updatePaymentStatus() {
            const payment_status = document.getElementById('paymentStatusSelect').value;
            API.patch(`/admin/orders/${orderId}/payment-status`, { payment_status })
                .then(response => {
                    alert('Payment status updated successfully');
                    loadOrder();
                })
                .catch(error => {
                    console.error('Error updating payment status:', error);
                    alert('Failed to update payment status');
                });
        }

        function updateTracking() {
            const tracking_number = document.getElementById('trackingNumber').value;
            API.patch(`/admin/orders/${orderId}/tracking`, { tracking_number })
                .then(response => {
                    alert('Tracking number updated successfully');
                    loadOrder();
                })
                .catch(error => {
                    console.error('Error updating tracking:', error);
                    alert('Failed to update tracking number');
                });
        }

        document.addEventListener('DOMContentLoaded', loadOrder);
    </script>
@endpush