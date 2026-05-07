<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 — Access Denied — DecoHomz</title>
  <link rel="stylesheet" href="/css/shared.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background: #F5F0E8; color: #2C1F14; min-height: 100vh; display: flex; flex-direction: column; }
    .error-page { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; text-align: center; }
    .error-code { font-size: 120px; font-weight: 800; color: #EDE8E2; line-height: 1; margin-bottom: 16px; }
    .error-title { font-size: 28px; font-weight: 700; color: #2C1F14; margin-bottom: 12px; }
    .error-sub { font-size: 15px; color: #888; max-width: 400px; line-height: 1.6; margin-bottom: 32px; }
    .btn-home { background: #2C1F14; color: #fff; border: none; padding: 14px 32px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; transition: 0.2s; }
    .btn-home:hover { background: #4A3020; }
    .error-illustration { margin-bottom: 32px; opacity: 0.6; }
  </style>
</head>
<body>

<div class="error-page">
  <svg class="error-illustration" viewBox="0 0 200 160" width="200" height="160" fill="none">
    <rect x="20" y="90" width="160" height="50" rx="8" fill="#C4A882" opacity="0.4"/>
    <rect x="20" y="75" width="32" height="45" rx="6" fill="#A07858" opacity="0.4"/>
    <rect x="148" y="75" width="32" height="45" rx="6" fill="#A07858" opacity="0.4"/>
    <text x="100" y="55" text-anchor="middle" font-size="28" font-weight="800" fill="#8B6A48" opacity="0.3">403</text>
  </svg>
  <div class="error-code">403</div>
  <div class="error-title">Access Denied</div>
  <div class="error-sub">You don't have permission to access this page. Please contact support if you believe this is an error.</div>
  <a href="/" class="btn-home">Back to Home</a>
</div>

</body>
</html>
