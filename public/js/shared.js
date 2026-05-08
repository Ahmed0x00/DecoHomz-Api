/**
 * DecoHomz — Shared Logic
 * Handles cart drawer and global UI interactions
 */

/**
 * Escape HTML entities to prevent XSS when inserting user data into innerHTML
 */
window.esc = function(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
};

/**
 * Persistence Helper
 * Handles localStorage with a fallback to window.name for file:// protocol support
 */
const DH_STORAGE = {
    get: (key) => {
        try {
            // Try localStorage first
            const localData = localStorage.getItem(key);
            if (localData) return JSON.parse(localData);
            
            // Fallback to window.name (persists across page loads in the same tab)
            if (window.name) {
                const sessionData = JSON.parse(window.name);
                return sessionData[key] || null;
            }
        } catch (e) { console.warn('Storage read error', e); }
        return null;
    },
    set: (key, value) => {
        try {
            // Set localStorage
            localStorage.setItem(key, JSON.stringify(value));
            
            // Sync with window.name for cross-page persistence on file://
            let sessionData = {};
            try { sessionData = JSON.parse(window.name || '{}'); } catch(e) {}
            sessionData[key] = value;
            window.name = JSON.stringify(sessionData);
        } catch (e) { console.warn('Storage write error', e); }
    }
};

// Note: window.Cart and window.addToCart are defined in api.js
// This file keeps only utility helpers: DH_STORAGE, Wishlist, Toast, Cart Drawer

/**
 * Global Wishlist Helpers (client-side, localStorage)
 */
window.Wishlist = {
    get: function() {
        return JSON.parse(localStorage.getItem('dh_wishlist') || '[]');
    },
    add: function(productId) {
        var list = Wishlist.get();
        if (list.indexOf(productId) === -1) {
            list.push(productId);
            localStorage.setItem('dh_wishlist', JSON.stringify(list));
            Wishlist.updateBadge();
            showToast('Added to wishlist!', 'success');
        } else {
            showToast('Already in wishlist.', 'info');
        }
    },
    remove: function(productId) {
        var list = Wishlist.get().filter(function(id) { return id !== productId; });
        localStorage.setItem('dh_wishlist', JSON.stringify(list));
        Wishlist.updateBadge();
        showToast('Removed from wishlist.', 'success');
    },
    toggle: function(productId) {
        var list = Wishlist.get();
        if (list.indexOf(productId) !== -1) {
            Wishlist.remove(productId);
        } else {
            Wishlist.add(productId);
        }
    },
    updateBadge: function() {
        var badge = document.querySelector('.badge-wishlist');
        if (badge) {
            var count = Wishlist.get().length;
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // Initial sync of window.name to localStorage if needed
    if (window.name && !localStorage.getItem('dh_cart')) {
        try {
            var data = JSON.parse(window.name);
            if (data.dh_cart) localStorage.setItem('dh_cart', JSON.stringify(data.dh_cart));
        } catch(e) {}
    }

    // initCartDrawer();
    renderCart();
    Cart.updateBadge();
    Wishlist.updateBadge();
    initGlobalNavEvents();
    initToast();
    initSearchBar();
});

function initToast() {
    const toastHTML = `
        <div id="toast" style="position:fixed; bottom:30px; left:50%; transform:translateX(-50%); background:#2C1F14; color:#fff; padding:12px 24px; border-radius:50px; font-size:13px; z-index:3000; display:none; align-items:center; gap:10px; box-shadow:0 10px 25px rgba(0,0,0,0.2)">
            <span id="toast-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path d="M20 6L9 17l-5-5"/></svg></span>
            <span id="toast-msg">Added to cart!</span>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', toastHTML);
}

function showToast(msg, type) {
    type = type || 'success';
    var bgColors = {
        success: '#2C1F14',
        error: '#c0392b',
        info: '#4A7C3F',
        warning: '#B8860B'
    };
    var icons = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path d="M20 6L9 17l-5-5"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
    };
    var toast = document.getElementById('toast');
    var toastMsg = document.getElementById('toast-msg');
    var toastIcon = document.getElementById('toast-icon');
    if (!toast) return;
    toast.style.background = bgColors[type] || bgColors.success;
    if (toastIcon) toastIcon.innerHTML = icons[type] || icons.success;
    toastMsg.textContent = msg;
    toast.style.display = 'flex';
    setTimeout(function() { toast.style.display = 'none'; }, 2500);
}

// Initialize Cart Drawer functionality
function initCartDrawer() {
    // Inject Cart Drawer HTML if it doesn't exist
    if (!document.getElementById('cart-drawer')) {
        const cartHTML = `
            <div class="cart-overlay" id="cart-overlay"></div>
            <div class="cart-drawer" id="cart-drawer">
                <div class="cart-header">
                    <h2>Your Shopping Cart</h2>
                    <button class="close-cart" id="close-cart">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <div class="cart-body" id="cart-items-container">
                    <!-- Static items for fail-safe prototype -->
                    <div class="cart-item" style="display:flex;gap:16px;margin-bottom:20px;align-items:center">
                        <div class="cart-item-img" style="width:70px;height:70px;background:#F5F0E8;border-radius:6px;display:flex;align-items:center;justify-content:center">
                            <svg viewBox="0 0 120 120" fill="none" style="width:40px"><rect x="10" y="55" width="100" height="42" rx="7" fill="#8B6A48"/><rect x="10" y="42" width="20" height="36" rx="5" fill="#A07858"/><rect x="90" y="42" width="20" height="36" rx="5" fill="#A07858"/></svg>
                        </div>
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:600;color:#2C1F14;margin-bottom:4px">Luna Sofa</div>
                            <div style="font-size:11px;color:#999;margin-bottom:8px">1 × EGP 12,999</div>
                        </div>
                    </div>
                </div>
                <div class="cart-footer">
                    <div class="cart-total-row">
                        <span>Subtotal</span>
                        <span id="cart-subtotal">EGP 0</span>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px">
                        <button class="btn-checkout" onclick="location.href='/cart'" style="background:#F5F0E8; color:#2C1F14; width:100%; border:none; padding:12px; font-weight:600; cursor:pointer; border-radius:6px">View Cart</button>
                        <button class="btn-checkout" onclick="location.href='/checkout'" style="background:#2C1F14; color:#fff; width:100%; border:none; padding:12px; font-weight:600; cursor:pointer; border-radius:6px">Checkout</button>
                    </div>
                    <button onclick="closeCart()" class="btn-outline" style="width:100%; border:1px solid #EDE8E2; background:none; color:#888; font-size:11px; padding:10px; border-radius:6px; cursor:pointer">Continue Shopping</button>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', cartHTML);
    }

    const cartOverlay = document.getElementById('cart-overlay');
    const cartDrawer = document.getElementById('cart-drawer');
    const closeBtn = document.getElementById('close-cart');
    const cartToggle = document.querySelector('.cart-trigger');

    if (cartToggle) {
        cartToggle.addEventListener('click', openCart);
    }

    if (closeBtn) closeBtn.addEventListener('click', closeCart);
    if (cartOverlay) cartOverlay.addEventListener('click', closeCart);
}

function openCart() {
    document.getElementById('cart-overlay').classList.add('active');
    document.getElementById('cart-drawer').classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent scroll
}

function closeCart() {
    document.getElementById('cart-overlay').classList.remove('active');
    document.getElementById('cart-drawer').classList.remove('active');
    document.body.style.overflow = '';
}


function initGlobalNavEvents() {
    // Search
    const searchBtn = document.querySelector('.search-trigger');
    if (searchBtn) {
        searchBtn.addEventListener('click', toggleSearch);
    }

}

// Global function to add to cart — delegates to Cart.add() from api.js
window.addToCart = function(product) {
    if (window.Cart && typeof Cart.add === 'function') {
        Cart.add(product);
        if (typeof updateBadges === 'function') updateBadges();
        if (typeof renderCart === 'function') renderCart();
    }
};

function removeFromCart(index) {
    let cart = DH_STORAGE.get('dh_cart') || [];
    cart.splice(index, 1);
    DH_STORAGE.set('dh_cart', cart);
    updateBadges();
    renderCart();
}

function updateQuantity(index, delta) {
    let cart = DH_STORAGE.get('dh_cart') || [];
    cart[index].quantity += delta;
    
    if (cart[index].quantity < 1) {
        cart.splice(index, 1);
    }
    
    DH_STORAGE.set('dh_cart', cart);
    updateBadges();
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cart-items-container');
    const subtotalEl = document.getElementById('cart-subtotal');

    if (!container) return;

    // Always fetch fresh cart from API
    API.get('/cart').then(res => {
        const cart = res.cart?.items || [];
        const badge = document.querySelector('.badge-cart');
        if (badge) {
            const count = res.cart?.items_count || 0;
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }

        if (cart.length === 0) {
            container.innerHTML = `
                <div style="text-align:center;padding:40px 20px;color:#aaa">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#C4A882" stroke-width="1.5" style="width:48px;height:48px;margin-bottom:12px">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <p style="font-size:14px">Your cart is empty</p>
                </div>
            `;
            if (subtotalEl) subtotalEl.textContent = 'EGP 0';
            return;
        }

        let subtotal = 0;
        container.innerHTML = cart.map((item, index) => {
            const itemTotal = parseFloat(item.price) * (parseInt(item.quantity) || 1);
            subtotal += itemTotal;
            const imgSrc = item.product?.image || '/img/placeholder.svg';
            return `
                <div class="cart-item" style="display:flex;gap:16px;margin-bottom:20px;align-items:center">
                    <div class="cart-item-img" style="width:70px;height:70px;background:#F5F0E8;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden">
                        <img src="${imgSrc}" alt="${item.name}" style="width:100%;height:100%;object-fit:contain" onerror="this.style.display='none'">
                    </div>
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:600;color:#2C1F14;margin-bottom:4px">${item.name}</div>
                        <div style="font-size:11px;color:#999;margin-bottom:8px">${item.variant || 'Standard'} × ${item.quantity}</div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <div class="qty-ctrl" style="transform:scale(0.8);transform-origin:left">
                                <button class="qty-btn" onclick="cartDrawerQty(${item.id}, -1)">−</button>
                                <span class="qty-num">${item.quantity}</span>
                                <button class="qty-btn" onclick="cartDrawerQty(${item.id}, 1)">+</button>
                            </div>
                            <div style="font-size:13px;font-weight:700">EGP ${itemTotal.toLocaleString()}</div>
                        </div>
                    </div>
                    <button onclick="cartDrawerRemove(${item.id})" style="background:none;border:none;cursor:pointer;color:#aaa">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            `;
        }).join('');

        if (subtotalEl) subtotalEl.textContent = `EGP ${subtotal.toLocaleString()}`;
    }).catch(() => {});
}

// ── Cart drawer quantity controls (call API) ───────────────────────────────
window.cartDrawerQty = async function(itemId, delta) {
    const res = await API.get('/cart').catch(() => ({}));
    const items = res.cart?.items || [];
    const item = items.find(i => i.id == itemId);
    if (!item) return;
    const newQty = Math.max(0, item.quantity + delta);
    if (newQty === 0) {
        await Cart.remove(itemId);
    } else {
        await Cart.updateQty(itemId, newQty);
    }
    renderCart();
};

window.cartDrawerRemove = async function(itemId) {
    await Cart.remove(itemId);
    renderCart();
};

function updateBadges() {
    // Delegate to Cart.updateBadge() which fetches from API
    if (window.Cart && window.Cart.updateBadge) {
        Cart.updateBadge();
    }
}

function initSearchBar() {
    const navRight = document.querySelector('.nav-right');
    if (!navRight) return;

    const searchBarHTML = `
        <div class="search-bar" id="search-bar">
            <input type="text" id="search-input" placeholder="Search furniture...">
            <button id="search-close" style="background:none; border:none; cursor:pointer; color:#aaa; font-size:18px">×</button>
        </div>
    `;
    navRight.insertAdjacentHTML('afterbegin', searchBarHTML);

    const input = document.getElementById('search-input');
    const close = document.getElementById('search-close');

    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const query = input.value.trim();
            if (query) location.href = '/shop?search=' + encodeURIComponent(query);
        }
    });

    if (close) close.addEventListener('click', toggleSearch);
}

function toggleSearch() {
    const bar = document.getElementById('search-bar');
    if (bar) {
        bar.classList.toggle('active');
        if (bar.classList.contains('active')) {
            document.getElementById('search-input').focus();
        }
    }
}

window.validateForm = function(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let valid = true;
    inputs.forEach(input => {
        if (!input.value.trim()) {
            valid = false;
            input.style.borderColor = '#c0392b';
        } else {
            input.style.borderColor = '#EDE8E2';
        }
    });

    if (!valid) alert('Please fill in all required fields.');
    return valid;
};
