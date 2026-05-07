/**
 * DecoHomz — Home Page Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    renderBestSellers();
});

function renderBestSellers() {
    const grid = document.querySelector('.prod-grid');
    if (!grid) return;

    const bestSellers = PRODUCTS.slice(0, 4);

    grid.innerHTML = bestSellers.map(p => {
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
                <div class="prod-price">EGP ${p.price.toLocaleString()}</div>
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