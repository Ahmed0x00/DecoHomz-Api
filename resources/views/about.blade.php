@extends('layouts.app')

@section('title', 'About Us — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="{{ asset_v('/css/about.css') }}">
@endsection

@section('content')

{{-- ═══ HERO ═══ --}}
<div class="about-hero">
  <div class="about-hero-content animate-fade-up">
    <h1>{{ __('Crafting Comfort.') }}<br>{{ __('Designing Life.') }}</h1>
    <p>{{ __('DecoHomz was born out of a passion for bringing premium, handcrafted furniture into every home. We believe your space should be a reflection of who you are.') }}</p>
  </div>
</div>

{{-- ═══ OUR STORY ═══ --}}
<div class="about-story">
  <div class="story-grid">
    <div class="story-img animate-fade-right">
      <svg viewBox="0 0 400 300" fill="none" style="width:100%;height:100%;background:linear-gradient(135deg, #E8DFD0, #D4C8B4)">
        <rect x="100" y="80" width="200" height="140" rx="8" fill="#8B6A48" />
        <rect x="120" y="100" width="160" height="100" rx="4" fill="#A07858" />
        <circle cx="200" cy="150" r="30" fill="#C4A882" opacity="0.8" />
        <path d="M100 220 L300 220 L320 280 L80 280 Z" fill="#6B4832" />
      </svg>
    </div>
    <div class="story-text animate-fade-left">
      <div class="sec-label">{{ __('Our Story') }}</div>
      <h2>{{ __('From a small workshop to your living room.') }}</h2>
      <p>{{ __('Founded in 2015, DecoHomz started with a simple idea: furniture should be built to last and designed to inspire. We began in a small woodworking studio in Cairo, crafting custom pieces for local clients.') }}</p>
      <p>{{ __('Today, we partner with master craftsmen across Egypt to bring you collections that blend traditional techniques with modern aesthetics. Every piece is rigorously tested for comfort, durability, and style.') }}</p>
    </div>
  </div>
</div>

{{-- ═══ STATS ═══ --}}
<div class="about-stats">
  <div class="stats-grid">
    <div class="stat-item animate-on-scroll">
      <div class="stat-num" data-target="2015">2015</div>
      <div class="stat-label">{{ __('Year Founded') }}</div>
    </div>
    <div class="stat-item animate-on-scroll stagger-2">
      <div class="stat-num" data-target="50">+50K</div>
      <div class="stat-label">{{ __('Happy Customers') }}</div>
    </div>
    <div class="stat-item animate-on-scroll stagger-4">
      <div class="stat-num" data-target="400">+400</div>
      <div class="stat-label">{{ __('Products') }}</div>
    </div>
    <div class="stat-item animate-on-scroll stagger-6">
      <div class="stat-num" data-target="100">100%</div>
      <div class="stat-label">{{ __('Quality Guarantee') }}</div>
    </div>
  </div>
</div>

{{-- ═══ VALUES ═══ --}}
<div class="about-values">
  <div class="values-header animate-on-scroll">
    <div class="sec-label">{{ __('Our Principles') }}</div>
    <h2 class="premium-title" style="font-size:36px">{{ __('What Drives Us') }}</h2>
  </div>
  <div class="values-grid">
    <div class="value-card animate-on-scroll">
      <div class="value-icon">
        <svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
      </div>
      <h3>{{ __('Uncompromising Quality') }}</h3>
      <p>{{ __('We source only the finest materials, from solid woods to premium fabrics, ensuring every piece withstands the test of time.') }}</p>
    </div>
    <div class="value-card animate-on-scroll stagger-2">
      <div class="value-icon">
        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      </div>
      <h3>{{ __('Thoughtful Design') }}</h3>
      <p>{{ __('Aesthetics meet functionality. Our designs are crafted to look beautiful while providing maximum comfort and utility.') }}</p>
    </div>
    <div class="value-card animate-on-scroll stagger-4">
      <div class="value-icon">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <h3>{{ __('Customer First') }}</h3>
      <p>{{ __('Your satisfaction is our priority. From easy returns to a 5-year warranty, we stand by our products and our service.') }}</p>
    </div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
// Optional: Add counter animation for stats if desired
</script>
@endsection
