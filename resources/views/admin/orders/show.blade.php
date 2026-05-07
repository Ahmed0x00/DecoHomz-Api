@extends('admin.layouts.app')
@section('title', 'Order #' . $id)

@section('content')
<style>
.order-back-link { display: inline-block; margin-bottom: 24px; font-size: 13px; color: #c9a96e; text-decoration: none; }
.order-back-link:hover { text-decoration: underline; }
.order-grid { display: grid; grid-template-columns: 1fr 380px; gap: 24px; }
.order-main { display: flex; flex-direction: column; gap: 24px; }
.order-sidebar { display: flex; flex-direction: column; gap: 24px; }
.order-detail-item { margin-bottom: 16px; }
.order-detail-item:last-child { margin-bottom: 0; }
.order-detail-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.order-detail-value { font-size: 14px; color: #1a1a1a; }
.order-status-row { display: flex; align-items: center; gap: 12px; margin-top: 8px; }
.order-select { padding: 6px 12px; border: 1px solid #e5e5e5; border-radius: 6px; font-size: 13px; color: #333; background: #fff; min-width: 130px; }
.order-btn { padding: 6px 14px; background: #c9a96e; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer; transition: 0.2s; }
.order-btn:hover { background: #b8985d; }
.order-input { width: 100%; padding: 8px 12px; border: 1px solid #e5e5e5; border-radius: 6px; font-size: 13px; color: #333; box-sizing: border-box; }
.order-input:focus { outline: none; border-color: #c9a96e; }
.order-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
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
                    <input type="text" id="trackingNumber" class="order-input" placeholder="Enter tracking number" style="flex: 1;">
                    <button type="button" class="order-btn" onclick="updateTracking()">Update Tracking</button>
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
            .then(response => {
                renderOrderDetails(response);
                renderOrderItems(response.items);
                renderCustomerInfo(response.user);
                renderShippingAddress(response.shipping_address);
                document.getElementById('trackingNumber').value = response.tracking_number || '';
            })
            .catch(error => {
                console.error('Error loading order:', error);
                document.getElementById('orderDetails').innerHTML =
                    '<div style="text-align: center; color: #ef4444;">Failed to load order details</div>';
            });
    }

    function renderOrderDetails(order) {
        const statusOptions = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        const paymentOptions = ['paid', 'unpaid'];

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
                    <div class="order-detail-value">${parseFloat(order.subtotal).toFixed(2)} EGP</div>
                </div>
                <div class="order-detail-item">
                    <div class="order-detail-label">Shipping Cost</div>
                    <div class="order-detail-value">${parseFloat(order.shipping_cost).toFixed(2)} EGP</div>
                </div>
                <div class="order-detail-item">
                    <div class="order-detail-label">Total</div>
                    <div class="order-detail-value"><strong>${parseFloat(order.total).toFixed(2)} EGP</strong></div>
                </div>
                <div class="order-detail-item">
                    <div class="order-detail-label">Payment Method</div>
                    <div class="order-detail-value">${order.payment_method || 'N/A'}</div>
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
                            ${paymentOptions.map(opt => `<option value="${opt}" ${order.payment_status === opt ? 'selected' : ''}>${opt.charAt(0).toUpperCase() + opt.slice(1)}</option>`).join('')}
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

        tbody.innerHTML = items.map(item => `
            <tr>
                <td>
                    ${item.product_image
                        ? `<img src="${item.product_image}" alt="${esc(item.product_name)}" style="width: 50px; height: 50px; object-fit: cover;">`
                        : '<div style="width: 50px; height: 50px; background: #e5e5e5;"></div>'
                    }
                </td>
                <td>${esc(item.product_name)}</td>
                <td>${item.quantity}</td>
                <td>${parseFloat(item.unit_price).toFixed(2)} EGP</td>
                <td>${(parseFloat(item.unit_price) * item.quantity).toFixed(2)} EGP</td>
            </tr>
        `).join('');
    }

    function renderCustomerInfo(user) {
        if (!user) {
            document.getElementById('customerInfo').innerHTML = '<p style="color: #888; font-style: italic;">No customer information available</p>';
            return;
        }

        document.getElementById('customerInfo').innerHTML = `
            <p class="order-detail-item"><span class="order-detail-label">Name</span><span class="order-detail-value">${esc(user.name || 'N/A')}</span></p>
            <p class="order-detail-item"><span class="order-detail-label">Email</span><span class="order-detail-value">${esc(user.email || 'N/A')}</span></p>
            <p class="order-detail-item"><span class="order-detail-label">Phone</span><span class="order-detail-value">${esc(user.phone || 'N/A')}</span></p>
        `;
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
