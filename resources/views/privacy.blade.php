@extends('layouts.app')

@section('title', 'Privacy Policy — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="{{ asset_v('/css/privacy.css') }}">
@endsection

@section('content')

<div class="legal-page">

  {{-- ═══ HERO ═══ --}}
  <div class="legal-hero">
    <div class="legal-hero-content animate-fade-up">
      <h1>{{ __('Privacy Policy') }}</h1>
      <p>{{ __('How we collect, use, and protect your personal information.') }}</p>
    </div>
  </div>

  {{-- ═══ CONTENT ═══ --}}
  <div class="legal-content">

    <div class="legal-updated">
      {{ __('Last updated: January 1, 2025') }}
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('1. Introduction') }}</h2>
      <p>{{ __('Welcome to DecoHomz ("we," "our," or "us"). We are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and purchase our furniture products.') }}</p>
      <p>{{ __('By accessing or using our website, you agree to the collection and use of information in accordance with this policy. If you do not agree with the terms of this Privacy Policy, please do not access the site.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('2. Information We Collect') }}</h2>

      <h3>{{ __('Personal Information') }}</h3>
      <p>{{ __('We may collect personally identifiable information that you voluntarily provide to us when you register on the site, place an order, subscribe to our newsletter, or contact us. This information may include:') }}</p>
      <ul>
        <li>{{ __('Full name and contact details (email address, phone number, shipping address)') }}</li>
        <li>{{ __('Billing address') }}</li>
        <li>{{ __('Account credentials (username and password)') }}</li>
        <li>{{ __('Order history and preferences') }}</li>
      </ul>

      <h3>{{ __('Payment Processing') }}</h3>
      <p>{{ __('All payment transactions are processed through secure, PCI-compliant third-party payment processors. We do not store, have access to, or process your credit card or debit card details on our servers. Your payment information is encrypted and handled directly by our payment partners.') }}</p>

      <h3>{{ __('Automatically Collected Information') }}</h3>
      <p>{{ __('When you access our website, we may automatically collect certain information about your device and usage, including:') }}</p>
      <ul>
        <li>{{ __('IP address and browser type') }}</li>
        <li>{{ __('Operating system and device information') }}</li>
        <li>{{ __('Pages visited, time spent, and navigation patterns') }}</li>
        <li>{{ __('Referring website or source') }}</li>
      </ul>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('3. How We Use Your Information') }}</h2>
      <p>{{ __('We use the information we collect for the following purposes:') }}</p>
      <ul>
        <li>{{ __('To process and fulfill your orders, including shipping and delivery of furniture') }}</li>
        <li>{{ __('To send order confirmations, updates, and delivery notifications') }}</li>
        <li>{{ __('To provide customer support and respond to your inquiries') }}</li>
        <li>{{ __('To personalize your shopping experience and recommend products') }}</li>
        <li>{{ __('To send promotional communications (with your consent)') }}</li>
        <li>{{ __('To improve our website, products, and services') }}</li>
        <li>{{ __('To detect and prevent fraud or unauthorized access') }}</li>
        <li>{{ __('To comply with legal obligations') }}</li>
      </ul>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('4. Cookies and Tracking Technologies') }}</h2>
      <p>{{ __('We use cookies and similar tracking technologies to enhance your browsing experience. Cookies are small data files stored on your device that help us understand how you use our site.') }}</p>
      <p>{{ __('You can control cookie settings through your browser preferences. However, disabling cookies may affect the functionality of certain features on our website.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('5. Information Sharing') }}</h2>
      <p>{{ __('We do not sell, trade, or rent your personal information to third parties. We may share your information with:') }}</p>
      <ul>
        <li>{{ __('Secure payment processors to handle transactions (they operate under their own privacy policies)') }}</li>
        <li>{{ __('Shipping and logistics partners for order delivery') }}</li>
        <li>{{ __('Analytics providers to help us understand site usage') }}</li>
        <li>{{ __('Law enforcement agencies when required by law') }}</li>
      </ul>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('6. Data Security') }}</h2>
      <p>{{ __('We implement industry-standard security measures to protect your personal information, including SSL encryption, secure payment processing, and regular security audits. However, no method of transmission over the Internet is 100% secure, and we cannot guarantee absolute security.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('7. Your Rights') }}</h2>
      <p>{{ __('You have the right to:') }}</p>
      <ul>
        <li>{{ __('Access and review your personal information') }}</li>
        <li>{{ __('Request correction of inaccurate data') }}</li>
        <li>{{ __('Request deletion of your personal data') }}</li>
        <li>{{ __('Opt out of marketing communications') }}</li>
        <li>{{ __('Withdraw consent for data processing') }}</li>
      </ul>
      <p>{{ __('To exercise any of these rights, please contact us at privacy@decohomz.com.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('8. Data Retention') }}</h2>
      <p>{{ __('We retain your personal information only for as long as necessary to fulfill the purposes for which it was collected, including to satisfy legal, accounting, or reporting requirements.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('9. Children\'s Privacy') }}</h2>
      <p>{{ __('Our website is not intended for individuals under the age of 18. We do not knowingly collect personal information from children. If we become aware that we have collected personal information from a child, we will take steps to delete it promptly.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('10. Changes to This Policy') }}</h2>
      <p>{{ __('We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new policy on this page and updating the "Last updated" date.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('11. Contact Us') }}</h2>
      <p>{{ __('If you have any questions about this Privacy Policy, please contact us:') }}</p>
      <p>{{ __('Email: privacy@decohomz.com') }}</p>
      <p>{{ __('Phone: +20 123 456 7890') }}</p>
      <p>{{ __('Address: Cairo, Egypt') }}</p>
    </div>

  </div>
</div>

@endsection
