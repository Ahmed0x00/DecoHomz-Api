@extends('layouts.app')

@section('title', 'Pre-Order Confirmed — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="{{ asset_v('/css/order-confirmation.css') }}">
@endsection

@section('content')

<div class="confirm-wrap">
  <div class="success-banner">
    <div class="check-circle">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>
    <div class="confirm-title">{{ __('Pre-Order Request Sent!') }}</div>
    <div class="confirm-sub">{{ __('Thank you! Our team will review your request and contact you within 24 hours.') }}</div>
    <div class="cta-row">
      <a href="/pre-order" class="btn" style="background:var(--color-primary);color:#fff;padding:14px 32px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;text-decoration:none;letter-spacing:0.02em;">
        {{ __('Submit Another Request') }}
      </a>
      <a href="/account#preorders" class="btn" style="background:var(--color-surface);color:var(--color-text);border:1.5px solid var(--color-border);padding:14px 32px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;text-decoration:none;letter-spacing:0.02em;">
        {{ __('My Pre-Orders') }}
      </a>
    </div>
  </div>
</div>

@endsection
