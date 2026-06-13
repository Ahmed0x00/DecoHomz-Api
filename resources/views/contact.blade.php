@extends('layouts.app')

@section('title', 'Contact Us — DecoHomz')

@section('extra_css')
  <style>
    .contact-layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      padding: 60px 40px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .contact-info h1 {
      font-size: 32px;
      color: #2C1F14;
      margin-bottom: 20px;
    }

    .contact-info p {
      color: #666;
      line-height: 1.8;
      margin-bottom: 30px;
    }

    .info-item {
      display: flex;
      gap: 15px;
      margin-bottom: 24px;
    }

    .info-icon {
      width: 40px;
      height: 40px;
      background: #F5F0E8;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .info-icon svg {
      width: 20px;
      height: 20px;
      stroke: #8B6A48;
      fill: none;
    }

    .info-text h3 {
      font-size: 14px;
      color: #2C1F14;
      margin-bottom: 4px;
    }

    .info-text p {
      font-size: 13px;
      color: #888;
      margin: 0;
    }

    .contact-form {
      background: #fff;
      border: 1px solid #EDE8E2;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
    }

    .form-title {
      font-size: 18px;
      font-weight: 700;
      color: #2C1F14;
      margin-bottom: 24px;
    }

    .submit-btn {
      width: 100%;
      background: #2C1F14;
      color: #fff;
      padding: 14px;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    .submit-btn:hover {
      background: #444;
    }

    .map-section {
      height: 400px;
      background: #F5F0E8;
      border-radius: 12px;
      margin: 0 40px 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      border: 1px solid #EDE8E2;
    }

    @media (max-width: 768px) {
      .contact-layout {
        grid-template-columns: 1fr;
        padding: 30px 20px;
        gap: 40px;
      }

      .map-section {
        margin: 0 20px 40px;
        height: 300px;
      }

      .contact-form {
        padding: 24px;
      }
    }
  </style>
@endsection

@section('content')

  <div class="breadcrumb">{{ __('Home') }} › <span>{{ __('Contact Us') }}</span></div>

  <div class="contact-layout">
    <div class="contact-info">
      <h1>{{ __('Get in Touch') }}</h1>
      <p>
        {{ __('Have a question about our collections or need help with an order? Our team is here to assist you. Fill out the form or reach us through our contact details.') }}
      </p>

      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
            <circle cx="12" cy="10" r="3" />
          </svg>
        </div>
        <div class="info-text">
          <h3>{{ __('Visit Our Showroom') }}</h3>
          <p>{{ __('14 El Nasr St, Maadi, Cairo, Egypt') }}</p>
        </div>
      </div>

      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5">
            <path
              d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
          </svg>
        </div>
        <div class="info-text">
          <h3>{{ __('Call Us') }}</h3>
          <p>+20 100 123 4567</p>
        </div>
      </div>

      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
            <polyline points="22,6 12,13 2,6" />
          </svg>
        </div>
        <div class="info-text">
          <h3>{{ __('Email Us') }}</h3>
          <p>hello@decohomz.com</p>
        </div>
      </div>
    </div>

    <div class="contact-form">
      <div class="form-title">{{ __('Send a Message') }}</div>
      <form id="contact-form">
        <div class="field">
          <label>{{ __('Full Name') }}</label>
          <input type="text" name="name" placeholder="{{ __('John Doe') }}" required>
        </div>
        <div class="field">
          <label>{{ __('Email Address') }}</label>
          <input type="email" name="email" placeholder="john@example.com" required>
        </div>
        <div class="field">
          <label>{{ __('Phone (optional)') }}</label>
          <input type="tel" name="phone" placeholder="+20 1XX XXX XXXX">
        </div>
        <div class="field">
          <label>{{ __('Subject') }}</label>
          <select name="subject">
            <option>{{ __('General Inquiry') }}</option>
            <option>{{ __('Order Status') }}</option>
            <option>{{ __('Product Return') }}</option>
            <option>{{ __('Wholesale') }}</option>
          </select>
        </div>
        <div class="field">
          <label>{{ __('Message') }}</label>
          <textarea name="message" placeholder="{{ __('How can we help?') }}"
            style="width:100%; border:1px solid #E0D8CE; border-radius:6px; padding:12px; font-family:inherit; min-height:120px;"
            required></textarea>
        </div>
        <button type="submit" class="submit-btn" id="btn-submit">{{ __('Send Message') }}</button>
      </form>
    </div>
  </div>

  <div class="map-section">
    <div style="text-align:center; color:#8B6A48">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
        style="margin-bottom:12px">
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
        <circle cx="12" cy="10" r="3" />
      </svg>
      <div style="font-weight:600">Interactive Map Placeholder</div>
      <div style="font-size:12px; color:#aaa">Showroom Location: Maadi, Cairo</div>
    </div>
  </div>

@endsection

@section('extra_js')
  <script>
    (function () {
      Cart.updateBadge();

      document.getElementById('contact-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btn-submit');

        const payload = {
          name: form.querySelector('[name="name"]').value.trim(),
          email: form.querySelector('[name="email"]').value.trim(),
          phone: form.querySelector('[name="phone"]')?.value.trim() || '',
          subject: form.querySelector('[name="subject"]')?.value || 'General Inquiry',
          message: form.querySelector('[name="message"]').value.trim(),
        };

        if (!payload.name || !payload.email || !payload.message) {
          showToast("{{ __('Please fill in your name, email, and message.') }}");
          return;
        }

        btn.disabled = true;
        btn.textContent = "{{ __('Sending...') }}";

        try {
          await API.post('/contact', payload);
          showToast("{{ __('Message sent! Well get back to you soon.') }}");
          form.reset();
        } catch (e) {
          showToast(e.data?.message || "{{ __('Failed to send message. Please try again.') }}");
        } finally {
          btn.disabled = false;
          btn.textContent = "{{ __('Send Message') }}";
        }
      });
    })();
  </script>
@endsection