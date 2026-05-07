# Frontend Implementation Plan — DecoHomz

## Overview

Convert 14 static HTML mockups into Laravel Blade views backed by the REST API. Every page keeps its original CSS/JS unchanged — only HTML structure becomes Blade templates with dynamic data from the API.

**Project root:** `/home/ahmex/Downloads/DecoHomz-api/`
**Public assets:** `/home/ahmex/Downloads/DecoHomz-api/public/` (CSS, JS, images)
**Blade views:** `/home/ahmex/Downloads/DecoHomz-api/resources/views/`

---

## Architecture

### Blade Layout Structure

```
resources/views/
├── layouts/
│   ├── app.blade.php          # Main public layout (header + footer)
│   └── admin.blade.php        # Admin panel layout
├── home.blade.php             # /
├── shop.blade.php             # /shop
├── product.blade.php          # /product/{id}
├── cart.blade.php             # /cart
├── checkout.blade.php         # /checkout
├── orders/
│   └── confirmation.blade.php # /orders/confirmation
├── account.blade.php          # /account
├── auth.blade.php            # /auth (login + register tabs)
├── contact.blade.php          # /contact
├── about.blade.php            # /about
├── categories.blade.php        # /categories
├── deals.blade.php            # /deals
├── faq.blade.php             # /faq
└── new-arrivals.blade.php    # /new-arrivals
```

### Routing

All routes served through Laravel. Add to `routes/web.php`:

```php
use Illuminate\Support\Facades\Route;

// Public pages
Route::get('/', fn() => view('home'));
Route::get('/shop', fn() => view('shop'));
Route::get('/product/{id}', fn($id) => view('product', ['id' => $id]));
Route::get('/cart', fn() => view('cart'));
Route::get('/checkout', fn() => view('checkout'));
Route::get('/orders/confirmation/{orderId}', fn($orderId) => view('orders.confirmation'));
Route::get('/account', fn() => view('account'))->middleware('auth:sanctum');
Route::get('/auth', fn() => view('auth'));
Route::get('/contact', fn() => view('contact'));
Route::get('/about', fn() => view('about'));
Route::get('/categories', fn() => view('categories'));
Route::get('/deals', fn() => view('deals'));
Route::get('/faq', fn() => view('faq'));
Route::get('/new-arrivals', fn() => view('new-arrivals'));
```

### API Client (`public/js/api.js`)

All Blade views load this instead of `products-data.js`. It provides a unified fetch wrapper.

```javascript
// File: public/js/api.js
const API = {
  base: '/api',
  token: localStorage.getItem('dh_token') || null,

  setToken(token) {
    this.token = token;
    localStorage.setItem('dh_token', token);
  },
  clearToken() {
    this.token = null;
    localStorage.removeItem('dh_token');
  },

  headers() {
    const h = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
    if (this.token) h['Authorization'] = `Bearer ${this.token}`;
    return h;
  },

  async get(path) {
    const res = await fetch(`${this.base}${path}`, { headers: this.headers() });
    return this._handle(res);
  },
  async post(path, body = {}) {
    const res = await fetch(`${this.base}${path}`, {
      method: 'POST',
      headers: this.headers(),
      body: JSON.stringify(body)
    });
    return this._handle(res);
  },
  async put(path, body = {}) {
    const res = await fetch(`${this.base}${path}`, {
      method: 'PUT',
      headers: this.headers(),
      body: JSON.stringify(body)
    });
    return this._handle(res);
  },
  async patch(path, body = {}) {
    const res = await fetch(`${this.base}${path}`, {
      method: 'PATCH',
      headers: this.headers(),
      body: JSON.stringify(body)
    });
    return this._handle(res);
  },
  async del(path) {
    const res = await fetch(`${this.base}${path}`, {
      method: 'DELETE',
      headers: this.headers()
    });
    return this._handle(res);
  },

  async _handle(res) {
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw { status: res.status, data };
    return data;
  }
};

// Cart state (synced with API when logged in)
const Cart = {
  key: 'dh_cart', // localStorage key for guest cart

  get() {
    return JSON.parse(localStorage.getItem(this.key) || '[]');
  },

  set(items) {
    localStorage.setItem(this.key, JSON.stringify(items));
    this.sync();
    this.updateBadge();
  },

  async sync() {
    // When logged in, sync local cart to API
    if (!API.token) return;
    // POST local cart items to /api/cart/sync endpoint (if implemented)
    // For now, merge with server cart on page load
  },

  async mergeOnLogin() {
    if (!API.token) return;
    const localCart = this.get();
    for (const item of localCart) {
      try {
        await API.post('/cart/items', {
          product_id: item.id,
          quantity: item.quantity,
          variant: item.variant || 'Standard'
        });
      } catch(e) {}
    }
    this.set([]); // clear local after merge
  },

  updateBadge() {
    const cart = this.get();
    const total = cart.reduce((s, i) => s + (parseInt(i.quantity) || 0), 0);
    const badge = document.querySelector('.badge-cart');
    if (badge) {
      badge.textContent = total;
      badge.style.display = total > 0 ? 'flex' : 'none';
    }
  },

  count() {
    return this.get().reduce((s, i) => s + (parseInt(i.quantity) || 0), 0);
  }
};

// Auth state helper
const Auth = {
  token: () => localStorage.getItem('dh_token'),
  user: () => JSON.parse(localStorage.getItem('dh_user') || 'null'),

  async login(email, password) {
    const data = await API.post('/auth/login', { email, password });
    API.setToken(data.token);
    localStorage.setItem('dh_user', JSON.stringify(data.user));
    await Cart.mergeOnLogin();
    return data.user;
  },

  async register(payload) {
    const data = await API.post('/auth/register', payload);
    API.setToken(data.token);
    localStorage.setItem('dh_user', JSON.stringify(data.user));
    return data.user;
  },

  logout() {
    if (API.token) API.post('/auth/logout').catch(() => {});
    API.clearToken();
    localStorage.removeItem('dh_user');
    Cart.set([]);
  }
};
```

---

## Step-by-Step Implementation

---

### F1: Main Layout (`layouts/app.blade.php`)

**File:** `resources/views/layouts/app.blade.php`

**What it contains:**
- `<head>` with CSS links (same as original HTML)
- Topbar (dynamic message via `@settings` or static)
- Navigation (logo, nav links, search icon, cart icon, user icon)
- `@yield('content')` slot
- Footer (4-column grid + bottom bar)
- JS includes at bottom (same scripts as original)

**Blade-specific logic:**

```blade
{{-- Auth state: conditionally show login link or account link --}}
@if(Auth::check())
    <a href="/account">My Account</a>
@else
    <a href="/auth">Sign In</a>
@endif

{{-- Cart badge count via JS on page load --}}

{{-- Active nav link highlighting --}}
<a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Home</a>
```

**Nav items with active state:**
```blade
<li><a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Home</a></li>
<li><a href="/shop" class="{{ request()->is('shop') ? 'active' : '' }}">Shop</a></li>
<li><a href="/categories" class="{{ request()->is('categories') ? 'active' : '' }}">Categories</a></li>
<li><a href="/new-arrivals" class="{{ request()->is('new-arrivals') ? 'active' : '' }}">New Arrivals</a></li>
<li><a href="/deals" class="{{ request()->is('deals') ? 'active' : '' }}">Deals</a></li>
<li><a href="/about" class="{{ request()->is('about') ? 'active' : '' }}">About</a></li>
<li><a href="/contact" class="{{ request()->is('contact') ? 'active' : '' }}">Contact</a></li>
```

**Cart badge:** Rendered by `Cart.updateBadge()` called on `DOMContentLoaded` in `shared.js`.

**No style changes.** All CSS classes remain identical.

---

### F2: Home Page (`home.blade.php`)

**File:** `resources/views/home.blade.php`
**URL:** `/`
**Route:** `Route::get('/', fn() => view('home'));`

**Sections:**
1. Hero — static text + SVG (no API needed)
2. Categories row — load from `GET /api/categories` via JS
3. Best Sellers grid — load from `GET /api/products/featured` via JS
4. Banner — static
5. Footer — via layout

**Dynamic sections JS logic:**

```javascript
// In home.blade.php script block
(async () => {
  // Categories
  try {
    const { data: categories } = await API.get('/categories');
    renderCategories(categories);
  } catch(e) {}

  // Featured products
  try {
    const { data: products } = await API.get('/products/featured');
    renderBestSellers(products);
  } catch(e) {}
})();

function renderCategories(categories) {
  const row = document.querySelector('.cat-row');
  if (!row) return;
  row.innerHTML = categories.map(cat => `
    <a href="/shop?category=${cat.name}" class="cat-item">
      <div class="cat-box">
        <svg viewBox="0 0 48 48" fill="none">
          ${cat.svg || '<circle cx="24" cy="24" r="20" fill="#C4A882"/>'}
        </svg>
      </div>
      <div class="cat-name">${cat.name}</div>
    </a>
  `).join('');
}

function renderBestSellers(products) {
  const grid = document.querySelector('.products .prod-grid');
  if (!grid) return;
  grid.innerHTML = products.map(p => `
    <a href="/product/${p.id}" class="prod-card">
      ${p.badge ? `<div class="badge-tag">${p.badge}</div>` : ''}
      <div class="prod-img">
        <img src="${p.images?.[0]?.url || '/img/placeholder.svg'}" alt="${p.name}">
      </div>
      <div class="prod-info">
        <div class="prod-cat">${p.category?.name || ''}</div>
        <div class="prod-name">${p.name}</div>
        <div class="prod-price">EGP ${parseFloat(p.price).toLocaleString()}</div>
      </div>
    </a>
  `).join('');
}
```

---

### F3: Shop Page (`shop.blade.php`)

**File:** `resources/views/shop.blade.php`
**URL:** `/shop`
**Route:** `Route::get('/shop', fn() => view('shop'));`

**Static → Dynamic conversions:**
- Sidebar filters: Category, Price, Material, Color checkboxes rendered from JS (initial data from API)
- Product grid: loads from `GET /api/products` with query params
- Result count, sort dropdown, pagination

**Filter sidebar (static → dynamic):**

```javascript
// Load categories for sidebar
const { data: categories } = await API.get('/categories');
categories.forEach(cat => {
  // Render checkbox items with product counts
});
```

**Product grid rendering:**
```javascript
async function loadProducts(params = {}) {
  const query = new URLSearchParams(params).toString();
  const { data, links } = await API.get(`/products?${query}`);

  // Render grid
  document.getElementById('product-grid').innerHTML = data.map(p => `
    <a href="/product/${p.id}" class="prod-card">
      ${p.badge ? `<div class="badge-tag">${p.badge}</div>` : ''}
      <div class="prod-img">
        <img src="${p.images?.[0]?.url || '/img/placeholder.svg'}" alt="${p.name}">
      </div>
      <div class="prod-info">
        <div class="prod-cat">${p.category?.name || ''}</div>
        <div class="prod-name">${p.name}</div>
        <div class="prod-price">
          ${p.old_price ? `<span class="old-price">EGP ${p.old_price}</span>` : ''}
          EGP ${parseFloat(p.price).toLocaleString()}
        </div>
      </div>
    </a>
  `).join('');

  // Render pagination from `links`
}
```

**URL param support for filters:**
```
/shop?category=Bedroom&min_price=1000&max_price=5000&sort=price_asc&page=2
```

---

### F4: Product Detail Page (`product.blade.php`)

**File:** `resources/views/product.blade.php`
**URL:** `/product/{id}`
**Route:** `Route::get('/product/{id}', fn($id) => view('product', ['id' => $id]));`

**Sections:**
1. Breadcrumb — `Home > {Category} > {Product Name}`
2. Image gallery — main image + thumbnails
3. Product info — name, rating, price, color swatches, size buttons, quantity, add to cart
4. Tabs — Description, Specifications, Reviews
5. Related products — `GET /api/products/{id}/related`

**Product data loading:**
```javascript
const productId = document.getElementById('product-id')?.value; // injected by Blade

(async () => {
  try {
    const { data: product } = await API.get(`/products/${productId}`);
    renderProduct(product);
    loadRelated(product.id);
    loadReviews(productId);
  } catch(e) {
    console.error('Failed to load product', e);
  }
})();

function renderProduct(p) {
  // Update page title
  document.title = `${p.name} — DecoHomz`;

  // Images
  const images = p.images || [];
  document.querySelector('.main-img img').src = images[0]?.url || '/img/placeholder.svg';

  // Thumbnails
  document.querySelector('.thumb-row').innerHTML = images.map((img, i) => `
    <div class="thumb ${i === 0 ? 'active' : ''}" onclick="setMainImage('${img.url}')">
      <img src="${img.url}" alt="">
    </div>
  `).join('');

  // Info
  document.querySelector('.prod-title').textContent = p.name;
  document.querySelector('.main-price').textContent = `EGP ${parseFloat(p.price).toLocaleString()}`;
  if (p.old_price) {
    document.querySelector('.old-price').textContent = `EGP ${parseFloat(p.old_price).toLocaleString()}`;
    document.querySelector('.sale-tag').textContent = `${Math.round((1 - p.price/p.old_price)*100)}% Off`;
  }

  // Colors
  if (p.colors) {
    document.querySelector('.color-row').innerHTML = p.colors.map((c, i) => `
      <div class="color-swatch ${i === 0 ? 'active' : ''}"
           style="background:${c}"
           onclick="selectColor(this, '${c}')"></div>
    `).join('');
  }

  // Add to cart
  document.querySelector('.btn-cart').onclick = () => {
    Cart.add({ id: p.id, name: p.name, price: p.price, quantity: qty, variant: selectedVariant });
  };
}
```

**Reviews tab:**
```javascript
async function loadReviews(productId) {
  try {
    const { reviews, stats } = await API.get(`/products/${productId}/reviews`);
    renderReviewStats(stats);
    document.querySelector('.tab[data-tab="reviews"]').textContent = `Reviews (${stats.count})`;
    document.querySelector('.reviews-list').innerHTML = reviews.map(r => `
      <div class="review-item">
        <div class="review-stars">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</div>
        <div class="review-user">${r.user?.name || 'Anonymous'}</div>
        <div class="review-comment">${r.comment || ''}</div>
      </div>
    `).join('');
  } catch(e) {}
}
```

**Add to Cart:**
```javascript
Cart.add = function(item) {
  const items = Cart.get();
  const existing = items.findIndex(i => i.id === item.id);
  if (existing > -1) items[existing].quantity += item.quantity;
  else items.push(item);
  Cart.set(items);
  showToast(`${item.name} added to cart!`);
};
```

---

### F5: Cart Page (`cart.blade.php`)

**File:** `resources/views/cart.blade.php`
**URL:** `/cart`
**Route:** `Route::get('/cart', fn() => view('cart'));`

**Fully dynamic — no static fallback items.**

```javascript
(async () => {
  Cart.updateBadge();
  await renderCartPage();
})();

async function renderCartPage() {
  let items = Cart.get();

  // If logged in, fetch from API
  if (Auth.token()) {
    try {
      const { data: serverCart } = await API.get('/cart');
      // Merge: API cart takes priority, but preserve local-only items
      items = serverCart.items || items;
    } catch(e) {}
  }

  if (items.length === 0) {
    document.getElementById('cart-page-container').innerHTML = `
      <div style="text-align:center; padding:60px; color:#888">
        <svg viewBox="0 0 24 24" width="64" stroke="#C4A882" fill="none" stroke-width="1.5">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
        <p style="margin-top:16px; font-size:16px; color:#2C1F14">Your cart is empty</p>
        <a href="/shop" class="btn-dark" style="display:inline-block; margin-top:16px">Browse Products</a>
      </div>
    `;
    return;
  }

  const subtotal = items.reduce((s, i) => s + (i.price * i.quantity), 0);

  document.getElementById('cart-page-container').innerHTML = items.map((item, i) => `
    <div class="cart-item">
      <div class="item-img">
        <img src="${item.image || '/img/placeholder.svg'}" alt="${item.name}">
      </div>
      <div class="item-info">
        <div class="item-name">${item.name}</div>
        <div class="item-meta">${item.variant || 'Standard'}</div>
        <div class="qty-ctrl">
          <button class="qty-btn" onclick="updateCartQty(${i}, -1)">−</button>
          <span class="qty-num">${item.quantity}</span>
          <button class="qty-btn" onclick="updateCartQty(${i}, 1)">+</button>
        </div>
      </div>
      <div class="item-price-col">
        <button class="remove-btn" onclick="removeCartItem(${i})">×</button>
        <div class="item-price">EGP ${(item.price * item.quantity).toLocaleString()}</div>
      </div>
    </div>
  `).join('');

  updateCartSummary(items);
}

async function updateCartQty(index, delta) {
  const items = Cart.get();
  items[index].quantity += delta;
  if (items[index].quantity < 1) items.splice(index, 1);

  if (Auth.token()) {
    const item = items[index];
    if (item) {
      await API.put(`/cart/items/${item.id}`, { quantity: item.quantity });
    }
  }

  Cart.set(items);
  renderCartPage();
}

async function removeCartItem(index) {
  const items = Cart.get();
  const item = items[index];
  items.splice(index, 1);

  if (Auth.token() && item) {
    await API.delete(`/cart/items/${item.id}`).catch(() => {});
  }

  Cart.set(items);
  renderCartPage();
}
```

**Coupon on cart page:**
```javascript
document.getElementById('apply-coupon').onclick = async () => {
  const code = document.getElementById('coupon-input').value.trim();
  if (!code) return;
  try {
    const data = await API.post('/cart/coupon', { code });
    showToast(`Coupon applied: ${data.coupon?.code}`);
    renderCartPage();
  } catch(e) {
    showToast(e.data?.message || 'Invalid coupon');
  }
};
```

---

### F6: Checkout Page (`checkout.blade.php`)

**File:** `resources/views/checkout.blade.php`
**URL:** `/checkout`
**Route:** `Route::get('/checkout', fn() => view('checkout'));`

**Behavior:**
- If cart is empty, redirect to `/cart`
- Load cart items from localStorage/API
- Two-step: Shipping → Payment (shown/hidden via JS, no page reload)
- Submit creates order via `POST /api/orders`

**JS flow:**
```javascript
(async () => {
  // Redirect if cart empty
  const items = Cart.get();
  if (items.length === 0 && !Auth.token()) {
    // fetch API cart
    const { data: cart } = await API.get('/cart');
    if (!cart.items?.length) { location.href = '/cart'; return; }
  }

  renderCheckoutSummary();
  loadUserAddresses();
})();

async function loadUserAddresses() {
  if (!Auth.token()) return;
  try {
    const { addresses } = await API.get('/addresses');
    if (addresses.length > 0) {
      // Auto-fill first address or show address picker
      const addr = addresses[0];
      document.querySelector('[name="first_name"]').value = addr.first_name;
      // etc.
    }
  } catch(e) {}
}

document.getElementById('checkout-form').onsubmit = async (e) => {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const payload = Object.fromEntries(formData.entries());

  // Determine payment method
  const paymentMethod = document.querySelector('input[name="payment"]:checked')?.value || 'cod';

  try {
    const data = await API.post('/orders', {
      // Contact
      email: payload.email,
      phone: payload.phone,
      // Shipping address fields
      shipping_address: {
        first_name: payload.first_name,
        last_name: payload.last_name,
        address_line_1: payload.address_line_1,
        address_line_2: payload.address_line_2,
        city: payload.city,
        state: payload.state,
        postal_code: payload.postal_code,
        country: payload.country || 'Egypt',
        phone: payload.phone,
      },
      payment_method: paymentMethod,
      notes: payload.notes || '',
    });

    Cart.set([]); // clear cart
    location.href = `/orders/confirmation/${data.order.id}`;
  } catch(e) {
    showToast(e.data?.message || 'Checkout failed. Please try again.');
  }
};
```

---

### F7: Order Confirmation (`orders/confirmation.blade.php`)

**File:** `resources/views/orders/confirmation.blade.php`
**URL:** `/orders/confirmation/{orderId}`
**Route:** `Route::get('/orders/confirmation/{orderId}', fn($orderId) => view('orders.confirmation', ['orderId' => $orderId]));`

```javascript
(async () => {
  const orderId = document.getElementById('order-id')?.value;
  try {
    const { order } = await API.get(`/orders/${orderId}`);
    renderConfirmation(order);
  } catch(e) {
    // Show error, still display basic info from localStorage if available
  }
})();

function renderConfirmation(order) {
  document.getElementById('confirm-order-id').innerHTML =
    `Order ID: <span>#${order.order_number}</span>`;

  document.getElementById('confirm-sub').textContent =
    `Thank you! We've received your order and will start processing it shortly.`;

  // Render items
  document.getElementById('confirm-items-container').innerHTML =
    order.items.map(item => `
      <div class="order-item">
        <div class="order-thumb">
          <img src="${item.image || '/img/placeholder.svg'}" alt="${item.name}">
          <div class="qty-badge">${item.quantity}</div>
        </div>
        <div class="order-item-info">
          <div class="order-item-name">${item.name}</div>
          <div class="order-item-variant">${item.variant || ''}</div>
        </div>
        <div class="order-item-price">EGP ${(item.price * item.quantity).toLocaleString()}</div>
      </div>
    `).join('');

  // Timeline — highlight current status
  const statusIndex = { pending: 0, processing: 1, shipped: 2, delivered: 3 };
  const current = statusIndex[order.status] ?? 0;
  document.querySelectorAll('.tl-step').forEach((el, i) => {
    if (i < current) el.classList.add('done');
    if (i === current) el.classList.add('active');
  });
}
```

---

### F8: Auth Page (`auth.blade.php`)

**File:** `resources/views/auth.blade.php`
**URL:** `/auth`
**Route:** `Route::get('/auth', fn() => view('auth'));`

**Login + Register in one page (tab switching via JS, same as original).**

```javascript
// Sign In tab
document.querySelector('#signin-form button.btn-submit').onclick = async () => {
  const email = document.querySelector('#signin-form [name="email"]').value;
  const password = document.querySelector('#signin-form [name="password"]').value;
  try {
    await Auth.login(email, password);
    showToast('Welcome back!');
    location.href = '/account';
  } catch(e) {
    showToast(e.data?.message || 'Invalid credentials');
  }
};

// Register tab
document.querySelector('#register-form button.btn-submit').onclick = async () => {
  const form = document.getElementById('register-form');
  const name = form.querySelector('[name="name"]').value;
  const email = form.querySelector('[name="email"]').value;
  const password = form.querySelector('[name="password"]').value;
  const phone = form.querySelector('[name="phone"]').value;

  try {
    await Auth.register({ name, email, password, phone });
    showToast('Account created!');
    location.href = '/account';
  } catch(e) {
    showToast(e.data?.message || 'Registration failed');
  }
};

// Logout
document.querySelector('.logout a')?.addEventListener('click', (e) => {
  e.preventDefault();
  Auth.logout();
  location.href = '/';
});
```

**If already logged in**, redirect to `/account`.

---

### F9: Account Page (`account.blade.php`)

**File:** `resources/views/account.blade.php`
**URL:** `/account`
**Route:** `Route::get('/account', fn() => view('account'))->middleware('auth:sanctum');`

**4 tabs: Overview, Orders, Profile, Addresses**

```javascript
(async () => {
  if (!Auth.token()) { location.href = '/auth'; return; }

  // Load user data
  try {
    const { user } = await API.get('/auth/user');
    renderUserInfo(user);
    loadOverview(user.id);
  } catch(e) {
    location.href = '/auth';
  }
})();

async function loadOverview(userId) {
  const { data: orders } = await API.get('/orders');
  renderRecentOrders(orders.slice(0, 3));
  document.querySelector('.stat-card:nth-child(1) .stat-num').textContent = orders.length;
  document.querySelector('.stat-card:nth-child(2) .stat-num').textContent =
    orders.filter(o => o.status === 'delivered').length;
}

async function renderOrdersTab() {
  const { data: orders } = await API.get('/orders');
  document.querySelector('#tab-orders .orders-list').innerHTML = orders.map(o => `
    <div class="order-card">
      <div>
        <div class="order-top">
          <span class="order-id">#${o.order_number}</span>
          <span class="order-status status-${o.status}">${o.status}</span>
          <span class="order-date">${new Date(o.created_at).toLocaleDateString()}</span>
        </div>
        <div class="order-items-preview">
          ${o.items?.map(item => `
            <div class="order-thumb">
              <img src="${item.image || '/img/placeholder.svg'}" alt="${item.name}">
            </div>
          `).join('') || ''}
        </div>
      </div>
      <div>
        <div class="order-total">EGP ${parseFloat(o.total).toLocaleString()}</div>
        <a class="order-action" href="/orders/${o.id}">Details →</a>
      </div>
    </div>
  `).join('');
}

async function renderAddressesTab() {
  const { addresses } = await API.get('/addresses');
  document.getElementById('addresses-container').innerHTML = addresses.map(addr => `
    <div class="address-card">
      <div class="address-label">${addr.label || 'Address'} ${addr.is_default ? '— Default' : ''}</div>
      <div class="address-details">
        ${addr.first_name} ${addr.last_name}<br>
        ${addr.address_line_1}${addr.address_line_2 ? ', ' + addr.address_line_2 : ''}<br>
        ${addr.city}, ${addr.state} ${addr.postal_code}<br>
        ${addr.phone}
      </div>
      <div class="address-actions">
        <button class="btn-edit" onclick="editAddress(${addr.id})">Edit</button>
        <button class="btn-edit" style="color:#c0392b" onclick="deleteAddress(${addr.id})">Remove</button>
      </div>
    </div>
  `).join('');
}

// Profile update
document.querySelector('#tab-profile .save-btn')?.addEventListener('click', async () => {
  const form = document.getElementById('tab-profile form');
  const payload = {
    name: form.querySelector('[name="name"]').value,
    phone: form.querySelector('[name="phone"]').value,
  };
  try {
    await API.put('/auth/profile', payload);
    showToast('Profile updated!');
  } catch(e) {
    showToast('Update failed');
  }
});
```

---

### F10: Contact Page (`contact.blade.php`)

**File:** `resources/views/contact.blade.php`
**URL:** `/contact`
**Route:** `Route::get('/contact', fn() => view('contact'));`

```javascript
document.getElementById('contact-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.target;
  const payload = {
    name: form.querySelector('[name="name"]').value,
    email: form.querySelector('[name="email"]').value,
    phone: form.querySelector('[name="phone"]')?.value || '',
    subject: form.querySelector('[name="subject"]')?.value || 'General Inquiry',
    message: form.querySelector('[name="message"]').value,
  };

  try {
    await API.post('/contact', payload);
    showToast('Message sent! We\'ll get back to you soon.');
    form.reset();
  } catch(e) {
    showToast(e.data?.message || 'Failed to send message');
  }
});
```

---

### F11: Static Pages (About, FAQ, Categories, Deals, New Arrivals)

**Files:** `about.blade.php`, `faq.blade.php`, `categories.blade.php`, `deals.blade.php`, `new-arrivals.blade.php`

**Approach:**
- Copy HTML structure as Blade `@extends`
- Categories, Deals, New Arrivals load data from API
- About and FAQ are fully static (just Blade template structure)

**Categories page:**
```javascript
// Load all categories with their images/counts
const { data: categories } = await API.get('/categories');
const { data: products } = await API.get('/products');
categories.forEach(cat => {
  const count = products.filter(p => p.category_id === cat.id).length;
  // render category card with count
});
```

**Deals page:** `GET /api/products?has_discount=true` or filter by `old_price != null`

**New Arrivals page:** `GET /api/products?sort=newest` or filter by created date

---

### F12: shared.js Updates

**File:** `public/js/shared.js`

**Changes:**
1. Replace `DH_STORAGE` with `Cart` from `api.js`
2. Remove inline product SVG rendering (use image URLs now)
3. Cart badge: `Cart.updateBadge()` instead of manual calculation
4. `addToCart()` → use `Cart.add()`
5. Keep `showToast()` and `initSearchBar()` as-is

---

### F13: products-data.js

**File:** `public/js/products-data.js`

**Replace with:** Just a `window.PRODUCTS = []` empty array or remove entirely. All product data now comes from the API. Keep the file to avoid 404 errors in old cached scripts, but make it a no-op:

```javascript
// Legacy file — products now loaded from API
const PRODUCTS = [];
```

---

### F14: Admin Layout (`admin.blade.php`)

**File:** `resources/views/admin/layouts/app.blade.php`

**Structure:**
- Dark sidebar with nav links (Dashboard, Products, Orders, Users, Reviews, Coupons, Contacts, Settings)
- Top header with user info + logout
- Main content area via `@yield('content')`

**Sidebar nav (admin-specific):**
```blade
<ul class="admin-nav">
  <li><a href="/admin/dashboard" class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Dashboard</a>
  </li>
  <li><a href="/admin/products"><svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/></svg>Products</a></li>
  <li><a href="/admin/orders"><svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/></svg>Orders</a></li>
  <li><a href="/admin/users"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>Users</a></li>
  <li><a href="/admin/reviews"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Reviews</a></li>
  <li><a href="/admin/coupons"><svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/></svg>Coupons</a></li>
  <li><a href="/admin/contacts"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>Contacts</a></li>
  <li><a href="/admin/settings"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>Settings</a></li>
</ul>
```

**Admin routes in web.php:**
```php
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'));
    Route::get('/products', fn() => view('admin.products.index'));
    Route::get('/products/{id}/edit', fn($id) => view('admin.products.edit', ['id' => $id]));
    Route::get('/orders', fn() => view('admin.orders.index'));
    Route::get('/orders/{id}', fn($id) => view('admin.orders.show', ['id' => $id]));
    Route::get('/users', fn() => view('admin.users.index'));
    Route::get('/reviews', fn() => view('admin.reviews.index'));
    Route::get('/coupons', fn() => view('admin.coupons.index'));
    Route::get('/contacts', fn() => view('admin.contacts.index'));
    Route::get('/settings', fn() => view('admin.settings'));
});
```

**Admin Dashboard page (`admin/dashboard.blade.php`):**
```javascript
(async () => {
  const { data: stats } = await API.get('/admin/dashboard');
  renderStats(stats);
  renderRecentOrders(stats.recent_orders);
  renderTopProducts(stats.top_products);
})();
```

---

## Build Order

| Step | File | Notes |
|------|------|-------|
| F1 | `layouts/app.blade.php` | Master layout — done first, all pages extend this |
| F2 | `layouts/admin.blade.php` | Admin layout |
| F3 | `home.blade.php` | Landing page |
| F4 | `shop.blade.php` | Product listing |
| F5 | `product.blade.php` | Product detail |
| F6 | `cart.blade.php` | Cart page |
| F7 | `checkout.blade.php` | Checkout |
| F8 | `orders/confirmation.blade.php` | Order success |
| F9 | `auth.blade.php` | Login + register |
| F10 | `account.blade.php` | User account |
| F11 | `contact.blade.php` | Contact form |
| F12 | Static pages | about, faq, categories, deals, new-arrivals |
| F13 | `admin/dashboard.blade.php` | Admin stats |
| F14 | Admin CRUD views | products, orders, users, reviews, coupons, contacts, settings |
| F15 | `web.php` routes | All route definitions |
| F16 | `public/js/api.js` | API client library |
| F17 | `public/js/shared.js` | Updated for API auth |
| F18 | `public/js/products-data.js` | Deprecate to empty array |

---

## CSS/JS Strategy

- **CSS files:** All 10 files in `public/css/` remain unchanged
- **JS files:** All 11 files in `public/js/` remain unchanged EXCEPT:
  - `products-data.js` → deprecated (empty `PRODUCTS` array)
  - `shared.js` → updated to use `Cart` from `api.js`
  - `home.js` → updated to fetch from API instead of `PRODUCTS` array
  - `shop.js` → updated to fetch from API with filter params
  - `product.js` → updated to fetch product detail + reviews from API
  - `cart.js` → updated to sync with API cart
  - `checkout.js` → updated to POST order to API
  - `account.js` → updated to use Auth + API
  - `signin.js` → updated to use `Auth.login()/register()`
  - `order-confirmation.js` → updated to fetch order from API

- **New file:** `public/js/api.js` — unified API client + Cart + Auth helpers

---

## Testing Checklist

After each page conversion, verify:
1. Page loads without errors (check browser console)
2. Navigation links work (Blade `@extends` + layout)
3. Dynamic data loads from API
4. Cart works (add, update quantity, remove, clear)
5. Auth flow works (login, register, logout)
6. Protected pages redirect to `/auth` when not logged in
7. Forms submit correctly and show success/error feedback
8. Mobile layout (CSS unchanged, just verify)
9. No broken images (use placeholder if API image missing)
10. Admin pages require admin role (middleware)

---

## Key API Endpoints Used

| Page | Endpoints |
|------|-----------|
| Home | `GET /api/categories`, `GET /api/products/featured` |
| Shop | `GET /api/products`, `GET /api/categories` |
| Product | `GET /api/products/{id}`, `GET /api/products/{id}/related`, `GET /api/products/{id}/reviews` |
| Cart | `GET /api/cart`, `POST /api/cart/items`, `PUT /api/cart/items/{id}`, `DELETE /api/cart/items/{id}`, `POST /api/cart/coupon`, `DELETE /api/cart/coupon` |
| Checkout | `POST /api/orders`, `GET /api/addresses` |
| Confirmation | `GET /api/orders/{id}` |
| Auth | `POST /api/auth/login`, `POST /api/auth/register`, `POST /api/auth/logout`, `GET /api/auth/user` |
| Account | `GET /api/orders`, `PUT /api/auth/profile`, `GET /api/addresses`, `POST /api/addresses`, `DELETE /api/addresses/{id}` |
| Contact | `POST /api/contact` |
| Admin Dashboard | `GET /api/admin/dashboard` |
| Categories | `GET /api/categories` |
| Deals | `GET /api/products` (filter `old_price != null`) |

---

## File Manifest

```
resources/views/
├── layouts/
│   ├── app.blade.php              [NEW]
│   └── admin/
│       └── app.blade.php          [NEW]
├── home.blade.php                 [NEW — from index.html]
├── shop.blade.php                 [NEW — from shop.html]
├── product.blade.php              [NEW — from product.html]
├── cart.blade.php                 [NEW — from cart.html]
├── checkout.blade.php             [NEW — from checkout.html]
├── orders/
│   └── confirmation.blade.php     [NEW — from order-confirmation.html]
├── account.blade.php              [NEW — from account.html]
├── auth.blade.php                 [NEW — from signin.html]
├── contact.blade.php              [NEW — from contact.html]
├── about.blade.php                [NEW — from about.html]
├── categories.blade.php           [NEW — from categories.html]
├── deals.blade.php                [NEW — from deals.html]
├── faq.blade.php                  [NEW — from faq.html]
├── new-arrivals.blade.php         [NEW — from new-arrivals.html]
└── admin/
    ├── dashboard.blade.php        [NEW]
    ├── products/
    │   ├── index.blade.php        [NEW]
    │   └── edit.blade.php         [NEW]
    ├── orders/
    │   ├── index.blade.php        [NEW]
    │   └── show.blade.php         [NEW]
    ├── users.blade.php            [NEW]
    ├── reviews.blade.php          [NEW]
    ├── coupons.blade.php          [NEW]
    ├── contacts.blade.php         [NEW]
    └── settings.blade.php         [NEW]

public/
├── css/
│   └── *.css                      [UNCHANGED — 10 files]
├── js/
│   ├── api.js                     [NEW — API client]
│   ├── shared.js                  [MODIFIED — use Cart from api.js]
│   ├── home.js                    [MODIFIED — API calls]
│   ├── shop.js                    [MODIFIED — API calls + filters]
│   ├── product.js                 [MODIFIED — API calls + reviews]
│   ├── cart.js                    [MODIFIED — API sync]
│   ├── checkout.js                [MODIFIED — POST order]
│   ├── account.js                 [MODIFIED — Auth + API]
│   ├── signin.js                  [MODIFIED — Auth.login/register]
│   ├── order-confirmation.js      [MODIFIED — fetch order]
│   └── products-data.js           [DEPRECATE — empty array]

routes/
└── web.php                        [MODIFIED — add all page routes]
```
