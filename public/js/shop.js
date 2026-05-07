document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('search');
    const catQuery = urlParams.get('category');
    const sortQuery = urlParams.get('sort');

    let displayProducts = [...PRODUCTS];

    if (searchQuery) {
        document.querySelector('.breadcrumb span').textContent = `Search results for "${searchQuery}"`;
        displayProducts = displayProducts.filter(p =>
            p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            p.category.toLowerCase().includes(searchQuery.toLowerCase())
        );
    } else if (catQuery) {
        document.querySelector('.breadcrumb span').textContent = catQuery;
        displayProducts = displayProducts.filter(p => p.category === catQuery);
        // Also check the checkbox in sidebar
        const check = Array.from(document.querySelectorAll('.filter-item label')).find(l => l.textContent.includes(catQuery));
        if (check) check.previousElementSibling.checked = true;
    }

    if (sortQuery === 'new') {
        displayProducts.reverse();
    } else if (sortQuery === 'sale') {
        displayProducts = displayProducts.filter(p => p.badge === 'Sale');
    }

    renderProducts(displayProducts);
    initFilters();
    initSort();
});

function renderProducts(productsToRender) {
    const grid = document.querySelector('.prod-grid');
    const countEl = document.querySelector('.result-count');
    if (!grid) return;

    if (countEl) countEl.textContent = `${productsToRender.length} products found`;

    if (productsToRender.length === 0) {
        grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 60px; color: #999;">No products match your filters.</div>`;
        return;
    }

    grid.innerHTML = productsToRender.map(p => {
        const starsStr = '★'.repeat(p.stars || 5) + '☆'.repeat(5 - (p.stars || 5));
        return `
            <div class="prod-card" data-id="${p.id}">
                <div class="prod-img">
                    ${p.badge ? `<div class="prod-badge" style="background:${p.badgeColor || '#B8860B'}">${p.badge}</div>` : ''}
                    ${p.svg}
                </div>
                <div class="stars">${starsStr}</div>
                <div class="prod-name">${p.name}</div>
                <div class="prod-cat">${p.category}</div>
                <div class="prod-price">
                    EGP ${p.price.toLocaleString()}
                    ${p.oldPrice ? `<s>EGP ${p.oldPrice.toLocaleString()}</s>` : ''}
                </div>
                <button class="btn-cart">Add to Cart</button>
            </div>
        `;
    }).join('');

    grid.querySelectorAll('.prod-card').forEach(card => {
        const id = card.dataset.id;
        const product = PRODUCTS.find(p => p.id === id);

        card.querySelector('.btn-cart').addEventListener('click', (e) => {
            e.stopPropagation();
            addToCart(product);
        });

        card.addEventListener('click', () => {
            location.href = `product.html?id=${id}`;
        });
    });
}

function initFilters() {
    const checks = document.querySelectorAll('.filter-item input');
    checks.forEach(check => {
        check.addEventListener('change', applyFilters);
    });

    const applyBtn = document.querySelector('.btn-apply');
    if (applyBtn) applyBtn.addEventListener('click', applyFilters);
}

function applyFilters() {
    const activeCats = Array.from(document.querySelectorAll('.filter-item input:checked'))
        .map(input => input.nextElementSibling.textContent.split('(')[0].trim());

    let filtered = PRODUCTS;
    if (activeCats.length > 0) {
        filtered = PRODUCTS.filter(p => activeCats.includes(p.category));
    }

    renderProducts(filtered);
}

function initSort() {
    const select = document.querySelector('.sort-row select');
    if (select) {
        select.addEventListener('change', (e) => {
            let sorted = [...PRODUCTS];
            if (e.target.value === 'price-low') sorted.sort((a, b) => a.price - b.price);
            if (e.target.value === 'price-high') sorted.sort((a, b) => b.price - a.price);
            renderProducts(sorted);
        });
    }
}