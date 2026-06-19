/**
 * DecoHomz — Shared Logic (v2 — Premium Redesign)
 * Handles mobile menu, search overlay, cart drawer,
 * scroll animations, toast, and global UI interactions
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
 */
const DH_STORAGE = {
    get: (key) => {
        try {
            const localData = localStorage.getItem(key);
            if (localData) return JSON.parse(localData);
            if (window.name) {
                const sessionData = JSON.parse(window.name);
                return sessionData[key] || null;
            }
        } catch (e) { console.warn('Storage read error', e); }
        return null;
    },
    set: (key, value) => {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            let sessionData = {};
            try { sessionData = JSON.parse(window.name || '{}'); } catch(e) {}
            sessionData[key] = value;
            window.name = JSON.stringify(sessionData);
        } catch (e) { console.warn('Storage write error', e); }
    }
};

// ──────────────────────────────────────────────
// DOMContentLoaded — Initialize Everything
// ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initSearchOverlay();
    initScrollEffects();
    initScrollToTop();
    initScrollProgress();
    initScrollAnimations();
    initToast();
    initCartDrawer();
    renderCart();

    // Badges
    if (window.Cart && Cart.updateBadge) {
        // Skip fetching badge count on cart/checkout pages to avoid duplicate requests,
        // since those pages load the cart details and update the badge themselves.
        if (window.location.pathname !== '/cart' && window.location.pathname !== '/checkout') {
            Cart.updateBadge();
        }
    }
});


// ══════════════════════════════════════════════
// MOBILE MENU
// ══════════════════════════════════════════════
function initMobileMenu() {
    const hamburger = document.getElementById('hamburger-btn');
    const overlay = document.getElementById('mobile-nav-overlay');
    const drawer = document.getElementById('mobile-nav-drawer');
    const closeBtn = document.getElementById('mobile-nav-close');

    if (!hamburger || !drawer) return;

    function openMenu() {
        hamburger.classList.add('active');
        hamburger.setAttribute('aria-expanded', 'true');
        overlay.classList.add('active');
        drawer.classList.add('active');
        document.body.classList.add('menu-open');
    }

    function closeMenu() {
        hamburger.classList.remove('active');
        hamburger.setAttribute('aria-expanded', 'false');
        overlay.classList.remove('active');
        drawer.classList.remove('active');
        document.body.classList.remove('menu-open');
    }

    hamburger.addEventListener('click', function() {
        if (drawer.classList.contains('active')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    if (closeBtn) closeBtn.addEventListener('click', closeMenu);
    if (overlay) overlay.addEventListener('click', closeMenu);

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && drawer.classList.contains('active')) {
            closeMenu();
        }
    });
}


// ══════════════════════════════════════════════
// SEARCH OVERLAY
// ══════════════════════════════════════════════
function initSearchOverlay() {
    const trigger = document.getElementById('search-trigger');
    const overlay = document.getElementById('search-overlay');
    const input = document.getElementById('search-input');
    const closeBtn = document.getElementById('search-close-btn');

    if (!trigger || !overlay) return;

    function openSearch() {
        overlay.classList.add('active');
        document.body.classList.add('menu-open');
        setTimeout(function() {
            if (input) input.focus();
        }, 200);
    }

    function closeSearch() {
        overlay.classList.remove('active');
        document.body.classList.remove('menu-open');
        if (input) input.value = '';
        var resultsEl = document.getElementById('search-results');
        if (resultsEl) { resultsEl.innerHTML = ''; resultsEl.style.display = 'none'; }
    }

    trigger.addEventListener('click', openSearch);
    if (closeBtn) closeBtn.addEventListener('click', closeSearch);

    // Close on overlay click (outside the search box)
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeSearch();
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) {
            closeSearch();
        }
    });

    // Search instantly on input — show results inline
    if (input) {
        var _searchDebounce;
        var resultsEl = document.getElementById('search-results');

        function renderSearchResults(products) {
            if (!resultsEl) return;
            if (!products.length) {
                resultsEl.innerHTML = '<div class="search-results-empty">No products found</div>';
                resultsEl.style.display = 'block';
                return;
            }
            resultsEl.innerHTML = products.map(function(p) {
                var img = (p.primary_image && p.primary_image.thumbnail_url) || (p.primary_image && p.primary_image.url) || (p.images && p.images[0] && (p.images[0].thumbnail_url || p.images[0].url)) || '/img/placeholder.svg';
                var price = parseFloat(p.price) || 0;
                var oldPrice = parseFloat(p.old_price) || 0;
                var slug = p.slug || p.id;
                var categoryName = (p.category && p.category.name) || '';
                var desc = p.description ? p.description.replace(/<[^>]*>/g, '').substring(0, 80) : '';
                var stars = parseInt(p.stars) || 0;
                var reviewCount = (p.approvedReviews && p.approvedReviews.length) || 0;
                var stock = parseInt(p.stock) || 0;
                var inStock = stock > 0 || (p.colors && p.colors.some(function(c) { return (parseInt(c.stock) || 0) > 0; }));

                var starsHtml = '';
                if (stars > 0) {
                    starsHtml = '<div class="search-result-rating">';
                    for (var i = 1; i <= 5; i++) {
                        starsHtml += '<span class="star' + (i <= stars ? ' filled' : '') + '">&#9733;</span>';
                    }
                    if (reviewCount > 0) starsHtml += '<span class="review-count">(' + reviewCount + ')</span>';
                    starsHtml += '</div>';
                }

                return '<a href="/product/' + esc(slug) + '" class="search-result-item">' +
                    '<div class="search-result-img"><img src="' + img + '" alt="' + esc(p.name) + '" onerror="this.src=\'/img/placeholder.svg\'"></div>' +
                    '<div class="search-result-info">' +
                        '<div class="search-result-name">' + esc(p.name) + '</div>' +
                        (categoryName ? '<div class="search-result-meta">' + esc(categoryName) + (p.material ? ' · ' + esc(p.material) : '') + '</div>' : (p.material ? '<div class="search-result-meta">' + esc(p.material) + '</div>' : '')) +
                        (desc ? '<div class="search-result-desc">' + esc(desc) + '...</div>' : '') +
                        starsHtml +
                        '<div class="search-result-bottom">' +
                            '<div class="search-result-price">EGP ' + price.toLocaleString() + (oldPrice > price ? ' <span class="search-result-old-price">EGP ' + oldPrice.toLocaleString() + '</span>' : '') + '</div>' +
                            '<span class="search-result-stock ' + (inStock ? 'in-stock' : 'out-stock') + '">' + (inStock ? 'In Stock' : 'Out of Stock') + '</span>' +
                        '</div>' +
                    '</div>' +
                '</a>';
            }).join('');
            resultsEl.style.display = 'block';
        }

        input.addEventListener('input', function() {
            clearTimeout(_searchDebounce);
            var query = input.value.trim();
            if (query.length < 2) {
                if (resultsEl) { resultsEl.innerHTML = ''; resultsEl.style.display = 'none'; }
                return;
            }
            if (resultsEl) {
                resultsEl.innerHTML = '<div class="search-results-loading"></div>';
                resultsEl.style.display = 'block';
            }
            _searchDebounce = setTimeout(function() {
                API.get('/products', { params: { search: query, per_page: 8 } }).then(function(res) {
                    var products = res.products || res.data || res || [];
                    if (!Array.isArray(products)) products = products.data || [];
                    renderSearchResults(products);
                }).catch(function() {
                    if (resultsEl) { resultsEl.innerHTML = ''; resultsEl.style.display = 'none'; }
                });
            }, 300);
        });

        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(_searchDebounce);
                var query = input.value.trim();
                if (query) {
                    location.href = '/shop?search=' + encodeURIComponent(query);
                }
            }
        });
    }
}


// ══════════════════════════════════════════════
// SCROLL EFFECTS
// ══════════════════════════════════════════════
function initScrollEffects() {
    var nav = document.getElementById('main-nav');
    if (!nav) return;

    var lastScroll = 0;

    window.addEventListener('scroll', function() {
        var currentScroll = window.pageYOffset;

        // Nav shadow on scroll
        if (currentScroll > 10) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    }, { passive: true });
}

function initScrollToTop() {
    var btn = document.getElementById('scroll-top-btn');
    if (!btn) return;

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 400) {
            btn.classList.add('visible');
        } else {
            btn.classList.remove('visible');
        }
    }, { passive: true });

    btn.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

function initScrollProgress() {
    var progressBar = document.getElementById('scroll-progress');
    if (!progressBar) return;

    window.addEventListener('scroll', function() {
        var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        var scrolled = (winScroll / height) * 100;
        progressBar.style.width = scrolled + '%';
    }, { passive: true });
}


// ══════════════════════════════════════════════
// SCROLL ANIMATIONS (Intersection Observer)
// ══════════════════════════════════════════════
function initScrollAnimations() {
    var elements = document.querySelectorAll('.animate-on-scroll');
    if (!elements.length) return;

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -40px 0px'
        });

        elements.forEach(function(el) {
            observer.observe(el);
        });
    } else {
        // Fallback: show all immediately
        elements.forEach(function(el) {
            el.classList.add('is-visible');
        });
    }
}


// ══════════════════════════════════════════════
// TOAST NOTIFICATIONS
// ══════════════════════════════════════════════
var _toastTimeout = null;

function initToast() {
    if (document.getElementById('toast')) return;

    var toastHTML =
        '<div id="toast">' +
            '<span id="toast-icon"></span>' +
            '<span id="toast-msg">Notification</span>' +
        '</div>';
    document.body.insertAdjacentHTML('beforeend', toastHTML);
}

window.showToast = function(msg, type) {
    type = type || 'success';

    var bgColors = {
        success: 'var(--color-primary)',
        error: 'var(--color-error)',
        info: '#2E6FBA',
        warning: 'var(--color-accent)'
    };

    var icons = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path d="M20 6L9 17l-5-5"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
    };

    var toast = document.getElementById('toast');
    var toastMsg = document.getElementById('toast-msg');
    var toastIcon = document.getElementById('toast-icon');

    if (!toast) {
        initToast();
        toast = document.getElementById('toast');
        toastMsg = document.getElementById('toast-msg');
        toastIcon = document.getElementById('toast-icon');
    }
    if (!toast) return;

    // Clear previous timeout
    if (_toastTimeout) {
        clearTimeout(_toastTimeout);
        toast.classList.remove('show', 'hiding');
    }

    toast.style.background = bgColors[type] || bgColors.success;
    if (toastIcon) toastIcon.innerHTML = icons[type] || icons.success;
    if (toastMsg) toastMsg.textContent = msg;

    // Show
    toast.style.display = 'flex';
    requestAnimationFrame(function() {
        toast.classList.add('show');
        toast.classList.remove('hiding');
    });

    // Auto-hide after 3 seconds
    _toastTimeout = setTimeout(function() {
        toast.classList.add('hiding');
        toast.classList.remove('show');
        setTimeout(function() {
            toast.style.display = 'none';
            toast.classList.remove('hiding');
        }, 300);
    }, 3000);
};


// ══════════════════════════════════════════════
// CART DRAWER
// ══════════════════════════════════════════════
function initCartDrawer() {
    var t = window.__cartTranslations || {};
    if (!document.getElementById('cart-drawer')) {
        var cartHTML =
            '<div class="cart-overlay" id="cart-overlay"></div>' +
            '<div class="cart-drawer" id="cart-drawer">' +
                '<div class="drag-handle"></div>' +
                '<div class="cart-header">' +
                    '<h2>' + (t.title || 'Your Shopping Cart') + '</h2>' +
                    '<button class="close-cart" id="close-cart">' +
                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>' +
                    '</button>' +
                '</div>' +
                '<div class="cart-body" id="cart-items-container"></div>' +
                '<div class="cart-footer">' +
                    '<div class="cart-total-row"><span>' + (t.subtotal || 'Subtotal') + '</span><span id="cart-subtotal">EGP 0</span></div>' +
                    '<div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px">' +
                        '<a href="/cart" class="btn-outline" style="text-align:center;padding:12px;font-size:13px;">' + (t.viewCart || 'View Cart') + '</a>' +
                        '<a href="/checkout" class="btn-dark" style="text-align:center;padding:12px;font-size:13px;">' + (t.checkout || 'Checkout') + '</a>' +
                    '</div>' +
                    '<button onclick="closeCart()" style="width:100%;border:1px solid var(--color-border);background:none;color:var(--color-text-muted);font-size:12px;padding:10px;border-radius:var(--radius-sm);cursor:pointer;">' + (t.continueShopping || 'Continue Shopping') + '</button>' +
                '</div>' +
            '</div>';
        document.body.insertAdjacentHTML('beforeend', cartHTML);
    }

    var cartOverlay = document.getElementById('cart-overlay');
    var closeBtn = document.getElementById('close-cart');

    if (closeBtn) closeBtn.addEventListener('click', closeCart);
    if (cartOverlay) cartOverlay.addEventListener('click', closeCart);
}

let cartCloseTimeout = null;

window.openCart = function(isAutoClose = true) {
    var overlay = document.getElementById('cart-overlay');
    var drawer = document.getElementById('cart-drawer');
    if (!drawer) return;

    renderCart();

    if (overlay) {
        if (isAutoClose) {
            overlay.classList.remove('active');
        } else {
            overlay.classList.add('active');
            overlay.style.background = '';
            overlay.style.backdropFilter = '';
            overlay.style.pointerEvents = '';
        }
    }
    
    drawer.classList.add('active');
    
    if (isAutoClose) {
        document.body.style.overflow = '';
    } else {
        document.body.style.overflow = 'hidden';
    }

    if (cartCloseTimeout) {
        clearTimeout(cartCloseTimeout);
        cartCloseTimeout = null;
    }

    if (isAutoClose) {
        var progress = document.getElementById('cart-drawer-progress');
        if (!progress) {
            progress = document.createElement('div');
            progress.id = 'cart-drawer-progress';
            progress.style.position = 'absolute';
            progress.style.top = '0';
            progress.style.left = '0';
            progress.style.height = '3px';
            progress.style.background = 'var(--color-accent)';
            progress.style.width = '100%';
            progress.style.transition = 'width 4s linear';
            drawer.appendChild(progress);
        }
        progress.style.width = '100%';
        progress.getBoundingClientRect();
        progress.style.width = '0%';

        var startTimeout = function() {
            cartCloseTimeout = setTimeout(function() {
                closeCart();
            }, 4000);
        };

        startTimeout();

        drawer.onmouseenter = function() {
            if (cartCloseTimeout) {
                clearTimeout(cartCloseTimeout);
                cartCloseTimeout = null;
            }
            if (progress) {
                progress.style.transition = 'none';
                progress.style.width = '100%';
            }
        };

        drawer.onmouseleave = function() {
            if (progress) {
                progress.style.transition = 'width 2s linear';
                progress.getBoundingClientRect();
                progress.style.width = '0%';
            }
            cartCloseTimeout = setTimeout(function() {
                closeCart();
            }, 2000);
        };
    } else {
        var progress = document.getElementById('cart-drawer-progress');
        if (progress) progress.remove();
        drawer.onmouseenter = null;
        drawer.onmouseleave = null;
    }
};

window.closeCart = function() {
    var overlay = document.getElementById('cart-overlay');
    var drawer = document.getElementById('cart-drawer');
    if (overlay) {
        overlay.classList.remove('active');
        overlay.style.background = '';
        overlay.style.backdropFilter = '';
        overlay.style.pointerEvents = '';
    }
    if (drawer) {
        drawer.classList.remove('active');
        var progress = document.getElementById('cart-drawer-progress');
        if (progress) progress.remove();
    }
    document.body.style.overflow = '';
    if (cartCloseTimeout) {
        clearTimeout(cartCloseTimeout);
        cartCloseTimeout = null;
    }
};

// Global add to cart
window.addToCart = function(product) {
    if (window.Cart && typeof Cart.add === 'function') {
        Cart.add(product);
        if (typeof updateBadges === 'function') updateBadges();
        if (typeof renderCart === 'function') renderCart();
    }
};

function renderCart() {
    var container = document.getElementById('cart-items-container');
    var subtotalEl = document.getElementById('cart-subtotal');

    if (!container) return;

    if (typeof API === 'undefined') {
        if ((renderCart._retries || 0) < 5) {
            renderCart._retries = (renderCart._retries || 0) + 1;
            setTimeout(renderCart, 100);
        }
        return;
    }
    renderCart._retries = 0;

    API.get('/cart').then(function(res) {
        var cart = res.cart?.items || [];
        var badge = document.querySelector('.badge-cart');
        if (badge) {
            var count = res.cart?.items_count || 0;
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }

        if (cart.length === 0) {
            var t = window.__cartTranslations || {};
            container.innerHTML =
                '<div style="text-align:center;padding:40px 20px;color:var(--color-text-faint)">' +
                    '<svg viewBox="0 0 24 24" fill="none" stroke="var(--color-accent-light)" stroke-width="1.5" style="width:48px;height:48px;margin-bottom:12px">' +
                        '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>' +
                        '<line x1="3" y1="6" x2="21" y2="6"/>' +
                        '<path d="M16 10a4 4 0 0 1-8 0"/>' +
                    '</svg>' +
                    '<p style="font-size:14px;margin-bottom:16px">' + (t.empty || 'Your cart is empty') + '</p>' +
                    '<a href="/shop" class="btn-dark" style="display:inline-block;padding:10px 24px;font-size:13px;text-decoration:none">' + (t.startShopping || 'Start Shopping') + '</a>' +
                '</div>';
            if (subtotalEl) subtotalEl.textContent = 'EGP 0';
            return;
        }

        var t = window.__cartTranslations || {};
        var subtotal = 0;
        cart.reverse();
        container.innerHTML = cart.map(function(item) {
            var itemTotal = parseFloat(item.price) * (parseInt(item.quantity) || 1);
            subtotal += itemTotal;
            var imgSrc = item.product?.image || '/img/placeholder.svg';
            return '<div style="display:flex;gap:16px;margin-bottom:20px;align-items:center">' +
                '<div style="width:70px;height:70px;background:var(--color-bg-warm);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">' +
                    '<img src="' + imgSrc + '" alt="' + esc(item.name) + '" style="width:100%;height:100%;object-fit:contain" onerror="this.style.display=\'none\'">' +
                '</div>' +
                '<div style="flex:1;min-width:0">' +
                    '<div style="font-size:14px;font-weight:600;color:var(--color-text);margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + esc(item.name) + '</div>' +
                    '<div style="font-size:12px;color:var(--color-text-faint);margin-bottom:8px">' + (item.color ? '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:' + esc(item.color.hex_code) + ';vertical-align:middle;margin-right:4px;border:1px solid var(--color-border)"></span>' + esc(item.color.name) : (t.standard || 'Standard')) + ' × ' + item.quantity + '</div>' +
                    '<div style="display:flex;justify-content:space-between;align-items:center">' +
                        '<div class="qty-ctrl" style="transform:scale(0.85);transform-origin:left">' +
                            '<button class="qty-btn" onclick="cartDrawerQty(' + item.id + ', -1)">−</button>' +
                            '<span class="qty-num">' + item.quantity + '</span>' +
                            '<button class="qty-btn" onclick="cartDrawerQty(' + item.id + ', 1)">+</button>' +
                        '</div>' +
                        '<div style="font-size:14px;font-weight:700;color:var(--color-text)">EGP ' + itemTotal.toLocaleString() + '</div>' +
                    '</div>' +
                '</div>' +
                '<button onclick="cartDrawerRemove(' + item.id + ')" style="background:none;border:none;cursor:pointer;color:var(--color-text-faint);padding:4px;transition:color 0.15s" onmouseover="this.style.color=\'var(--color-error)\'" onmouseout="this.style.color=\'var(--color-text-faint)\'">' +
                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>' +
                '</button>' +
            '</div>';
        }).join('');

        if (subtotalEl) subtotalEl.textContent = 'EGP ' + subtotal.toLocaleString();
    }).catch(function() {});
}

// Cart drawer quantity controls
window.cartDrawerQty = async function(itemId, delta) {
    var res = await API.get('/cart').catch(function() { return {}; });
    var items = res.cart?.items || [];
    var item = items.find(function(i) { return i.id == itemId; });
    if (!item) return;
    var newQty = Math.max(0, item.quantity + delta);
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
    if (window.Cart && window.Cart.updateBadge) {
        Cart.updateBadge();
    }
}


// ══════════════════════════════════════════════
// FORM VALIDATION
// ══════════════════════════════════════════════
window.validateForm = function(formId) {
    var form = document.getElementById(formId);
    if (!form) return true;

    var inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    var valid = true;
    inputs.forEach(function(input) {
        if (!input.value.trim()) {
            valid = false;
            input.style.borderColor = 'var(--color-error)';
            input.style.boxShadow = '0 0 0 3px rgba(192,57,43,0.1)';
        } else {
            input.style.borderColor = 'var(--color-border)';
            input.style.boxShadow = 'none';
        }
    });

    if (!valid) showToast('Please fill in all required fields.', 'warning');
    return valid;
};
