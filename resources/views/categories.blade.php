@extends('layouts.app')

@section('title', 'Categories — DecoHomz')

@section('extra_css')
<style>
.cat-hero { 
    background: #FAFAF8; 
    padding: 100px 40px; 
    text-align: center; 
    border-bottom: 1px solid #F0F0F0;
}
.cat-grid-large { 
    max-width: 1400px; 
    margin: 80px auto; 
    padding: 0 80px; 
    display: grid; 
    grid-template-columns: repeat(3, 1fr); 
    gap: 40px; 
}
.cat-card-l { 
    background: #fff; 
    border-radius: 20px; 
    overflow: hidden; 
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); 
    text-decoration: none; 
    display: flex; 
    flex-direction: column; 
    border: 1px solid #F0F0F0;
}
.cat-card-l:hover { 
    transform: translateY(-10px); 
    box-shadow: 0 30px 60px rgba(0,0,0,0.08); 
    border-color: #B8860B;
}
.cat-img-l { 
    height: 300px; 
    background: #FAFAFA; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    overflow: hidden;
}
.cat-img-l img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}
.cat-card-l:hover .cat-img-l img {
    transform: scale(1.05);
}
.cat-info-l { 
    padding: 32px; 
    text-align: center; 
}
.cat-info-l h3 { 
    font-size: 22px; 
    font-weight: 800;
    color: #1A1A1A; 
    margin-bottom: 12px; 
    letter-spacing: -0.01em;
}
.cat-info-l p { 
    font-size: 14px; 
    color: #888; 
    line-height: 1.6;
}
@media (max-width: 1200px) {
    .cat-grid-large { padding: 0 40px; }
}
@media (max-width: 992px) {
    .cat-grid-large { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .cat-grid-large { grid-template-columns: 1fr; padding: 0 20px; margin: 40px auto; gap: 24px; }
    .cat-hero { padding: 60px 20px; }
    .cat-img-l { height: 240px; }
}
</style>
@endsection

@section('content')

<div class="breadcrumb">{{ __('Home') }} › <span>{{ __('Categories') }}</span></div>

<div class="cat-hero">
  <h1 class="premium-title">{{ __('Browse Our Collections') }}</h1>
  <p class="premium-subtitle">{{ __('Expertly curated furniture for every corner of your home.') }}</p>
</div>

<div class="cat-grid-large" id="categories-container">
  <!-- Loading state -->
  <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
    <div class="spinner"></div>
    <p style="color: #888; margin-top: 16px;">{{ __('Loading collections...') }}</p>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(async function() {
  Cart.updateBadge();
  
  const container = document.getElementById('categories-container');
  
  try {
    const res = await API.get('/categories');
    const categories = res.categories || [];
    
    if (categories.length === 0) {
      container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 60px; color: #888;">' + "{{ __('No categories found.') }}" + '</div>';
      return;
    }
    
    container.innerHTML = categories.map(cat => {
      const imgSrc = cat.url || '/img/placeholder.svg';
      return `
        <a href="/shop?category=${encodeURIComponent(cat.slug || cat.name)}" class="cat-card-l">
          <div class="cat-img-l">
            <img src="${imgSrc}" alt="${cat.name}" onerror="this.innerHTML='<svg viewBox=&quot;0 0 48 48&quot;><rect x=&quot;4&quot; y=&quot;20&quot; width=&quot;40&quot; height=&quot;18&quot; rx=&quot;4&quot; fill=&quot;none&quot; stroke=&quot;#8B6A48&quot; stroke-width=&quot;1.5&quot;/></svg>'">
          </div>
          <div class="cat-info-l">
            <h3>${cat.name}</h3>
            <p>${cat.description || (cat.products_count + " {{ __('Products') }}")}</p>
          </div>
        </a>
      `;
    }).join('');
    
  } catch (e) {
    container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 60px; color: #C0392B;">' + "{{ __('Failed to load categories. Please try again.') }}" + '</div>';
  }
})();
</script>
@endsection
