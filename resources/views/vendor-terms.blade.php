@extends('layouts.app')

@section('title', 'Vendor Terms & Policies — DecoHomz')

@section('content')
<div style="max-width:800px; margin: 60px auto; padding: 0 20px;">
  
  <div class="content-header" style="margin-bottom:40px; text-align:center;">
    <h1 style="font-size:36px; font-weight:700; color:#1a1a1a; margin-bottom:16px;">Vendor Marketplace Policies &amp; Terms</h1>
    <p style="font-size:18px; color:#666;">Please review our operational, financial, and quality assurance guidelines to ensure a successful partnership.</p>
  </div>

  <div class="policy-container" style="display:flex; flex-direction:column; gap:32px;">
    
    <!-- 1. Financial Policy -->
    <div class="policy-card" style="background:#fff; border:1px solid #e5e5e5; border-radius:12px; padding:32px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
      <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
        <div style="background:#fef3c7; color:#d97706; padding:12px; border-radius:10px;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="10" x2="12" y2="10"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        </div>
        <h3 style="margin:0; font-size:22px; color:#1a1a1a;">1. Financial &amp; Payout Policy</h3>
      </div>
      <div style="color:#444; font-size:16px; line-height:1.7;">
        <p style="margin-bottom:12px;"><strong>15-Day Holding Period:</strong> To protect our customers and ensure product quality, all vendor earnings from sales are initially placed in a <strong>Pending Balance</strong>.</p>
        <p style="margin-bottom:12px;">Funds remain pending for <strong>15 working days</strong> from the date of the sale to cover any potential returns, disputes, or delivery issues.</p>
        <p style="margin-bottom:0;"><strong>Payouts:</strong> Once the 15-working-day hold expires, the funds will automatically move to your <strong>Available (Cleared)</strong> balance. At this point, payouts can be processed to your registered bank account by our finance team.</p>
      </div>
    </div>

    <!-- 2. Product Review & Inspection -->
    <div class="policy-card" style="background:#fff; border:1px solid #e5e5e5; border-radius:12px; padding:32px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
      <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
        <div style="background:#e0f2fe; color:#0284c7; padding:12px; border-radius:10px;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <h3 style="margin:0; font-size:22px; color:#1a1a1a;">2. Product Approval &amp; Warehouse Inspection</h3>
      </div>
      <div style="color:#444; font-size:16px; line-height:1.7;">
        <p style="margin-bottom:12px;">All products submitted through the vendor portal go through a rigorous 2-step review process before being published to the storefront:</p>
        <ol style="margin-bottom:12px; padding-left:24px; color:#1a1a1a;">
          <li style="margin-bottom:8px;"><strong>Digital Review:</strong> Our administrative team reviews your submitted product photos, pricing, and specifications. If everything meets our standards, the product is marked as <strong>Approved</strong>.</li>
          <li style="margin-bottom:8px;"><strong>Warehouse QA:</strong> After digital approval, you must ship the physical product to our warehouse. Our quality assurance team will inspect the item(s) to verify material, dimensions, and build quality.</li>
        </ol>
        <p style="margin-bottom:0;">Only products that physically pass the Warehouse QA inspection will be marked as <strong>Published</strong> and become visible to customers.</p>
      </div>
    </div>

    <!-- 3. Violations & Penalties -->
    <div class="policy-card" style="background:#fff; border:1px solid #e5e5e5; border-radius:12px; padding:32px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
      <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
        <div style="background:#fee2e2; color:#dc2626; padding:12px; border-radius:10px;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <h3 style="margin:0; font-size:22px; color:#1a1a1a;">3. Violations &amp; Quality Failures</h3>
      </div>
      <div style="color:#444; font-size:16px; line-height:1.7;">
        <p style="margin-bottom:12px;">DecoHomz maintains strict quality standards. Violations of our policies will result in penalty points applied to your vendor account.</p>
        <ul style="margin-bottom:12px; padding-left:24px; color:#1a1a1a;">
          <li style="margin-bottom:8px;"><strong>Quality Failures:</strong> If a submitted product completely fails the physical warehouse inspection (0 accepted units), the product is rejected, and a <strong>Quality Failure Violation (3 Points)</strong> is automatically issued.</li>
          <li style="margin-bottom:8px;"><strong>Action Taken:</strong> Accumulating penalty points leads to formal warnings. Excessive violations may result in the temporary suspension or permanent termination of your vendor account.</li>
        </ul>
        <p style="margin-bottom:0;">By submitting an application, you agree to abide by these guidelines and maintain the high quality expected by DecoHomz customers.</p>
      </div>
    </div>

  </div>
</div>
@endsection
