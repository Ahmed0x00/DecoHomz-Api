@extends('layouts.app')

@section('title', 'Track Your Order — DecoHomz')

@section('extra_css')
<style>
.track-page {
  padding: 60px 20px;
  background: #fafafa;
  min-height: calc(100vh - 300px);
  display: flex;
  align-items: center;
  justify-content: center;
}
.track-container {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: 40px;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}
.track-icon {
  width: 48px;
  height: 48px;
  background: #fdf5e6;
  color: var(--color-primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
}
.track-title {
  font-size: 24px;
  font-weight: 700;
  text-align: center;
  margin-bottom: 8px;
  color: var(--color-text);
}
.track-desc {
  font-size: 15px;
  color: var(--color-text-secondary);
  text-align: center;
  margin-bottom: 32px;
  line-height: 1.5;
}
.track-form label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--color-text);
}
.track-form input {
  width: 100%;
  padding: 14px 16px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: 15px;
  margin-bottom: 20px;
  transition: border-color 0.2s;
}
.track-form input:focus {
  outline: none;
  border-color: var(--color-primary);
}
.btn-track {
  width: 100%;
  background: var(--color-primary);
  color: #fff;
  padding: 14px;
  border: none;
  border-radius: var(--radius-md);
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}
.btn-track:hover {
  opacity: 0.9;
}
.btn-track:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
#track-msg {
  margin-top: 16px;
  font-size: 14px;
  text-align: center;
  display: none;
}
</style>
@endsection

@section('content')
<div class="track-page">
  <div class="track-container">
    <div class="track-icon">
      <svg viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" style="width:24px; height:24px;">
        <path d="M5 12h14M12 5l7 7-7 7" />
      </svg>
    </div>
    <h1 class="track-title">{{ __('Track Your Order') }}</h1>
    <p class="track-desc">{{ __('Enter your order number and email or phone number to check the status of your shipment.') }}</p>
    
    <form class="track-form" onsubmit="handleTrack(event)">
      <div>
        <label for="track_num">{{ __('Order Number') }}</label>
        <input type="text" id="track_num" placeholder="e.g. ORD-123456" required>
      </div>
      <div>
        <label for="track_contact">{{ __('Email Address or Phone Number') }}</label>
        <input type="text" id="track_contact" placeholder="{{ __('Email or phone') }}" required>
      </div>
      <button type="submit" class="btn-track" id="btn-track">{{ __('Track Order') }}</button>
      <div id="track-msg"></div>
    </form>
  </div>
</div>
@endsection

@section('extra_js')
<script>
async function handleTrack(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-track');
  const msg = document.getElementById('track-msg');
  const num = document.getElementById('track_num').value.trim();
  const contact = document.getElementById('track_contact').value.trim();

  btn.disabled = true;
  btn.textContent = 'Tracking...';
  msg.style.display = 'none';

  try {
    const res = await fetch('/api/orders/track', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        order_number: num,
        contact: contact
      })
    });

    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Could not track order.');
    
    if (data.session_id) {
      document.cookie = "session_id=" + data.session_id + "; path=/; max-age=31536000; SameSite=Lax";
      localStorage.setItem('dh_session_id', data.session_id);
    }

    location.href = data.redirect_url;
  } catch(err) {
    msg.style.color = '#e74c3c';
    msg.textContent = err.message;
    msg.style.display = 'block';
    btn.disabled = false;
    btn.textContent = 'Track Order';
  }
}
</script>
@endsection
