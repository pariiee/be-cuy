<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>API Sandbox — {{ config('app.name') }}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root {
  --bg: #0f1117; --sidebar-bg: #0d0f18; --card-bg: #1a1d2e;
  --border: #2a2d3e; --accent: #4f46e5; --accent2: #818cf8;
  --text: #e2e8f0; --muted: #64748b; --success: #22c55e;
  --sidebar-w: 230px; --topbar-h: 56px;
}
* { box-sizing: border-box; margin: 0; }
body { background: var(--bg); color: var(--text); font-family: 'Segoe UI', system-ui, sans-serif; font-size: 14px; }

/* topbar */
.topbar {
  position: fixed; top: 0; left: 0; right: 0; height: var(--topbar-h);
  background: var(--sidebar-bg); border-bottom: 1px solid var(--border);
  display: flex; align-items: center; padding: 0 20px; gap: 12px; z-index: 100;
}
.topbar-brand { font-weight: 700; font-size: 15px; color: var(--text); text-decoration: none; white-space: nowrap; }
.topbar-brand span { color: var(--accent2); }
.topbar-badge { background: var(--accent); color: #c7d2fe; font-size: 10px; padding: 2px 7px; border-radius: 20px; font-weight: 600; }
.token-row { display: flex; align-items: center; gap: 8px; margin-left: auto; max-width: 520px; flex: 1; }
.token-row label { color: var(--muted); font-size: 11px; white-space: nowrap; }
.token-row input {
  flex: 1; background: #1e2030; border: 1px solid var(--border); border-radius: 6px;
  color: var(--text); padding: 5px 10px; font-size: 12px; font-family: monospace; outline: none;
}
.token-row input:focus { border-color: var(--accent); }
.btn-save { background: var(--accent); color: #fff; border: none; border-radius: 6px; padding: 5px 14px; font-size: 12px; cursor: pointer; white-space: nowrap; }
.btn-save:hover { background: #4338ca; }
#token-status { font-size: 11px; color: var(--muted); white-space: nowrap; }
#token-status.ok { color: var(--success); }

/* sidebar */
.sidebar {
  position: fixed; top: var(--topbar-h); left: 0; bottom: 0;
  width: var(--sidebar-w); background: var(--sidebar-bg);
  border-right: 1px solid var(--border); overflow-y: auto; padding: 10px 0;
}
.sidebar::-webkit-scrollbar { width: 3px; }
.sidebar::-webkit-scrollbar-thumb { background: #2a2d3e; }
.grp-title { color: #4b5563; font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: 10px 16px 3px; }
.slink {
  display: flex; align-items: center; gap: 8px; padding: 5px 16px;
  color: #9ca3af; font-size: 12px; text-decoration: none; cursor: pointer;
  border-left: 2px solid transparent; transition: all .1s;
}
.slink:hover { color: var(--text); background: #1e2030; }
.slink.active { color: var(--text); border-left-color: var(--accent); background: #1a1d2e; }
.badge-m {
  font-size: 8px; font-weight: 700; padding: 1px 5px; border-radius: 3px;
  flex-shrink: 0; letter-spacing: .04em;
}
.m-get    { background: #1d4ed8; color: #bfdbfe; }
.m-post   { background: #166534; color: #bbf7d0; }
.m-put    { background: #92400e; color: #fde68a; }
.m-delete { background: #7f1d1d; color: #fecaca; }

/* main */
.main { margin-left: var(--sidebar-w); margin-top: var(--topbar-h); padding: 28px 36px; max-width: 900px; }
.section-title { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin: 28px 0 12px; padding-bottom: 6px; border-bottom: 1px solid var(--border); }

/* endpoint card */
.ep-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 10px; margin-bottom: 12px; overflow: hidden; }
.ep-head { display: flex; align-items: center; gap: 10px; padding: 12px 16px; cursor: pointer; user-select: none; }
.ep-head:hover { background: #1e2030; }
.ep-method { font-size: 9px; font-weight: 700; padding: 2px 7px; border-radius: 4px; flex-shrink: 0; }
.ep-path { font-family: monospace; font-size: 13px; color: var(--text); flex: 1; }
.ep-desc { font-size: 12px; color: var(--muted); margin-left: auto; text-align: right; }
.ep-lock { font-size: 11px; color: #f59e0b; flex-shrink: 0; }
.ep-body { display: none; padding: 16px; border-top: 1px solid var(--border); }
.ep-body.open { display: block; }

/* form inside card */
.param-label { font-size: 11px; color: var(--muted); margin-bottom: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
.param-row { display: flex; gap: 8px; align-items: flex-start; margin-bottom: 8px; }
.param-name { font-family: monospace; font-size: 12px; color: var(--accent2); min-width: 160px; padding-top: 7px; }
.param-name span { color: #ef4444; font-size: 10px; }
.param-input, .param-select {
  flex: 1; background: #0f1117; border: 1px solid var(--border); border-radius: 6px;
  color: var(--text); padding: 6px 10px; font-size: 13px; font-family: monospace; outline: none;
}
.param-input:focus, .param-select:focus { border-color: var(--accent); }
.param-select option { background: #1a1d2e; }
.btn-try {
  background: var(--accent); color: #fff; border: none; border-radius: 6px;
  padding: 7px 20px; font-size: 13px; font-weight: 600; cursor: pointer; margin-top: 8px;
}
.btn-try:hover { background: #4338ca; }
.btn-try:disabled { background: var(--muted); cursor: not-allowed; }

/* response */
.resp-wrap { margin-top: 14px; display: none; }
.resp-wrap.open { display: block; }
.resp-meta { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; font-size: 12px; }
.resp-status { font-weight: 700; padding: 2px 8px; border-radius: 4px; font-family: monospace; }
.resp-2xx { background: #14532d; color: #86efac; }
.resp-4xx { background: #7f1d1d; color: #fca5a5; }
.resp-5xx { background: #78350f; color: #fcd34d; }
.resp-time { color: var(--muted); }
.resp-body {
  background: #0a0c14; border: 1px solid var(--border); border-radius: 8px;
  padding: 14px; max-height: 340px; overflow: auto; font-family: monospace; font-size: 12px;
  white-space: pre; color: #a5f3fc; line-height: 1.6;
}
.resp-body::-webkit-scrollbar { width: 4px; height: 4px; }
.resp-body::-webkit-scrollbar-thumb { background: #2a2d3e; border-radius: 2px; }
.loading { color: var(--muted); font-size: 13px; font-style: italic; }
</style>
</head>
<body>

<!-- ── Top Bar ── -->
<header class="topbar">
  <a href="/docs" class="topbar-brand">{{ config('app.name') }} <span>API</span></a>
  <span class="topbar-badge">Sandbox</span>
  <div class="token-row">
    <label>API Key:</label>
    <input id="g-token" type="text" placeholder="wtu_... (dari POST /api/login)" autocomplete="off">
    <button class="btn-save" onclick="saveToken()">Simpan</button>
    <span id="token-status">Belum ada key</span>
  </div>
</header>

<!-- ── Sidebar (auto-generated by JS) ── -->
<nav class="sidebar" id="sidebar"></nav>

<!-- ── Main Content (auto-generated by JS) ── -->
<div class="main" id="main"></div>

<script>
const BASE = '{{ url('') }}';

// ── All Endpoints Config ─────────────────────────────────────────
const E = [
  // ── AUTENTIKASI ──────────────────────────────────────────────
  { id:'register', grp:'Autentikasi', m:'POST', path:'/api/register', auth:false,
    desc:'Buat akun baru — kirim OTP ke email',
    body:[
      {n:'name',             t:'text',     ph:'Nama lengkap',      req:1},
      {n:'email',            t:'email',    ph:'email@example.com', req:1},
      {n:'password',         t:'password', ph:'Min 8 karakter',    req:1},
      {n:'password_confirmation', t:'password', ph:'Ulangi password', req:1},
      {n:'phone',            t:'text',     ph:'08123456789',        req:0},
    ]},
  { id:'login', grp:'Autentikasi', m:'POST', path:'/api/login', auth:false,
    desc:'Login — dapatkan API Key',
    body:[
      {n:'email',    t:'email',    ph:'email@example.com', req:1},
      {n:'password', t:'password', ph:'Password',           req:1},
    ]},
  { id:'verify-otp', grp:'Autentikasi', m:'POST', path:'/api/verify-otp', auth:false,
    desc:'Verifikasi OTP dari email — balikan token jika sukses',
    body:[
      {n:'email', t:'email', ph:'email@example.com', req:1},
      {n:'otp',   t:'text',  ph:'6 digit OTP',        req:1},
    ]},
  { id:'resend-otp', grp:'Autentikasi', m:'POST', path:'/api/resend-otp', auth:false,
    desc:'Kirim ulang OTP (maks 3x per 10 menit)',
    body:[{n:'email', t:'email', ph:'email@example.com', req:1}]},
  { id:'forgot-password', grp:'Autentikasi', m:'POST', path:'/api/forgot-password', auth:false,
    desc:'Kirim link reset password ke email',
    body:[{n:'email', t:'email', ph:'email@example.com', req:1}]},
  { id:'reset-password', grp:'Autentikasi', m:'POST', path:'/api/reset-password', auth:false,
    desc:'Reset password dengan token dari email',
    body:[
      {n:'email',                 t:'email',    ph:'email@example.com', req:1},
      {n:'token',                 t:'text',     ph:'Token dari email',   req:1},
      {n:'password',              t:'password', ph:'Password baru',      req:1},
      {n:'password_confirmation', t:'password', ph:'Ulangi password',    req:1},
    ]},
  { id:'logout', grp:'Autentikasi', m:'POST', path:'/api/logout', auth:true,
    desc:'Logout — hapus token aktif', body:[]},

  // ── PROFIL ────────────────────────────────────────────────────
  { id:'me-get', grp:'Profil', m:'GET', path:'/api/me', auth:true,
    desc:'Data profil + saldo user yang sedang login'},
  { id:'me-put', grp:'Profil', m:'PUT', path:'/api/me', auth:true,
    desc:'Update nama / nomor HP',
    body:[
      {n:'name',  t:'text', ph:'Nama baru',       req:0},
      {n:'phone', t:'text', ph:'08123456789', req:0},
    ]},
  { id:'me-password', grp:'Profil', m:'PUT', path:'/api/me/password', auth:true,
    desc:'Ganti password',
    body:[
      {n:'current_password',      t:'password', ph:'Password lama',         req:1},
      {n:'password',              t:'password', ph:'Password baru',          req:1},
      {n:'password_confirmation', t:'password', ph:'Ulangi password baru',   req:1},
    ]},

  // ── PRODUK OKECONNECT ─────────────────────────────────────────
  { id:'categories', grp:'Produk', m:'GET', path:'/api/categories', auth:false,
    desc:'Semua kategori produk OkeConnect'},
  { id:'products', grp:'Produk', m:'GET', path:'/api/products', auth:false,
    desc:'Daftar produk berdasarkan kategori',
    query:[{n:'category', ph:'pulsa / data / game / pln / saldo_gojek / token_pln / voucher / paket_data', req:1}]},
  { id:'products-by-provider', grp:'Produk', m:'GET', path:'/api/products/{category}/{provider}', auth:false,
    desc:'Produk berdasarkan kategori & provider',
    pp:[{n:'category', ph:'pulsa', req:1}, {n:'provider', ph:'Telkomsel', req:1}]},

  // ── E-WALLET ──────────────────────────────────────────────────
  { id:'ewallet-options', grp:'E-Wallet', m:'GET', path:'/api/ewallet/options', auth:false,
    desc:'Daftar layanan e-wallet nominal bebas & biaya'},
  { id:'ewallet-topup', grp:'E-Wallet', m:'POST', path:'/api/ewallet/topup', auth:true,
    desc:'Top-up e-wallet nominal bebas',
    body:[
      {n:'product_code',   t:'text',   ph:'BBSGOP / BBSD / BBSOVON / BBSSH / ...',  req:1},
      {n:'destination',    t:'text',   ph:'08123456789',                              req:1},
      {n:'nominal',        t:'number', ph:'50000',                                    req:1},
      {n:'payment_method', t:'select', options:['balance','midtrans'],                req:1},
    ]},

  // ── TRANSAKSI OKECONNECT ──────────────────────────────────────
  { id:'transactions', grp:'Transaksi', m:'POST', path:'/api/transactions', auth:true,
    desc:'Buat transaksi top-up (OkeConnect fixed-price)',
    body:[
      {n:'product_code',   t:'text',   ph:'S10 / T5 / XL5 / ...',                   req:1},
      {n:'destination',    t:'text',   ph:'08123456789',                              req:1},
      {n:'product_name',   t:'text',   ph:'Telkomsel 10rb',                           req:0},
      {n:'category',       t:'text',   ph:'pulsa / data / game / pln',               req:0},
      {n:'base_price',     t:'number', ph:'10500',                                    req:1},
      {n:'payment_method', t:'select', options:['balance','midtrans'],                req:1},
    ]},

  // ── ORDER ─────────────────────────────────────────────────────
  { id:'orders', grp:'Order', m:'GET', path:'/api/orders', auth:true,
    desc:'Riwayat order (paginated)',
    query:[
      {n:'status',   ph:'pending / processing / completed / failed', req:0},
      {n:'provider', ph:'okeconnect / smmpanel',                      req:0},
      {n:'page',     ph:'1',                                          req:0},
    ]},
  { id:'orders-show', grp:'Order', m:'GET', path:'/api/orders/{id}', auth:true,
    desc:'Detail lengkap order',
    pp:[{n:'id', ph:'1', req:1}]},
  { id:'orders-status', grp:'Order', m:'GET', path:'/api/orders/{id}/status', auth:true,
    desc:'Polling status order (ringan — untuk frontend)',
    pp:[{n:'id', ph:'1', req:1}]},
  { id:'orders-invoice', grp:'Order', m:'GET', path:'/api/orders/{id}/invoice', auth:true,
    desc:'Invoice terstruktur untuk order',
    pp:[{n:'id', ph:'1', req:1}]},

  // ── DEPOSIT ───────────────────────────────────────────────────
  { id:'deposits', grp:'Deposit', m:'GET', path:'/api/deposits', auth:true,
    desc:'Riwayat deposit / pembayaran Midtrans',
    query:[{n:'purpose', ph:'deposit / order_payment', req:0}]},
  { id:'deposits-store', grp:'Deposit', m:'POST', path:'/api/deposits', auth:true,
    desc:'Buat deposit saldo via Midtrans Snap (returns snap_token + redirect_url)',
    body:[{n:'amount', t:'number', ph:'50000 (min 1.000)', req:1}]},
  { id:'deposits-show', grp:'Deposit', m:'GET', path:'/api/deposits/{id}', auth:true,
    desc:'Detail deposit (termasuk snap_token jika masih pending)',
    pp:[{n:'id', ph:'1', req:1}]},
  { id:'deposits-check', grp:'Deposit', m:'GET', path:'/api/deposits/{id}/check', auth:true,
    desc:'Cek & sync status pembayaran Midtrans',
    pp:[{n:'id', ph:'1', req:1}]},

  // ── SMM PANEL ─────────────────────────────────────────────────
  { id:'smm-apps', grp:'SMM Panel', m:'GET', path:'/api/smm/apps', auth:false,
    desc:'Daftar aplikasi / kategori SMM Panel'},
  { id:'smm-search', grp:'SMM Panel', m:'GET', path:'/api/smm/search', auth:false,
    desc:'Cari layanan SMM berdasarkan nama aplikasi',
    query:[{n:'app_name', ph:'Instagram / TikTok / YouTube', req:1}]},
  { id:'smm-balance', grp:'SMM Panel', m:'GET', path:'/api/smm/balance', auth:true,
    desc:'Saldo akun SMM Panel provider'},
  { id:'smm-services', grp:'SMM Panel', m:'GET', path:'/api/smm/services', auth:true,
    desc:'Semua layanan SMM tersedia'},
  { id:'smm-order', grp:'SMM Panel', m:'POST', path:'/api/smm/order', auth:true,
    desc:'Buat order SMM (followers, likes, views, dll)',
    body:[
      {n:'service_id',     t:'number', ph:'ID layanan (dari /api/smm/services)',     req:1},
      {n:'target',         t:'text',   ph:'https://instagram.com/username',          req:1},
      {n:'quantity',       t:'number', ph:'1000',                                    req:1},
      {n:'payment_method', t:'select', options:['balance','midtrans'],              req:1},
    ]},
  { id:'smm-status', grp:'SMM Panel', m:'GET', path:'/api/smm/status/{orderId}', auth:true,
    desc:'Status order SMM',
    pp:[{n:'orderId', ph:'1', req:1}]},
  { id:'smm-refill', grp:'SMM Panel', m:'POST', path:'/api/smm/refill/{orderId}', auth:true,
    desc:'Request refill order SMM yang kurang',
    pp:[{n:'orderId', ph:'1', req:1}]},
  { id:'smm-refill-status', grp:'SMM Panel', m:'GET', path:'/api/smm/refill/status/{refillId}', auth:true,
    desc:'Status refill SMM',
    pp:[{n:'refillId', ph:'1', req:1}]},

  // ── PRODUK DIGITAL (LOKAL) ────────────────────────────────────
  { id:'dig-categories', grp:'Produk Digital', m:'GET', path:'/api/digital/categories', auth:false,
    desc:'Semua kategori produk digital lokal'},
  { id:'dig-category', grp:'Produk Digital', m:'GET', path:'/api/digital/categories/{slug}', auth:false,
    desc:'Detail kategori + daftar produknya',
    pp:[{n:'slug', ph:'mobile-legends', req:1}]},
  { id:'dig-all', grp:'Produk Digital', m:'GET', path:'/api/digital/all-products', auth:false,
    desc:'Semua produk dikelompokkan per kategori'},
  { id:'dig-products', grp:'Produk Digital', m:'GET', path:'/api/digital/products', auth:false,
    desc:'Filter produk digital',
    query:[
      {n:'category', ph:'slug kategori', req:0},
      {n:'q',        ph:'kata kunci',     req:0},
    ]},
  { id:'dig-product', grp:'Produk Digital', m:'GET', path:'/api/digital/products/{kode_produk}', auth:false,
    desc:'Detail produk digital',
    pp:[{n:'kode_produk', ph:'ML-100', req:1}]},

  // ── INQUIRY ───────────────────────────────────────────────────
  { id:'inquiry-products', grp:'Inquiry', m:'GET', path:'/api/inquiry/products', auth:false,
    desc:'Daftar produk inquiry (PLN, BPJS, TV kabel, dll)'},
  { id:'inquiry-check', grp:'Inquiry', m:'POST', path:'/api/inquiry/check', auth:false,
    desc:'Cek tagihan / nomor pelanggan',
    body:[
      {n:'product_code', t:'text', ph:'PLN / BPJS / TELKOM / ...', req:1},
      {n:'customer_id',  t:'text', ph:'ID Pelanggan / Nomor HP',    req:1},
    ]},
  { id:'inquiry-bank-codes', grp:'Inquiry', m:'GET', path:'/api/inquiry/bank-codes', auth:false,
    desc:'Kode bank untuk transfer / virtual account'},

  // ── MIDTRANS ──────────────────────────────────────────────────
  { id:'mid-snap-deposit', grp:'Midtrans', m:'POST', path:'/api/midtrans/snap', auth:true,
    desc:'Buat Snap token untuk TOP UP SALDO',
    body:[
      {n:'purpose', t:'select', options:['deposit'], req:1},
      {n:'amount',  t:'number', ph:'50000 (min 1.000)',              req:1},
    ]},
  { id:'mid-snap-order', grp:'Midtrans', m:'POST', path:'/api/midtrans/snap', auth:true,
    desc:'Buat Snap token untuk membayar ORDER yang pending',
    body:[
      {n:'purpose',  t:'select', options:['order'], req:1},
      {n:'order_id', t:'number', ph:'ID order yang pending', req:1},
    ]},
  { id:'mid-snap-trx', grp:'Midtrans', m:'POST', path:'/api/midtrans/snap', auth:true,
    desc:'Buat order + Snap token sekaligus (untuk produk OkeConnect)',
    body:[
      {n:'purpose',      t:'select', options:['transaction'],      req:1},
      {n:'product_code', t:'text',   ph:'S10 / T5 / ...',           req:1},
      {n:'destination',  t:'text',   ph:'08123456789',               req:1},
      {n:'product_name', t:'text',   ph:'Telkomsel 10rb',            req:0},
      {n:'category',     t:'text',   ph:'pulsa / data / game / pln', req:0},
      {n:'base_price',   t:'number', ph:'10500',                     req:1},
    ]},
  { id:'mid-status', grp:'Midtrans', m:'GET', path:'/api/midtrans/status/{invoiceNo}', auth:true,
    desc:'Cek & sync status pembayaran Midtrans (poll dari frontend)',
    pp:[{n:'invoiceNo', ph:'MID-DEP-1-1234567890', req:1}]},
  { id:'mid-cancel', grp:'Midtrans', m:'POST', path:'/api/midtrans/cancel/{invoiceNo}', auth:true,
    desc:'Batalkan transaksi Midtrans yang masih pending',
    pp:[{n:'invoiceNo', ph:'MID-DEP-1-1234567890', req:1}]},
  { id:'mid-webhook', grp:'Midtrans', m:'POST', path:'/api/midtrans/webhook', auth:false,
    desc:'Webhook notifikasi dari server Midtrans (jangan dipanggil manual)',
    body:[
      {n:'order_id',           t:'text', ph:'MID-DEP-1-...', req:1},
      {n:'transaction_status', t:'text', ph:'settlement',     req:1},
      {n:'status_code',        t:'text', ph:'200',            req:1},
      {n:'gross_amount',       t:'text', ph:'50000.00',       req:1},
      {n:'signature_key',      t:'text', ph:'(generated by Midtrans)', req:1},
    ]},
];

// ── API Key handling ─────────────────────────────────────────────
let TOKEN = localStorage.getItem('api_key') || '';
function saveToken() {
  TOKEN = document.getElementById('g-token').value.trim();
  if (TOKEN) {
    localStorage.setItem('api_key', TOKEN);
    document.getElementById('token-status').textContent = '✓ Key tersimpan';
    document.getElementById('token-status').className = 'ok';
  } else {
    localStorage.removeItem('api_key');
    document.getElementById('token-status').textContent = 'Belum ada key';
    document.getElementById('token-status').className = '';
  }
}
window.addEventListener('DOMContentLoaded', () => {
  if (TOKEN) {
    document.getElementById('g-token').value = TOKEN;
    document.getElementById('token-status').textContent = '✓ Key aktif';
    document.getElementById('token-status').className = 'ok';
  }
  render();
});

// ── Render ─────────────────────────────────────────────────────
function render() {
  const groups = [...new Set(E.map(e => e.grp))];
  const sidebar = document.getElementById('sidebar');
  const main    = document.getElementById('main');

  // Sidebar
  sidebar.innerHTML = groups.map(g => `
    <div class="grp-title">${g}</div>
    ${E.filter(e => e.grp === g).map(e => `
      <a class="slink" onclick="scrollTo('${e.id}')">
        <span class="badge-m m-${e.m.toLowerCase()}">${e.m}</span>
        <span style="font-family:monospace;font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${shortPath(e.path)}</span>
      </a>`).join('')}
  `).join('');

  // Cards
  main.innerHTML = groups.map(g => `
    <div class="section-title" id="grp-${slug(g)}">${g}</div>
    ${E.filter(e => e.grp === g).map(e => renderCard(e)).join('')}
  `).join('');
}

function shortPath(p) {
  return p.replace('/api/','').replace(/\{([^}]+)\}/g,'…');
}
function slug(s) { return s.toLowerCase().replace(/\s+/g,'-'); }

function renderCard(e) {
  const body   = e.body   || [];
  const query  = e.query  || [];
  const pp     = e.pp     || [];
  const hasForm = body.length || query.length || pp.length;

  return `
<div class="ep-card" id="card-${e.id}">
  <div class="ep-head" onclick="toggleCard('${e.id}')">
    <span class="ep-method badge-m m-${e.m.toLowerCase()}">${e.m}</span>
    <span class="ep-path">${e.path}</span>
    ${e.auth ? '<span class="ep-lock" title="Butuh API Key (x-api-key)">🔒</span>' : ''}
    <span class="ep-desc">${e.desc}</span>
  </div>
  <div class="ep-body" id="body-${e.id}">
    ${pp.length ? `<div class="param-label">Path Parameters</div>${pp.map(p => paramRow(e.id,'pp',p)).join('')}` : ''}
    ${query.length ? `<div class="param-label" style="margin-top:${pp.length?10:0}px">Query Parameters</div>${query.map(p => paramRow(e.id,'q',p)).join('')}` : ''}
    ${body.length ? `<div class="param-label" style="margin-top:${(pp.length||query.length)?10:0}px">Request Body (JSON)</div>${body.map(p => paramRow(e.id,'b',p)).join('')}` : ''}
    ${!hasForm && !e.auth ? '<p style="color:var(--muted);font-size:12px;margin-bottom:8px">Tidak ada parameter</p>' : ''}
    ${!hasForm && e.auth  ? '<p style="color:var(--muted);font-size:12px;margin-bottom:8px">Tidak ada body — hanya butuh token</p>' : ''}
    <button class="btn-try" onclick="send('${e.id}')">▶ Kirim Request</button>
    <div class="resp-wrap" id="resp-${e.id}">
      <div class="resp-meta">
        <span class="resp-status" id="rs-${e.id}">—</span>
        <span class="resp-time"  id="rt-${e.id}"></span>
      </div>
      <div class="resp-body" id="rb-${e.id}"></div>
    </div>
  </div>
</div>`;
}

function paramRow(eid, ptype, p) {
  const id = `f-${eid}-${ptype}-${p.n}`;
  const req = p.req ? '<span title="wajib"> *</span>' : '';
  let input;
  if (p.t === 'select') {
    input = `<select class="param-select" id="${id}">${p.options.map(o=>`<option>${o}</option>`).join('')}</select>`;
  } else {
    input = `<input class="param-input" id="${id}" type="${p.t||'text'}" placeholder="${p.ph||''}" autocomplete="off">`;
  }
  return `<div class="param-row"><span class="param-name">${p.n}${req}</span>${input}</div>`;
}

// ── Toggle card ───────────────────────────────────────────────────
function toggleCard(id) {
  document.getElementById('body-'+id).classList.toggle('open');
}

// ── Scroll to card ────────────────────────────────────────────────
function scrollTo(id) {
  const el = document.getElementById('card-'+id);
  if (!el) return;
  el.scrollIntoView({behavior:'smooth', block:'start'});
  document.getElementById('body-'+id).classList.add('open');
  document.querySelectorAll('.slink').forEach(l => l.classList.remove('active'));
  event.currentTarget && event.currentTarget.classList.add('active');
}

// ── Send request ──────────────────────────────────────────────────
async function send(eid) {
  const ep = E.find(e => e.id === eid);
  if (!ep) return;

  const btn = document.querySelector(`#card-${eid} .btn-try`);
  btn.disabled = true;
  btn.textContent = '⏳ Mengirim...';

  const respWrap = document.getElementById('resp-'+eid);
  const respBody = document.getElementById('rb-'+eid);
  const respStat = document.getElementById('rs-'+eid);
  const respTime = document.getElementById('rt-'+eid);
  respWrap.classList.add('open');
  respBody.textContent = 'Mengirim...';
  respBody.className = 'resp-body loading';
  respStat.textContent = '—';
  respStat.className = 'resp-status';
  respTime.textContent = '';

  // Build URL — replace path params
  let url = BASE + ep.path;
  (ep.pp || []).forEach(p => {
    const val = document.getElementById(`f-${eid}-pp-${p.n}`)?.value?.trim() || '';
    url = url.replace(`{${p.n}}`, encodeURIComponent(val) || `{${p.n}}`);
  });

  // Build query string
  const qparams = new URLSearchParams();
  (ep.query || []).forEach(p => {
    const val = document.getElementById(`f-${eid}-q-${p.n}`)?.value?.trim();
    if (val) qparams.set(p.n, val);
  });
  if ([...qparams].length) url += '?' + qparams.toString();

  // Build body
  const bodyFields = ep.body || [];
  let bodyData = null;
  if (bodyFields.length && ep.m !== 'GET') {
    const obj = {};
    bodyFields.forEach(p => {
      const el = document.getElementById(`f-${eid}-b-${p.n}`);
      if (el) {
        const v = el.value.trim();
        if (v !== '') {
          // Auto-cast numbers
          obj[p.n] = (p.t === 'number' && v !== '') ? Number(v) : v;
        }
      }
    });
    bodyData = JSON.stringify(obj);
  }

  // Headers
  const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
  const tok = document.getElementById('g-token')?.value?.trim() || TOKEN;
  if (tok) headers['x-api-key'] = tok;

  const t0 = Date.now();
  try {
    const res = await fetch(url, {
      method: ep.m,
      headers,
      body: bodyData,
    });
    const ms = Date.now() - t0;

    let parsed, text;
    try {
      parsed = await res.json();
      text = JSON.stringify(parsed, null, 2);
    } catch { text = await res.text(); }

    // Auto-save api_key on successful login / verify-otp
    if (res.ok && parsed?.data?.api_key) {
      const key = parsed.data.api_key;
      TOKEN = key;
      localStorage.setItem('api_key', key);
      document.getElementById('g-token').value = key;
      document.getElementById('token-status').textContent = '✓ Key tersimpan otomatis';
      document.getElementById('token-status').className = 'ok';
    }

    const cls = res.status < 300 ? 'resp-2xx' : res.status < 500 ? 'resp-4xx' : 'resp-5xx';
    respStat.textContent = res.status + ' ' + res.statusText;
    respStat.className = 'resp-status ' + cls;
    respTime.textContent = ms + ' ms';
    respBody.textContent = text;
    respBody.className = 'resp-body';
  } catch (err) {
    respStat.textContent = 'Error';
    respStat.className = 'resp-status resp-5xx';
    respBody.textContent = 'Fetch error: ' + err.message;
    respBody.className = 'resp-body';
  }

  btn.disabled = false;
  btn.textContent = '▶ Kirim Request';
}
</script>
</body>
</html>
