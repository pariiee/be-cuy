<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 270px;
            --topbar-height: 58px;
            --sidebar-bg: #0f1117;
            --sidebar-hover: #1e2030;
            --sidebar-active: #4f46e5;
            --content-bg: #f8f9fc;
            --card-bg: #ffffff;
            --border: #e5e7eb;
            --text-muted: #6b7280;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', system-ui, sans-serif; background: var(--content-bg); }

        /* ── Top Bar ── */
        .topbar {
            position: fixed; top: 0; left: 0; right: 0; height: var(--topbar-height);
            background: var(--sidebar-bg); border-bottom: 1px solid #1e2030;
            display: flex; align-items: center; padding: 0 20px; gap: 16px; z-index: 1000;
        }
        .topbar-brand { color: #fff; font-weight: 700; font-size: 16px; text-decoration: none; white-space: nowrap; }
        .topbar-brand span { color: #818cf8; }
        .topbar-version { background: #1e2030; color: #818cf8; font-size: 11px; padding: 2px 8px; border-radius: 20px; }
        .token-wrap { display: flex; align-items: center; gap: 8px; margin-left: auto; max-width: 460px; width: 100%; }
        .token-wrap label { color: #9ca3af; font-size: 12px; white-space: nowrap; }
        .token-wrap input {
            flex: 1; background: #1e2030; border: 1px solid #374151; border-radius: 6px;
            color: #e5e7eb; padding: 6px 10px; font-size: 12px; font-family: monospace;
        }
        .token-wrap input:focus { outline: none; border-color: #4f46e5; }
        .btn-save-token { background: #4f46e5; color: #fff; border: none; border-radius: 6px; padding: 6px 14px; font-size: 12px; cursor: pointer; white-space: nowrap; }
        .btn-save-token:hover { background: #4338ca; }
        .token-status { font-size: 11px; color: #6b7280; white-space: nowrap; }
        .token-status.active { color: #34d399; }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed; top: var(--topbar-height); left: 0; bottom: 0;
            width: var(--sidebar-width); background: var(--sidebar-bg);
            overflow-y: auto; padding: 16px 0; z-index: 999;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: #374151; border-radius: 2px; }
        .sidebar-section-title {
            color: #4b5563; font-size: 10px; font-weight: 700; letter-spacing: .08em;
            text-transform: uppercase; padding: 12px 20px 4px;
        }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px; padding: 7px 20px;
            color: #9ca3af; font-size: 13px; text-decoration: none; transition: all .15s;
            border-left: 3px solid transparent;
        }
        .sidebar-link:hover { color: #e5e7eb; background: var(--sidebar-hover); }
        .sidebar-link.active { color: #e5e7eb; border-left-color: var(--sidebar-active); background: #1a1d2e; }
        .method-pill {
            font-size: 9px; font-weight: 700; padding: 1px 6px; border-radius: 3px;
            letter-spacing: .05em; flex-shrink: 0;
        }
        .pill-get { background: #1d4ed8; color: #bfdbfe; }
        .pill-post { background: #166534; color: #bbf7d0; }
        .pill-put { background: #92400e; color: #fde68a; }
        .pill-delete { background: #7f1d1d; color: #fecaca; }

        /* ── Main Content ── */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 32px 40px; max-width: 960px;
        }

        /* ── Section Header ── */
        .section-header { margin: 40px 0 16px; }
        .section-header h2 { font-size: 22px; font-weight: 700; margin: 0 0 4px; color: #111827; }
        .section-header p { font-size: 14px; color: var(--text-muted); margin: 0; }
        .section-divider { border: none; border-top: 1px solid var(--border); margin: 0 0 24px; }

        /* ── Endpoint Card ── */
        .endpoint-card {
            background: var(--card-bg); border: 1px solid var(--border);
            border-radius: 10px; margin-bottom: 16px; overflow: hidden;
        }
        .endpoint-header {
            display: flex; align-items: center; gap: 12px; padding: 14px 18px;
            cursor: pointer; user-select: none; transition: background .15s;
        }
        .endpoint-header:hover { background: #f9fafb; }
        .endpoint-header.open { border-bottom: 1px solid var(--border); }
        .method-badge {
            font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 5px;
            letter-spacing: .06em; flex-shrink: 0;
        }
        .badge-get { background: #dbeafe; color: #1d4ed8; }
        .badge-post { background: #dcfce7; color: #166534; }
        .badge-put { background: #fef3c7; color: #92400e; }
        .badge-delete { background: #fee2e2; color: #7f1d1d; }
        .endpoint-path { font-family: monospace; font-size: 14px; font-weight: 600; color: #111827; flex: 1; }
        .endpoint-title { font-size: 13px; color: var(--text-muted); }
        .badge-auth { font-size: 10px; background: #fef9c3; color: #a16207; padding: 2px 8px; border-radius: 20px; }
        .badge-public { font-size: 10px; background: #f0fdf4; color: #15803d; padding: 2px 8px; border-radius: 20px; }
        .badge-rate { font-size: 10px; background: #f0f0f0; color: #6b7280; padding: 2px 8px; border-radius: 20px; }
        .chevron { color: #9ca3af; transition: transform .2s; margin-left: auto; flex-shrink: 0; }
        .chevron.open { transform: rotate(180deg); }

        .endpoint-body { padding: 18px 20px; display: none; }
        .endpoint-body.show { display: block; }

        .endpoint-desc { font-size: 14px; color: #374151; margin-bottom: 16px; line-height: 1.6; }

        /* ── Tables ── */
        .params-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 16px; }
        .params-table th {
            background: #f3f4f6; color: #374151; font-weight: 600; font-size: 11px;
            text-transform: uppercase; letter-spacing: .06em; padding: 8px 12px; text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .params-table td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; color: #374151; vertical-align: top; }
        .params-table tr:last-child td { border-bottom: none; }
        .param-name { font-family: monospace; font-weight: 600; color: #4f46e5; }
        .param-type { font-family: monospace; font-size: 11px; color: #6b7280; }
        .param-required { font-size: 10px; background: #fee2e2; color: #b91c1c; padding: 1px 6px; border-radius: 3px; }
        .param-optional { font-size: 10px; background: #f3f4f6; color: #6b7280; padding: 1px 6px; border-radius: 3px; }

        /* ── Code Blocks ── */
        .code-label { font-size: 11px; font-weight: 600; color: var(--text-muted); letter-spacing: .06em; text-transform: uppercase; margin-bottom: 6px; }
        .code-wrap { position: relative; }
        .code-wrap pre { margin: 0; border-radius: 8px; font-size: 13px; }
        .btn-copy {
            position: absolute; top: 8px; right: 8px; background: rgba(255,255,255,.1);
            color: #9ca3af; border: none; border-radius: 4px; padding: 4px 10px;
            font-size: 11px; cursor: pointer; transition: all .15s;
        }
        .btn-copy:hover { background: rgba(255,255,255,.2); color: #fff; }
        .btn-copy.copied { background: #166534; color: #bbf7d0; }

        /* ── Tester ── */
        .btn-try {
            background: #4f46e5; color: #fff; border: none; border-radius: 6px;
            padding: 6px 14px; font-size: 12px; cursor: pointer; margin-top: 12px;
        }
        .btn-try:hover { background: #4338ca; }
        .tester-panel { margin-top: 12px; background: #0f1117; border-radius: 8px; overflow: hidden; }
        .tester-tabs { display: flex; border-bottom: 1px solid #1e2030; }
        .tester-tab { padding: 8px 16px; font-size: 12px; color: #6b7280; cursor: pointer; border-bottom: 2px solid transparent; }
        .tester-tab.active { color: #818cf8; border-bottom-color: #818cf8; }
        .tester-body-input {
            width: 100%; background: transparent; border: none; color: #e5e7eb;
            font-family: monospace; font-size: 12px; padding: 12px 16px; resize: vertical;
            min-height: 120px; outline: none;
        }
        .tester-footer { display: flex; align-items: center; gap: 10px; padding: 10px 16px; border-top: 1px solid #1e2030; }
        .btn-send { background: #4f46e5; color: #fff; border: none; border-radius: 6px; padding: 6px 16px; font-size: 12px; cursor: pointer; }
        .btn-send:hover { background: #4338ca; }
        .btn-send:disabled { background: #374151; cursor: not-allowed; }
        .tester-status { font-size: 12px; font-family: monospace; }
        .status-2xx { color: #34d399; }
        .status-4xx { color: #f87171; }
        .status-5xx { color: #f87171; }
        .tester-response { background: #0f1117; border-top: 1px solid #1e2030; }
        .tester-response pre { margin: 0; border-radius: 0; max-height: 300px; overflow-y: auto; font-size: 12px; }

        /* ── Sandbox Form ── */
        .sandbox-form { background:#0f1117; border-radius:8px; padding:14px 16px; margin-top:12px; }
        .sandbox-form-title { font-size:11px; font-weight:600; color:#818cf8; text-transform:uppercase; letter-spacing:.06em; margin-bottom:10px; }
        .sf-row { margin-bottom:8px; }
        .sf-label { font-size:11px; color:#9ca3af; display:block; margin-bottom:3px; }
        .sf-label span { font-size:10px; color:#f87171; margin-left:4px; }
        .sf-input, .sf-select { width:100%; background:#1e2030; border:1px solid #374151; border-radius:5px; color:#e5e7eb; padding:6px 10px; font-size:12px; font-family:monospace; outline:none; }
        .sf-input:focus, .sf-select:focus { border-color:#4f46e5; }
        .sf-select option { background:#1e2030; }
        .sf-note { font-size:11px; color:#6b7280; margin-top:4px; font-style:italic; }
        .sf-footer { display:flex; align-items:center; gap:10px; margin-top:10px; padding-top:10px; border-top:1px solid #1e2030; }
        .sf-response { margin-top:8px; border-top:1px solid #1e2030; }
        .sf-response pre { margin:0; border-radius:0; max-height:280px; overflow-y:auto; font-size:12px; }
        .sf-hint { background:#1e2030; border-radius:4px; padding:6px 10px; font-size:11px; color:#9ca3af; margin-top:8px; font-family:monospace; }
        /* ── Inquiry Reference Table ── */
        .inquiry-ref { background:#fff; border:1px solid var(--border); border-radius:10px; margin-bottom:16px; overflow:hidden; }
        .inquiry-ref-header { background:#f9fafb; padding:10px 18px; font-weight:700; font-size:13px; color:#111827; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px; cursor:pointer; user-select:none; }
        .inquiry-ref-header:hover { background:#f3f4f6; }
        .inquiry-ref-body { padding:0; display:none; }
        .inquiry-ref-body.open { display:block; }
        .ref-toggle-btn { margin-left:auto; width:22px; height:22px; border-radius:50%; border:1.5px solid #d1d5db; background:#fff; color:#374151; font-size:16px; font-weight:700; line-height:1; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; transition:all .15s; }
        .ref-toggle-btn:hover { background:#e5e7eb; border-color:#9ca3af; }
        .inquiry-cat { padding:10px 18px 6px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#6b7280; border-top:1px solid #f3f4f6; }
        .inquiry-cat:first-child { border-top:none; }
        .inquiry-row { display:flex; align-items:center; gap:0; padding:6px 18px; border-bottom:1px solid #f9fafb; font-size:13px; }
        .inquiry-row:last-child { border-bottom:none; }
        .inquiry-slug { font-family:monospace; font-weight:600; color:#4f46e5; width:160px; flex-shrink:0; }
        .inquiry-label { color:#374151; flex:1; }
        .inquiry-param { font-family:monospace; font-size:11px; color:#6b7280; width:80px; flex-shrink:0; }
        .inquiry-example { font-family:monospace; font-size:11px; color:#059669; }

        /* ── Base URL Banner ── */
        .base-url-banner {
            background: var(--card-bg); border: 1px solid var(--border); border-radius: 10px;
            padding: 16px 20px; margin-bottom: 28px; display: flex; align-items: center; gap: 12px;
        }
        .base-url-label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .06em; }
        .base-url-value { font-family: monospace; font-size: 14px; color: #4f46e5; font-weight: 600; }

        /* ── Hero ── */
        .docs-hero { padding: 8px 0 28px; }
        .docs-hero h1 { font-size: 28px; font-weight: 800; color: #111827; margin: 0 0 8px; }
        .docs-hero p { font-size: 15px; color: var(--text-muted); margin: 0; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; padding: 16px; }
            .token-wrap { max-width: 240px; }
        }
    </style>
</head>
<body>

{{-- ── Top Bar ── --}}
<div class="topbar">
    <a href="/admin" class="topbar-brand">{{ config('app.name') }} <span>API</span></a>
    <span class="topbar-version">v1.0</span>
    <button id="modeToggle" onclick="toggleMode()" style="border:none;padding:2px 10px;border-radius:4px;font-size:11px;font-weight:600;cursor:pointer;background:#166534;color:#bbf7d0;transition:all .2s;" title="Klik untuk ganti mode">SANDBOX</button>
    <a href="/docs/sandbox" style="background:#1e2030;color:#818cf8;border:1px solid #374151;border-radius:4px;padding:2px 10px;font-size:11px;font-weight:600;text-decoration:none;white-space:nowrap;" title="Buka API Sandbox Lengkap">⚡ Full Sandbox</a>
    <div class="token-wrap">
        <label for="apiKeyInput"><i class="bi bi-key"></i> API Key:</label>
        <input type="password" id="apiKeyInput" placeholder="wtu_..." autocomplete="off">
        <button class="btn-save-token" onclick="saveCredentials()">Simpan</button>
        <span class="token-status" id="tokenStatus">Belum ada key</span>
    </div>
    <a href="/admin" style="color:#6b7280;font-size:12px;text-decoration:none;margin-left:8px;" title="Kembali ke Filament">
        <i class="bi bi-house"></i>
    </a>
</div>

{{-- ── Sidebar ── --}}
<nav class="sidebar" id="sidebar">
    <div class="sidebar-section-title">Autentikasi</div>
    <a href="#auth" class="sidebar-link"><span class="method-pill pill-post">POST</span> Register</a>
    <a href="#login" class="sidebar-link"><span class="method-pill pill-post">POST</span> Login</a>
    <a href="#verify-otp" class="sidebar-link"><span class="method-pill pill-post">POST</span> Verify OTP</a>
    <a href="#resend-otp" class="sidebar-link"><span class="method-pill pill-post">POST</span> Resend OTP</a>
    <a href="#forgot-password" class="sidebar-link"><span class="method-pill pill-post">POST</span> Lupa Password</a>
    <a href="#reset-password" class="sidebar-link"><span class="method-pill pill-post">POST</span> Reset Password</a>
    <a href="#logout" class="sidebar-link"><span class="method-pill pill-post">POST</span> Logout</a>
    <a href="#me" class="sidebar-link"><span class="method-pill pill-get">GET</span> Profil</a>
    <a href="#regenerate-key" class="sidebar-link"><span class="method-pill pill-post">POST</span> Regenerate API Key</a>
    <a href="#update-me" class="sidebar-link"><span class="method-pill pill-put">PUT</span> Update Profil</a>
    <a href="#change-password" class="sidebar-link"><span class="method-pill pill-put">PUT</span> Ganti Password</a>

    <div class="sidebar-section-title">Produk</div>
    <a href="#okeconnect-ref" class="sidebar-link" style="font-size:12px;"><i class="bi bi-table me-1"></i> Ref. Kategori OkeConnect</a>
    <a href="#categories" class="sidebar-link"><span class="method-pill pill-get">GET</span> Kategori</a>
    <a href="#products" class="sidebar-link"><span class="method-pill pill-get">GET</span> Daftar Produk</a>
    <a href="#digital-products" class="sidebar-link"><span class="method-pill pill-get">GET</span> productv1 (List)</a>
    <a href="#digital-products-detail" class="sidebar-link"><span class="method-pill pill-get">GET</span> productv1 (Detail)</a>
    <a href="#category-by-app" class="sidebar-link"><span class="method-pill pill-get">GET</span> Produk per App</a>

    <div class="sidebar-section-title">E-Wallet Nominal Bebas</div>
    <a href="#ewallet-options" class="sidebar-link"><span class="method-pill pill-get">GET</span> Provider & Biaya</a>
    <a href="#ewallet-topup" class="sidebar-link"><span class="method-pill pill-post">POST</span> Top Up E-Wallet</a>

    <div class="sidebar-section-title">Inquiry / Cek ID</div>
    <a href="#inquiry-ref" class="sidebar-link" style="font-size:12px;"><i class="bi bi-table me-1"></i> Ref. Produk & Game</a>
    <a href="#bank-codes-ref" class="sidebar-link" style="font-size:12px;"><i class="bi bi-bank me-1"></i> Kode Bank</a>
    <a href="#inquiry-check" class="sidebar-link"><span class="method-pill pill-post">POST</span> Cek ID / Sandbox</a>
    <a href="#inquiry-products" class="sidebar-link"><span class="method-pill pill-get">GET</span> Produk Inquiry</a>

    <div class="sidebar-section-title">Transaksi</div>
    <a href="#digital-order" class="sidebar-link"><span class="method-pill pill-post">POST</span> Beli Produk Digital</a>
    <a href="#transactions" class="sidebar-link"><span class="method-pill pill-post">POST</span> Buat Transaksi</a>
    <a href="#orders-list" class="sidebar-link"><span class="method-pill pill-get">GET</span> Riwayat Order</a>
    <a href="#orders-show" class="sidebar-link"><span class="method-pill pill-get">GET</span> Detail Order</a>
    <a href="#orders-invoice" class="sidebar-link"><span class="method-pill pill-get">GET</span> Invoice</a>

    <div class="sidebar-section-title">Deposit / Top Up</div>
    <a href="#deposits-store" class="sidebar-link"><span class="method-pill pill-post">POST</span> Buat Deposit</a>
    <a href="#deposits-list" class="sidebar-link"><span class="method-pill pill-get">GET</span> Riwayat Deposit</a>
    <a href="#deposits-check" class="sidebar-link"><span class="method-pill pill-get">GET</span> Cek Status Pembayaran</a>

    <div class="sidebar-section-title">SMM Panel</div>
    <a href="#smm-apps-ref" class="sidebar-link" style="font-size:12px;"><i class="bi bi-table me-1"></i> Ref. Layanan per App</a>
    <a href="#smm-apps" class="sidebar-link"><span class="method-pill pill-get">GET</span> List Apps</a>
    <a href="#smm-search" class="sidebar-link"><span class="method-pill pill-get">GET</span> Cari per App</a>
    <a href="#smm-services" class="sidebar-link"><span class="method-pill pill-get">GET</span> Semua Layanan</a>
    <a href="#smm-order" class="sidebar-link"><span class="method-pill pill-post">POST</span> Order SMM</a>
    <a href="#smm-status" class="sidebar-link"><span class="method-pill pill-get">GET</span> Status SMM</a>

    <div class="sidebar-section-title">Redeem Code</div>
    <a href="#redeem-validate" class="sidebar-link"><span class="method-pill pill-post">POST</span> Validasi Kode</a>
    <a href="#redeem-apply" class="sidebar-link"><span class="method-pill pill-post">POST</span> Pakai Kode</a>
</nav>

{{-- ── Main Content ── --}}
<div class="main-content">

    <div class="docs-hero">
        <h1>{{ config('app.name') }} — API Reference</h1>
        <p>Dokumentasi lengkap semua endpoint REST API. Semua endpoint (kecuali Register/Login/OTP) wajib menyertakan <code>x-api-key</code> di header. Endpoint bertanda <span style="background:#fef9c3;color:#a16207;font-size:10px;padding:1px 6px;border-radius:10px;font-weight:600;">Auth Required</span> butuh API Key dari akun yang sudah login.</p>
    </div>

    <div class="base-url-banner">
        <span class="base-url-label">Base URL</span>
        <span class="base-url-value" id="baseUrlDisplay"></span>
        <button class="btn-copy ms-auto" onclick="copyText(BASE_URL, this)"><i class="bi bi-clipboard"></i> Copy</button>
    </div>

    {{-- ════════════════════════════════════════
         AUTH
    ════════════════════════════════════════ --}}
    <div class="section-header" id="auth">
        <h2><i class="bi bi-shield-lock text-primary me-2"></i>Autentikasi</h2>
        <p>Endpoint register, login, OTP verifikasi, reset password, dan manajemen sesi.</p>
    </div>
    <hr class="section-divider">

    @php
    $baseUrl = rtrim(config('app.url'), '/');
    @endphp

    {{-- Auth Info Box --}}
    <div style="background:#1e2030;border:1px solid #374151;border-left:4px solid #818cf8;border-radius:8px;padding:20px 24px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <i class="bi bi-key-fill" style="color:#818cf8;font-size:18px;"></i>
            <span style="color:#e5e7eb;font-weight:700;font-size:15px;">Cara Menggunakan API Key</span>
        </div>
        <p style="color:#9ca3af;font-size:13px;margin-bottom:12px;">
            Setelah <strong style="color:#e5e7eb;">login</strong> atau <strong style="color:#e5e7eb;">verify-otp</strong>, kamu mendapat <code style="color:#818cf8;">api_key</code>.
            Sertakan di <strong style="color:#e5e7eb;">setiap request</strong> — baik yang bertanda
            <span style="background:#f0fdf4;color:#15803d;font-size:10px;padding:1px 8px;border-radius:20px;font-weight:600;">Public</span> maupun
            <span style="background:#fef9c3;color:#a16207;font-size:10px;padding:1px 8px;border-radius:20px;font-weight:600;">Auth Required</span>.
            Cukup satu header, tidak perlu Bearer Token.
        </p>
        <div style="margin-bottom:10px;">
            <span style="color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">HEADER</span>
            <div style="background:#111827;border-radius:6px;padding:10px 14px;margin-top:6px;font-family:monospace;font-size:13px;">
                <span style="color:#818cf8;">x-api-key</span><span style="color:#9ca3af;">: </span><span style="color:#fbbf24;">wtu_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</span>
            </div>
        </div>
        <div style="margin-bottom:14px;">
            <span style="color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">CONTOH CURL</span>
            <div style="background:#111827;border-radius:6px;padding:10px 14px;margin-top:6px;font-family:monospace;font-size:12px;color:#e5e7eb;white-space:pre-wrap;word-break:break-all;">curl -X GET {{ rtrim(config('app.url'), '/') }}/api/me \
  -H <span style="color:#fbbf24;">"x-api-key: wtu_xxxxxxxx..."</span> \
  -H <span style="color:#fbbf24;">"Accept: application/json"</span></div>
        </div>
        <div style="background:#111827;border-radius:6px;padding:10px 14px;margin-bottom:14px;">
            <span style="color:#6b7280;font-size:11px;text-transform:uppercase;">FORMAT API KEY</span>
            <div style="margin-top:6px;font-family:monospace;font-size:12px;">
                <span style="color:#34d399;">wtu_</span><span style="color:#e5e7eb;">xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</span>
                <span style="color:#6b7280;"> (44 karakter)</span>
            </div>
        </div>
        <p style="color:#6b7280;font-size:12px;margin:0;">
            <i class="bi bi-info-circle me-1"></i>
            Masukkan API Key di kolom <strong style="color:#9ca3af;">API Key</strong> pojok kanan atas untuk mencoba endpoint langsung dari halaman ini.
        </p>
    </div>

    {{-- Register --}}
    <div class="endpoint-card" id="register">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/register</span>
            <span class="endpoint-title d-none d-md-inline">Daftar akun baru</span>
            <span class="badge-public">Public</span>
            <span class="badge-rate">3/menit</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Membuat akun baru. Setelah berhasil, kode OTP 6 digit akan dikirim ke email. <strong>Token belum dikembalikan</strong> — perlu verifikasi OTP dulu via <code>/api/verify-otp</code>.</p>
            <div class="code-label">Request Body</div>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">name</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Nama lengkap</td></tr>
                    <tr><td><span class="param-name">email</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Email unik</td></tr>
                    <tr><td><span class="param-name">password</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Min 8 karakter</td></tr>
                    <tr><td><span class="param-name">password_confirmation</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Konfirmasi password</td></tr>
                    <tr><td><span class="param-name">phone</span></td><td><span class="param-type">string</span></td><td><span class="param-optional">opsional</span> Nomor HP</td></tr>
                </tbody>
            </table>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="code-label">Contoh Request</div>
                    <div class="code-wrap"><pre><code class="language-json">{
  "name": "Budi Santoso",
  "email": "budi@gmail.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890"
}</code></pre><button class="btn-copy" onclick="copyCode(this)">Copy</button></div>
                </div>
                <div class="col-md-6">
                    <div class="code-label">Contoh Response <span class="text-success">201</span></div>
                    <div class="code-wrap"><pre><code class="language-json">{
  "success": true,
  "message": "Registrasi berhasil. Silakan cek email Anda untuk kode OTP verifikasi.",
  "data": { "email": "budi@gmail.com" }
}</code></pre></div>
                </div>
            </div>
            <div class="sandbox-form">
                <div class="sandbox-form-title"><i class="bi bi-terminal me-1"></i>Sandbox — Register</div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="sf-row">
                            <label class="sf-label">name <span>*</span></label>
                            <input type="text" class="sf-input" id="reg-name" placeholder="Budi Santoso" value="Budi Santoso">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sf-row">
                            <label class="sf-label">phone</label>
                            <input type="text" class="sf-input" id="reg-phone" placeholder="081234567890">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sf-row">
                            <label class="sf-label">email <span>*</span></label>
                            <input type="email" class="sf-input" id="reg-email" placeholder="budi@gmail.com" value="budi@gmail.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sf-row">
                            <label class="sf-label">password <span>*</span> <span style="color:#6b7280;font-size:10px;">(min 8)</span></label>
                            <input type="password" class="sf-input" id="reg-password" placeholder="password123" value="password123">
                        </div>
                    </div>
                </div>
                <div class="sf-footer">
                    <button class="btn-send" onclick="sendRegister()"><i class="bi bi-send"></i> Register</button>
                    <span class="tester-status" id="reg-status"></span>
                </div>
                <div class="sf-response" id="reg-resp" style="display:none;">
                    <pre><code class="language-json" id="reg-code"></code></pre>
                </div>
            </div>
        </div>
    </div>

    {{-- Login --}}
    <div class="endpoint-card" id="login">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/login</span>
            <span class="endpoint-title d-none d-md-inline">Login</span>
            <span class="badge-public">Public</span>
            <span class="badge-rate">5/menit</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Login dengan email dan password. Akan diblokir jika email belum diverifikasi OTP, atau akun dibanned.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">email</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span></td></tr>
                    <tr><td><span class="param-name">password</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span></td></tr>
                </tbody>
            </table>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="code-label">Contoh Request</div>
                    <div class="code-wrap"><pre><code class="language-json">{
  "email": "budi@gmail.com",
  "password": "password123"
}</code></pre><button class="btn-copy" onclick="copyCode(this)">Copy</button></div>
                </div>
                <div class="col-md-6">
                    <div class="code-label">Contoh Response <span class="text-success">200</span></div>
                    <div class="code-wrap"><pre><code class="language-json">{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { "id": 1, "name": "Budi", "role": "member", "balance": 50000 },
    "token": "1|abc123xyz..."
  }
}</code></pre></div>
                </div>
            </div>
            <div class="sandbox-form">
                <div class="sandbox-form-title"><i class="bi bi-terminal me-1"></i>Sandbox — Login</div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="sf-row">
                            <label class="sf-label">email <span>*</span></label>
                            <input type="email" class="sf-input" id="login-email" placeholder="budi@gmail.com" value="budi@gmail.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sf-row">
                            <label class="sf-label">password <span>*</span></label>
                            <input type="password" class="sf-input" id="login-password" placeholder="password123" value="password123">
                        </div>
                    </div>
                </div>
                <div class="sf-footer">
                    <button class="btn-send" onclick="sendLogin()"><i class="bi bi-send"></i> Login</button>
                    <span class="tester-status" id="login-status"></span>
                </div>
                <div class="sf-response" id="login-resp" style="display:none;">
                    <pre><code class="language-json" id="login-code"></code></pre>
                </div>
            </div>
        </div>
    </div>

    {{-- Verify OTP --}}
    <div class="endpoint-card" id="verify-otp">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/verify-otp</span>
            <span class="endpoint-title d-none d-md-inline">Verifikasi OTP Email</span>
            <span class="badge-public">Public</span>
            <span class="badge-rate">10/menit</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Verifikasi kode OTP 6 digit yang dikirim ke email saat register. Setelah berhasil, token Sanctum dikembalikan dan akun aktif.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">email</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span></td></tr>
                    <tr><td><span class="param-name">otp</span></td><td><span class="param-type">string(6)</span></td><td><span class="param-required">wajib</span> Kode 6 digit dari email</td></tr>
                </tbody>
            </table>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="code-wrap"><pre><code class="language-json">{
  "email": "budi@gmail.com",
  "otp": "123456"
}</code></pre><button class="btn-copy" onclick="copyCode(this)">Copy</button></div>
                </div>
                <div class="col-md-6">
                    <div class="code-wrap"><pre><code class="language-json">{
  "success": true,
  "message": "Email berhasil diverifikasi. Selamat datang!",
  "data": {
    "user": { "id": 1, "name": "Budi", "email_verified_at": "..." },
    "token": "1|abc123xyz..."
  }
}</code></pre></div>
                </div>
            </div>
            <button class="btn-try" onclick="openTester(this, 'POST', '/api/verify-otp', false, '{\n  \"email\": \"budi@gmail.com\",\n  \"otp\": \"123456\"\n}')">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- Resend OTP --}}
    <div class="endpoint-card" id="resend-otp">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/resend-otp</span>
            <span class="endpoint-title d-none d-md-inline">Kirim Ulang OTP</span>
            <span class="badge-public">Public</span>
            <span class="badge-rate">5/menit · 3x/10mnt</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Kirim ulang kode OTP ke email. Maksimal 3 permintaan per 10 menit per email.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">email</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span></td></tr>
                </tbody>
            </table>
            <button class="btn-try" onclick="openTester(this, 'POST', '/api/resend-otp', false, '{\n  \"email\": \"budi@gmail.com\"\n}')">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- Forgot Password --}}
    <div class="endpoint-card" id="forgot-password">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/forgot-password</span>
            <span class="endpoint-title d-none d-md-inline">Lupa Password</span>
            <span class="badge-public">Public</span>
            <span class="badge-rate">5/menit · 3x/10mnt</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Kirim OTP reset password ke email. Gunakan kode ini di <code>/api/reset-password</code>. Selalu mengembalikan sukses untuk mencegah email enumeration.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">email</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span></td></tr>
                </tbody>
            </table>
            <button class="btn-try" onclick="openTester(this, 'POST', '/api/forgot-password', false, '{\n  \"email\": \"budi@gmail.com\"\n}')">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- Reset Password --}}
    <div class="endpoint-card" id="reset-password">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/reset-password</span>
            <span class="endpoint-title d-none d-md-inline">Reset Password</span>
            <span class="badge-public">Public</span>
            <span class="badge-rate">5/menit</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Verifikasi OTP dan set password baru sekaligus. Semua token lama akan direvoke — user harus login ulang.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">email</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span></td></tr>
                    <tr><td><span class="param-name">otp</span></td><td><span class="param-type">string(6)</span></td><td><span class="param-required">wajib</span> Dari email reset</td></tr>
                    <tr><td><span class="param-name">password</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Min 8 karakter</td></tr>
                    <tr><td><span class="param-name">password_confirmation</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span></td></tr>
                </tbody>
            </table>
            <button class="btn-try" onclick="openTester(this, 'POST', '/api/reset-password', false, '{\n  \"email\": \"budi@gmail.com\",\n  \"otp\": \"123456\",\n  \"password\": \"newpassword123\",\n  \"password_confirmation\": \"newpassword123\"\n}')">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- Logout --}}
    <div class="endpoint-card" id="logout">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/logout</span>
            <span class="endpoint-title d-none d-md-inline">Logout</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Logout dari sesi aktif. Membutuhkan header <code>x-api-key</code>.</p>
            <button class="btn-try" onclick="openTester(this, 'POST', '/api/logout', true, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- Me --}}
    <div class="endpoint-card" id="me">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/me</span>
            <span class="endpoint-title d-none d-md-inline">Data profil saya</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Mendapatkan data profil user yang sedang login.</p>
            <div class="code-label">Contoh Response <span class="text-success">200</span></div>
            <div class="code-wrap"><pre><code class="language-json">{
  "success": true,
  "data": {
    "id": 1,
    "name": "Budi Santoso",
    "email": "budi@gmail.com",
    "phone": "081234567890",
    "role": "member",
    "balance": 150000.00,
    "is_banned": false,
    "email_verified_at": "2026-05-01T00:00:00+07:00",
    "created_at": "2026-05-01T00:00:00+07:00"
  }
}</code></pre><button class="btn-copy" onclick="copyCode(this)">Copy</button></div>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/me', true, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- Regenerate API Key --}}
    <div class="endpoint-card" id="regenerate-key">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/me/regenerate-key</span>
            <span class="endpoint-title d-none d-md-inline">Regenerate API Key</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Generate ulang API Key akun kamu. Key lama langsung tidak bisa dipakai — update semua client yang menggunakan key lama.</p>
            <div class="code-label">Contoh Response <span class="text-success">200</span></div>
            <div class="code-wrap"><pre><code class="language-json">{
  "success": true,
  "message": "API Key berhasil diperbarui.",
  "data": {
    "api_key": "wtu_newKeyxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}</code></pre><button class="btn-copy" onclick="copyCode(this)">Copy</button></div>
            <button class="btn-try" onclick="openTester(this, 'POST', '/api/me/regenerate-key', true, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- Update Me --}}
    <div class="endpoint-card" id="update-me">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-put">PUT</span>
            <span class="endpoint-path">/api/me</span>
            <span class="endpoint-title d-none d-md-inline">Update profil</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">name</span></td><td><span class="param-type">string</span></td><td><span class="param-optional">opsional</span></td></tr>
                    <tr><td><span class="param-name">phone</span></td><td><span class="param-type">string</span></td><td><span class="param-optional">opsional</span></td></tr>
                </tbody>
            </table>
            <button class="btn-try" onclick="openTester(this, 'PUT', '/api/me', true, '{\n  \"name\": \"Nama Baru\",\n  \"phone\": \"089999999999\"\n}')">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="endpoint-card" id="change-password">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-put">PUT</span>
            <span class="endpoint-path">/api/me/password</span>
            <span class="endpoint-title d-none d-md-inline">Ganti password</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">current_password</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span></td></tr>
                    <tr><td><span class="param-name">password</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Min 8 karakter</td></tr>
                    <tr><td><span class="param-name">password_confirmation</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span></td></tr>
                </tbody>
            </table>
            <button class="btn-try" onclick="openTester(this, 'PUT', '/api/me/password', true, '{\n  \"current_password\": \"password123\",\n  \"password\": \"newpassword123\",\n  \"password_confirmation\": \"newpassword123\"\n}')">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         PRODUK
    ════════════════════════════════════════ --}}
    <div class="section-header" id="produk">
        <h2><i class="bi bi-box-seam text-success me-2"></i>Produk</h2>
        <p>Daftar kategori dan produk top-up. Semua endpoint publik, tidak perlu token.</p>
    </div>
    <hr class="section-divider">

    {{-- OkeConnect Product Category Reference --}}
    <div class="inquiry-ref" id="okeconnect-ref">
        <div class="inquiry-ref-header" onclick="toggleRef('okeconnect-ref-body')">
            <i class="bi bi-grid-3x3-gap-fill text-success"></i> Referensi Kategori Produk OkeConnect
            <span style="font-size:11px;font-weight:400;color:#6b7280;">Gunakan nilai <code style="background:#f3f4f6;padding:1px 5px;border-radius:3px;">category</code> saat POST ke <code style="background:#f3f4f6;padding:1px 5px;border-radius:3px;">/api/transactions</code></span>
            <button class="ref-toggle-btn" id="okeconnect-ref-btn">+</button>
        </div>
        <div class="inquiry-ref-body" id="okeconnect-ref-body">
            <div class="inquiry-cat">📱 Pulsa</div>
            <div class="inquiry-row"><span class="inquiry-slug">pulsa</span><span class="inquiry-label">Pulsa reguler semua operator (Telkomsel, Indosat, XL, Tri, Axis, Smartfren, dll)</span></div>

            <div class="inquiry-cat">🌐 Kuota Internet</div>
            <div class="inquiry-row"><span class="inquiry-slug">kuota_nasional</span><span class="inquiry-label">Kuota internet nasional — semua operator</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">kuota_telkomsel</span><span class="inquiry-label">Kuota internet Telkomsel (0811–0813, 0821–0823, 0851–0853)</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">kuota_byu</span><span class="inquiry-label">Kuota internet by.U — anak Telkomsel (0851)</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">kuota_indosat</span><span class="inquiry-label">Kuota internet Indosat / IM3 (0814–0816, 0855–0858)</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">kuota_tri</span><span class="inquiry-label">Kuota internet 3 / Tri (0895–0899)</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">kuota_xl</span><span class="inquiry-label">Kuota internet XL Axiata (0817–0819, 0859, 0877–0878)</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">kuota_axis</span><span class="inquiry-label">Kuota internet Axis / anak XL (0831–0833, 0838)</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">kuota_smartfren</span><span class="inquiry-label">Kuota internet Smartfren (0881–0889)</span></div>

            <div class="inquiry-cat">📦 Paket Bundling</div>
            <div class="inquiry-row"><span class="inquiry-slug">bulk_telkomsel</span><span class="inquiry-label">Paket bundling Telkomsel (combo pulsa + kuota + telepon)</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">bulk_cashback</span><span class="inquiry-label">Paket promo / cashback operator</span></div>

            <div class="inquiry-cat">⚡ Token & Listrik</div>
            <div class="inquiry-row"><span class="inquiry-slug">token_pln</span><span class="inquiry-label">Token listrik PLN prabayar — nomor meter 11 digit</span></div>

            <div class="inquiry-cat">💚 E-Wallet / Saldo</div>
            <div class="inquiry-row"><span class="inquiry-slug">ewallet</span><span class="inquiry-label">E-Wallet nominal bebas (GoPay, OVO, Dana, ShopeePay, LinkAja, dll) — gunakan <code>/api/ewallet/topup</code></span></div>

            <div class="inquiry-cat">🧾 Tagihan / Pascabayar</div>
            <div class="inquiry-row"><span class="inquiry-slug">tagihan</span><span class="inquiry-label">Tagihan umum (PLN pascabayar, BPJS, TV kabel, internet, dll)</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">air_pdam</span><span class="inquiry-label">Tagihan air PDAM — isi nomor pelanggan PDAM</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">pascabayar</span><span class="inquiry-label">Tagihan pascabayar operator (Telkomsel Halo, Indosat Matrix, XL Prioritas, dll)</span></div>
        </div>
    </div>

    <div class="endpoint-card" id="categories">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/categories</span>
            <span class="endpoint-title d-none d-md-inline">Daftar kategori (OkeConnect)</span>
            <span class="badge-public">Public</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Mengembalikan semua kategori produk dari OkeConnect.</p>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/categories', false, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    <div class="endpoint-card" id="products">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/products</span>
            <span class="endpoint-title d-none d-md-inline">Daftar produk per kategori</span>
            <span class="badge-public">Public</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Ambil daftar produk OkeConnect berdasarkan kategori. Produk dikelompokkan per provider.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Query Param</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">category</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Slug kategori dari <code>GET /api/categories</code></td></tr>
                </tbody>
            </table>

            {{-- Sandbox --}}
            <div class="sandbox-form" id="sf-products">
                <div class="sf-title"><i class="bi bi-terminal me-1"></i> Sandbox — Pilih Kategori & Kirim</div>
                <div class="sf-row">
                    <label class="sf-label">category <span class="sf-required">*</span></label>
                    <select class="sf-select" id="sf-products-cat">
                        <option value="pulsa">pulsa — Pulsa</option>
                        <option value="kuota_nasional">kuota_nasional — Kuota Nasional</option>
                        <option value="kuota_telkomsel">kuota_telkomsel — Kuota Telkomsel</option>
                        <option value="bulk_telkomsel">bulk_telkomsel — Paket Bundling Telkomsel</option>
                        <option value="bulk_cashback">bulk_cashback — Paket Cashback</option>
                        <option value="kuota_byu">kuota_byu — Kuota by.U</option>
                        <option value="kuota_indosat">kuota_indosat — Kuota Indosat</option>
                        <option value="kuota_tri">kuota_tri — Kuota Tri</option>
                        <option value="kuota_xl">kuota_xl — Kuota XL</option>
                        <option value="kuota_axis">kuota_axis — Kuota Axis</option>
                        <option value="kuota_smartfren">kuota_smartfren — Kuota Smartfren</option>
                        <option value="token_pln">token_pln — Token PLN</option>
                        <option value="saldo_gojek">saldo_gojek — E-Wallet (GoPay, OVO, Dana, dll)</option>
                        <option value="tagihan">tagihan — Tagihan</option>
                        <option value="air_pdam">air_pdam — Air PDAM</option>
                        <option value="pascabayar">pascabayar — Pascabayar</option>
                    </select>
                </div>
                <div class="sf-footer">
                    <button class="sf-send-btn" onclick="sendProductsRequest()"><i class="bi bi-send me-1"></i> Kirim</button>
                    <span id="sf-products-status" style="font-size:11px;color:#6b7280;"></span>
                </div>
                <div class="sf-response" id="sf-products-response" style="display:none;">
                    <pre><code id="sf-products-output" class="language-json"></code></pre>
                </div>
            </div>
        </div>
    </div>

    <div class="endpoint-card" id="digital-products">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/productv1</span>
            <span class="endpoint-title d-none d-md-inline">List semua produk lokal</span>
            <span class="badge-public">Public</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Daftar semua produk digital lokal dalam format flat (tanpa kategori). Setiap produk berisi <code>kode_produk</code>, <code>app_category</code>, harga, stok, dan garansi.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Query Param</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">app</span></td><td><span class="param-optional">opsional</span> Filter by app name (misal: <code>capcut</code>)</td></tr>
                    <tr><td><span class="param-name">search</span></td><td><span class="param-optional">opsional</span> Cari nama/kode produk</td></tr>
                    <tr><td><span class="param-name">in_stock</span></td><td><span class="param-optional">opsional</span> <code>1</code> = hanya yang ada stok</td></tr>
                    <tr><td><span class="param-name">per_page</span></td><td><span class="param-optional">opsional</span> Default 50</td></tr>
                </tbody>
            </table>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/productv1', false, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    <div class="endpoint-card" id="digital-products-detail">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/productv1/{kode_produk}</span>
            <span class="endpoint-title d-none d-md-inline">Detail produk lokal</span>
            <span class="badge-public">Public</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Ambil detail satu produk berdasarkan kode produk. Contoh: <code>GET /api/productv1/CC35H</code></p>
            <div class="code-wrap"><pre><code class="language-json">{
  "product_data": {
    "id": 1,
    "nama_produk": "CAPCUT 35H",
    "kode_produk": "CC35H",
    "kategori": "PRODUCTV1",
    "app_category": "Video Editing",
    "harga_user": 50000,
    "harga_reseller": 45000,
    "stok": 10,
    "garansi": true
  }
}</code></pre></div>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/productv1/CC35H', false, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    <div class="endpoint-card" id="category-by-app">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/category/{nama_apps}</span>
            <span class="endpoint-title d-none d-md-inline">Produk berdasarkan app</span>
            <span class="badge-public">Public</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Shortcut: ambil semua produk lokal berdasarkan nama aplikasi (pencarian partial pada <code>app_category</code>). Contoh: <code>/api/category/capcut</code> → semua produk CapCut.</p>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/category/capcut', false, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         E-WALLET NOMINAL BEBAS
    ════════════════════════════════════════ --}}
    <div class="section-header" id="ewallet">
        <h2><i class="bi bi-wallet2 text-success me-2"></i>E-Wallet Nominal Bebas</h2>
        <p>Top up e-wallet (GoPay, OVO, Dana, dll) dengan nominal bebas. User input nominal, sistem hitung biaya layanan otomatis.</p>
    </div>
    <hr class="section-divider">

    <div class="endpoint-card" id="ewallet-options">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/ewallet/options</span>
            <span class="endpoint-title d-none d-md-inline">Daftar provider & biaya layanan</span>
            <span class="badge-public">Public</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Mengembalikan semua provider e-wallet nominal bebas beserta biaya layanan per transaksi. <code>total_fee</code> = biaya OkeConnect + fee admin.</p>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/ewallet/options', false, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
            <div class="code-wrap mt-3"><pre><code class="language-json">[
  {
    "code":        "BBSGOP",
    "name":        "GoPay",
    "provider":    "GoPay",
    "base_fee":    850,
    "admin_fee":   0,
    "total_fee":   850,
    "min_nominal": 10000,
    "max_nominal": 1000000,
    "note":        "Harga = nominal + Rp 850 (biaya layanan)"
  },
  { "code": "BBSOVON", "name": "OVO",       "total_fee": 680, ... },
  { "code": "BBSD",    "name": "Dana",      "total_fee": 48,  ... },
  { "code": "BBSSH",   "name": "ShopeePay", "total_fee": 500, ... },
  { "code": "BBSTC",   "name": "LinkAja",   "total_fee": 0,   ... },
  { "code": "BBSGOD",  "name": "GoPay Driver","total_fee": 450,... },
  ...
]</code></pre></div>
        </div>
    </div>

    <div class="endpoint-card" id="ewallet-topup">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/ewallet/topup</span>
            <span class="endpoint-title d-none d-md-inline">Top up e-wallet nominal bebas</span>
            <span class="badge-auth">Auth</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Submit top up e-wallet nominal bebas. Total yang dibayar = <code>nominal</code> + <code>total_fee</code>.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">product_code</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Kode provider dari <code>/api/ewallet/options</code> (misal: <code>BBSGOP</code>)</td></tr>
                    <tr><td><span class="param-name">destination</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Nomor HP terdaftar di e-wallet</td></tr>
                    <tr><td><span class="param-name">nominal</span></td><td><span class="param-type">integer</span></td><td><span class="param-required">wajib</span> Jumlah top up dalam Rupiah (min: 10.000, max: 1.000.000)</td></tr>
                    <tr><td><span class="param-name">payment_method</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> <code>balance</code> atau <code>midtrans</code></td></tr>
                </tbody>
            </table>
            <div class="code-wrap mb-3"><pre><code class="language-json">// Request
{
  "product_code":   "BBSGOP",
  "destination":    "08123456789",
  "nominal":        50000,
  "payment_method": "balance"
}

// Response (balance)
{
  "status": true,
  "message": "Transaksi diproses",
  "data": {
    "order_id":          1,
    "ref_id":            "EW-1746541200-AbCdEf",
    "provider":          "GoPay",
    "nominal":           50000,
    "fee":               850,
    "sell_price":        50850,
    "payment_method":    "balance",
    "status":            "processing",
    "balance_remaining": 149150
  }
}</code></pre></div>
            <button class="btn-try" onclick="openTester(this, 'POST', '/api/ewallet/topup', true, {product_code:'BBSGOP',destination:'08123456789',nominal:50000,payment_method:'balance'})">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         INQUIRY
    ════════════════════════════════════════ --}}
    <div class="section-header" id="inquiry">
        <h2><i class="bi bi-search text-warning me-2"></i>Inquiry / Cek ID &amp; Nama</h2>
        <p>Cek nama/ID akun sebelum transaksi — mendukung game, e-wallet, tagihan, dan rekening bank.</p>
    </div>
    <hr class="section-divider">

    {{-- Product Reference Table --}}
    <div class="inquiry-ref" id="inquiry-ref">
        <div class="inquiry-ref-header" onclick="toggleRef('inquiry-ref-body')">
            <i class="bi bi-table text-warning"></i> Referensi Produk Inquiry
            <span style="font-size:11px;font-weight:400;color:#6b7280;">Gunakan nilai kolom <code style="background:#f3f4f6;padding:1px 5px;border-radius:3px;">product</code> saat POST ke <code style="background:#f3f4f6;padding:1px 5px;border-radius:3px;">/api/inquiry/check</code></span>
            <button class="ref-toggle-btn" id="inquiry-ref-btn">+</button>
        </div>
        <div class="inquiry-ref-body" id="inquiry-ref-body">

            {{-- Games --}}
            <div class="inquiry-cat">🎮 Game</div>
            <div class="inquiry-row"><span class="inquiry-slug">freefire</span><span class="inquiry-label">Free Fire</span><span class="inquiry-param">?id=</span><span class="inquiry-example">id=123456789</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">mobilelegends</span><span class="inquiry-label">Mobile Legends</span><span class="inquiry-param">?id=</span><span class="inquiry-example">id=12345678 (format: userID/serverID)</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">pubg</span><span class="inquiry-label">PUBG Mobile</span><span class="inquiry-param">?id=</span><span class="inquiry-example">id=5270399733</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">valorant</span><span class="inquiry-label">Valorant</span><span class="inquiry-param">?id=</span><span class="inquiry-example">id=RiotID#TAG</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">codm</span><span class="inquiry-label">Call of Duty Mobile</span><span class="inquiry-param">?id=</span><span class="inquiry-example">id=218053371</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">aov</span><span class="inquiry-label">Arena of Valor</span><span class="inquiry-param">?id=</span><span class="inquiry-example">id=218053371</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">tom-jerry</span><span class="inquiry-label">Tom and Jerry Chase</span><span class="inquiry-param">?id=</span><span class="inquiry-example">id=218053371</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">undawn</span><span class="inquiry-label">Undawn</span><span class="inquiry-param">?id=</span><span class="inquiry-example">id=218053371</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">zepeto</span><span class="inquiry-label">Zepeto</span><span class="inquiry-param">?id=</span><span class="inquiry-example">id=218053371</span></div>

            {{-- E-Wallet --}}
            <div class="inquiry-cat">💳 E-Wallet</div>
            <div class="inquiry-row"><span class="inquiry-slug">gopay</span><span class="inquiry-label">GoPay</span><span class="inquiry-param">?hp=</span><span class="inquiry-example">hp=081234567890</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">dana</span><span class="inquiry-label">DANA</span><span class="inquiry-param">?hp=</span><span class="inquiry-example">hp=081234567890</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">ovo</span><span class="inquiry-label">OVO</span><span class="inquiry-param">?hp=</span><span class="inquiry-example">hp=081234567890</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">shopeepay</span><span class="inquiry-label">ShopeePay</span><span class="inquiry-param">?hp=</span><span class="inquiry-example">hp=081234567890</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">linkaja</span><span class="inquiry-label">LinkAja</span><span class="inquiry-param">?hp=</span><span class="inquiry-example">hp=081234567890</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">gopay_driver</span><span class="inquiry-label">GoPay Driver</span><span class="inquiry-param">?hp=</span><span class="inquiry-example">hp=081234567890</span></div>

            {{-- Tagihan --}}
            <div class="inquiry-cat">🧾 Tagihan / Bill</div>
            <div class="inquiry-row"><span class="inquiry-slug">pln</span><span class="inquiry-label">Token PLN</span><span class="inquiry-param">?no=</span><span class="inquiry-example">no=12345678901 (11 digit)</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">telkom</span><span class="inquiry-label">Tagihan Telkom</span><span class="inquiry-param">?no=</span><span class="inquiry-example">no=02112345678</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">my_republic</span><span class="inquiry-label">MyRepublic</span><span class="inquiry-param">?no=</span><span class="inquiry-example">no=MR123456</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">pdam</span><span class="inquiry-label">PDAM <span style="font-size:10px;color:#f59e0b;">(butuh extra.area)</span></span><span class="inquiry-param">?no=</span><span class="inquiry-example">no=123456, extra: {area: "kota_surabaya"}</span></div>

            {{-- Bank --}}
            <div class="inquiry-cat">🏦 Rekening Bank <span style="font-weight:400;font-size:11px;">(butuh extra.kode — lihat tabel bank di bawah)</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">bank</span><span class="inquiry-label">Cek Rekening Bank (Server 1)</span><span class="inquiry-param">?norek=</span><span class="inquiry-example">norek=1234567890, extra: {kode: "014"}</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">bank_s2</span><span class="inquiry-label">Cek Rekening Bank (Server 2)</span><span class="inquiry-param">?norek=</span><span class="inquiry-example">norek=1234567890, extra: {kode: "014"}</span></div>

        </div>
    </div>

    {{-- Bank Code Reference --}}
    <div class="endpoint-card" id="bank-codes-ref">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <i class="bi bi-bank text-primary me-1"></i>
            <span class="endpoint-path" style="font-size:13px;">Kode Bank — extra.kode</span>
            <span class="endpoint-title d-none d-md-inline" style="flex:1;">Digunakan saat product=bank atau bank_s2</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:4px 20px;">
                @foreach([
                    '014'=>'BCA','008'=>'Mandiri','002'=>'BRI','009'=>'BNI','451'=>'BSI',
                    '022'=>'CIMB Niaga','535'=>'SeaBank','542'=>'Bank Jago','947'=>'Aladin Syariah',
                    '501'=>'BCA Digital (Blu)','484'=>'LINE Bank / KEB Hana','490'=>'Neo Commerce (BNC)',
                    '503'=>'Nobu Bank','566'=>'Superbank','023'=>'TMRW / UOB','441'=>'Bukopin',
                    '521'=>'Bukopin Syariah','536'=>'BCA Syariah','200'=>'BTN','422'=>'BTN Syariah',
                    '213'=>'BTPN','547'=>'BTPN Syariah','031'=>'Citibank','011'=>'Danamon',
                    '472'=>'Bank Jasa Jakarta','097'=>'Mayapada','426'=>'Bank Mega','506'=>'Mega Syariah',
                    '028'=>'OCBC NISP','019'=>'Panin Bank','013'=>'Permata','784'=>'Permata Syariah',
                    '129'=>'BPD Bali','137'=>'BPD Banten','110'=>'BJB','425'=>'BJB Syariah',
                    '111'=>'Bank DKI','113'=>'Bank Jateng','114'=>'Bank Jatim','110'=>'BJB',
                    '112'=>'BPD DIY','115'=>'Bank Jambi','123'=>'Bank Kalbar','122'=>'Bank Kalsel',
                    '125'=>'Bank Kalteng','124'=>'Bank Kaltimtara','121'=>'Bank Lampung','131'=>'Bank Maluku',
                    '118'=>'Bank Nagari (Sumbar)','128'=>'NTB Syariah','130'=>'Bank NTT',
                    '132'=>'Bank Papua','119'=>'Bank Riau Kepri','111'=>'Bank DKI',
                    '126'=>'Sulselbar','134'=>'Sulteng','135'=>'Sultra','127'=>'SulutGo',
                    '120'=>'Sumsel Babel','117'=>'Bank Sumut',
                ] as $code => $name)
                <div style="font-size:12px;padding:3px 0;display:flex;gap:6px;">
                    <code style="background:#f3f4f6;padding:1px 6px;border-radius:3px;color:#4f46e5;width:36px;text-align:center;flex-shrink:0;">{{ $code }}</code>
                    <span style="color:#374151;">{{ $name }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Inquiry Sandbox --}}
    <div class="endpoint-card" id="inquiry-check">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/inquiry/check</span>
            <span class="endpoint-title d-none d-md-inline">Cek ID / Cek Nama — Sandbox</span>
            <span class="badge-public">Public</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Kirim inquiry ke Payday TrueID API. Pilih produk dari dropdown, lalu isi ID/nomor sesuai kategori. Untuk bank, isi juga kode bank.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">product</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Slug produk (lihat tabel di atas)</td></tr>
                    <tr><td><span class="param-name">target_id</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> ID / nomor HP / nomor rekening</td></tr>
                    <tr><td><span class="param-name">extra</span></td><td><span class="param-type">object</span></td><td><span class="param-optional">kondisional</span> <code>kode</code> untuk bank, <code>area</code> untuk PDAM</td></tr>
                </tbody>
            </table>

            <div class="sandbox-form" id="sf-inquiry">
                <div class="sandbox-form-title"><i class="bi bi-terminal me-1"></i>Sandbox — Isi &amp; Kirim</div>
                <div class="sf-row">
                    <label class="sf-label">product <span>*</span></label>
                    <select class="sf-select" id="inq-product" onchange="inquiryProductChanged()">
                        <optgroup label="🎮 Game">
                            <option value="freefire">freefire — Free Fire</option>
                            <option value="mobilelegends">mobilelegends — Mobile Legends</option>
                            <option value="pubg">pubg — PUBG Mobile</option>
                            <option value="valorant">valorant — Valorant</option>
                            <option value="codm">codm — Call of Duty Mobile</option>
                            <option value="aov">aov — Arena of Valor</option>
                            <option value="tom-jerry">tom-jerry — Tom and Jerry Chase</option>
                            <option value="undawn">undawn — Undawn</option>
                            <option value="zepeto">zepeto — Zepeto</option>
                        </optgroup>
                        <optgroup label="💳 E-Wallet">
                            <option value="gopay">gopay — GoPay</option>
                            <option value="dana">dana — DANA</option>
                            <option value="ovo">ovo — OVO</option>
                            <option value="shopeepay">shopeepay — ShopeePay</option>
                            <option value="linkaja">linkaja — LinkAja</option>
                            <option value="gopay_driver">gopay_driver — GoPay Driver</option>
                        </optgroup>
                        <optgroup label="🧾 Tagihan">
                            <option value="pln">pln — Token PLN</option>
                            <option value="telkom">telkom — Tagihan Telkom</option>
                            <option value="my_republic">my_republic — MyRepublic</option>
                            <option value="pdam">pdam — PDAM (butuh area)</option>
                        </optgroup>
                        <optgroup label="🏦 Bank">
                            <option value="bank">bank — Rekening Bank (Server 1)</option>
                            <option value="bank_s2">bank_s2 — Rekening Bank (Server 2)</option>
                        </optgroup>
                    </select>
                </div>
                <div class="sf-row">
                    <label class="sf-label" id="inq-target-label">target_id (ID Game) <span>*</span></label>
                    <input type="text" class="sf-input" id="inq-target" placeholder="Masukkan ID / nomor HP / nomor rekening">
                </div>
                <div class="sf-row" id="inq-extra-bank" style="display:none;">
                    <label class="sf-label">extra.kode — Kode Bank <span>*</span></label>
                    <select class="sf-select" id="inq-bank-code">
                        @foreach(['014'=>'BCA','008'=>'Mandiri','002'=>'BRI','009'=>'BNI','451'=>'BSI','022'=>'CIMB Niaga','535'=>'SeaBank','542'=>'Bank Jago','200'=>'BTN','011'=>'Danamon','013'=>'Permata','028'=>'OCBC NISP','019'=>'Panin Bank','023'=>'TMRW/UOB','441'=>'Bukopin','566'=>'Superbank','501'=>'BCA Digital','484'=>'LINE Bank','490'=>'Neo Commerce'] as $c => $n)
                        <option value="{{ $c }}">{{ $c }} — {{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sf-row" id="inq-extra-pdam" style="display:none;">
                    <label class="sf-label">extra.area — Kota PDAM <span>*</span></label>
                    <input type="text" class="sf-input" id="inq-area" placeholder="kota_surabaya / kota_jakarta / ...">
                    <div class="sf-note">Format snake_case, contoh: kota_surabaya, kota_bandung</div>
                </div>
                <div class="sf-hint" id="inq-hint">Contoh: id=123456789</div>
                <div class="sf-footer">
                    <button class="btn-send" onclick="sendInquiry()"><i class="bi bi-send"></i> Kirim Inquiry</button>
                    <span class="tester-status" id="inq-status"></span>
                </div>
                <div class="sf-response" id="inq-resp" style="display:none;">
                    <pre><code class="language-json" id="inq-code"></code></pre>
                </div>
            </div>
        </div>
    </div>

    <div class="endpoint-card" id="inquiry-products">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/inquiry/products</span>
            <span class="endpoint-title d-none d-md-inline">Daftar produk inquiry (JSON)</span>
            <span class="badge-public">Public</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Mengembalikan semua produk yang didukung dalam format JSON, dikelompokkan per kategori. Juga tersedia <code>GET /api/inquiry/bank-codes</code> untuk daftar kode bank.</p>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/inquiry/products', false, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         BELI PRODUK DIGITAL
    ════════════════════════════════════════ --}}
    <div class="endpoint-card" id="digital-order">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/digital/order</span>
            <span class="endpoint-title d-none d-md-inline">Beli produk digital</span>
            <span class="badge-auth">Auth Required</span>
            <span class="badge-rate">10/menit</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Beli produk digital (akun, voucher, dll). Mendukung 2 metode pembayaran: <strong>saldo (balance)</strong> — delivery langsung di response, atau <strong>QRIS</strong> — bayar dulu, delivery tersedia di detail order setelah pembayaran dikonfirmasi. Harga otomatis disesuaikan berdasarkan role user (<strong>harga_user</strong> atau <strong>harga_reseller</strong>).</p>

            <div style="background:#fffbeb;border:1px solid #fde68a;border-left:4px solid #f59e0b;border-radius:8px;padding:14px 18px;margin-bottom:16px;">
                <strong style="color:#92400e;"><i class="bi bi-info-circle me-1"></i>Alur Pembelian (Saldo):</strong>
                <ol style="color:#78350f;font-size:13px;margin:8px 0 0;padding-left:20px;">
                    <li>Lihat daftar produk via <code>GET /api/digital/products</code> atau <code>GET /api/productv1</code></li>
                    <li>Pastikan saldo cukup (cek via <code>GET /api/me</code>)</li>
                    <li>Kirim <code>POST /api/digital/order</code> dengan <code>payment_method: "balance"</code></li>
                    <li>Terima isi akun/voucher di field <code>delivery</code> pada response</li>
                </ol>
                <strong style="color:#92400e;margin-top:12px;display:block;"><i class="bi bi-qr-code me-1"></i>Alur Pembelian (QRIS):</strong>
                <ol style="color:#78350f;font-size:13px;margin:8px 0 0;padding-left:20px;">
                    <li>Kirim <code>POST /api/digital/order</code> dengan <code>payment_method: "qris"</code></li>
                    <li>Gunakan <code>snap_token</code> / <code>redirect_url</code> untuk menampilkan QRIS</li>
                    <li>Setelah pembayaran dikonfirmasi, akun/voucher otomatis tersedia di <code>GET /api/orders/{id}</code> (field <code>sn</code>)</li>
                </ol>
            </div>

            <div class="code-label">Request Body</div>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">kode_produk</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Kode produk digital (dari <code>/api/digital/products</code>)</td></tr>
                    <tr><td><span class="param-name">quantity</span></td><td><span class="param-type">integer</span></td><td><span class="param-optional">opsional</span> Jumlah (default: 1, max: 10)</td></tr>
                    <tr><td><span class="param-name">payment_method</span></td><td><span class="param-type">string</span></td><td><span class="param-optional">opsional</span> <code>balance</code> (default) atau <code>qris</code></td></tr>
                </tbody>
            </table>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="code-label">Contoh Request</div>
                    <div class="code-wrap"><pre><code class="language-json">{
  "kode_produk": "CC35H",
  "quantity": 1
}</code></pre><button class="btn-copy" onclick="copyCode(this)">Copy</button></div>
                </div>
                <div class="col-md-6">
                    <div class="code-label">Contoh Response <span class="text-success">200</span></div>
                    <div class="code-wrap"><pre><code class="language-json">{
  "status": true,
  "message": "Pembelian berhasil! Cek delivery untuk detail akun/voucher.",
  "data": {
    "order_id": 55,
    "order_ref": "DIG-1746...",
    "produk": "CAPCUT 35H",
    "kode_produk": "CC35H",
    "quantity": 1,
    "harga_satuan": 15000,
    "total_bayar": 15000,
    "delivery": "email@contoh.com | password123",
    "sisa_stok": 4,
    "balance_remaining": 85000,
    "status": "completed"
  }
}</code></pre></div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <div class="code-label">Contoh Request (QRIS)</div>
                    <div class="code-wrap"><pre><code class="language-json">{
  "kode_produk": "CC35H",
  "quantity": 1,
  "payment_method": "qris"
}</code></pre><button class="btn-copy" onclick="copyCode(this)">Copy</button></div>
                </div>
                <div class="col-md-6">
                    <div class="code-label">Response (QRIS) <span class="text-warning">201</span></div>
                    <div class="code-wrap"><pre><code class="language-json">{
  "status": true,
  "message": "Silakan selesaikan pembayaran via QRIS.",
  "data": {
    "order_id": 57,
    "order_ref": "DIG-1746...",
    "produk": "CAPCUT 35H",
    "kode_produk": "CC35H",
    "quantity": 1,
    "harga_satuan": 15000,
    "total_bayar": 15000,
    "payment_method": "qris",
    "snap_token": "abc123...",
    "redirect_url": "https://app.midtrans.com/snap/v4/...",
    "client_key": "Mid-client-xxx",
    "status": "pending"
  }
}</code></pre></div>
                </div>
            </div>

            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #22c55e;border-radius:8px;padding:12px 18px;margin-top:12px;margin-bottom:4px;">
                <strong style="color:#166534;font-size:13px;"><i class="bi bi-check-circle me-1"></i>Setelah QRIS dibayar:</strong>
                <p style="color:#15803d;font-size:12px;margin:6px 0 0;">Midtrans webhook otomatis memproses order. Cek detail via <code>GET /api/orders/{order_id}</code> — field <code>sn</code> berisi isi akun/voucher. Status berubah dari <code>pending</code> → <code>completed</code>.</p>
            </div>

            <div class="code-label mt-3">Error Responses</div>
            <table class="params-table">
                <thead><tr><th>HTTP Code</th><th>Kondisi</th><th>Message</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-type">404</span></td><td>Produk tidak ditemukan</td><td><code>Produk tidak ditemukan.</code></td></tr>
                    <tr><td><span class="param-type">422</span></td><td>Produk nonaktif</td><td><code>Produk sedang tidak aktif.</code></td></tr>
                    <tr><td><span class="param-type">422</span></td><td>Stok habis / kurang</td><td><code>Stok tidak mencukupi. Tersedia: X, diminta: Y.</code></td></tr>
                    <tr><td><span class="param-type">422</span></td><td>Saldo kurang (balance)</td><td><code>Saldo tidak cukup. Dibutuhkan Rp..., saldo Anda Rp...</code></td></tr>
                    <tr><td><span class="param-type">503</span></td><td>QRIS belum dikonfigurasi</td><td><code>Pembayaran QRIS belum dikonfigurasi.</code></td></tr>
                    <tr><td><span class="param-type">502</span></td><td>Gagal buat QRIS</td><td><code>Gagal membuat pembayaran QRIS. Coba lagi nanti.</code></td></tr>
                </tbody>
            </table>

            <div class="sandbox-form">
                <div class="sandbox-form-title"><i class="bi bi-terminal me-1"></i>Sandbox — Beli Produk Digital</div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="sf-row">
                            <label class="sf-label">kode_produk <span>*</span></label>
                            <input type="text" class="sf-input" id="digorder-kode" placeholder="CC35H" value="CC35H">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sf-row">
                            <label class="sf-label">quantity</label>
                            <input type="number" class="sf-input" id="digorder-qty" placeholder="1" value="1" min="1" max="10">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sf-row">
                            <label class="sf-label">payment_method</label>
                            <select class="sf-select" id="digorder-method">
                                <option value="balance">balance (Saldo)</option>
                                <option value="qris">qris (QRIS)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="sf-footer">
                    <button class="btn-send" onclick="sendDigitalOrder()"><i class="bi bi-send"></i> Beli Sekarang</button>
                    <span class="tester-status" id="digorder-status"></span>
                </div>
                <div class="sf-response" id="digorder-resp" style="display:none;">
                    <pre><code class="language-json" id="digorder-code"></code></pre>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         TRANSAKSI
    ════════════════════════════════════════ --}}
    <div class="section-header" id="transaksi">
        <h2><i class="bi bi-lightning-charge text-danger me-2"></i>Transaksi</h2>
        <p>Buat transaksi top-up via OkeConnect H2H. Pembayaran bisa dengan saldo atau Midtrans.</p>
    </div>
    <hr class="section-divider">

    <div class="endpoint-card" id="transactions">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/transactions</span>
            <span class="endpoint-title d-none d-md-inline">Buat transaksi top-up</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Buat transaksi top-up melalui OkeConnect. Jika <code>payment_method=balance</code>, saldo dipotong langsung. Jika <code>payment_method=midtrans</code>, dikembalikan <code>snap_token</code> untuk dibayar via Midtrans.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">product_code</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Kode produk OkeConnect</td></tr>
                    <tr><td><span class="param-name">destination</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Nomor/ID tujuan</td></tr>
                    <tr><td><span class="param-name">base_price</span></td><td><span class="param-type">numeric</span></td><td><span class="param-required">wajib</span> Harga dasar produk</td></tr>
                    <tr><td><span class="param-name">payment_method</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> <code>balance</code> atau <code>midtrans</code></td></tr>
                    <tr><td><span class="param-name">product_name</span></td><td><span class="param-type">string</span></td><td><span class="param-optional">opsional</span> Nama produk</td></tr>
                    <tr><td><span class="param-name">category</span></td><td><span class="param-type">string</span></td><td><span class="param-optional">opsional</span> Kategori produk</td></tr>
                </tbody>
            </table>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="code-label">Contoh Request (Saldo)</div>
                    <div class="code-wrap"><pre><code class="language-json">{
  "product_code": "TSEL10",
  "destination": "081234567890",
  "product_name": "Telkomsel 10rb",
  "category": "pulsa",
  "base_price": 10000,
  "payment_method": "balance"
}</code></pre><button class="btn-copy" onclick="copyCode(this)">Copy</button></div>
                </div>
                <div class="col-md-6">
                    <div class="code-label">Contoh Response (Saldo) <span class="text-success">200</span></div>
                    <div class="code-wrap"><pre><code class="language-json">{
  "success": true,
  "message": "Transaksi diproses",
  "data": {
    "order_id": 42,
    "ref_id": "TRX-1746...",
    "payment_method": "balance",
    "sell_price": 11000,
    "status": "processing",
    "balance_remaining": 139000
  }
}</code></pre></div>
                </div>
            </div>
            <button class="btn-try" onclick="openTester(this, 'POST', '/api/transactions', true, '{\n  \"product_code\": \"TSEL10\",\n  \"destination\": \"081234567890\",\n  \"product_name\": \"Telkomsel 10rb\",\n  \"category\": \"pulsa\",\n  \"base_price\": 10000,\n  \"payment_method\": \"balance\"\n}')">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- Orders --}}
    <div class="endpoint-card" id="orders-list">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/orders</span>
            <span class="endpoint-title d-none d-md-inline">Riwayat order</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Daftar semua order milik user yang sedang login. Hasil dipaginasi (20/halaman).</p>
            <table class="params-table mb-3">
                <thead><tr><th>Query Param</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">status</span></td><td><span class="param-type">string</span></td><td><span class="param-optional">opsional</span> pending, processing, completed, failed, refunded</td></tr>
                    <tr><td><span class="param-name">provider</span></td><td><span class="param-type">string</span></td><td><span class="param-optional">opsional</span> okeconnect, smmpanel</td></tr>
                    <tr><td><span class="param-name">page</span></td><td><span class="param-type">integer</span></td><td><span class="param-optional">opsional</span> Halaman (default: 1)</td></tr>
                </tbody>
            </table>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/orders?page=1', true, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    <div class="endpoint-card" id="orders-show">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/orders/{id}</span>
            <span class="endpoint-title d-none d-md-inline">Detail order</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Detail lengkap satu order berdasarkan ID.</p>
            <table class="params-table mb-3">
                <thead><tr><th>URL Param</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">id</span></td><td><span class="param-required">wajib</span> ID order</td></tr>
                </tbody>
            </table>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/orders/1', true, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    <div class="endpoint-card" id="orders-invoice">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/orders/{id}/invoice</span>
            <span class="endpoint-title d-none d-md-inline">Invoice order</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Mendapatkan data invoice terstruktur untuk ditampilkan di frontend (nomor invoice, data customer, detail produk, rincian harga, status pembayaran).</p>
            <div class="code-label">Contoh Response <span class="text-success">200</span></div>
            <div class="code-wrap"><pre><code class="language-json">{
  "success": true,
  "data": {
    "invoice_number": "INV-00000042",
    "order_id": 42,
    "ref_id": "TRX-1746...",
    "date": "2026-05-01T00:00:00+07:00",
    "customer": { "id": 1, "name": "Budi", "email": "budi@gmail.com" },
    "item": { "provider": "okeconnect", "product_name": "Telkomsel 10rb", "target": "081234567890" },
    "pricing": { "base_price": 10000, "markup": 1000, "sell_price": 11000, "payment_fee": 0, "total_pay": 11000 },
    "payment": { "method": "balance", "method_label": "Saldo", "status": "lunas", "status_label": "Lunas" },
    "status": "completed",
    "status_label": "Selesai"
  }
}</code></pre><button class="btn-copy" onclick="copyCode(this)">Copy</button></div>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/orders/1/invoice', true, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         DEPOSIT
    ════════════════════════════════════════ --}}
    <div class="section-header" id="deposit">
        <h2><i class="bi bi-wallet2 text-info me-2"></i>Deposit / Top Up Saldo</h2>
        <p>Isi saldo akun via Midtrans Snap. Setelah dapat <code>snap_token</code>, buka Snap popup di frontend untuk memilih metode bayar (transfer, QRIS, dll).</p>
    </div>
    <hr class="section-divider">

    <div class="endpoint-card" id="deposits-store">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/deposits</span>
            <span class="endpoint-title d-none d-md-inline">Buat deposit via Midtrans</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Generate Snap token Midtrans untuk top up saldo. Gunakan <code>snap_token</code> di frontend dengan Midtrans Snap.js. Cek status via <code>GET /api/deposits/{id}/check</code>.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">amount</span></td><td><span class="param-type">integer</span></td><td><span class="param-required">wajib</span> Nominal (min 1.000)</td></tr>
                </tbody>
            </table>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="code-wrap"><pre><code class="language-json">{
  "amount": 50000
}</code></pre><button class="btn-copy" onclick="copyCode(this)">Copy</button></div>
                </div>
                <div class="col-md-6">
                    <div class="code-wrap"><pre><code class="language-json">{
  "success": true,
  "message": "Snap token berhasil dibuat. Selesaikan pembayaran via Midtrans.",
  "data": {
    "deposit_id": 5,
    "invoice_no": "MID-DEP-1-1746834000",
    "amount": 50000,
    "snap_token": "66e4fa55-fdac-4ef9-91b5-733b97d1b862",
    "redirect_url": "https://app.sandbox.midtrans.com/snap/v2/vtweb/...",
    "client_key": "SB-Mid-client-xxxx"
  }
}</code></pre></div>
                </div>
            </div>
            <div class="sandbox-form">
                <div class="sandbox-form-title"><i class="bi bi-terminal me-1"></i>Sandbox — Buat Deposit Midtrans <span style="background:#374151;color:#818cf8;font-size:10px;padding:1px 6px;border-radius:3px;font-weight:400;margin-left:4px;">Auth Required</span></div>
                <div class="sf-row">
                    <label class="sf-label">amount <span>*</span> <span style="color:#6b7280;font-size:10px;">nominal deposit (min 1.000)</span></label>
                    <input type="number" class="sf-input" id="dep-amount" placeholder="50000" value="50000" min="1000">
                </div>
                <div class="sf-footer">
                    <button class="btn-send" onclick="sendDeposit()"><i class="bi bi-credit-card"></i> Buat Deposit</button>
                    <span class="tester-status" id="dep-status"></span>
                </div>
                <div class="sf-response" id="dep-resp" style="display:none;">
                    <pre><code class="language-json" id="dep-code"></code></pre>
                </div>
            </div>
        </div>
    </div>

    <div class="endpoint-card" id="deposits-list">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/deposits</span>
            <span class="endpoint-title d-none d-md-inline">Riwayat deposit</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <table class="params-table mb-3">
                <thead><tr><th>Query Param</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">purpose</span></td><td><span class="param-optional">opsional</span> <code>deposit</code> atau <code>order_payment</code></td></tr>
                </tbody>
            </table>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/deposits', true, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    <div class="endpoint-card" id="deposits-check">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/deposits/{id}/check</span>
            <span class="endpoint-title d-none d-md-inline">Cek status pembayaran</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Cek & sync status pembayaran Midtrans. Status: <code>pending</code> → <code>paid</code> / <code>failed</code>. Berlaku untuk deposit saldo maupun pembayaran order.</p>
            <div class="code-label">Contoh Response (paid) <span class="text-success">200</span></div>
            <div class="code-wrap"><pre><code class="language-json">{
  "success": true,
  "message": "Pembayaran berhasil! Saldo telah ditambahkan.",
  "data": {
    "deposit_id": 5,
    "status": "paid",
    "purpose": "deposit",
    "amount": 50000,
    "new_balance": 200000
  }
}</code></pre><button class="btn-copy" onclick="copyCode(this)">Copy</button></div>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/deposits/1/check', true, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         SMM PANEL
    ════════════════════════════════════════ --}}
    <div class="section-header" id="smm">
        <h2><i class="bi bi-graph-up-arrow text-purple me-2"></i>SMM Panel</h2>
        <p>Order layanan social media marketing (followers, likes, views, dsb) via Fayupedia.</p>
    </div>
    <hr class="section-divider">

    {{-- SMM Apps Reference --}}
    <div class="inquiry-ref" id="smm-apps-ref">
        <div class="inquiry-ref-header" onclick="toggleRef('smm-apps-ref-body')">
            <i class="bi bi-grid-fill" style="color:#7c3aed;"></i> Referensi Layanan SMM per Aplikasi
            <span style="font-size:11px;font-weight:400;color:#6b7280;">Gunakan <code style="background:#f3f4f6;padding:1px 5px;border-radius:3px;">GET /api/smm/search?app=</code> untuk melihat layanan &amp; harga terkini</span>
            <button class="ref-toggle-btn" id="smm-apps-ref-btn">+</button>
        </div>
        <div class="inquiry-ref-body" id="smm-apps-ref-body">

            {{-- Instagram --}}
            <div class="inquiry-cat">📸 Instagram</div>
            <div class="inquiry-row"><span class="inquiry-slug">Followers</span><span class="inquiry-label">Tambah followers akun Instagram (real / HQ / non-drop)</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 10–1.000.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Likes</span><span class="inquiry-label">Like postingan / reel / carousel</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 10–50.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Views</span><span class="inquiry-label">Views video, Reels, atau Story</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 100–10.000.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Comments</span><span class="inquiry-label">Komentar acak / custom di postingan</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 5–500</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Saves</span><span class="inquiry-label">Simpan/Bookmark postingan</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 10–10.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Shares / Story Views</span><span class="inquiry-label">Share postingan atau views story</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 100–100.000</span></div>

            {{-- TikTok --}}
            <div class="inquiry-cat">🎵 TikTok</div>
            <div class="inquiry-row"><span class="inquiry-slug">Followers</span><span class="inquiry-label">Tambah followers akun TikTok</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 10–500.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Likes</span><span class="inquiry-label">Like video TikTok</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–100.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Views</span><span class="inquiry-label">Views video (terhitung dari FYP)</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 500–10.000.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Comments</span><span class="inquiry-label">Komentar di video TikTok</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 5–1.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Shares</span><span class="inquiry-label">Share video ke sesama pengguna</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 100–50.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Live Views / Stream</span><span class="inquiry-label">Penonton live TikTok real-time</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 10–5.000</span></div>

            {{-- Facebook --}}
            <div class="inquiry-cat">📘 Facebook</div>
            <div class="inquiry-row"><span class="inquiry-slug">Page Likes</span><span class="inquiry-label">Like halaman Facebook (page)</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–500.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Post Likes</span><span class="inquiry-label">Like postingan / foto / status</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 10–50.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Followers</span><span class="inquiry-label">Followers profil / halaman Facebook</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–100.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Views</span><span class="inquiry-label">Views video Facebook</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 500–5.000.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Group Members</span><span class="inquiry-label">Tambah anggota grup Facebook</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–10.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Comments / Shares</span><span class="inquiry-label">Komentar atau share postingan</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 5–1.000</span></div>

            {{-- YouTube --}}
            <div class="inquiry-cat">▶️ YouTube</div>
            <div class="inquiry-row"><span class="inquiry-slug">Subscribers</span><span class="inquiry-label">Subscriber channel YouTube (non-drop / HQ)</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 10–500.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Views</span><span class="inquiry-label">Views video YouTube (organic-looking)</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 500–10.000.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Likes</span><span class="inquiry-label">Like video YouTube</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 20–100.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Comments</span><span class="inquiry-label">Komentar custom / acak di video</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 5–500</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Watch Time</span><span class="inquiry-label">Jam tayang (jam tonton, untuk monetisasi)</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 100–10.000 jam</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Live Stream Views</span><span class="inquiry-label">Penonton live YouTube real-time</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–5.000</span></div>

            {{-- Shopee / Tokopedia --}}
            <div class="inquiry-cat">🛒 Shopee / Tokopedia</div>
            <div class="inquiry-row"><span class="inquiry-slug">Followers</span><span class="inquiry-label">Follower toko Shopee atau Tokopedia</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–10.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Product Likes / Wishlist</span><span class="inquiry-label">Like/wishlist produk di toko</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–5.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Reviews / Rating</span><span class="inquiry-label">Ulasan bintang produk (diproses manual/pelan)</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 5–500</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Product Views</span><span class="inquiry-label">Kunjungan halaman produk</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 100–100.000</span></div>

            {{-- Twitter / X --}}
            <div class="inquiry-cat">🐦 Twitter / X</div>
            <div class="inquiry-row"><span class="inquiry-slug">Followers</span><span class="inquiry-label">Follower akun Twitter/X</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–500.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Likes / Favorites</span><span class="inquiry-label">Like tweet/postingan</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 10–50.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Retweets</span><span class="inquiry-label">Retweet/repost tweet</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 10–10.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Views / Impressions</span><span class="inquiry-label">Tayangan tweet</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 1.000–5.000.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Replies</span><span class="inquiry-label">Balasan/komentar di tweet</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 5–500</span></div>

            {{-- Twitch --}}
            <div class="inquiry-cat">🎮 Twitch</div>
            <div class="inquiry-row"><span class="inquiry-slug">Followers</span><span class="inquiry-label">Follower channel Twitch</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–100.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Live Viewers</span><span class="inquiry-label">Penonton live stream real-time</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 10–2.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Channel Views</span><span class="inquiry-label">Total view channel Twitch</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 100–100.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Clip Views</span><span class="inquiry-label">Views klip/highlight Twitch</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 100–50.000</span></div>

            {{-- Pinterest --}}
            <div class="inquiry-cat">📌 Pinterest</div>
            <div class="inquiry-row"><span class="inquiry-slug">Followers</span><span class="inquiry-label">Follower akun / board Pinterest</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–50.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Repins / Saves</span><span class="inquiry-label">Simpan/repin pin ke board</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–10.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Likes</span><span class="inquiry-label">Like pin Pinterest</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–5.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Views</span><span class="inquiry-label">Monthly views / tayangan pin</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 500–1.000.000</span></div>

            {{-- Spotify --}}
            <div class="inquiry-cat">🎧 Spotify</div>
            <div class="inquiry-row"><span class="inquiry-slug">Followers</span><span class="inquiry-label">Follower akun / artis Spotify</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–100.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Streams / Plays</span><span class="inquiry-label">Putar lagu (stream) Spotify</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 1.000–10.000.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Monthly Listeners</span><span class="inquiry-label">Pendengar bulanan artis</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 500–500.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Playlist Followers</span><span class="inquiry-label">Follower playlist Spotify</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–50.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Saves / Likes Lagu</span><span class="inquiry-label">Simpan lagu ke library pengguna</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 100–100.000</span></div>

            {{-- Telegram --}}
            <div class="inquiry-cat">✈️ Telegram</div>
            <div class="inquiry-row"><span class="inquiry-slug">Channel Members</span><span class="inquiry-label">Anggota channel Telegram</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 100–500.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Group Members</span><span class="inquiry-label">Anggota grup Telegram</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 100–200.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Post Views</span><span class="inquiry-label">Tayangan pesan/postingan di channel</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 500–5.000.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Reactions</span><span class="inquiry-label">Emoji reaction pada pesan Telegram</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 50–10.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Story Views</span><span class="inquiry-label">Views story Telegram</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 100–100.000</span></div>

            {{-- LinkedIn --}}
            <div class="inquiry-cat">💼 LinkedIn</div>
            <div class="inquiry-row"><span class="inquiry-slug">Followers</span><span class="inquiry-label">Follower profil / halaman perusahaan</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 20–10.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Post Likes</span><span class="inquiry-label">Like postingan / artikel LinkedIn</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 10–5.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Views / Impressions</span><span class="inquiry-label">Tayangan postingan LinkedIn</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 500–500.000</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Comments</span><span class="inquiry-label">Komentar di postingan LinkedIn</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 5–500</span></div>

            {{-- Lainnya --}}
            <div class="inquiry-cat">🌐 Lainnya</div>
            <div class="inquiry-row"><span class="inquiry-slug">Discord</span><span class="inquiry-label">Members server / Online members Discord</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Members, Live viewers</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Reddit</span><span class="inquiry-label">Upvotes postingan / Subscribers subreddit</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Upvotes, Subscribers</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Snapchat</span><span class="inquiry-label">Followers / Story Views Snapchat</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Followers, Views</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Clubhouse</span><span class="inquiry-label">Followers Clubhouse</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Followers</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Website Traffic</span><span class="inquiry-label">Kunjungan ke website (traffic organik)</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Biasanya 1.000–10.000.000 kunjungan</span></div>
            <div class="inquiry-row"><span class="inquiry-slug">Google</span><span class="inquiry-label">Google Maps Reviews / Play Store Reviews &amp; Downloads</span><span class="inquiry-param">min–max</span><span class="inquiry-example">Reviews, Downloads</span></div>

        </div>
        <div style="padding:10px 16px;background:#f9fafb;border-top:1px solid #e5e7eb;font-size:11px;color:#6b7280;border-radius:0 0 8px 8px;">
            ⚠️ <strong>Catatan:</strong> Nilai <code>min_order</code> dan <code>max_order</code> aktual dapat berbeda tergantung provider. Selalu cek <a href="#smm-search" style="color:#7c3aed;text-decoration:none;">GET /api/smm/search?app=xxx</a> untuk data terkini sebelum order.
        </div>
    </div>
    <hr class="section-divider">

    <div class="endpoint-card" id="smm-apps">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/smm/apps</span>
            <span class="endpoint-title d-none d-md-inline">List semua aplikasi</span>
            <span class="badge-public">Public</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Daftar semua nama aplikasi/kategori yang tersedia di SMM panel, diurutkan A–Z. Gunakan nilai ini sebagai parameter <code>?app=</code> di endpoint <code>/api/smm/search</code>.</p>
            <div class="code-wrap"><pre><code class="language-json">{
  "success": true,
  "data": {
    "total": 42,
    "apps": ["Facebook", "Instagram", "TikTok", "Twitter", "YouTube", ...]
  }
}</code></pre></div>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/smm/apps', false, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    <div class="endpoint-card" id="smm-search">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/smm/search</span>
            <span class="endpoint-title d-none d-md-inline">Cari layanan per app</span>
            <span class="badge-public">Public</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Cari layanan SMM berdasarkan nama aplikasi, diurutkan dari <strong>termurah ke termahal</strong>. Setiap layanan sudah mencantumkan <code>min_order</code> dan <code>max_order</code>.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Query Param</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">app</span></td><td><span class="param-required">wajib</span> Nama aplikasi, misal: <code>instagram</code>, <code>tiktok</code>, <code>youtube</code></td></tr>
                    <tr><td><span class="param-name">min_qty</span></td><td><span class="param-optional">opsional</span> Filter: tampilkan hanya layanan dengan <code>min_order ≤ nilai ini</code></td></tr>
                </tbody>
            </table>
            <div class="code-wrap"><pre><code class="language-json">GET /api/smm/search?app=instagram

{
  "success": true,
  "data": {
    "app": "instagram",
    "total": 18,
    "services": [
      {
        "id": "1038",
        "name": "Instagram Followers - Real",
        "category": "Instagram",
        "min_order": 100,
        "max_order": 100000,
        "price": 1500,
        "base_price": 1200
      },
      ...
    ]
  }
}</code></pre></div>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/smm/search?app=instagram', false, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    <div class="endpoint-card" id="smm-services">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/smm/services</span>
            <span class="endpoint-title d-none d-md-inline">Semua layanan SMM</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Daftar <strong>semua</strong> layanan SMM dari Fayupedia beserta harga (sudah termasuk markup). Gunakan <code>/api/smm/search?app=xxx</code> untuk pencarian lebih cepat. Juga tersedia <code>GET /api/smm/balance</code> untuk cek saldo provider.</p>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/smm/services', true, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    <div class="endpoint-card" id="smm-order">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/smm/order</span>
            <span class="endpoint-title d-none d-md-inline">Buat order SMM</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Buat order SMM. Pastikan <code>quantity</code> berada di antara <code>min_order</code> dan <code>max_order</code> yang ditampilkan di <code>/api/smm/search</code>.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">service</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> ID layanan dari /smm/search atau /smm/services</td></tr>
                    <tr><td><span class="param-name">target</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> URL/username target</td></tr>
                    <tr><td><span class="param-name">quantity</span></td><td><span class="param-type">integer</span></td><td><span class="param-required">wajib</span> Jumlah order (harus antara <code>min_order</code>–<code>max_order</code>)</td></tr>
                    <tr><td><span class="param-name">payment_method</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> <code>balance</code> atau <code>midtrans</code></td></tr>
                    <tr><td><span class="param-name">base_price</span></td><td><span class="param-type">numeric</span></td><td><span class="param-required">wajib</span> Harga dari /smm/search atau /smm/services</td></tr>
                    <tr><td><span class="param-name">service_name</span></td><td><span class="param-type">string</span></td><td><span class="param-optional">opsional</span> Nama layanan (untuk catatan order)</td></tr>
                    <tr><td><span class="param-name">category</span></td><td><span class="param-type">string</span></td><td><span class="param-optional">opsional</span> Nama kategori/app (untuk markup)</td></tr>
                </tbody>
            </table>
            <button class="btn-try" onclick="openTester(this, 'POST', '/api/smm/order', true, '{\n  \"service\": \"1038\",\n  \"target\": \"https://instagram.com/username\",\n  \"quantity\": 1000,\n  \"payment_method\": \"balance\",\n  \"base_price\": 1500,\n  \"service_name\": \"Instagram Followers\",\n  \"category\": \"Instagram\"\n}')">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    <div class="endpoint-card" id="smm-status">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-get">GET</span>
            <span class="endpoint-path">/api/smm/status/{orderId}</span>
            <span class="endpoint-title d-none d-md-inline">Status order SMM</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Cek status order SMM dari provider. Juga tersedia <code>POST /api/smm/refill/{orderId}</code> untuk refill dan <code>GET /api/smm/refill/status/{refillId}</code> untuk status refill.</p>
            <button class="btn-try" onclick="openTester(this, 'GET', '/api/smm/status/1', true, null)">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         REDEEM
    ════════════════════════════════════════ --}}
    <div class="section-header" id="redeem">
        <h2><i class="bi bi-ticket-perforated text-success me-2"></i>Redeem Code</h2>
        <p>Validasi dan gunakan kode redeem untuk mendapatkan saldo atau produk.</p>
    </div>
    <hr class="section-divider">

    <div class="endpoint-card" id="redeem-validate">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/digital/redeem/validate</span>
            <span class="endpoint-title d-none d-md-inline">Validasi kode redeem</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Cek apakah kode redeem valid sebelum digunakan.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">code</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span> Kode redeem</td></tr>
                </tbody>
            </table>
            <button class="btn-try" onclick="openTester(this, 'POST', '/api/digital/redeem/validate', true, '{\n  \"code\": \"REDEEM-ABCD-1234\"\n}')">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    <div class="endpoint-card" id="redeem-apply">
        <div class="endpoint-header" onclick="toggleEndpoint(this)">
            <span class="method-badge badge-post">POST</span>
            <span class="endpoint-path">/api/digital/redeem/apply</span>
            <span class="endpoint-title d-none d-md-inline">Pakai kode redeem</span>
            <span class="badge-auth">Auth Required</span>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="endpoint-body">
            <p class="endpoint-desc">Gunakan kode redeem. Kode hanya bisa dipakai sekali.</p>
            <table class="params-table mb-3">
                <thead><tr><th>Field</th><th>Tipe</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="param-name">code</span></td><td><span class="param-type">string</span></td><td><span class="param-required">wajib</span></td></tr>
                </tbody>
            </table>
            <button class="btn-try" onclick="openTester(this, 'POST', '/api/digital/redeem/apply', true, '{\n  \"code\": \"REDEEM-ABCD-1234\"\n}')">
                <i class="bi bi-play-fill"></i> Coba Endpoint
            </button>
            <div class="tester-container"></div>
        </div>
    </div>

    <div style="height: 60px;"></div>
</div>{{-- end main-content --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
    hljs.highlightAll();
    let BASE_URL = window.location.origin;
    let _docMode = localStorage.getItem('doc_mode') || 'sandbox';

    // ── Mode Toggle (Sandbox / Production) ──
    const PROD_URL = '{{ rtrim(config("app.url"), "/") }}';
    function toggleMode() {
        _docMode = _docMode === 'sandbox' ? 'production' : 'sandbox';
        localStorage.setItem('doc_mode', _docMode);
        applyMode();
    }
    function applyMode() {
        const btn = document.getElementById('modeToggle');
        const baseEl = document.getElementById('baseUrlDisplay');
        if (_docMode === 'production') {
            BASE_URL = PROD_URL;
            btn.textContent = 'PRODUCTION';
            btn.style.background = '#7c3aed';
            btn.style.color = '#ede9fe';
        } else {
            BASE_URL = window.location.origin;
            btn.textContent = 'SANDBOX';
            btn.style.background = '#166534';
            btn.style.color = '#bbf7d0';
        }
        if (baseEl) baseEl.textContent = BASE_URL;
    }

    // ── GET /api/products Sandbox ──
    function sendProductsRequest() {
        const cat    = document.getElementById('sf-products-cat').value;
        const status = document.getElementById('sf-products-status');
        const resp   = document.getElementById('sf-products-response');
        const out    = document.getElementById('sf-products-output');
        const url    = BASE_URL + '/api/products?category=' + encodeURIComponent(cat);
        status.textContent = 'Mengirim…';
        resp.style.display = 'none';
        fetch(url)
            .then(r => r.json().then(d => ({ ok: r.ok, status: r.status, data: d })))
            .then(({ ok, status: s, data }) => {
                status.textContent = 'HTTP ' + s;
                status.style.color = ok ? '#059669' : '#dc2626';
                out.textContent    = JSON.stringify(data, null, 2);
                resp.style.display = 'block';
                if (window.hljs) hljs.highlightElement(out);
            })
            .catch(e => { status.textContent = 'Error: ' + e.message; status.style.color='#dc2626'; });
    }

    // ── Reference Table Toggle ──
    function toggleRef(bodyId) {
        const body = document.getElementById(bodyId);
        const btnId = bodyId.replace('-body', '-btn');
        const btn = document.getElementById(btnId);
        if (!body) return;
        const isOpen = body.classList.contains('open');
        body.classList.toggle('open', !isOpen);
        if (btn) btn.textContent = isOpen ? '+' : '−';
        localStorage.setItem('ref_' + bodyId, isOpen ? '0' : '1');
    }

    // ── Credentials Management ──
    function saveCredentials() {
        const apiKey = document.getElementById('apiKeyInput').value.trim();
        if (apiKey) localStorage.setItem('api_key', apiKey); else localStorage.removeItem('api_key');
        updateTokenStatus(!!apiKey);
    }
    function getApiKey() { return localStorage.getItem('api_key') || ''; }
    function getToken()  { return getApiKey(); }
    function updateTokenStatus(hasKey) {
        const el = document.getElementById('tokenStatus');
        el.textContent = hasKey ? '✓ Key aktif' : 'Belum ada key';
        el.className = 'token-status' + (hasKey ? ' active' : '');
    }

    // Load credentials on page load
    window.addEventListener('DOMContentLoaded', () => {
        applyMode();
        ['inquiry-ref-body', 'okeconnect-ref-body', 'smm-apps-ref-body'].forEach(id => {
            const saved = localStorage.getItem('ref_' + id);
            if (saved === '1') {
                const body = document.getElementById(id);
                const btn = document.getElementById(id.replace('-body', '-btn'));
                if (body) body.classList.add('open');
                if (btn) btn.textContent = '−';
            }
        });
        const savedKey = getApiKey();
        if (savedKey) document.getElementById('apiKeyInput').value = savedKey;
        updateTokenStatus(!!savedKey);
    });

    // ── Endpoint Toggle ──
    function toggleEndpoint(header) {
        const body = header.nextElementSibling;
        const chevron = header.querySelector('.chevron');
        const isOpen = body.classList.contains('show');
        body.classList.toggle('show', !isOpen);
        header.classList.toggle('open', !isOpen);
        chevron.classList.toggle('open', !isOpen);
    }

    // ── Copy Helpers ──
    function copyCode(btn) {
        const code = btn.closest('.code-wrap').querySelector('code');
        navigator.clipboard.writeText(code.innerText).then(() => {
            btn.textContent = '✓ Copied';
            btn.classList.add('copied');
            setTimeout(() => { btn.innerHTML = 'Copy'; btn.classList.remove('copied'); }, 2000);
        });
    }
    function copyText(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> Copied';
            btn.classList.add('copied');
            setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('copied'); }, 2000);
        });
    }

    // ── Live Tester ──
    function openTester(btn, method, path, needsAuth, defaultBody) {
        const container = btn.nextElementSibling;
        if (container.innerHTML !== '') { container.innerHTML = ''; return; }

        const hasBody = method !== 'GET' && method !== 'DELETE' && defaultBody !== null;
        const bodyHtml = hasBody
            ? `<textarea class="tester-body-input" id="tbody_${path.replace(/\//g,'_')}" rows="6">${defaultBody || ''}</textarea>`
            : `<p class="text-muted px-3 py-2 mb-0" style="font-size:12px;color:#6b7280!important;">Tidak ada request body</p>`;

        container.innerHTML = `
        <div class="tester-panel mt-2">
            <div class="tester-tabs">
                <div class="tester-tab active">Request Body</div>
            </div>
            ${bodyHtml}
            <div class="tester-footer">
                <button class="btn-send" onclick="sendRequest(this,'${method}','${path}',${needsAuth},${hasBody})">
                    <i class="bi bi-send"></i> Kirim
                </button>
                <span class="tester-status" id="tstatus_${path.replace(/\//g,'_')}"></span>
            </div>
            <div class="tester-response" id="tresp_${path.replace(/\//g,'_')}" style="display:none;">
                <pre><code class="language-json" id="tcode_${path.replace(/\//g,'_')}"></code></pre>
            </div>
        </div>`;
    }

    async function sendRequest(sendBtn, method, path, needsAuth, hasBody) {
        const key = path.replace(/\//g,'_');
        const statusEl = document.getElementById(`tstatus_${key}`);
        const respEl   = document.getElementById(`tresp_${key}`);
        const codeEl   = document.getElementById(`tcode_${key}`);

        if (needsAuth && !getApiKey()) {
            statusEl.textContent = '⚠ Masukkan API Key di atas';
            statusEl.style.color = '#f59e0b';
            return;
        }

        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengirim...';
        statusEl.textContent = '';

        const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
        if (getApiKey()) headers['x-api-key'] = getApiKey();

        let body = undefined;
        if (hasBody) {
            const taEl = document.getElementById(`tbody_${key}`);
            if (taEl && taEl.value.trim()) body = taEl.value.trim();
        }

        try {
            const res = await fetch(BASE_URL + path, { method, headers, body });
            const text = await res.text();
            let formatted;
            try { formatted = JSON.stringify(JSON.parse(text), null, 2); } catch { formatted = text; }

            statusEl.innerHTML = `HTTP <strong>${res.status}</strong>`;
            const cls = res.status < 300 ? 'status-2xx' : res.status < 500 ? 'status-4xx' : 'status-5xx';
            statusEl.className = `tester-status ${cls}`;

            codeEl.textContent = formatted;
            respEl.style.display = 'block';
            hljs.highlightElement(codeEl);
        } catch (err) {
            statusEl.textContent = '✗ Gagal: ' + err.message;
            statusEl.className = 'tester-status status-4xx';
        } finally {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="bi bi-send"></i> Kirim';
        }
    }

    // ── Form Sandbox Helpers ──
    async function sfSend(statusEl, respEl, codeEl, sendBtn, method, path, body, needsAuth) {
        if (needsAuth && !getApiKey()) {
            statusEl.textContent = '⚠ Masukkan API Key di atas';
            statusEl.style.color = '#f59e0b';
            return;
        }
        sendBtn.disabled = true;
        const origLabel = sendBtn.innerHTML;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        statusEl.textContent = '';
        const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
        if (getApiKey()) headers['x-api-key'] = getApiKey();
        try {
            const res = await fetch(BASE_URL + path, { method, headers, body: JSON.stringify(body) });
            const text = await res.text();
            let fmt;
            try { fmt = JSON.stringify(JSON.parse(text), null, 2); } catch { fmt = text; }
            statusEl.innerHTML = `HTTP <strong>${res.status}</strong>`;
            statusEl.className = 'tester-status ' + (res.status < 300 ? 'status-2xx' : 'status-4xx');
            codeEl.textContent = fmt;
            respEl.style.display = 'block';
            hljs.highlightElement(codeEl);
            return { ok: res.ok, data: JSON.parse(text) };
        } catch(e) {
            statusEl.textContent = '✗ ' + e.message;
            statusEl.className = 'tester-status status-4xx';
        } finally {
            sendBtn.disabled = false;
            sendBtn.innerHTML = origLabel;
        }
    }

    // ── Register Sandbox ──
    async function sendRegister() {
        const btn = document.querySelector('#reg-resp').previousElementSibling.querySelector('.btn-send');
        const result = await sfSend(
            document.getElementById('reg-status'),
            document.getElementById('reg-resp'),
            document.getElementById('reg-code'),
            btn, 'POST', '/api/register',
            {
                name: document.getElementById('reg-name').value,
                email: document.getElementById('reg-email').value,
                password: document.getElementById('reg-password').value,
                password_confirmation: document.getElementById('reg-password').value,
                phone: document.getElementById('reg-phone').value || undefined,
            }, false
        );
    }

    // ── Login Sandbox ──
    async function sendLogin() {
        const btn = document.querySelector('#login-resp').previousElementSibling.querySelector('.btn-send');
        const result = await sfSend(
            document.getElementById('login-status'),
            document.getElementById('login-resp'),
            document.getElementById('login-code'),
            btn, 'POST', '/api/login',
            {
                email: document.getElementById('login-email').value,
                password: document.getElementById('login-password').value,
            }, false
        );
        // Auto-save api_key on successful login
        if (result && result.ok && result.data?.data?.api_key) {
            const key = result.data.data.api_key;
            localStorage.setItem('api_key', key);
            document.getElementById('apiKeyInput').value = key;
            updateTokenStatus(true);
            document.getElementById('login-status').innerHTML += ' &nbsp;✓ <span style="color:#34d399;">API Key tersimpan!</span>';
        }
    }

    // ── Deposit Sandbox ──
    async function sendDeposit() {
        const btn = document.getElementById('dep-resp').previousElementSibling.querySelector('.btn-send');
        const amount = parseInt(document.getElementById('dep-amount').value, 10);
        if (!amount || amount < 100 || amount > 2000000) {
            document.getElementById('dep-status').textContent = '⚠ Amount harus 100 – 2.000.000';
            document.getElementById('dep-status').className = 'tester-status status-4xx';
            return;
        }
        await sfSend(
            document.getElementById('dep-status'),
            document.getElementById('dep-resp'),
            document.getElementById('dep-code'),
            btn, 'POST', '/api/deposits', { amount }, true
        );
    }

    // ── Digital Order Sandbox ──
    async function sendDigitalOrder() {
        const btn = document.getElementById('digorder-resp').previousElementSibling.querySelector('.btn-send');
        const kode = document.getElementById('digorder-kode').value.trim();
        const qty = parseInt(document.getElementById('digorder-qty').value, 10) || 1;
        const method = document.getElementById('digorder-method').value;
        if (!kode) {
            document.getElementById('digorder-status').textContent = '⚠ Masukkan kode_produk';
            document.getElementById('digorder-status').className = 'tester-status status-4xx';
            return;
        }
        const result = await sfSend(
            document.getElementById('digorder-status'),
            document.getElementById('digorder-resp'),
            document.getElementById('digorder-code'),
            btn, 'POST', '/api/digital/order',
            { kode_produk: kode, quantity: qty, payment_method: method }, true
        );
        if (result && result.ok && result.data?.data?.redirect_url) {
            document.getElementById('digorder-status').innerHTML += ' &nbsp;<a href="' + result.data.data.redirect_url + '" target="_blank" style="color:#818cf8;">Buka QRIS →</a>';
        }
    }

    // ── Inquiry Sandbox ──
    const INQ_HINTS = {
        game: { label: 'target_id (ID Game)', hint: 'Contoh: id=123456789', ph: 'ID akun game kamu' },
        ewallet: { label: 'target_id (Nomor HP)', hint: 'Contoh: hp=081234567890', ph: '081234567890' },
        bill: { label: 'target_id (Nomor Pelanggan)', hint: 'Contoh: no=12345678901', ph: 'Nomor pelanggan / ID tagihan' },
        bank: { label: 'target_id (Nomor Rekening)', hint: 'Contoh: norek=1234567890, extra.kode wajib diisi', ph: 'Nomor rekening bank' },
    };
    const INQ_CAT = {
        freefire:'game', mobilelegends:'game', pubg:'game', valorant:'game', codm:'game',
        aov:'game', 'tom-jerry':'game', undawn:'game', zepeto:'game',
        gopay:'ewallet', dana:'ewallet', ovo:'ewallet', shopeepay:'ewallet', linkaja:'ewallet', gopay_driver:'ewallet',
        pln:'bill', telkom:'bill', my_republic:'bill', pdam:'bill',
        bank:'bank', bank_s2:'bank',
    };

    function inquiryProductChanged() {
        const p = document.getElementById('inq-product').value;
        const cat = INQ_CAT[p] || 'game';
        const info = INQ_HINTS[cat];
        document.getElementById('inq-target-label').innerHTML = info.label + ' <span>*</span>';
        document.getElementById('inq-target').placeholder = info.ph;
        document.getElementById('inq-hint').textContent = info.hint;
        document.getElementById('inq-extra-bank').style.display = (cat === 'bank') ? '' : 'none';
        document.getElementById('inq-extra-pdam').style.display = (p === 'pdam') ? '' : 'none';
    }

    async function sendInquiry() {
        const product = document.getElementById('inq-product').value;
        const target_id = document.getElementById('inq-target').value.trim();
        const cat = INQ_CAT[product] || 'game';
        const body = { product, target_id };
        if (cat === 'bank') {
            body.extra = { kode: document.getElementById('inq-bank-code').value };
        } else if (product === 'pdam') {
            const area = document.getElementById('inq-area').value.trim();
            if (area) body.extra = { area };
        }
        const btn = document.getElementById('inq-resp').previousElementSibling.querySelector('.btn-send');
        await sfSend(
            document.getElementById('inq-status'),
            document.getElementById('inq-resp'),
            document.getElementById('inq-code'),
            btn, 'POST', '/api/inquiry/check', body, false
        );
    }

    // ── Active Sidebar Link on scroll ──
    const sections = document.querySelectorAll('[id]');
    const links = document.querySelectorAll('.sidebar-link');
    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(s => {
            if (window.scrollY >= s.offsetTop - 90) current = s.id;
        });
        links.forEach(l => {
            l.classList.remove('active');
            if (l.getAttribute('href') === '#' + current) l.classList.add('active');
        });
    });
</script>
</body>
</html>
