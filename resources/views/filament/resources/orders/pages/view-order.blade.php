<x-filament-panels::page>
    <div class="max-w-3xl mx-auto">
        {{-- Invoice Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-primary-600 to-primary-500 px-6 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-primary-200 mb-1">Invoice</p>
                        <p class="text-lg font-bold text-white font-mono">#{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div class="px-3 py-1.5 rounded-lg
                        @if($record->status === 'completed') bg-green-400/20 border border-green-400/30
                        @elseif($record->status === 'processing') bg-amber-400/20 border border-amber-400/30
                        @elseif($record->status === 'failed') bg-red-400/20 border border-red-400/30
                        @else bg-white/20 border border-white/30
                        @endif
                    ">
                        <span class="text-xs font-bold
                            @if($record->status === 'completed') text-green-100
                            @elseif($record->status === 'processing') text-amber-100
                            @elseif($record->status === 'failed') text-red-100
                            @else text-white
                            @endif
                        ">{{ $this->getStatusLabel() }}</span>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">

                {{-- Order Info Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Tanggal</p>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $record->created_at->format('d M Y') }}</p>
                        <p class="text-[10px] text-gray-400">{{ $record->created_at->format('H:i') }} WIB</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Ref ID</p>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 font-mono break-all">{{ $record->order_ref }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Provider</p>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $this->getProviderLabel() }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Pembeli</p>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $record->user?->name ?? '-' }}</p>
                        <p class="text-[10px] text-gray-400">{{ $record->user?->email }}</p>
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-700">

                {{-- Product Detail --}}
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3">Detail Produk</p>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Produk</span>
                            <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $record->product_name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Kode Produk</span>
                            <span class="text-xs font-mono font-semibold text-gray-800 dark:text-gray-200">{{ $record->product_code }}</span>
                        </div>
                        @if($record->category)
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Kategori</span>
                            <span class="text-xs text-gray-800 dark:text-gray-200">{{ $record->category }}</span>
                        </div>
                        @endif
                        @if($record->target && $record->target !== '-')
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Target</span>
                            <span class="text-xs font-mono text-gray-800 dark:text-gray-200">{{ $record->target }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Jumlah</span>
                            <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $record->quantity }}x</span>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-700">

                {{-- Pricing --}}
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3">Rincian Harga</p>
                    <div class="space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Harga Dasar</span>
                            <span class="text-xs text-gray-800 dark:text-gray-200">Rp {{ number_format($record->base_price, 0, ',', '.') }}</span>
                        </div>
                        @if((float)$record->markup > 0)
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Markup</span>
                            <span class="text-xs text-gray-800 dark:text-gray-200">+ Rp {{ number_format($record->markup, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Harga Jual</span>
                            <span class="text-xs text-gray-800 dark:text-gray-200">Rp {{ number_format($record->sell_price, 0, ',', '.') }}</span>
                        </div>
                        @if($record->quantity > 1)
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Subtotal ({{ $record->quantity }}x)</span>
                            <span class="text-xs text-gray-800 dark:text-gray-200">Rp {{ number_format($record->sell_price * $record->quantity, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if((float)$record->payment_fee > 0)
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Biaya Pembayaran</span>
                            <span class="text-xs text-gray-800 dark:text-gray-200">Rp {{ number_format($record->payment_fee, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between pt-3 border-t border-gray-200 dark:border-gray-600">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Total Bayar</span>
                            <span class="text-lg font-extrabold text-primary-600 dark:text-primary-400">Rp {{ number_format($record->total_pay, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-700">

                {{-- Payment Info --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Metode Bayar</p>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold
                            @if($record->payment_method === 'balance') bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300
                            @elseif($record->payment_method === 'qris') bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300
                            @else bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                            @endif
                        ">{{ $this->getPaymentMethodLabel() }}</span>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Status Bayar</p>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold
                            @if($record->payment_status === 'lunas') bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300
                            @else bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300
                            @endif
                        ">{{ $this->getPaymentStatusLabel() }}</span>
                    </div>
                    @if((float)$record->profit > 0)
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Profit</p>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                            + Rp {{ number_format($record->profit, 0, ',', '.') }}
                        </span>
                    </div>
                    @endif
                </div>

                {{-- SN / Delivery --}}
                @if($record->sn)
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">SN / Delivery</p>
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                        <pre class="text-xs font-mono text-green-800 dark:text-green-300 whitespace-pre-wrap break-all m-0">{{ $record->sn }}</pre>
                    </div>
                </div>
                @endif

                {{-- Notes --}}
                @if($record->notes)
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">Catatan</p>
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                        <p class="text-xs text-amber-800 dark:text-amber-300">{{ $record->notes }}</p>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</x-filament-panels::page>
