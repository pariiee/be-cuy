@extends('layouts.app')

@section('title', 'Pengaturan Markup QRIS')

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pengaturan Markup QRIS</h1>
        <p class="text-sm text-gray-500 mt-1">Atur markup otomatis untuk pembayaran via QRIS. Admin & Reseller tidak terkena markup.</p>
    </div>

    {{-- Alert --}}
    <div id="alertBox" class="hidden mb-4 px-4 py-3 rounded-xl text-sm font-medium"></div>

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form id="markupForm" class="space-y-6">
            @csrf

            {{-- Status Toggle --}}
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                <div>
                    <p class="text-sm font-semibold text-gray-700">Markup Aktif</p>
                    <p class="text-xs text-gray-400 mt-0.5">Aktifkan/nonaktifkan markup QRIS untuk semua user biasa</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="is_active" name="is_active" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#3B82F6]"></div>
                </label>
            </div>

            {{-- Markup Deposit --}}
            <div class="border border-gray-100 rounded-xl p-5 space-y-4">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Markup Deposit (Top Up Saldo)
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Tipe Markup</label>
                        <select id="markup_deposit_type" name="markup_deposit_type" class="w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all">
                            <option value="fixed">Nominal Tetap (Rp)</option>
                            <option value="percentage">Persentase (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Nilai Markup</label>
                        <input type="number" id="markup_deposit_value" name="markup_deposit_value" min="0" step="0.01" value="0" class="w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all" placeholder="0">
                    </div>
                </div>
                <p class="text-xs text-gray-400" id="depositPreview">Contoh: Deposit Rp100.000 → markup Rp0 → Total Rp100.000</p>
            </div>

            {{-- Markup Transaction --}}
            <div class="border border-gray-100 rounded-xl p-5 space-y-4">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Markup Transaksi (Pembelian Produk)
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Tipe Markup</label>
                        <select id="markup_transaction_type" name="markup_transaction_type" class="w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all">
                            <option value="fixed">Nominal Tetap (Rp)</option>
                            <option value="percentage">Persentase (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Nilai Markup</label>
                        <input type="number" id="markup_transaction_value" name="markup_transaction_value" min="0" step="0.01" value="0" class="w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all" placeholder="0">
                    </div>
                </div>
                <p class="text-xs text-gray-400" id="transactionPreview">Contoh: Transaksi Rp50.000 → markup Rp0 → Total Rp50.000</p>
            </div>

            {{-- Info Box --}}
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-xs text-blue-700 space-y-1">
                        <p class="font-semibold">Catatan Penting:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            <li>Markup hanya berlaku untuk user dengan role <strong>member</strong>.</li>
                            <li>User dengan role <strong>admin</strong> atau <strong>reseller</strong> mendapat harga normal (markup = 0).</li>
                            <li>Markup diterapkan otomatis saat user memilih pembayaran QRIS.</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end">
                <button type="submit" id="submitBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#3B82F6] to-[#60A5FA] text-white text-sm font-semibold hover:opacity-90 transition-all shadow-md shadow-blue-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const API_BASE = '/api/admin/qris-markup';
    const TOKEN = '{{ session("api_token", "") }}';

    function fmt(n) {
        return new Intl.NumberFormat('id-ID').format(Math.round(n));
    }

    function updatePreview(prefix, sampleAmount) {
        const type = document.getElementById(prefix + '_type').value;
        const value = parseFloat(document.getElementById(prefix + '_value').value) || 0;
        let markup = type === 'percentage' ? sampleAmount * (value / 100) : value;
        const total = sampleAmount + markup;
        const label = prefix === 'markup_deposit' ? 'Deposit' : 'Transaksi';
        const el = prefix === 'markup_deposit' ? 'depositPreview' : 'transactionPreview';
        document.getElementById(el).textContent =
            `Contoh: ${label} Rp${fmt(sampleAmount)} → markup Rp${fmt(markup)} → Total Rp${fmt(total)}`;
    }

    function showAlert(message, isError = false) {
        const box = document.getElementById('alertBox');
        box.className = `mb-4 px-4 py-3 rounded-xl text-sm font-medium ${isError ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200'}`;
        box.textContent = message;
        box.classList.remove('hidden');
        setTimeout(() => box.classList.add('hidden'), 5000);
    }

    // Load current settings
    async function loadSettings() {
        try {
            const headers = { 'Accept': 'application/json' };
            if (TOKEN) headers['Authorization'] = `Bearer ${TOKEN}`;

            const res = await fetch(API_BASE, { headers });
            const json = await res.json();

            if (json.status && json.data) {
                const d = json.data;
                document.getElementById('is_active').checked = d.is_active;
                document.getElementById('markup_deposit_type').value = d.markup_deposit_type;
                document.getElementById('markup_deposit_value').value = d.markup_deposit_value;
                document.getElementById('markup_transaction_type').value = d.markup_transaction_type;
                document.getElementById('markup_transaction_value').value = d.markup_transaction_value;
                updatePreview('markup_deposit', 100000);
                updatePreview('markup_transaction', 50000);
            }
        } catch (e) {
            showAlert('Gagal memuat pengaturan: ' + e.message, true);
        }
    }

    // Save settings
    document.getElementById('markupForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        try {
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            };
            if (TOKEN) headers['Authorization'] = `Bearer ${TOKEN}`;

            const res = await fetch(API_BASE, {
                method: 'PUT',
                headers,
                body: JSON.stringify({
                    markup_deposit_type: document.getElementById('markup_deposit_type').value,
                    markup_deposit_value: parseFloat(document.getElementById('markup_deposit_value').value) || 0,
                    markup_transaction_type: document.getElementById('markup_transaction_type').value,
                    markup_transaction_value: parseFloat(document.getElementById('markup_transaction_value').value) || 0,
                    is_active: document.getElementById('is_active').checked,
                }),
            });

            const json = await res.json();
            if (json.status) {
                showAlert(json.message || 'Berhasil disimpan');
            } else {
                showAlert(json.message || 'Gagal menyimpan', true);
            }
        } catch (e) {
            showAlert('Error: ' + e.message, true);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Simpan Perubahan';
        }
    });

    // Live preview listeners
    ['markup_deposit_type', 'markup_deposit_value'].forEach(id => {
        document.getElementById(id).addEventListener('input', () => updatePreview('markup_deposit', 100000));
    });
    ['markup_transaction_type', 'markup_transaction_value'].forEach(id => {
        document.getElementById(id).addEventListener('input', () => updatePreview('markup_transaction', 50000));
    });

    loadSettings();
</script>
@endsection
