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
  key: 'dh_cart',

  get() {
    return JSON.parse(localStorage.getItem(this.key) || '[]');
  },

  set(items) {
    localStorage.setItem(this.key, JSON.stringify(items));
    this.updateBadge();
  },

  add(item) {
    const items = this.get();
    const existing = items.findIndex(i => i.id === item.id && i.variant === item.variant);
    if (existing > -1) {
      items[existing].quantity += item.quantity || 1;
    } else {
      items.push({ ...item, quantity: item.quantity || 1 });
    }
    this.set(items);
    showToast(`${item.name} added to cart!`);
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
    await this._mergeCart();
    return data.user;
  },

  async register(payload) {
    const data = await API.post('/auth/register', payload);
    API.setToken(data.token);
    localStorage.setItem('dh_user', JSON.stringify(data.user));
    return data.user;
  },

  async logout() {
    if (API.token) API.post('/auth/logout').catch(() => {});
    API.clearToken();
    localStorage.removeItem('dh_user');
    Cart.set([]);
    location.href = '/auth';
  },

  async _mergeCart() {
    if (!API.token) return;
    const localCart = Cart.get();
    for (const item of localCart) {
      try {
        await API.post('/cart/items', {
          product_id: item.id,
          quantity: item.quantity,
          variant: item.variant || 'Standard'
        });
      } catch(e) {}
    }
    Cart.set([]);
  }
};
