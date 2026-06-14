// File: public/js/api.js

const API = {
  base: '/api',

  // Getter reads live from localStorage every time (handles admin token set after page load)
  get token() { return localStorage.getItem('dh_token') || null; },

  setToken(token) {
    localStorage.setItem('dh_token', token);
    document.cookie = "dh_token=" + token + "; path=/; max-age=31536000; SameSite=Lax";
  },
  clearToken() {
    localStorage.removeItem('dh_token');
    document.cookie = "dh_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT";
  },

  // Session ID for guest cart tracking
  getSessionId() {
    let sid = localStorage.getItem('dh_session_id');
    if (!sid) {
      sid = 'guest_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
      localStorage.setItem('dh_session_id', sid);
    }
    // Always ensure cookie is set for server-side views
    document.cookie = "session_id=" + sid + "; path=/; max-age=31536000; SameSite=Lax";
    return sid;
  },

  headers(isFormData = false) {
    const h = {
      'Accept': 'application/json',
      'X-Session-ID': this.getSessionId(),
    };
    if (!isFormData) h['Content-Type'] = 'application/json';
    if (this.token) h['Authorization'] = `Bearer ${this.token}`;
    return h;
  },

  async get(path, options = {}) {
    let url = `${this.base}${path}`;
    if (options.params) {
      const q = new URLSearchParams();
      for (const [key, val] of Object.entries(options.params)) {
        if (val !== null && val !== undefined) q.append(key, val);
      }
      const qs = q.toString();
      if (qs) url += (url.includes('?') ? '&' : '?') + qs;
    }
    const res = await fetch(url, { headers: this.headers() });
    return this._handle(res);
  },
  async post(path, body = {}) {
    const isFormData = body instanceof FormData;
    const res = await fetch(`${this.base}${path}`, {
      method: 'POST',
      headers: this.headers(isFormData),
      body: isFormData ? body : JSON.stringify(body)
    });
    return this._handle(res);
  },
  async put(path, body = {}) {
    const isFormData = body instanceof FormData;
    const res = await fetch(`${this.base}${path}`, {
      method: 'PUT',
      headers: this.headers(isFormData),
      body: isFormData ? body : JSON.stringify(body)
    });
    return this._handle(res);
  },
  async patch(path, body = {}) {
    const isFormData = body instanceof FormData;
    const res = await fetch(`${this.base}${path}`, {
      method: 'PATCH',
      headers: this.headers(isFormData),
      body: isFormData ? body : JSON.stringify(body)
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
  delete(path) { return this.del(path); },

  async _handle(res) {
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw { status: res.status, data };
    return data;
  }
};

// ── Cart (API-first) ──────────────────────────────────────────────────────────
window.Cart = {
  // Fetch cart from API and return items array
  async fetch() {
    try {
      const res = await API.get('/cart');
      return res.cart?.items || [];
    } catch {
      return [];
    }
  },

  // Add item to cart via API
  async add(product) {
    try {
      await API.post('/cart/items', {
        product_id: product.id,
        quantity: product.quantity || 1,
        color_slug: product.color_slug || null,
      });
      showToast(`${product.name} added to cart!`);
      this.updateBadge();
    } catch (e) {
      showToast(e.data?.message || 'Failed to add item to cart', 'error');
    }
  },

  // Remove item from cart via API
  async remove(itemId) {
    try {
      await API.del('/cart/items/' + itemId);
      this.updateBadge();
    } catch (e) {
      showToast(e.data?.message || 'Failed to remove item', 'error');
    }
  },

  // Update quantity via API
  async updateQty(itemId, quantity) {
    try {
      await API.put('/cart/items/' + itemId, { quantity });
      this.updateBadge();
    } catch (e) {
      showToast(e.data?.message || 'Failed to update quantity', 'error');
    }
  },

  // Clear cart via API
  async clear() {
    try {
      await API.del('/cart');
      this.updateBadge();
    } catch (e) {
      showToast(e.data?.message || 'Failed to clear cart', 'error');
    }
  },

  // Update the cart badge in the nav
  updateBadge() {
    API.get('/cart').then(res => {
      const count = res.cart?.items_count || 0;
      const badge = document.querySelector('.badge-cart');
      if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
      }
    }).catch(() => {});
  },

  // Redirect to cart page
  count() {
    return 0; // Always use API count via updateBadge() for accurate number
  }
};

// ── Auth helper ───────────────────────────────────────────────────────────────
const Auth = {
  token: () => localStorage.getItem('dh_token'),
  user: () => JSON.parse(localStorage.getItem('dh_user') || 'null'),

  async login(email, password) {
    const data = await API.post('/auth/login', { email, password });
    API.setToken(data.token);
    localStorage.setItem('dh_user', JSON.stringify(data.user));
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
    location.href = '/auth';
  }
};
