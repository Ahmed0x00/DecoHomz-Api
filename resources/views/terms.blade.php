@extends('layouts.app')

@section('title', 'Terms of Service — DecoHomz')

@section('extra_css')
<link rel="stylesheet" href="{{ asset_v('/css/privacy.css') }}">
@endsection

@section('content')

<div class="legal-page">

  {{-- ═══ HERO ═══ --}}
  <div class="legal-hero">
    <div class="legal-hero-content animate-fade-up">
      <h1>{{ __('Terms of Service') }}</h1>
      <p>{{ __('The rules and guidelines governing your use of our website and services.') }}</p>
    </div>
  </div>

  {{-- ═══ CONTENT ═══ --}}
  <div class="legal-content">

    <div class="legal-updated">
      {{ __('Last updated: January 1, 2025') }}
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('1. Acceptance of Terms') }}</h2>
      <p>{{ __('By accessing and using the DecoHomz website and services, you accept and agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our website or services.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('2. Products and Services') }}</h2>

      <h3>{{ __('Product Descriptions') }}</h3>
      <p>{{ __('We strive to provide accurate descriptions and images of our furniture products. However, colors, textures, and dimensions may vary slightly due to screen settings and manufacturing tolerances. Product images are for illustrative purposes only.') }}</p>

      <h3>{{ __('Pricing') }}</h3>
      <p>{{ __('All prices are displayed in Egyptian Pounds (EGP) and include applicable taxes unless stated otherwise. We reserve the right to modify prices at any time without prior notice. In the event of a pricing error, we reserve the right to cancel orders placed at the incorrect price.') }}</p>

      <h3>{{ __('Availability') }}</h3>
      <p>{{ __('Product availability is subject to change without notice. We cannot guarantee that all items displayed on the website are in stock at all times.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('3. Orders and Payment') }}</h2>
      <p>{{ __('By placing an order, you are making an offer to purchase the selected products. We reserve the right to accept or decline any order at our discretion.') }}</p>
      <p>{{ __('Payment must be received in full before we process and ship your order. We accept major credit cards, debit cards, and other payment methods as displayed at checkout.') }}</p>
      <p>{{ __('For orders with deposit or installment options, the full balance must be paid before delivery can be scheduled.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('4. Shipping and Delivery') }}</h2>
      <p>{{ __('We deliver furniture products across Egypt. Delivery times are estimated and may vary based on your location, product availability, and other factors.') }}</p>
      <p>{{ __('Risk of loss and title for items purchased pass to you upon delivery of the products to the carrier. Please inspect your delivery carefully and report any damage within 48 hours.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('5. Returns and Exchanges') }}</h2>
      <p>{{ __('We want you to be completely satisfied with your purchase. If you are not satisfied, you may return or exchange eligible items within 14 days of delivery, subject to the following conditions:') }}</p>
      <ul>
        <li>{{ __('Items must be in their original condition and packaging') }}</li>
        <li>{{ __('Custom-made or personalized items are non-returnable') }}</li>
        <li>{{ __('Return shipping costs may apply unless the item is defective') }}</li>
        <li>{{ __('Refunds will be processed within 7-14 business days after we receive the returned item') }}</li>
      </ul>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('6. Warranty') }}</h2>
      <p>{{ __('DecoHomz provides a limited warranty on furniture products against manufacturing defects. The standard warranty period is 5 years from the date of delivery.') }}</p>
      <p>{{ __('This warranty does not cover normal wear and tear, damage from misuse, improper care, or modifications made by the customer.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('7. Intellectual Property') }}</h2>
      <p>{{ __('All content on this website, including text, graphics, logos, images, and software, is the property of DecoHomz and is protected by copyright and trademark laws. You may not reproduce, distribute, or create derivative works without our express written permission.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('8. User Accounts') }}</h2>
      <p>{{ __('When you create an account with us, you are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.') }}</p>
      <p>{{ __('You agree to notify us immediately of any unauthorized use of your account or any other breach of security.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('9. Limitation of Liability') }}</h2>
      <p>{{ __('To the maximum extent permitted by law, DecoHomz shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of or related to your use of our website or products.') }}</p>
      <p>{{ __('Our total liability to you for any claims arising from the use of our products shall not exceed the amount you paid for the specific product in question.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('10. Indemnification') }}</h2>
      <p>{{ __('You agree to indemnify and hold DecoHomz harmless from any claims, losses, damages, liabilities, costs, and expenses (including legal fees) arising out of or related to your use of our website or violation of these Terms.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('11. Governing Law') }}</h2>
      <p>{{ __('These Terms of Service shall be governed by and construed in accordance with the laws of Egypt. Any disputes arising under these terms shall be subject to the exclusive jurisdiction of the courts in Cairo, Egypt.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('12. Changes to These Terms') }}</h2>
      <p>{{ __('We reserve the right to modify these Terms of Service at any time. Changes will be effective immediately upon posting on this page. Your continued use of the website after any changes constitutes acceptance of the new terms.') }}</p>
    </div>

    <div class="legal-section animate-on-scroll">
      <h2>{{ __('13. Contact Us') }}</h2>
      <p>{{ __('If you have any questions about these Terms of Service, please contact us:') }}</p>
      <p>{{ __('Email: support@decohomz.com') }}</p>
      <p>{{ __('Phone: +20 123 456 7890') }}</p>
      <p>{{ __('Address: Cairo, Egypt') }}</p>
    </div>

  </div>
</div>

@endsection
