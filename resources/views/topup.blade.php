@extends('layouts.app')

@section('title', 'Top Up ' . $game['name'])

@section('content')
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm mb-5">
        <a href="{{ route('home') }}" class="text-gray-400 hover:text-[#3B82F6] transition-colors">Home</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-700 font-medium">{{ $game['name'] }}</span>
    </nav>

    {{-- Game Header Card --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#3B82F6] to-[#60A5FA] p-5 sm:p-6 mb-5">
        <div class="absolute top-0 right-0 w-60 h-60 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4"></div>
        <div class="relative flex items-center gap-4">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden border-2 border-white/20 shadow-lg flex-shrink-0">
                <img src="{{ $game['image'] }}" alt="{{ $game['name'] }}" class="w-full h-full object-cover">
            </div>
            <div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-white/20 text-[10px] font-bold text-white mb-1.5">{{ $game['category'] }}</span>
                <h1 class="text-xl sm:text-2xl font-[Poppins] font-extrabold text-white">{{ $game['name'] }}</h1>
                <p class="text-xs text-blue-100 mt-0.5">{{ $game['developer'] }} &bull; {{ $game['description'] }}</p>
            </div>
        </div>
    </div>

    {{-- Top Up Form --}}
    <form action="{{ route('topup.process', $game['slug']) }}" method="POST" id="topupForm">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Left Column --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Step 1: User ID --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-[#3B82F6] flex items-center justify-center text-xs font-bold text-white">1</div>
                        <h2 class="text-sm font-bold text-gray-800">Masukkan Data Akun</h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">User ID</label>
                            <input type="text" name="user_id" required placeholder="Masukkan User ID" class="w-full px-3.5 py-2.5 rounded-xl bg-[#F0F4F8] border border-gray-200 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#3B82F6] focus:ring-2 focus:ring-[#3B82F6]/20 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Server ID <span class="text-gray-400">(Opsional)</span></label>
                            <input type="text" name="server_id" placeholder="Masukkan Server ID" class="w-full px-3.5 py-2.5 rounded-xl bg-[#F0F4F8] border border-gray-200 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#3B82F6] focus:ring-2 focus:ring-[#3B82F6]/20 transition-all">
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-2.5 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#3B82F6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pastikan User ID dan Server ID benar sebelum melanjutkan
                    </p>
                </div>

                {{-- Step 2: Select Item --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-[#3B82F6] flex items-center justify-center text-xs font-bold text-white">2</div>
                        <h2 class="text-sm font-bold text-gray-800">Pilih Nominal</h2>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($game['items'] as $index => $item)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="item" value="{{ $item['name'] }}" data-price="{{ $item['price'] }}" class="sr-only peer" {{ $index === 0 ? 'checked' : '' }}>
                            <div class="p-3.5 rounded-xl border-2 border-gray-100 bg-white peer-checked:border-[#3B82F6] peer-checked:bg-blue-50 hover:border-gray-200 transition-all">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <svg class="w-4 h-4 text-[#F59E0B]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                </div>
                                <div class="text-sm font-extrabold text-[#3B82F6]">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Step 3: Payment Method --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-[#3B82F6] flex items-center justify-center text-xs font-bold text-white">3</div>
                        <h2 class="text-sm font-bold text-gray-800">Pilih Pembayaran</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">E-Wallet</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach(['GoPay', 'OVO', 'DANA', 'ShopeePay'] as $i => $wallet)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="payment" value="{{ $wallet }}" class="sr-only peer" {{ $i === 0 ? 'checked' : '' }}>
                                    <div class="flex items-center justify-center p-2.5 rounded-xl border-2 border-gray-100 bg-white peer-checked:border-[#3B82F6] peer-checked:bg-blue-50 hover:border-gray-200 transition-all">
                                        <span class="text-xs font-semibold text-gray-700 peer-checked:text-[#3B82F6]">{{ $wallet }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Bank Transfer</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach(['BCA', 'BNI', 'BRI', 'Mandiri'] as $bank)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="payment" value="{{ $bank }}" class="sr-only peer">
                                    <div class="flex items-center justify-center p-2.5 rounded-xl border-2 border-gray-100 bg-white peer-checked:border-[#3B82F6] peer-checked:bg-blue-50 hover:border-gray-200 transition-all">
                                        <span class="text-xs font-semibold text-gray-700">{{ $bank }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Convenience Store</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach(['Indomaret', 'Alfamart'] as $store)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="payment" value="{{ $store }}" class="sr-only peer">
                                    <div class="flex items-center justify-center p-2.5 rounded-xl border-2 border-gray-100 bg-white peer-checked:border-[#3B82F6] peer-checked:bg-blue-50 hover:border-gray-200 transition-all">
                                        <span class="text-xs font-semibold text-gray-700">{{ $store }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column - Order Summary --}}
            <div class="lg:col-span-1">
                <div class="sticky top-20 bg-white rounded-2xl border border-gray-100 p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Ringkasan Pesanan</h3>

                    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                        <div class="w-11 h-11 rounded-xl overflow-hidden flex-shrink-0">
                            <img src="{{ $game['image'] }}" alt="{{ $game['name'] }}" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-800">{{ $game['name'] }}</div>
                            <div class="text-[10px] text-gray-400">{{ $game['category'] }}</div>
                        </div>
                    </div>

                    <div class="space-y-2.5 mb-4 pb-4 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400">Item</span>
                            <span id="summaryItem" class="text-xs text-gray-800 font-semibold">{{ $game['items'][0]['name'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400">Harga</span>
                            <span id="summaryPrice" class="text-xs text-gray-800 font-semibold">Rp {{ number_format($game['items'][0]['price'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400">Biaya Admin</span>
                            <span class="text-xs text-green-500 font-semibold">Gratis</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400">Pembayaran</span>
                            <span id="summaryPayment" class="text-xs text-gray-800 font-semibold">GoPay</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-5">
                        <span class="text-xs font-bold text-gray-800">Total Bayar</span>
                        <span id="summaryTotal" class="text-lg font-extrabold text-[#3B82F6]">Rp {{ number_format($game['items'][0]['price'], 0, ',', '.') }}</span>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl text-sm font-bold bg-gradient-to-r from-[#3B82F6] to-[#60A5FA] text-white hover:opacity-90 transition-all shadow-lg shadow-blue-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Bayar Sekarang
                    </button>

                    <p class="text-[10px] text-gray-400 text-center mt-2.5">Dengan menekan tombol ini, kamu menyetujui syarat & ketentuan yang berlaku.</p>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    function formatRupiah(number) {
        return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    document.querySelectorAll('input[name="item"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('summaryItem').textContent = this.value;
            document.getElementById('summaryPrice').textContent = formatRupiah(this.dataset.price);
            document.getElementById('summaryTotal').textContent = formatRupiah(this.dataset.price);
        });
    });

    document.querySelectorAll('input[name="payment"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('summaryPayment').textContent = this.value;
        });
    });
</script>
@endsection
