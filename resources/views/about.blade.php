@extends('layouts.app')

@section('title', 'About Us — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="/css/about.css">
@endsection

@section('content')

<!-- HERO -->
<div class="about-hero">
  <div class="hero-text">
    <div class="hero-bg">A</div>
    <div class="hero-label">Our Story</div>
    <h1 class="hero-h1">Crafting Spaces<br>That Feel Like Home</h1>
    <p class="hero-sub">Since 2018, DecoHomz has been bringing premium, thoughtfully designed furniture to homes across Egypt and the region.</p>
  </div>
  <div class="hero-right">
    <svg viewBox="0 0 340 260" fill="none">
      <rect x="0" y="220" width="340" height="40" fill="rgba(255,255,255,0.04)"/>
      <ellipse cx="170" cy="222" rx="140" ry="10" fill="rgba(184,134,11,0.12)"/>
      <rect x="40" y="140" width="260" height="75" rx="12" fill="#4A3020"/>
      <rect x="40" y="116" width="52" height="72" rx="9" fill="#5C3D28"/>
      <rect x="248" y="116" width="52" height="72" rx="9" fill="#5C3D28"/>
      <rect x="92" y="128" width="70" height="87" rx="6" fill="#6B4832"/>
      <rect x="178" y="128" width="70" height="87" rx="6" fill="#6B4832"/>
      <rect x="55" y="215" width="18" height="16" rx="3" fill="#3D2418"/>
      <rect x="267" y="215" width="18" height="16" rx="3" fill="#3D2418"/>
      <ellipse cx="170" cy="218" rx="110" ry="8" fill="rgba(184,134,11,0.12)"/>
      <rect x="295" y="166" width="36" height="6" rx="2" fill="#7A5540"/>
      <rect x="300" y="172" width="4" height="46" rx="2" fill="#5C3D28"/>
      <rect x="323" y="172" width="4" height="46" rx="2" fill="#5C3D28"/>
      <rect x="308" y="138" width="4" height="30" rx="2" fill="#8B7060"/>
      <ellipse cx="310" cy="136" rx="18" ry="8" fill="#C4A882" opacity="0.65"/>
      <rect x="48" y="82" width="5" height="40" rx="2" fill="#6B5040"/>
      <circle cx="50" cy="74" r="20" fill="#3D5228" opacity="0.85"/>
      <circle cx="40" cy="70" r="13" fill="#4A6830" opacity="0.7"/>
      <circle cx="62" cy="68" r="11" fill="#3D5228" opacity="0.65"/>
      <rect x="43" y="120" width="16" height="18" rx="3" fill="#5C3D28"/>
      <rect x="140" y="48" width="60" height="52" rx="3" fill="rgba(255,255,255,0.06)"/>
      <rect x="144" y="52" width="52" height="44" rx="2" fill="rgba(184,134,11,0.15)"/>
      <ellipse cx="170" cy="206" rx="50" ry="12" fill="#5C3D28"/>
      <ellipse cx="170" cy="196" rx="50" ry="12" fill="#7A5540"/>
      <rect x="120" y="196" width="100" height="10" rx="2" fill="#8B6448"/>
      <rect x="155" y="208" width="6" height="16" rx="2" fill="#5C3D28"/>
      <rect x="179" y="208" width="6" height="16" rx="2" fill="#5C3D28"/>
    </svg>
  </div>
</div>

<!-- STATS -->
<div class="stats-bar">
  <div class="stat"><div class="stat-num">2018</div><div class="stat-label">Founded</div></div>
  <div class="stat"><div class="stat-num">50K+</div><div class="stat-label">Happy Customers</div></div>
  <div class="stat"><div class="stat-num">400+</div><div class="stat-label">Curated Products</div></div>
  <div class="stat"><div class="stat-num">6</div><div class="stat-label">Showrooms</div></div>
</div>

<!-- STORY -->
<div class="story">
  <div class="story-text">
    <div class="sec-label">Who We Are</div>
    <h2 class="sec-title">Furniture that tells<br>your story</h2>
    <p class="sec-body">DecoHomz was born from a simple belief — that your home should be a reflection of who you are. We started in a small Cairo studio in 2018, sourcing handcrafted pieces from local and regional artisans.</p>
    <p class="sec-body">Today, we offer over 400 carefully curated products, from statement sofas to subtle accents, all chosen for their quality, sustainability, and timeless design.</p>
  </div>
  <div class="story-visual">
    <svg viewBox="0 0 340 260" fill="none">
      <rect x="30" y="130" width="280" height="16" rx="4" fill="#8B6A48"/>
      <rect x="30" y="146" width="14" height="90" rx="4" fill="#7A5540"/>
      <rect x="296" y="146" width="14" height="90" rx="4" fill="#7A5540"/>
      <rect x="55" y="100" width="50" height="32" rx="4" fill="#A07858"/>
      <rect x="55" y="96" width="50" height="6" rx="2" fill="#B89068"/>
      <rect x="120" y="88" width="30" height="44" rx="3" fill="#C4A882"/>
      <rect x="120" y="84" width="30" height="6" rx="2" fill="#D4B896"/>
      <rect x="220" y="40" width="90" height="92" rx="3" fill="#7A5540"/>
      <rect x="216" y="38" width="98" height="8" rx="2" fill="#8B6448"/>
      <rect x="216" y="128" width="98" height="5" rx="2" fill="#6B4832"/>
      <line x1="265" y1="46" x2="265" y2="128" stroke="#6B4832" stroke-width="1.5"/>
      <rect x="224" y="54" width="34" height="16" rx="2" fill="#C4A882"/>
      <rect x="272" y="54" width="34" height="16" rx="2" fill="#C4A882"/>
      <rect x="224" y="78" width="34" height="16" rx="2" fill="#B89068"/>
      <rect x="272" y="78" width="34" height="16" rx="2" fill="#B89068"/>
      <rect x="224" y="102" width="34" height="18" rx="2" fill="#A07858"/>
      <rect x="272" y="102" width="34" height="18" rx="2" fill="#A07858"/>
      <rect x="45" y="56" width="4" height="46" rx="2" fill="#6B5040"/>
      <circle cx="47" cy="48" r="16" fill="#3D5228" opacity="0.8"/>
      <circle cx="38" cy="44" r="10" fill="#4A6830" opacity="0.7"/>
      <rect x="40" y="100" width="14" height="14" rx="3" fill="#5C3D28"/>
      <rect x="155" y="160" width="55" height="40" rx="5" fill="#8B6A48"/>
      <rect x="148" y="156" width="14" height="46" rx="5" fill="#A07858"/>
      <rect x="203" y="156" width="14" height="46" rx="5" fill="#A07858"/>
      <rect x="163" y="200" width="10" height="30" rx="3" fill="#6B4832"/>
      <rect x="192" y="200" width="10" height="30" rx="3" fill="#6B4832"/>
    </svg>
  </div>
</div>

<!-- VALUES -->
<div class="values">
  <div class="values-header">
    <div class="sec-label">What We Stand For</div>
    <h2 class="sec-title">Our Core Values</h2>
  </div>
  <div class="values-grid">
    <div class="value-card">
      <div class="value-icon" style="background:#F5F0E8">
        <svg viewBox="0 0 24 24" stroke="#8B6A48" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div class="value-title">Uncompromising Quality</div>
      <div class="value-desc">Every piece passes a rigorous quality check. We work with craftsmen who share our obsession for detail and durability.</div>
    </div>
    <div class="value-card">
      <div class="value-icon" style="background:#F0F7EC">
        <svg viewBox="0 0 24 24" stroke="#4A7C3F" stroke-width="1.5"><path d="M12 22V12"/><path d="M5 12H2a10 10 0 0 0 20 0h-3"/><path d="M12 2a4 4 0 0 0-4 4c0 1.5.8 2.8 2 3.5"/><path d="M12 2a4 4 0 0 1 4 4c0 1.5-.8 2.8-2 3.5"/></svg>
      </div>
      <div class="value-title">Sustainable Sourcing</div>
      <div class="value-desc">We partner with suppliers who use responsibly sourced wood, recycled metals, and eco-friendly upholstery materials.</div>
    </div>
    <div class="value-card">
      <div class="value-icon" style="background:#FEF9EC">
        <svg viewBox="0 0 24 24" stroke="#B8860B" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="value-title">Customer First</div>
      <div class="value-desc">From free design consultations to white-glove delivery, we're with you every step from browsing to your final room reveal.</div>
    </div>
  </div>
</div>

<!-- TEAM -->
<div class="team">
  <div class="team-header">
    <div class="sec-label">The People Behind DecoHomz</div>
    <h2 class="sec-title">Meet Our Team</h2>
  </div>
  <div class="team-grid">
    <div class="team-card">
      <div class="team-avatar">
        <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <div class="team-name">Karim El Sayed</div>
      <div class="team-role">Founder & CEO</div>
      <div class="team-bio">Passionate about design and homes since age 12. Built DecoHomz from a single showroom in Maadi.</div>
    </div>
    <div class="team-card">
      <div class="team-avatar">
        <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <div class="team-name">Nadia Farouk</div>
      <div class="team-role">Head of Design</div>
      <div class="team-bio">Interior designer with 15 years of experience, curates every collection for aesthetic harmony.</div>
    </div>
    <div class="team-card">
      <div class="team-avatar">
        <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <div class="team-name">Ahmed Mostafa</div>
      <div class="team-role">Operations Lead</div>
      <div class="team-bio">Ensures every order arrives on time, perfectly assembled, and exactly as expected.</div>
    </div>
    <div class="team-card">
      <div class="team-avatar">
        <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <div class="team-name">Sara Hossam</div>
      <div class="team-role">Customer Experience</div>
      <div class="team-bio">Leads our support team to make every customer interaction warm, helpful, and memorable.</div>
    </div>
  </div>
</div>

<!-- SHOWROOMS -->
<div class="showrooms">
  <div class="showrooms-header">
    <div class="sec-label">Visit Us In Person</div>
    <h2 class="sec-title">Our Showrooms</h2>
  </div>
  <div class="showrooms-grid">
    <div class="showroom-card">
      <div class="showroom-visual" style="background:#EDE0CE">
        <svg viewBox="0 0 200 140" fill="none" width="160">
          <rect x="20" y="60" width="160" height="60" rx="6" fill="#8B6A48" opacity="0.5"/>
          <rect x="20" y="48" width="32" height="52" rx="5" fill="#A07858" opacity="0.5"/>
          <rect x="148" y="48" width="32" height="52" rx="5" fill="#A07858" opacity="0.5"/>
          <rect x="52" y="55" width="40" height="65" rx="4" fill="#C4A882" opacity="0.5"/>
          <rect x="108" y="55" width="40" height="65" rx="4" fill="#C4A882" opacity="0.5"/>
          <rect x="30" y="120" width="12" height="14" rx="2" fill="#6B4832" opacity="0.5"/>
          <rect x="158" y="120" width="12" height="14" rx="2" fill="#6B4832" opacity="0.5"/>
        </svg>
      </div>
      <div class="showroom-info">
        <div class="showroom-city">Cairo — Maadi</div>
        <div class="showroom-addr">14 Road 9, Maadi<br>Sun–Thu: 10am–9pm</div>
        <div><span class="showroom-tag">Flagship</span><span class="showroom-tag">Assembly Studio</span></div>
        <button class="btn-dir">Get Directions</button>
      </div>
    </div>
    <div class="showroom-card">
      <div class="showroom-visual" style="background:#D8E0CE">
        <svg viewBox="0 0 200 140" fill="none" width="160">
          <rect x="25" y="45" width="150" height="85" rx="5" fill="#7A5540" opacity="0.45"/>
          <rect x="25" y="38" width="150" height="10" rx="3" fill="#8B6448" opacity="0.45"/>
          <rect x="25" y="126" width="150" height="6" rx="2" fill="#6B4832" opacity="0.45"/>
          <line x1="100" y1="48" x2="100" y2="126" stroke="#6B4832" stroke-width="1" opacity="0.4"/>
          <rect x="34" y="56" width="58" height="14" rx="2" fill="#C4A882" opacity="0.5"/>
          <rect x="108" y="56" width="58" height="14" rx="2" fill="#C4A882" opacity="0.5"/>
        </svg>
      </div>
      <div class="showroom-info">
        <div class="showroom-city">Cairo — Heliopolis</div>
        <div class="showroom-addr">52 El Nozha St, Heliopolis<br>Sun–Thu: 10am–9pm</div>
        <div><span class="showroom-tag">Bedroom Specialist</span></div>
        <button class="btn-dir">Get Directions</button>
      </div>
    </div>
    <div class="showroom-card">
      <div class="showroom-visual" style="background:#CEd0E0">
        <svg viewBox="0 0 200 140" fill="none" width="160">
          <rect x="30" y="80" width="140" height="50" rx="5" fill="#8B6A48" opacity="0.4"/>
          <rect x="30" y="68" width="26" height="44" rx="4" fill="#A07858" opacity="0.4"/>
          <rect x="144" y="68" width="26" height="44" rx="4" fill="#A07858" opacity="0.4"/>
          <ellipse cx="100" cy="120" rx="50" ry="7" fill="#6B4832" opacity="0.3"/>
        </svg>
      </div>
      <div class="showroom-info">
        <div class="showroom-city">Alexandria</div>
        <div class="showroom-addr">7 Corniche Rd, Sidi Bishr<br>Sun–Thu: 10am–8pm</div>
        <div><span class="showroom-tag">Outdoor Collection</span></div>
        <button class="btn-dir">Get Directions</button>
      </div>
    </div>
  </div>
</div>

<!-- CTA -->
<div class="cta-band">
  <div class="cta-text">
    <div class="cta-title">Ready to design your space?</div>
    <div class="cta-sub">Browse our full collection or book a free design consultation.</div>
  </div>
  <div class="cta-btns">
    <button class="btn-outline-w" onclick="showToast('Book a consultation at our Maadi showroom!')">Book Consultation</button>
    <button class="btn-gold" onclick="location.href='/shop'">Shop Now</button>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  Cart.updateBadge();
})();
</script>
@endsection
