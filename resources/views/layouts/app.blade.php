<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
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
        <li><a href="/" class="{{ request()->is('/') ? 'active' : '' }}">{{ __('Home') }}</a></li>
        <li><a href="/shop" class="{{ request()->is('shop') ? 'active' : '' }}">{{ __('Shop') }}</a></li>
        <li><a href="/categories" class="{{ request()->is('categories') ? 'active' : '' }}">{{ __('Categories') }}</a></li>
        <li><a href="/new-arrivals" class="{{ request()->is('new-arrivals') ? 'active' : '' }}">{{ __('New Arrivals') }}</a></li>
        <li><a href="/deals" class="{{ request()->is('deals') ? 'active' : '' }}">{{ __('Deals') }}</a></li>
        <li><a href="/about" class="{{ request()->is('about') ? 'active' : '' }}">{{ __('About Us') }}</a></li>
        <li><a href="/contact" class="{{ request()->is('contact') ? 'active' : '' }}">{{ __('Contact Us') }}</a></li>
      </ul>
      <div class="nav-right">
        <!-- Language Switcher -->
        <div class="lang-switcher">
          @if(app()->getLocale() === 'ar')
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="lang-btn">English</a>
          @else
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}" class="lang-btn">العربية</a>
          @endif
        </div>

        <div class="search-container">
          <svg class="search-trigger" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.35-4.35" />
          </svg>
        </div>

        <a href="/cart" class="cart-trigger-nav" title="Shopping Cart" style="position:relative; cursor:pointer; display:flex; flex-direction:column; align-items:center; text-decoration:none; color:inherit;">
          <svg viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" style="width:24px; height:24px;">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
            <line x1="3" y1="6" x2="21" y2="6" />
            <path d="M16 10a4 4 0 0 1-8 0" />
          </svg>
          <span style="font-size:10px; font-weight:600; text-transform:uppercase; margin-top:2px;">{{ __('Cart') }}</span>
          <div class="badge badge-cart" style="display:none; top:-5px; right:-5px;">0</div>
        </a>

        @if(Auth::check())
          <a href="/account" class="user-trigger" title="Account" style="cursor:pointer; display:flex; flex-direction:column; align-items:center; text-decoration:none; color:inherit;">
            <svg viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" style="width:24px; height:24px;">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            <span style="font-size:10px; font-weight:600; text-transform:uppercase; margin-top:2px;">{{ __('Account') }}</span>
          </a>
        @else
          <a href="/auth" class="user-trigger" title="Account" style="cursor:pointer; display:flex; flex-direction:column; align-items:center; text-decoration:none; color:inherit;">
            <svg viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" style="width:24px; height:24px;">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            <span style="font-size:10px; font-weight:600; text-transform:uppercase; margin-top:2px;">{{ __('Login') }}</span>
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
