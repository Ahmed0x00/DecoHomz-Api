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

  {{-- Google Fonts — Inter --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/css/animations.css">
  <link rel="stylesheet" href="/css/shared.css">
  @yield('extra_css')
</head>

<body>

  {{-- Scroll Progress Bar --}}
  <div class="scroll-progress" id="scroll-progress"></div>

  {{-- Topbar --}}
  <div class="topbar">{{ __('Free Delivery on Orders Above EGP 2,000 — Shop Now') }}</div>

  {{-- Navigation --}}
  <nav id="main-nav">
    <div class="nav-inner">

      {{-- Hamburger (mobile) --}}
      <button class="hamburger" id="hamburger-btn" aria-label="{{ __('Open menu') }}" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
      </button>

      {{-- Logo --}}
      <a href="/" class="logo">Deco<span>Homz</span></a>

      {{-- Desktop Nav Links --}}
      <ul class="nav-links">
        <li><a href="/" class="{{ request()->is('/') ? 'active' : '' }}">{{ __('Home') }}</a></li>
        <li><a href="/shop" class="{{ request()->is('shop') ? 'active' : '' }}">{{ __('Shop') }}</a></li>
        <li><a href="/categories" class="{{ request()->is('categories') ? 'active' : '' }}">{{ __('Categories') }}</a></li>
        <li><a href="/new-arrivals" class="{{ request()->is('new-arrivals') ? 'active' : '' }}">{{ __('New Arrivals') }}</a></li>
        <li><a href="/deals" class="{{ request()->is('deals') ? 'active' : '' }}">{{ __('Deals') }}</a></li>
        <li><a href="/about" class="{{ request()->is('about') ? 'active' : '' }}">{{ __('About Us') }}</a></li>
        <li><a href="/contact" class="{{ request()->is('contact') ? 'active' : '' }}">{{ __('Contact Us') }}</a></li>
      </ul>

      {{-- Right Actions --}}
      <div class="nav-right">
        {{-- Language Switcher --}}
        <div class="lang-switcher">
          @if(app()->getLocale() === 'ar')
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="lang-btn">English</a>
          @else
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}" class="lang-btn">العربية</a>
          @endif
        </div>

        {{-- Search --}}
        <div class="search-container">
          <svg class="search-trigger" id="search-trigger" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.35-4.35" />
          </svg>
        </div>

        {{-- Cart --}}
        <a href="/cart" class="nav-icon-btn cart-trigger-nav" title="{{ __('Shopping Cart') }}">
          <svg viewBox="0 0 24 24">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
            <line x1="3" y1="6" x2="21" y2="6" />
            <path d="M16 10a4 4 0 0 1-8 0" />
          </svg>
          <span class="icon-label">{{ __('Cart') }}</span>
          <div class="badge badge-cart" style="display:none">0</div>
        </a>

        {{-- Account / Login (JS-driven since we use API tokens, not sessions) --}}
        <a href="/auth" class="nav-icon-btn user-trigger" id="nav-auth-link" title="{{ __('Login') }}">
          <svg viewBox="0 0 24 24">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
          <span class="icon-label" id="nav-auth-label">{{ __('Login') }}</span>
        </a>
      </div>
    </div>
  </nav>

  {{-- Mobile Nav Drawer --}}
  <div class="mobile-nav-overlay" id="mobile-nav-overlay"></div>
  <div class="mobile-nav-drawer" id="mobile-nav-drawer">
    <div class="mobile-nav-header">
      <a href="/" class="logo">Deco<span>Homz</span></a>
      <button class="mobile-nav-close" id="mobile-nav-close" aria-label="{{ __('Close menu') }}">&times;</button>
    </div>
    <div class="mobile-nav-links">
      <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">{{ __('Home') }}</a>
      <a href="/shop" class="{{ request()->is('shop') ? 'active' : '' }}">{{ __('Shop') }}</a>
      <a href="/categories" class="{{ request()->is('categories') ? 'active' : '' }}">{{ __('Categories') }}</a>
      <a href="/new-arrivals" class="{{ request()->is('new-arrivals') ? 'active' : '' }}">{{ __('New Arrivals') }}</a>
      <a href="/deals" class="{{ request()->is('deals') ? 'active' : '' }}">{{ __('Deals') }}</a>
      <a href="/about" class="{{ request()->is('about') ? 'active' : '' }}">{{ __('About Us') }}</a>
      <a href="/contact" class="{{ request()->is('contact') ? 'active' : '' }}">{{ __('Contact Us') }}</a>
    </div>
    <div class="mobile-nav-bottom">
      @if(app()->getLocale() === 'ar')
        <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="lang-btn" style="display:block;text-align:center;">English</a>
      @else
        <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}" class="lang-btn" style="display:block;text-align:center;">العربية</a>
      @endif
    </div>
  </div>

  {{-- Search Overlay --}}
  <div class="search-overlay" id="search-overlay">
    <div class="search-box">
      <svg viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="1.5" style="width:24px;height:24px;margin-inline-start:12px;color:var(--color-text-faint);flex-shrink:0;">
        <circle cx="11" cy="11" r="8" />
        <path d="m21 21-4.35-4.35" />
      </svg>
      <input type="text" id="search-input" placeholder="{{ __('Search for furniture, decor, and more...') }}" autocomplete="off">
      <button class="search-close-btn" id="search-close-btn">&times;</button>
    </div>
  </div>

  {{-- Main Content --}}
  <main>
    @yield('content')
  </main>

  {{-- Footer --}}
  <footer>
    <div class="footer-grid">
      <div>
        <div class="f-logo">Deco<span>Homz</span></div>
        <p class="f-desc">{{ __('Premium furniture for every space. Crafted for comfort, designed for life.') }}</p>
        <div class="f-social">
          <a href="#" aria-label="Facebook">
            <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
          </a>
          <a href="#" aria-label="Instagram">
            <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          </a>
          <a href="#" aria-label="Twitter">
            <svg viewBox="0 0 24 24"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>
          </a>
          <a href="#" aria-label="TikTok">
            <svg viewBox="0 0 24 24"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>
          </a>
        </div>
        <div class="f-newsletter">
          <p>{{ __('Get updates on new arrivals and deals') }}</p>
          <form class="newsletter-form" onsubmit="event.preventDefault(); showToast('{{ __("Subscribed! Thank you.") }}', 'success');">
            <input type="email" placeholder="{{ __('Your email address') }}" required>
            <button type="submit">{{ __('Subscribe') }}</button>
          </form>
        </div>
      </div>
      <div>
        <div class="f-head">{{ __('Shop') }}</div>
        <ul class="f-links">
          <li><a href="/shop?category=Living+Room">{{ __('Living Room') }}</a></li>
          <li><a href="/shop?category=Bedroom">{{ __('Bedroom') }}</a></li>
          <li><a href="/shop?category=Dining">{{ __('Dining') }}</a></li>
          <li><a href="/shop?category=Outdoor">{{ __('Outdoor') }}</a></li>
          <li><a href="/new-arrivals">{{ __('New Arrivals') }}</a></li>
        </ul>
      </div>
      <div>
        <div class="f-head">{{ __('Help') }}</div>
        <ul class="f-links">
          <li><a href="/faq">{{ __('FAQ') }}</a></li>
          <li><a href="/contact">{{ __('Shipping & Delivery') }}</a></li>
          <li><a href="/contact">{{ __('Returns & Exchanges') }}</a></li>
          <li><a href="/contact">{{ __('Contact Us') }}</a></li>
        </ul>
      </div>
      <div>
        <div class="f-head">{{ __('Company') }}</div>
        <ul class="f-links">
          <li><a href="/about">{{ __('About Us') }}</a></li>
          <li><a href="/faq">{{ __('Help Center') }}</a></li>
          <li><a href="/account">{{ __('My Account') }}</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© {{ date('Y') }} DecoHomz. {{ __('All rights reserved.') }}</span>
      <span><a href="#">{{ __('Privacy') }}</a> · <a href="#">{{ __('Terms') }}</a></span>
    </div>
  </footer>

  {{-- Scroll to Top --}}
  <button class="scroll-top-btn" id="scroll-top-btn" aria-label="{{ __('Scroll to top') }}">
    <svg viewBox="0 0 24 24">
      <path d="M18 15l-6-6-6 6" />
    </svg>
  </button>

  <script src="/js/shared.js"></script>
  <script src="/js/api.js"></script>
  <script>
  // Sync localStorage to cookies for server-side auth (fixes 403 for existing sessions)
  (function() {
    function getCookie(name) {
      var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
      return match ? match[2] : null;
    }
    var token = localStorage.getItem('dh_token');
    if (token) {
      if (getCookie('dh_token') !== token) {
        document.cookie = "dh_token=" + token + "; path=/; max-age=31536000; SameSite=Lax";
      }
    } else {
      if (getCookie('dh_token') !== null) {
        document.cookie = "dh_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT";
      }
    }
    var sid = localStorage.getItem('dh_session_id');
    if (sid) {
      if (getCookie('session_id') !== sid) {
        document.cookie = "session_id=" + sid + "; path=/; max-age=31536000; SameSite=Lax";
      }
    } else {
      if (getCookie('session_id') !== null) {
        document.cookie = "session_id=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT";
      }
    }
  })();

  // Update nav auth link based on token presence
  (function() {
    var link = document.getElementById('nav-auth-link');
    var label = document.getElementById('nav-auth-label');
    if (link && localStorage.getItem('dh_token')) {
      var user = JSON.parse(localStorage.getItem('dh_user') || 'null');
      if (user && (user.role === 'admin' || user.role === 'support')) {
        link.href = '/admin/dashboard';
        label.textContent = "{{ __('Admin') }}";
      } else {
        link.href = '/account';
        label.textContent = "{{ __('Account') }}";
      }
      link.title = label.textContent;
    }
  })();
  </script>
  @yield('extra_js')
</body>

</html>
