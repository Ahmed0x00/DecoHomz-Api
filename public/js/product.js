/**
 * DecoHomz — Product Page Logic
 */

let currentProduct = PRODUCTS[0]; // Default

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    
    if (productId) {
        const found = PRODUCTS.find(p => p.id === productId);
        if (found) {
            currentProduct = found;
            renderProductDetails(found);
        }
    }

    initProductTabs();
    initQuantitySelector();
    initAddToCart();
    initSelectionListeners();
    renderRelatedProducts();
});

function renderRelatedProducts() {
    const grid = document.querySelector('.rel-grid');
    if (!grid) return;

    // Show 4 related products from the same category
    const related = PRODUCTS
        .filter(p => p.category === currentProduct.category && p.id !== currentProduct.id)
        .slice(0, 4);
    
    if (related.length === 0) return;

    grid.innerHTML = related.map(p => `
        <div class="rel-card" onclick="location.href='product.html?id=${p.id}'">
            <div class="rel-img">${p.svg}</div>
            <div class="rel-info">
                <div class="rel-name">${p.name}</div>
                <div class="rel-price">EGP ${p.price.toLocaleString()}</div>
            </div>
        </div>
    `).join('');
}

function renderProductDetails(p) {
    document.title = `${p.name} — DecoHomz`;
    document.querySelector('.breadcrumb span').textContent = p.name;
    document.querySelector('.prod-title').textContent = p.name;
    document.querySelector('.prod-cat-tag').textContent = p.category;
    document.querySelector('.main-price').textContent = `EGP ${p.price.toLocaleString()}`;
    
    if (p.oldPrice) {
        document.querySelector('.old-price').textContent = `EGP ${p.oldPrice.toLocaleString()}`;
        const discount = Math.round((1 - p.price / p.oldPrice) * 100);
        document.querySelector('.sale-tag').textContent = `${discount}% Off`;
        document.querySelector('.sale-tag').style.display = 'inline-block';
    } else {
        document.querySelector('.old-price').textContent = '';
        document.querySelector('.sale-tag').style.display = 'none';
    }

    document.querySelector('.main-img').innerHTML = p.svg;
    const starsStr = '★'.repeat(p.stars) + '☆'.repeat(5 - p.stars);
    document.querySelector('.rating-row .stars').textContent = starsStr;
}

function initProductTabs() {
    const tabs = document.querySelectorAll('.tab');
    const content = document.querySelector('.tab-content');
    const specs = document.getElementById('specs');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            // Toggle content based on tab text
            const text = tab.textContent.trim();
            if (text.includes('Specifications')) {
                content.childNodes[0].textContent = '';
                specs.style.display = 'grid';
                const reviews = document.getElementById('reviews-content');
                if (reviews) reviews.style.display = 'none';
            } else if (text.includes('Description')) {
                content.childNodes[0].textContent = currentProduct.description || 'Premium quality furniture crafted for your home.';
                specs.style.display = 'none';
                const reviews = document.getElementById('reviews-content');
                if (reviews) reviews.style.display = 'none';
            } else if (text.includes('Reviews')) {
                content.childNodes[0].textContent = '';
                specs.style.display = 'none';
                let reviews = document.getElementById('reviews-content');
                if (!reviews) {
                    reviews = document.createElement('div');
                    reviews.id = 'reviews-content';
                    reviews.style.marginTop = '20px';
                    reviews.innerHTML = `
                        <div style="padding: 20px; border: 1px solid #EDE8E2; border-radius: 8px;">
                            <div style="font-weight:700; margin-bottom:10px">Very comfortable and stylish!</div>
                            <div style="color:#B8860B; margin-bottom:10px">★★★★★</div>
                            <div style="font-size:12px; color:#888">Verified Purchase — 2 days ago</div>
                        </div>
                    `;
                    content.appendChild(reviews);
                }
                reviews.style.display = 'block';
            }
        });
    });
}

function initQuantitySelector() {
    const qtyNum = document.querySelector('.qty-num');
    const minusBtn = document.querySelector('.qty-btn:first-child');
    const plusBtn = document.querySelector('.qty-btn:last-child');

    if (qtyNum && minusBtn && plusBtn) {
        minusBtn.addEventListener('click', () => {
            let val = parseInt(qtyNum.textContent);
            if (val > 1) qtyNum.textContent = val - 1;
        });

        plusBtn.addEventListener('click', () => {
            let val = parseInt(qtyNum.textContent);
            qtyNum.textContent = val + 1;
        });
    }
}

function initSelectionListeners() {
    // Color selection
    const colorSwatches = document.querySelectorAll('.color-swatch');
    colorSwatches.forEach(swatch => {
        swatch.addEventListener('click', () => {
            colorSwatches.forEach(s => s.classList.remove('active'));
            swatch.classList.add('active');
        });
    });

    // Size selection
    const sizeBtns = document.querySelectorAll('.size-btn');
    sizeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            sizeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });
}

function initAddToCart() {
    const addBtn = document.querySelector('.btn-cart');
    if (addBtn) {
        addBtn.addEventListener('click', () => {
            const product = {
                id: currentProduct.id,
                name: currentProduct.name,
                price: currentProduct.price,
                variant: document.querySelector('.size-btn.active')?.textContent || 'Standard',
                quantity: parseInt(document.querySelector('.qty-num').textContent),
                svg: currentProduct.svg
            };
            
            if (window.addToCart) {
                window.addToCart(product);
            }
        });
    }
}