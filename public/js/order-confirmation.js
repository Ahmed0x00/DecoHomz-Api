/**
 * DecoHomz — Order Confirmation Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    renderOrderConfirmation();
    
    // Add listeners to CTAs
    const ctAs = document.querySelectorAll('.cta-row button');
    if (ctAs.length >= 3) {
        ctAs[1].onclick = () => location.href = 'account.html';
        ctAs[2].onclick = () => location.href = 'shop.html';
    }
});

function renderOrderConfirmation() {
    const orders = DH_STORAGE.get('dh_orders') || [];
    if (orders.length === 0) return;

    const latestOrder = orders[orders.length - 1];
    
    // Update IDs and Summary
    const orderIdSpan = document.querySelector('#confirm-order-id span');
    if (orderIdSpan) orderIdSpan.textContent = `#${latestOrder.id}`;
    
    const totalEls = document.querySelectorAll('.sum-val.gold, .val[style*="font-size:15px"]');
    totalEls.forEach(el => el.textContent = `EGP ${latestOrder.total.toLocaleString()}`);

    const itemsCountEl = document.querySelector('.sum-item .sum-val:not(.gold)');
    if (itemsCountEl) itemsCountEl.textContent = `${latestOrder.items.length} pieces`;

    // Render Items
    const container = document.getElementById('confirm-items-container');
    if (container) {
        container.innerHTML = `
            <div class="items-title"><svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="23" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>Items Ordered (${latestOrder.items.length})</div>
            ${latestOrder.items.map(item => `
                <div class="order-item">
                    <div class="item-thumb">${item.svg || ''}</div>
                    <div>
                        <div class="item-name">${item.name}</div>
                        <div class="item-meta">${item.color || 'Standard'} · ${item.size || 'Regular'} · Qty: ${item.quantity}</div>
                    </div>
                    <div class="item-price">EGP ${(item.price * item.quantity).toLocaleString()}</div>
                </div>
            `).join('')}
        `;
    }
}