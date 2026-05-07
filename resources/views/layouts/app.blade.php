<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'DecoHomz — Premium Furniture')</title>
  <meta name="description" content="@yield('meta_description', 'DecoHomz offers premium, handcrafted furniture for every space. Sofas, beds, dining sets and more — delivered across Egypt.')">
  <meta property="og:title" content="@yield('og_title', 'DecoHomz — Premium Furniture')">
  <meta property="og:description" content="@yield('og_description', 'Discover premium, handcrafted furniture for every space. Shop sofas, beds, dining sets and more across Egypt.')">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="@yield('og_image', '/img/og-default.png')">
  <meta name="twitter:card" content="summary_large_image">
  <link rel="canonical" href="{{ url()->current() }}">
  <link rel="stylesheet" href="/css/shared.css">
  @yield('extra_css')
</head>

<body>

  <div class="topbar">Free Delivery on Orders Above EGP 2,000 — Shop Now</div>

  <nav>
    <div class="nav-inner">
      <a href="/" class="logo">Deco<span>Homz</span></a>
      <ul class="nav-links">
        <li><a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Home</a></li>
        <li><a href="/shop" class="{{ request()->is('shop') ? 'active' : '' }}">Shop</a></li>
        <li><a href="/categories" class="{{ request()->is('categories') ? 'active' : '' }}">Categories</a></li>
        <li><a href="/new-arrivals" class="{{ request()->is('new-arrivals') ? 'active' : '' }}">New Arrivals</a></li>
        <li><a href="/deals" class="{{ request()->is('deals') ? 'active' : '' }}">Deals</a></li>
        <li><a href="/about" class="{{ request()->is('about') ? 'active' : '' }}">About</a></li>
        <li><a href="/contact" class="{{ request()->is('contact') ? 'active' : '' }}">Contact</a></li>
      </ul>
      <div class="nav-right">
        <div class="search-container">
          <svg class="search-trigger" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.35-4.35" />
          </svg>
        </div>
        <a href="/cart" class="cart-trigger" title="Shopping Cart" style="position:relative; cursor:pointer; display:flex; align-items:center;">
          <svg viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
            <line x1="3" y1="6" x2="21" y2="6" />
            <path d="M16 10a4 4 0 0 1-8 0" />
          </svg>
          <div class="badge badge-cart" style="display:none">0</div>
        </a>
        @if(Auth::check())
          <a href="/account" class="user-trigger" title="Account" style="cursor:pointer; display:flex; align-items:center;">
            <svg viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
          </a>
        @else
          <a href="/auth" class="user-trigger" title="Account" style="cursor:pointer; display:flex; align-items:center;">
            <svg viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
          </a>
        @endif
      </div>
    </div>
  </nav>

  <main>
    @yield('content')
  </main>

  <footer>
    <div class="footer-grid">
      <div>
        <div class="f-logo">Deco<span>Homz</span></div>
        <p class="f-desc">Premium furniture for every space. Crafted for comfort, designed for life.</p>
      </div>
      <div>
        <div class="f-head">Shop</div>
        <ul class="f-links">
          <li><a href="/shop">Living Room</a></li>
          <li><a href="/shop">Bedroom</a></li>
          <li><a href="/shop">Dining</a></li>
          <li><a href="/shop">Outdoor</a></li>
        </ul>
      </div>
      <div>
        <div class="f-head">Help</div>
        <ul class="f-links">
          <li><a href="/faq">FAQ</a></li>
          <li><a href="/contact">Shipping</a></li>
          <li><a href="/contact">Returns</a></li>
          <li><a href="/contact">Contact</a></li>
        </ul>
      </div>
      <div>
        <div class="f-head">Company</div>
        <ul class="f-links">
          <li><a href="/about">About Us</a></li>
          <li><a href="/faq">Help Center</a></li>
          <li><a href="/account">My Account</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 DecoHomz. All rights reserved.</span>
      <span>Privacy · Terms</span>
    </div>
  </footer>

  <script src="/js/shared.js"></script>
  <script src="/js/api.js"></script>
  @yield('extra_js')
</body>

</html>
