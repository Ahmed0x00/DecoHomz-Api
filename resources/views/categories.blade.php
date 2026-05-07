@extends('layouts.app')

@section('title', 'Categories — DecoHomz')

@section('extra_css')
<style>
.cat-hero { background: #F5F0E8; padding: 80px 40px; text-align: center; }
.cat-hero h1 { font-size: 36px; color: #2C1F14; margin-bottom: 10px; }
.cat-grid-large { max-width: 1200px; margin: 60px auto; padding: 0 40px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
.cat-card-l { background: #fff; border: 1px solid #EDE8E2; border-radius: 12px; overflow: hidden; transition: 0.3s; text-decoration: none; display: flex; flex-direction: column; }
.cat-card-l:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
.cat-img-l { height: 200px; background: #F5F0E8; display: flex; align-items: center; justify-content: center; }
.cat-img-l svg { width: 80px; height: 80px; stroke: #8B6A48; fill: none; }
.cat-info-l { padding: 24px; text-align: center; }
.cat-info-l h3 { font-size: 18px; color: #2C1F14; margin-bottom: 8px; }
.cat-info-l p { font-size: 13px; color: #888; }
@media (max-width: 768px) {
  .cat-grid-large { grid-template-columns: 1fr; padding: 0 20px; margin: 30px auto; gap: 20px; }
  .cat-hero { padding: 40px 20px; }
  .cat-hero h1 { font-size: 28px; }
}
</style>
@endsection

@section('content')

<div class="breadcrumb">Home › <span>Categories</span></div>

<div class="cat-hero">
  <h1>Browse by Category</h1>
  <p>Find the perfect piece for every room in your home.</p>
</div>

<div class="cat-grid-large">
  <a href="/shop?category=Living%20Room" class="cat-card-l">
    <div class="cat-img-l">
      <svg viewBox="0 0 48 48"><rect x="4" y="22" width="40" height="18" rx="4" fill="none" stroke="#8B6A48" stroke-width="1.5"/><rect x="4" y="18" width="8" height="14" rx="3" fill="none" stroke="#8B6A48" stroke-width="1.5"/><rect x="36" y="18" width="8" height="14" rx="3" fill="none" stroke="#8B6A48" stroke-width="1.5"/></svg>
    </div>
    <div class="cat-info-l">
      <h3>Living Room</h3>
      <p>Sofas, coffee tables, and more.</p>
    </div>
  </a>

  <a href="/shop?category=Bedroom" class="cat-card-l">
    <div class="cat-img-l">
      <svg viewBox="0 0 48 48"><rect x="4" y="20" width="40" height="18" rx="4" fill="none" stroke="#8B6A48" stroke-width="1.5"/><rect x="4" y="14" width="40" height="10" rx="4" fill="none" stroke="#8B6A48" stroke-width="1.5"/></svg>
    </div>
    <div class="cat-info-l">
      <h3>Bedroom</h3>
      <p>Beds, nightstands, and wardrobes.</p>
    </div>
  </a>

  <a href="/shop?category=Dining" class="cat-card-l">
    <div class="cat-img-l">
      <svg viewBox="0 0 48 48"><rect x="8" y="22" width="32" height="6" rx="2" fill="none" stroke="#8B6A48" stroke-width="1.5"/><rect x="14" y="10" width="20" height="16" rx="3" fill="none" stroke="#8B6A48" stroke-width="1.5"/></svg>
    </div>
    <div class="cat-info-l">
      <h3>Dining</h3>
      <p>Tables, chairs, and sideboards.</p>
    </div>
  </a>

  <a href="/shop?category=Office" class="cat-card-l">
    <div class="cat-img-l">
      <svg viewBox="0 0 48 48"><rect x="6" y="10" width="36" height="24" rx="3" fill="none" stroke="#8B6A48" stroke-width="1.5"/><rect x="16" y="34" width="16" height="4" rx="1" fill="none" stroke="#8B6A48" stroke-width="1.5"/></svg>
    </div>
    <div class="cat-info-l">
      <h3>Office</h3>
      <p>Desks and ergonomic chairs.</p>
    </div>
  </a>

  <a href="/shop?category=Outdoor" class="cat-card-l">
    <div class="cat-img-l">
      <svg viewBox="0 0 48 48"><rect x="6" y="24" width="36" height="14" rx="4" fill="none" stroke="#8B6A48" stroke-width="1.5"/></svg>
    </div>
    <div class="cat-info-l">
      <h3>Outdoor</h3>
      <p>Garden furniture and sets.</p>
    </div>
  </a>

  <a href="/shop?category=Decor" class="cat-card-l">
    <div class="cat-img-l">
      <svg viewBox="0 0 48 48"><circle cx="24" cy="18" r="8" fill="none" stroke="#8B6A48" stroke-width="1.5"/><path d="M24 10 V6" stroke="#8B6A48" stroke-width="1.5"/></svg>
    </div>
    <div class="cat-info-l">
      <h3>Decor</h3>
      <p>Lamps, mirrors, and rugs.</p>
    </div>
  </a>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  Cart.updateBadge();
})();
</script>
@endsection
