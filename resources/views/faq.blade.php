@extends('layouts.app')

@section('title', 'FAQ — DecoHomz')

@section('extra_css')
<style>
.faq-hero { background: #2C1F14; color: #fff; padding: 80px 40px; text-align: center; }
.faq-hero h1 { font-size: 36px; margin-bottom: 12px; }
.faq-hero p { opacity: 0.7; font-size: 14px; }
.faq-layout { max-width: 800px; margin: 60px auto; padding: 0 20px; }
.faq-cat-title { font-size: 18px; font-weight: 700; color: #2C1F14; margin: 40px 0 20px; border-bottom: 1px solid #EDE8E2; padding-bottom: 10px; }
.faq-item { border: 1px solid #EDE8E2; border-radius: 8px; margin-bottom: 12px; background: #fff; overflow: hidden; }
.faq-q { padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; font-weight: 600; color: #2C1F14; font-size: 14px; transition: 0.3s; }
.faq-q:hover { background: #FAFAF8; }
.faq-q svg { width: 18px; height: 18px; transition: 0.3s; }
.faq-item.active .faq-q { background: #F5F0E8; }
.faq-item.active .faq-q svg { transform: rotate(180deg); }
.faq-a { padding: 0 24px; max-height: 0; overflow: hidden; transition: 0.4s ease-out; color: #666; font-size: 13px; line-height: 1.8; }
.faq-item.active .faq-a { padding: 0 24px 20px; max-height: 200px; }
</style>
@endsection

@section('content')

<div class="faq-hero">
  <h1>Frequently Asked Questions</h1>
  <p>Everything you need to know about our products and services.</p>
</div>

<div class="faq-layout">
  <div class="faq-cat-title">Orders & Shipping</div>

  <div class="faq-item">
    <div class="faq-q">How long does shipping take? <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
    <div class="faq-a">For standard orders within Cairo, shipping typically takes 3–5 business days. Express delivery is available in 2–3 business days. International shipping can take 10–15 business days depending on the location.</div>
  </div>

  <div class="faq-item">
    <div class="faq-q">Can I track my order? <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
    <div class="faq-a">Yes! Once your order is shipped, you will receive an email with a tracking number and a link to our tracking portal. You can also track your order in the My Orders section of your account.</div>
  </div>

  <div class="faq-item">
    <div class="faq-q">Do you offer free delivery? <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
    <div class="faq-a">Yes! We offer free standard delivery on all orders above EGP 2,000. Orders below that amount incur a flat delivery fee of EGP 149.</div>
  </div>

  <div class="faq-cat-title">Returns & Warranty</div>

  <div class="faq-item">
    <div class="faq-q">What is your return policy? <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
    <div class="faq-a">We offer a 14-day return policy for all unused items in their original packaging. Please contact our support team to initiate a return. Custom or made-to-order items are not eligible for returns.</div>
  </div>

  <div class="faq-item">
    <div class="faq-q">Do you offer warranty on furniture? <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
    <div class="faq-a">Most of our premium furniture pieces come with a 5-year structural warranty. Specific details can be found on each product page. The warranty covers manufacturing defects but not wear from normal use.</div>
  </div>

  <div class="faq-item">
    <div class="faq-q">How do I assemble my furniture? <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
    <div class="faq-a">All of our furniture comes with detailed assembly instructions. Standard delivery includes white-glove assembly where our team will deliver and assemble your furniture in your home at no extra cost.</div>
  </div>

  <div class="faq-cat-title">Payments & Pricing</div>

  <div class="faq-item">
    <div class="faq-q">What payment methods do you accept? <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
    <div class="faq-a">We accept Visa, Mastercard, American Express, Fawry, and Cash on Delivery (COD) for orders up to EGP 30,000. All online payments are processed securely through encrypted channels.</div>
  </div>

  <div class="faq-item">
    <div class="faq-q">Are prices inclusive of VAT? <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
    <div class="faq-a">Yes, all displayed prices on our website are inclusive of VAT. No additional taxes are charged at checkout.</div>
  </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
  Cart.updateBadge();

  document.querySelectorAll('.faq-q').forEach(function(q) {
    q.addEventListener('click', function() {
      var item = q.parentElement;
      item.classList.toggle('active');
    });
  });
})();
</script>
@endsection
