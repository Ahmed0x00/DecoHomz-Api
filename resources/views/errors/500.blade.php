<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>500 — Server Error — DecoHomz</title>
  <link rel="stylesheet" href="/css/shared.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background: #F5F0E8; color: #2C1F14; min-height: 100vh; display: flex; flex-direction: column; }

    .error-page { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; text-align: center; }
    .error-code { font-size: 120px; font-weight: 800; color: #EDE8E2; line-height: 1; margin-bottom: 16px; }
    .error-title { font-size: 28px; font-weight: 700; color: #2C1F14; margin-bottom: 12px; }
    .error-sub { font-size: 15px; color: #888; max-width: 400px; line-height: 1.6; margin-bottom: 32px; }
    .error-cta { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .btn-home { background: #2C1F14; color: #fff; border: none; padding: 14px 32px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; transition: 0.2s; }
    .btn-home:hover { background: #4A3020; }
    .btn-contact { background: #c9a96e; color: #fff; border: none; padding: 14px 32px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; transition: 0.2s; }
    .btn-contact:hover { background: #b8953d; }

    .error-illustration { margin-bottom: 32px; opacity: 0.6; }
  </style>
</head>
<body>

<div class="error-page">
  <svg class="error-illustration" viewBox="0 0 200 160" width="200" height="160" fill="none">
    <rect x="20" y="90" width="160" height="50" rx="8" fill="#C4A882" opacity="0.4"/>
    <rect x="20" y="75" width="32" height="45" rx="6" fill="#A07858" opacity="0.4"/>
    <rect x="148" y="75" width="32" height="45" rx="6" fill="#A07858" opacity="0.4"/>
    <rect x="52" y="82" width="40" height="58" rx="5" fill="#8B6A48" opacity="0.3"/>
    <rect x="108" y="82" width="40" height="58" rx="5" fill="#8B6A48" opacity="0.3"/>
    <text x="100" y="55" text-anchor="middle" font-size="28" font-weight="800" fill="#8B6A48" opacity="0.3">500</text>
  </svg>

  <div class="error-code">500</div>
  <div class="error-title">Something Went Wrong</div>
  <div class="error-sub">We're experiencing a technical issue. Our team has been notified and is working to fix it.</div>
  <div class="error-cta">
    <a href="/" class="btn-home">Back to Home</a>
    <a href="/contact" class="btn-contact">Contact Support</a>
  </div>
</div>

</body>
</html>
