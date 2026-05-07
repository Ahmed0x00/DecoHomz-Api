/**
 * DecoHomz — Cart Page Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    renderCartPage();
    
    const clearBtn = document.querySelector('.clear-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to clear your cart?')) {
                DH_STORAGE.set('dh_cart', []);
                renderCartPage();
                if (window.updateBadges) window.updateBadges();
            }
        });
    }

    const applyCouponBtn = document.getElementById('apply-coupon');
    if (applyCouponBtn) {
        applyCouponBtn.addEventListener('click', () => {
            const code = document.getElementById('coupon-input').value.trim();
            if (code.toUpperCase() === 'DECO10') {
                alert('Coupon applied! 10% discount added.');
                // In a real app, we'd update the summary
            } else {
                alert('Invalid coupon code.');
            }
        });
    }
});

function renderCartPage() {
    const cart = DH_STORAGE.get('dh_cart') || [];
    const container = document.getElementById('cart-page-container');
    const summarySubtotal = document.querySelector('.sum-row .val');
    const summaryTotal = document.querySelector('.sum-row.total .val');
    const cartTitle = document.querySelector('.cart-title');

    if (!container) return;

    if (cart.length === 0) {
        return; // Preserve static summary items
    }

    let subtotal = 0;
    cartTitle.textContent = `Your Cart (${cart.length} item${cart.length > 1 ? 's' : ''})`;

    container.innerHTML = cart.map((item, index) => {
        const product = PRODUCTS.find(p => p.id === item.id) || {};
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        return `
            <div class="cart-item">
                <div class="item-img">
                    ${product.svg || ''}
                </div>
                <div class="item-info">
                    <div class="item-name">${item.name}</div>
                    <div class="item-meta">Color: ${item.color || 'Standard'} · Size: ${item.size || 'Regular'}</div>
                    <div class="qty-ctrl">
                        <button class="qty-btn" onclick="updateCartPageQty(${index}, -1)">−</button>
                        <span class="qty-num">${item.quantity}</span>
                        <button class="qty-btn" onclick="updateCartPageQty(${index}, 1)">+</button>
                    </div>
                </div>
                <div class="item-price-col">
                    <button class="remove-btn" onclick="removeCartPageItem(${index})">×</button>
                    <div>
                        <div class="item-price">EGP ${itemTotal.toLocaleString()}</div>
                        ${item.oldPrice ? `<div class="item-old">EGP ${(item.oldPrice * item.quantity).toLocaleString()}</div>` : ''}
                    </div>
                </div>
            </div>
        `;
    }).join('');

    if (summarySubtotal) summarySubtotal.textContent = `EGP ${subtotal.toLocaleString()}`;
    if (summaryTotal) summaryTotal.textContent = `EGP ${subtotal.toLocaleString()}`;
}

window.removeCartPageItem = function(index) {
    let cart = DH_STORAGE.get('dh_cart') || [];
    cart.splice(index, 1);
    DH_STORAGE.set('dh_cart', cart);
    renderCartPage();
    if (window.updateBadges) window.updateBadges();
    if (window.renderCart) window.renderCart();
};

window.updateCartPageQty = function(index, delta) {
    let cart = DH_STORAGE.get('dh_cart') || [];
    cart[index].quantity += delta;
    if (cart[index].quantity < 1) cart.splice(index, 1);
    DH_STORAGE.set('dh_cart', cart);
    renderCartPage();
    if (window.updateBadges) window.updateBadges();
    if (window.renderCart) window.renderCart();
};