@extends('layouts.app')

@section('title', 'Contact Us — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="{{ asset_v('/css/contact.css') }}">
@endsection

@section('content')

<div class="contact-page">
  <div class="contact-header animate-fade-down">
    <div class="sec-label">{{ __('Get In Touch') }}</div>
    <h1>{{ __("We'd love to hear from you") }}</h1>
    <p>{{ __('Whether you have a question about our products, need help with an order, or just want to say hello, our team is ready to answer all your questions.') }}</p>
  </div>

  <div class="contact-grid">
    
    {{-- Left: Info --}}
    <div class="contact-info animate-fade-right">
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div class="info-text">
          <h3>{{ __('Our Showroom') }}</h3>
          <p>DecoHomz Studio<br>12 El Gezira Street, Zamalek<br>Cairo, Egypt</p>
        </div>
      </div>
      
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        </div>
        <div class="info-text">
          <h3>{{ __('Call Us') }}</h3>
          <p>+20 100 123 4567<br>{{ __('Sat - Thu, 10am - 8pm') }}</p>
        </div>
      </div>
      
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <div class="info-text">
          <h3>{{ __('Email Us') }}</h3>
          <p>hello@decohomz.com<br>support@decohomz.com</p>
        </div>
      </div>
    </div>
    
    {{-- Right: Form --}}
    <div class="contact-form-wrap animate-fade-left">
      <h2>{{ __('Send us a message') }}</h2>
      <form id="contact-form" onsubmit="event.preventDefault(); submitContact();">
        <div class="form-grid">
          <div class="field"><label>{{ __('First Name') }}</label><input type="text" required></div>
          <div class="field"><label>{{ __('Last Name') }}</label><input type="text" required></div>
          <div class="field full"><label>{{ __('Email Address') }}</label><input type="email" required></div>
          <div class="field full"><label>{{ __('Subject') }}</label>
            <select required>
              <option value="" disabled selected>{{ __('Select a topic') }}</option>
              <option>{{ __('Order Support') }}</option>
              <option>{{ __('Product Inquiry') }}</option>
              <option>{{ __('Feedback') }}</option>
              <option>{{ __('Other') }}</option>
            </select>
          </div>
          <div class="field full"><label>{{ __('Message') }}</label><textarea rows="5" required></textarea></div>
        </div>
        <button type="submit" class="btn-dark" id="btn-submit" style="width:100%;margin-top:16px">{{ __('Send Message') }}</button>
      </form>
    </div>
    
  </div>
  
  {{-- Map --}}
  <div class="contact-map animate-on-scroll">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d13813.06584285702!2d31.2185203!3d30.0578643!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x145840d0c3ebc9fb%3A0xe6bc2cb2b42d7658!2sZamalek%2C%20Cairo%20Governorate%2C%20Egypt!5e0!3m2!1sen!2sus!4v1687000000000!5m2!1sen!2sus" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
  </div>

</div>

@endsection

@section('extra_js')
<script>
window.submitContact = function() {
  const btn = document.getElementById('btn-submit');
  btn.classList.add('btn-loading');
  btn.textContent = "{{ __('Sending...') }}";
  
  // Simulate API call
  setTimeout(() => {
    btn.classList.remove('btn-loading');
    btn.textContent = "{{ __('Message Sent!') }}";
    btn.style.backgroundColor = 'var(--color-success)';
    
    showToast("{{ __('Thank you! Your message has been sent.') }}", 'success');
    
    document.getElementById('contact-form').reset();
    
    setTimeout(() => {
      btn.textContent = "{{ __('Send Message') }}";
      btn.style.backgroundColor = '';
    }, 3000);
    
  }, 1500);
};
</script>
@endsection