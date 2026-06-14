@extends('layouts.app')

@section('title', 'DecoHomz — Premium Furniture')

@section('extra_css')
<link rel="stylesheet" href="/css/home.css">
@endsection

@section('content')

{{-- ═══ HERO ═══ --}}
<div class="hero">
  <div class="hero-text">
    <div class="hero-label">{{ __('New Collection 2026') }}</div>
    <h1 class="hero-h1">{!! __('Design Your<br>Space with Style') !!}</h1>
    <p class="hero-sub">{{ __('Premium furniture crafted for comfort and elegance. Transform your home with pieces that tell your story.') }}</p>
    <div class="hero-btns">
      <button class="btn-dark" onclick="location.href='/shop'">
        <span>{{ __('Shop Now') }}</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </button>
      <button class="btn-outline" onclick="location.href='/categories'">{{ __('Explore Collections') }}</button>
    </div>
    <div class="hero-stats">
      <div class="hero-stat">
        <div class="hero-stat-num">50K+</div>
        <div class="hero-stat-label">{{ __('Happy Customers') }}</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-num">400+</div>
        <div class="hero-stat-label">{{ __('Curated Products') }}</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-num">5★</div>
        <div class="hero-stat-label">{{ __('Top Rated') }}</div>
      </div>
    </div>
  </div>
  <div class="hero-img">
    <div class="hero-float hero-float-1"></div>
    <div class="hero-float hero-float-2"></div>
    <svg viewBox="0 0 320 200" fill="none" xmlns="http://www.w3.org/2000/svg">
      <rect x="30" y="110" width="260" height="60" rx="10" fill="#8B6A48" />
      <rect x="30" y="90" width="44" height="58" rx="8" fill="#A07858" />
      <rect x="246" y="90" width="44" height="58" rx="8" fill="#A07858" />
      <rect x="74" y="102" width="72" height="68" rx="5" fill="#C4A882" />
      <rect x="174" y="102" width="72" height="68" rx="5" fill="#C4A882" />
      <rect x="48" y="170" width="18" height="20" rx="3" fill="#6B4832" />
      <rect x="254" y="170" width="18" height="20" rx="3" fill="#6B4832" />
      <ellipse cx="160" cy="195" rx="130" ry="6" fill="#C4A882" opacity="0.3" />
      <rect x="120" y="60" width="80" height="55" rx="30" fill="#D4B896" opacity="0.6" />
      <circle cx="220" cy="60" r="22" fill="#C4A882" opacity="0.7" />
      <rect x="217" y="30" width="3" height="30" rx="2" fill="#8B7060" />
      <ellipse cx="218" cy="28" rx="10" ry="6" fill="#5C7A40" opacity="0.75" />
    </svg>
  </div>
</div>

{{-- ═══ TRUST BADGES ═══ --}}
<div class="trust-strip">
  <div class="trust-grid">
    <div class="trust-item animate-on-scroll">
      <div class="trust-icon">
        <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      </div>
      <div class="trust-text">
        <h4>{{ __('Free Delivery') }}</h4>
        <p>{{ __('On orders above EGP 2,000') }}</p>
      </div>
    </div>
    <div class="trust-item animate-on-scroll stagger-2">
      <div class="trust-icon">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div class="trust-text">
        <h4>{{ __('5-Year Warranty') }}</h4>
        <p>{{ __('On all furniture') }}</p>
      </div>
    </div>
    <div class="trust-item animate-on-scroll stagger-4">
      <div class="trust-icon">
        <svg viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>
      </div>
      <div class="trust-text">
        <h4>{{ __('Easy Returns') }}</h4>
        <p>{{ __('14-day hassle-free returns') }}</p>
      </div>
    </div>
    <div class="trust-item animate-on-scroll stagger-6">
      <div class="trust-icon">
        <svg viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      </div>
      <div class="trust-text">
        <h4>{{ __('Secure Payment') }}</h4>
        <p>{{ __('100% secure checkout') }}</p>
      </div>
    </div>
  </div>
</div>

{{-- ═══ CATEGORIES ═══ --}}
<div class="cats">
  <div class="sec-row animate-on-scroll">
    <div class="sec-title">{{ __('Shop by Category') }}</div>
    <a href="/shop" class="sec-link">{{ __('View All') }} →</a>
  </div>
  <div class="cat-row" id="cat-row">
    {{-- Loaded dynamically via JS --}}
  </div>
</div>

{{-- ═══ BEST SELLERS ═══ --}}
<div class="products">
  <div class="sec-row animate-on-scroll">
    <div class="sec-title">{{ __('Best Sellers') }}</div>
    <a href="/shop" class="sec-link">{{ __('View All') }} →</a>
  </div>
  <div class="prod-grid" id="prod-grid">
    {{-- Loading skeleton --}}
    @for($i = 0; $i < 4; $i++)
    <div class="skeleton-card">
      <div class="skeleton-img skeleton"></div>
      <div class="skeleton-body">
        <div class="skeleton-text narrow skeleton"></div>
        <div class="skeleton-text wide skeleton"></div>
        <div class="skeleton-text medium skeleton"></div>
      </div>
    </div>
    @endfor
  </div>
</div>

{{-- ═══ BANNER ═══ --}}
<div class="banner animate-on-scroll">
  <div class="ban-left">
    <div class="ban-tag">{{ __('Limited Offer') }}</div>
    <div class="ban-h">{!! __('Up to 30% Off<br>Living Room Sets') !!}</div>
    <div class="ban-sub">{{ __('Refresh your home this season with our curated collection of premium living room furniture.') }}</div>
    <button class="btn-gold" onclick="location.href='/shop'">
      {{ __('Shop the Sale') }}
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </button>
  </div>
  <div class="ban-right">
    <svg viewBox="0 0 220 160" fill="none" width="220">
      <rect x="20" y="80" width="180" height="52" rx="8" fill="#8B6A48" opacity="0.7" />
      <rect x="20" y="62" width="38" height="58" rx="6" fill="#A07858" opacity="0.7" />
      <rect x="162" y="62" width="38" height="58" rx="6" fill="#A07858" opacity="0.7" />
      <rect x="58" y="72" width="46" height="60" rx="4" fill="#C4A882" opacity="0.6" />
      <rect x="116" y="72" width="46" height="60" rx="4" fill="#C4A882" opacity="0.6" />
      <rect x="28" y="132" width="14" height="18" rx="3" fill="#5C3D25" opacity="0.8" />
      <rect x="178" y="132" width="14" height="18" rx="3" fill="#5C3D25" opacity="0.8" />
    </svg>
  </div>
</div>

{{-- ═══ TESTIMONIALS ═══ --}}
<div class="testimonials">
  <div class="testimonials-header animate-on-scroll">
    <div class="sec-label">{{ __('What Our Customers Say') }}</div>
    <div class="sec-title">{{ __('Loved by Thousands') }}</div>
  </div>
  <div class="testimonials-grid">
    <div class="testimonial-card animate-on-scroll">
      <div class="testimonial-stars">★★★★★</div>
      <div class="testimonial-text">"{{ __('The quality of the Luna sofa exceeded my expectations. Beautiful craftsmanship and incredibly comfortable. DecoHomz is now my go-to for furniture.') }}"</div>
      <div class="testimonial-author">
        <div class="testimonial-avatar">S</div>
        <div>
          <div class="testimonial-name">Sara Ahmed</div>
          <div class="testimonial-role">{{ __('Cairo, Egypt') }}</div>
        </div>
      </div>
    </div>
    <div class="testimonial-card animate-on-scroll stagger-2">
      <div class="testimonial-stars">★★★★★</div>
      <div class="testimonial-text">"{{ __('From browsing to delivery, everything was seamless. The dining set arrived perfectly assembled. Five stars for service and quality!') }}"</div>
      <div class="testimonial-author">
        <div class="testimonial-avatar">M</div>
        <div>
          <div class="testimonial-name">Mohamed Karim</div>
          <div class="testimonial-role">{{ __('Alexandria, Egypt') }}</div>
        </div>
      </div>
    </div>
    <div class="testimonial-card animate-on-scroll stagger-4">
      <div class="testimonial-stars">★★★★★</div>
      <div class="testimonial-text">"{{ __('I redesigned my entire bedroom with DecoHomz pieces. The Aria bed frame is stunning and the warranty gives me peace of mind.') }}"</div>
      <div class="testimonial-author">
        <div class="testimonial-avatar">N</div>
        <div>
          <div class="testimonial-name">Nadia Hassan</div>
          <div class="testimonial-role">{{ __('Giza, Egypt') }}</div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  // Category SVG fallbacks keyed by category name
  const fallbackSvgs = {
    'Living Room': '<svg viewBox="0 0 48 48" fill="none"><rect x="4" y="24" width="40" height="16" rx="3" fill="#8B6A48"/><rect x="4" y="18" width="10" height="18" rx="2" fill="#A07858"/><rect x="34" y="18" width="10" height="18" rx="2" fill="#A07858"/><rect x="14" y="22" width="20" height="18" rx="2" fill="#C4A882" opacity="0.6"/></svg>',
    'Bedroom': '<svg viewBox="0 0 48 48" fill="none"><rect x="6" y="14" width="36" height="24" rx="2" fill="#8B6A48"/><rect x="6" y="26" width="36" height="12" rx="2" fill="#C4A882"/><rect x="10" y="38" width="4" height="6" rx="1" fill="#6B4832"/><rect x="34" y="38" width="4" height="6" rx="1" fill="#6B4832"/><rect x="10" y="18" width="12" height="6" rx="1" fill="#fff" opacity="0.3"/><rect x="26" y="18" width="12" height="6" rx="1" fill="#fff" opacity="0.3"/></svg>',
    'Dining': '<svg viewBox="0 0 48 48" fill="none"><rect x="8" y="24" width="32" height="6" rx="2" fill="#8B6A48"/><rect x="12" y="30" width="4" height="12" rx="1" fill="#6B4832"/><rect x="32" y="30" width="4" height="12" rx="1" fill="#6B4832"/><rect x="16" y="10" width="16" height="14" rx="2" fill="#A07858" opacity="0.8"/></svg>',
    'Office': '<svg viewBox="0 0 48 48" fill="none"><rect x="6" y="12" width="36" height="24" rx="2" fill="#8B6A48"/><rect x="10" y="16" width="28" height="6" rx="1" fill="#C4A882" opacity="0.4"/><rect x="10" y="24" width="28" height="6" rx="1" fill="#C4A882" opacity="0.4"/><rect x="16" y="36" width="16" height="4" rx="1" fill="#6B4832"/></svg>',
    'Outdoor': '<svg viewBox="0 0 48 48" fill="none"><rect x="12" y="20" width="24" height="20" rx="4" fill="#8B6A48"/><rect x="8" y="18" width="32" height="4" rx="2" fill="#A07858"/><rect x="16" y="40" width="4" height="6" rx="1" fill="#6B4832"/><rect x="28" y="40" width="4" height="6" rx="1" fill="#6B4832"/><circle cx="24" cy="12" r="6" fill="#B8860B" opacity="0.5"/></svg>',
    'Decor': '<svg viewBox="0 0 48 48" fill="none"><rect x="18" y="30" width="12" height="14" rx="2" fill="#6B4832"/><circle cx="24" cy="18" r="12" fill="#C4A882" opacity="0.6"/><path d="M24 6v6" stroke="#8B6A48" stroke-width="2"/></svg>',
  };

  function getCategorySvg(name) {
    return fallbackSvgs[name] || '<svg viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="20" fill="#C4A882"/></svg>';
  }

  function renderCategories(categories) {
    const row = document.getElementById('cat-row');
    if (!row) return;
    row.innerHTML = categories.map(function(cat, i) {
      var catContent = cat.url 
        ? '<img src="' + cat.url + '" alt="' + esc(cat.name) + '">' 
        : getCategorySvg(cat.name);
      return '<a href="/shop?category=' + encodeURIComponent(cat.slug || cat.name) + '" class="cat-item animate-on-scroll stagger-' + (i + 1) + '">' +
        '<div class="cat-box">' + catContent + '</div>' +
        '<div class="cat-name">' + esc(cat.name) + '</div>' +
        '</a>';
    }).join('');

    // Re-observe new elements
    initNewScrollElements();
  }

  function renderBestSellers(products) {
    const grid = document.getElementById('prod-grid');
    if (!grid) return;
    if (!products || products.length === 0) {
      grid.innerHTML = '<p style="padding:40px;color:var(--color-text-faint);text-align:center;grid-column:1/-1">' + "{{ __('No products found.') }}" + '</p>';
      return;
    }
    
    grid.innerHTML = products.map(function(p) {
      const imgUrl = (p.primary_image && p.primary_image.thumbnail_url) ? p.primary_image.thumbnail_url : ((p.primary_image && p.primary_image.url) ? p.primary_image.url : '/img/placeholder.svg');
      const stars = p.stars || 5;
      const starsStr = '★'.repeat(stars) + '☆'.repeat(5 - stars);
      const badgeHtml = p.badge
        ? '<div class="prod-badge" style="background:' + (p.badge_color || 'var(--color-accent)') + '">' + esc(p.badge) + '</div>'
        : '';
      const price = p.price ? parseFloat(p.price).toLocaleString() : '0';
      const oldPriceHtml = p.old_price
        ? ' <s>EGP ' + parseFloat(p.old_price).toLocaleString() + '</s>'
        : '';
      const catName = p.category ? esc(p.category.name) : '';
      
      return '<div class="prod-card" data-id="' + p.id + '" onclick="location.href=\'/product/' + (p.slug || p.id) + '\'">' +
          '<div class="prod-img">' +
            badgeHtml +
            '<img src="' + imgUrl + '" alt="' + esc(p.name) + '" onerror="this.src=\'/img/placeholder.svg\'">' +
          '</div>' +
          '<div class="prod-info">' +
            '<div class="stars">' + starsStr + '</div>' +
            '<div class="prod-name">' + esc(p.name) + '</div>' +
            '<div class="prod-cat">' + catName + '</div>' +
            '<div class="prod-price">EGP ' + price + oldPriceHtml + '</div>' +
            '<button class="btn-add-cart" onclick="event.stopPropagation(); homeAddToCart(' + p.id + ', \'' + esc(p.name) + '\', ' + (p.price || 0) + ')">' + "{{ __('Add to Cart') }}" + '</button>' +
          '</div>' +
        '</div>';
    }).join('');
  }

  window.homeAddToCart = function(id, name, price) {
    if (window.Cart && typeof Cart.add === 'function') {
      Cart.add({ id: id, name: name, price: price, quantity: 1, variant: 'Standard' });
      Cart.updateBadge();
      showToast("{{ __('Added to cart!') }}", 'success');
    }
  };

  function initNewScrollElements() {
    if ('IntersectionObserver' in window) {
      var elements = document.querySelectorAll('.animate-on-scroll:not(.is-visible)');
      var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

      elements.forEach(function(el) {
        observer.observe(el);
      });
    }
  }

  (async function() {
    Cart.updateBadge();

    // Categories
    try {
      const res = await API.get('/categories');
      renderCategories(res.categories || []);
    } catch(e) {
      console.error("Failed to load categories:", e);
    }

    // Featured products
    try {
      let res2 = await API.get('/products/featured');
      let products = res2.products || [];
      
      // Fallback to normal products if no featured products exist (so the home page is never empty)
      if (products.length === 0) {
          res2 = await API.get('/products?per_page=8');
          products = res2.products || [];
      }
      
      renderBestSellers(products);
    } catch(e) {
      console.error("Failed to load products:", e);
      const grid = document.getElementById('prod-grid');
      if (grid) grid.innerHTML = '<p style="padding:40px;color:var(--color-error);text-align:center;grid-column:1/-1">' + "{{ __('Failed to load products.') }}" + '</p>';
    }
  })();
})();
</script>
@endsection
