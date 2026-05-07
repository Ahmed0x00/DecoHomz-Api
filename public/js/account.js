/**
 * DecoHomz — Account Dashboard Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    renderAccountData();
    renderOrderHistory();
});

function showTab(tabId, el) {
    // Hide all tabs
    document.querySelectorAll('.acc-main > div').forEach(tab => tab.style.display = 'none');
    // Show active tab
    document.getElementById(`tab-${tabId}`).style.display = 'block';
    // Update active menu item
    document.querySelectorAll('.acc-menu a').forEach(a => a.classList.remove('active'));
    el.classList.add('active');
}

function renderConfirmation() {
    const orders = DH_STORAGE.get('dh_orders') || [];
    const wishlist = DH_STORAGE.get('dh_wishlist') || [];
    
    const stats = document.querySelectorAll('.stat-num');
    if (stats.length >= 4) {
        stats[0].textContent = orders.length;
        stats[1].textContent = wishlist.length;
        stats[2].textContent = orders.filter(o => o.status === 'Delivered').length;
        stats[3].textContent = orders.filter(o => o.status === 'Processing').length;
    }
}

function renderAccountData() {
    const orders = DH_STORAGE.get('dh_orders') || [];
    
    const stats = document.querySelectorAll('.stat-num');
    if (stats.length >= 3) {
        stats[0].textContent = orders.length;
        stats[1].textContent = orders.filter(o => o.status === 'Delivered').length;
        stats[2].textContent = orders.filter(o => o.status === 'Processing').length;
    }
}

function renderOrderHistory() {
    const orders = DH_STORAGE.get('dh_orders') || [];
    const container = document.getElementById('recent-orders-container');
    const fullContainer = document.querySelector('#tab-orders .orders-list');
    
    if (!container) return;

    if (orders.length === 0) {
        return;
    }

    const html = orders.reverse().map(order => `
        <div class="order-card">
            <div>
                <div class="order-top">
                    <span class="order-id">#${order.id}</span>
                    <span class="order-status status-${order.status.toLowerCase()}">${order.status}</span>
                    <span class="order-date">${order.date}</span>
                </div>
                <div class="order-items-preview">
                    ${order.items.slice(0, 3).map(item => `
                        <div class="order-thumb" title="${item.name}">${item.svg || ''}</div>
                    `).join('')}
                    ${order.items.length > 3 ? `<div class="order-thumb-more">+${order.items.length - 3}</div>` : ''}
                </div>
            </div>
            <div>
                <div class="order-total">EGP ${order.total.toLocaleString()}</div>
                <a class="order-action" href="#">View Details →</a>
            </div>
        </div>
    `).join('');

    container.innerHTML = html;
    if (fullContainer) fullContainer.innerHTML = html;
}