@extends('layouts.app')

@section('title', 'Invoice - ' . $invoice_id)

@section('content')
    <div class="max-w-lg mx-auto">
        {{-- Success Icon --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-50 border-2 border-green-100 mb-3">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-xl font-[Poppins] font-extrabold text-gray-800 mb-1">Pesanan Berhasil!</h1>
            <p class="text-xs text-gray-400">Silakan lakukan pembayaran sebelum batas waktu berakhir</p>
        </div>

        {{-- Invoice Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-[#3B82F6] to-[#60A5FA] px-5 py-3.5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] text-blue-200 mb-0.5">Invoice ID</div>
                        <div class="text-sm font-bold text-white font-mono">{{ $invoice_id }}</div>
                    </div>
                    <div class="px-2.5 py-1 rounded-lg bg-[#FCD34D] shadow-sm">
                        <span class="text-[10px] font-bold text-gray-900">Menunggu Pembayaran</span>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-5 space-y-4">
                {{-- Game Info --}}
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                    <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 border border-gray-100">
                        <img src="{{ $game['image'] }}" alt="{{ $game['name'] }}" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-800">{{ $game['name'] }}</div>
                        <div class="text-[10px] text-gray-400">{{ $game['category'] }} &bull; {{ $game['developer'] }}</div>
                    </div>
                </div>

                {{-- Details --}}
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">User ID</span>
                        <span class="text-xs text-gray-800 font-semibold font-mono">{{ $user_id }}</span>
                    </div>
                    @if($server_id)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Server ID</span>
                        <span class="text-xs text-gray-800 font-semibold font-mono">{{ $server_id }}</span>
                    </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Item</span>
                        <span class="text-xs text-gray-800 font-semibold">{{ $item['name'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Metode Pembayaran</span>
                        <span class="text-xs text-gray-800 font-semibold">{{ $payment }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Tanggal</span>
                        <span class="text-xs text-gray-800 font-semibold">{{ now()->format('d M Y, H:i') }} WIB</span>
                    </div>
                </div>

                {{-- Total --}}
                <div class="pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Subtotal</span>
                        <span class="text-xs text-gray-800 font-semibold">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between mt-1.5">
                        <span class="text-xs text-gray-400">Biaya Admin</span>
                        <span class="text-xs text-green-500 font-semibold">Gratis</span>
                    </div>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                        <span class="text-sm font-bold text-gray-800">Total Bayar</span>
                        <span class="text-xl font-extrabold text-[#3B82F6]">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Timer --}}
                <div class="rounded-xl bg-[#F0F4F8] border border-gray-200 p-3.5 text-center">
                    <div class="text-[10px] text-gray-400 mb-1.5 font-medium">Batas waktu pembayaran</div>
                    <div id="countdown" class="text-2xl font-extrabold text-[#3B82F6] font-mono">23:59:59</div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 bg-[#F0F4F8] border-t border-gray-100 flex flex-col sm:flex-row gap-2.5">
                <a href="{{ route('home') }}" class="flex-1 py-2.5 rounded-xl text-xs font-semibold text-center text-[#3B82F6] border-2 border-[#3B82F6]/20 hover:bg-blue-50 transition-all">
                    Kembali ke Home
                </a>
                <button onclick="alert('Ini adalah dummy website. Pembayaran tidak diproses.')" class="flex-1 py-2.5 rounded-xl text-xs font-bold text-center bg-gradient-to-r from-[#3B82F6] to-[#60A5FA] text-white hover:opacity-90 transition-all shadow-md shadow-blue-200">
                    Konfirmasi Pembayaran
                </button>
            </div>
        </div>

        {{-- Info --}}
        <div class="mt-4 p-3.5 rounded-xl bg-blue-50 border border-blue-100">
            <div class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-[#3B82F6] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-xs font-semibold text-gray-700 mb-0.5">Catatan</p>
                    <p class="text-[10px] text-gray-500 leading-relaxed">Ini adalah website dummy untuk demonstrasi. Tidak ada transaksi nyata yang diproses. Semua data bersifat fiktif.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    let timeLeft = 24 * 60 * 60 - 1;
    const countdownEl = document.getElementById('countdown');

    function updateCountdown() {
        const hours = Math.floor(timeLeft / 3600);
        const minutes = Math.floor((timeLeft % 3600) / 60);
        const seconds = timeLeft % 60;

        countdownEl.textContent =
            String(hours).padStart(2, '0') + ':' +
            String(minutes).padStart(2, '0') + ':' +
            String(seconds).padStart(2, '0');

        if (timeLeft > 0) {
            timeLeft--;
            setTimeout(updateCountdown, 1000);
        }
    }

    updateCountdown();
</script>
@endsection
