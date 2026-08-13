<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Two-Factor Authentication | NACS-Phil</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body style="min-height:100vh;display:grid;place-items:center;background:#edf5fb;font-family:Inter,system-ui,sans-serif">
<main style="width:min(440px,calc(100% - 30px));background:white;padding:32px;border-radius:20px;box-shadow:0 20px 50px rgba(5,39,75,.12)">
<h1 style="color:#072b55">Two-Factor Authentication</h1><p>Enter the current 6-digit authenticator code or one unused recovery code.</p>
@if($errors->any())<div style="background:#fff0f0;padding:12px;border-radius:10px;color:#8b1e24">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.two-factor.verify') }}" style="display:grid;gap:14px">@csrf
<label>Authenticator Code<input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="32" required style="width:100%;padding:13px;border:1px solid #ccd9e6;border-radius:10px"></label>
<button style="padding:13px;border:0;border-radius:10px;background:#072b55;color:white;font-weight:800">Verify &amp; Continue</button></form>
</main></body></html>
