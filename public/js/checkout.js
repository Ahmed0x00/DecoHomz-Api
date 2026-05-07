/**
 * DecoHomz — Checkout Page Logic
 */

let deliveryFee = 0;

let currentCheckoutStep = 1;

document.addEventListener('DOMContentLoaded', () => {
    renderCheckoutSummary();
    
    const wrap = document.querySelector('.checkout-wrap');
    if (wrap) wrap.classList.add('step-1-active');
    
    const placeOrderBtn = document.querySelector('.btn-place');
    if (placeOrderBtn) {
        placeOrderBtn.textContent = 'Continue to Payment →';
        placeOrderBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentCheckoutStep === 1) {
                if (!document.getElementById('checkout-form').checkValidity()) {
                    document.getElementById('checkout-form').reportValidity();
                    return;
                }
                document.getElementById('checkout-step-1').style.display = 'none';
                document.getElementById('checkout-step-2').style.display = 'block';
                
                document.querySelectorAll('.step')[2].classList.replace('active', 'done');
                document.querySelectorAll('.step')[2].querySelector('.step-num').textContent = '✓';
                document.querySelectorAll('.step-line')[2].classList.add('done');
                document.querySelectorAll('.step')[3].classList.replace('inactive', 'active');
                
                placeOrderBtn.textContent = 'Place Order →';
                if (wrap) wrap.classList.replace('step-1-active', 'step-2-active');
                currentCheckoutStep = 2;
                window.scrollTo(0,0);
            } else {
                submitOrder();
            }
        });
    }
});

window.backToShipping = function() {
    document.getElementById('checkout-step-2').style.display = 'none';
    document.getElementById('checkout-step-1').style.display = 'block';
    
    document.querySelectorAll('.step')[2].classList.replace('done', 'active');
    document.querySelectorAll('.step')[2].querySelector('.step-num').textContent = '3';
    document.querySelectorAll('.step-line')[2].classList.remove('done');
    document.querySelectorAll('.step')[3].classList.replace('active', 'inactive');
    
    document.querySelector('.btn-place').textContent = 'Continue to Payment →';
    const wrap = document.querySelector('.checkout-wrap');
    if (wrap) wrap.classList.replace('step-2-active', 'step-1-active');
    currentCheckoutStep = 1;
    window.scrollTo(0,0);
};

function submitOrder() {
    const cart = DH_STORAGE.get('dh_cart') || [];
    
    // If cart is empty, we use dummy data for the order so the prototype flow works
    const orderItems = cart.length > 0 ? cart : [
        { id: 'luna-sofa', name: 'Luna 3-Seater Sofa', price: 12999, quantity: 1, variant: 'Walnut' },
        { id: 'elio-table', name: 'Elio Coffee Table', price: 4999, quantity: 1, variant: 'Natural' }
    ];

    // Save order to history
    const orders = DH_STORAGE.get('dh_orders') || [];
    const newOrder = {
        id: 'DH' + Math.floor(Math.random() * 100000),
        date: new Date().toLocaleDateString(),
        status: 'Processing',
        items: orderItems,
        total: calculateTotal(orderItems) + deliveryFee
    };
    orders.push(newOrder);
    DH_STORAGE.set('dh_orders', orders);
    
    // Clear cart
    DH_STORAGE.set('dh_cart', []);
    
    // Redirect
    location.href = 'order-confirmation.html';
}

function renderCheckoutSummary() {
    const cart = DH_STORAGE.get('dh_cart') || [];
    const container = document.getElementById('checkout-summary-items');
    const subtotalEl = document.querySelector('.sum-row .v');
    const totalEl = document.querySelector('.sum-total span:last-child');

    if (!container) return;

    if (cart.length === 0) {
        return; // Preserve static summary items
    }

    let subtotal = 0;
    container.innerHTML = cart.map(item => {
        const product = PRODUCTS.find(p => p.id === item.id) || {};
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        return `
            <div class="sum-item">
                <div class="sum-thumb">
                    ${product.svg || ''}
                    <div class="qty-badge">${item.quantity}</div>
                </div>
                <div class="sum-item-info">
                    <div class="sum-item-name">${item.name}</div>
                    <div class="sum-item-meta">${item.color || 'Standard'} · ${item.size || 'Regular'}</div>
                </div>
                <div class="sum-item-price">EGP ${itemTotal.toLocaleString()}</div>
            </div>
        `;
    }).join('');

    subtotalEl.textContent = `EGP ${subtotal.toLocaleString()}`;
    updateFinalTotal();
}

function calculateTotal(cart) {
    return cart.reduce((total, item) => total + ((parseFloat(item.price) || 0) * (parseInt(item.quantity) || 0)), 0);
}

window.selectDelivery = function(el) {
    document.querySelectorAll('.delivery-opt').forEach(opt => opt.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
    
    const priceText = el.querySelector('.opt-price').textContent;
    if (priceText.toLowerCase() === 'free') {
        deliveryFee = 0;
    } else {
        deliveryFee = parseInt(priceText.replace('EGP ', '').replace(',', ''));
    }
    
    const deliveryEl = document.querySelector('.sum-row:nth-last-child(2) .v');
    if (deliveryEl) {
        deliveryEl.textContent = deliveryFee === 0 ? 'Free' : `EGP ${deliveryFee}`;
        deliveryEl.className = deliveryFee === 0 ? 'v free' : 'v';
    }
    
    updateFinalTotal();
};

function updateFinalTotal() {
    const cart = DH_STORAGE.get('dh_cart') || [];
    const subtotal = calculateTotal(cart);
    const totalEl = document.querySelector('.sum-total span:last-child');
    if (totalEl) {
        totalEl.textContent = `EGP ${(subtotal + deliveryFee).toLocaleString()}`;
    }
}

window.switchPay = function(method, el) {
    document.querySelectorAll('.pay-tab').forEach(tab => tab.classList.remove('active'));
    el.classList.add('active');
    
    document.getElementById('pay-card').style.display = method === 'card' ? 'block' : 'none';
    document.getElementById('pay-fawry').style.display = method === 'fawry' ? 'block' : 'none';
    document.getElementById('pay-cod').style.display = method === 'cod' ? 'block' : 'none';
};