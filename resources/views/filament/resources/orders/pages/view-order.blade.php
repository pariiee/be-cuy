<x-filament-panels::page>
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.06);">

            {{-- ═══ HEADER ═══ --}}
            <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%); padding: 32px; position: relative; overflow: hidden;">
                <div style="position: absolute; top: -40%; right: -10%; width: 250px; height: 250px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%;"></div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; position: relative; z-index: 1;">
                    <div>
                        <div style="font-size: 11px; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px;">Invoice</div>
                        <div style="font-size: 28px; font-weight: 900; color: #fff; font-family: monospace; letter-spacing: -1px;">#{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}</div>
                        <div style="font-size: 12px; color: rgba(255,255,255,0.45); font-family: monospace; margin-top: 8px;">{{ $record->order_ref }}</div>
                    </div>
                    <div style="text-align: right;">
                        @php
                            $statusBg = match($record->status) {
                                'completed' => 'rgba(52,211,153,0.2)',
                                'processing' => 'rgba(251,191,36,0.2)',
                                'failed' => 'rgba(248,113,113,0.2)',
                                default => 'rgba(255,255,255,0.15)',
                            };
                            $statusBorder = match($record->status) {
                                'completed' => 'rgba(52,211,153,0.4)',
                                'processing' => 'rgba(251,191,36,0.4)',
                                'failed' => 'rgba(248,113,113,0.4)',
                                default => 'rgba(255,255,255,0.3)',
                            };
                        @endphp
                        <span style="display: inline-block; padding: 8px 16px; border-radius: 10px; background: {{ $statusBg }}; border: 1px solid {{ $statusBorder }}; color: #fff; font-size: 13px; font-weight: 700;">
                            {{ $this->getStatusLabel() }}
                        </span>
                        <div style="font-size: 11px; color: rgba(255,255,255,0.45); margin-top: 12px;">{{ $record->created_at->format('d M Y') }} &bull; {{ $record->created_at->format('H:i') }} WIB</div>
                    </div>
                </div>
            </div>

            {{-- ═══ BODY ═══ --}}
            <div style="padding: 32px; background: #111827;">

                {{-- Info Cards Grid --}}
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 28px;">
                    {{-- Provider --}}
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px;">
                        <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: #9ca3af; margin-bottom: 8px; font-weight: 600;">Provider</div>
                        <div style="font-size: 14px; font-weight: 700; color: #f3f4f6;">{{ $this->getProviderLabel() }}</div>
                    </div>
                    {{-- Pembeli --}}
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px;">
                        <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: #9ca3af; margin-bottom: 8px; font-weight: 600;">Pembeli</div>
                        <div style="font-size: 14px; font-weight: 700; color: #f3f4f6;">{{ $record->user?->name ?? '-' }}</div>
                        <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">{{ $record->user?->email }}</div>
                    </div>
                    {{-- Pembayaran --}}
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px;">
                        <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: #9ca3af; margin-bottom: 8px; font-weight: 600;">Pembayaran</div>
                        @php
                            $payBg = match($record->payment_method) {
                                'balance' => '#374151',
                                'qris' => 'rgba(147,51,234,0.2)',
                                default => 'rgba(59,130,246,0.2)',
                            };
                            $payColor = match($record->payment_method) {
                                'balance' => '#d1d5db',
                                'qris' => '#c084fc',
                                default => '#93c5fd',
                            };
                        @endphp
                        <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; background: {{ $payBg }}; color: {{ $payColor }}; font-size: 12px; font-weight: 700;">{{ $this->getPaymentMethodLabel() }}</span>
                    </div>
                    {{-- Status Bayar --}}
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px;">
                        <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: #9ca3af; margin-bottom: 8px; font-weight: 600;">Status Bayar</div>
                        @php
                            $psBg = $record->payment_status === 'lunas' ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)';
                            $psColor = $record->payment_status === 'lunas' ? '#6ee7b7' : '#fca5a5';
                        @endphp
                        <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; background: {{ $psBg }}; color: {{ $psColor }}; font-size: 12px; font-weight: 700;">{{ $this->getPaymentStatusLabel() }}</span>
                    </div>
                </div>

                {{-- ─── DETAIL PRODUK ─── --}}
                <div style="border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
                    <div style="padding: 12px 20px; background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.08);">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #9ca3af;">Detail Produk</span>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <span style="font-size: 13px; color: #9ca3af;">Produk</span>
                            <span style="font-size: 13px; font-weight: 600; color: #f3f4f6;">{{ $record->product_name ?? '-' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <span style="font-size: 13px; color: #9ca3af;">Kode Produk</span>
                            <code style="font-size: 13px; font-weight: 600; color: #a5b4fc; background: rgba(99,102,241,0.1); padding: 3px 10px; border-radius: 6px; font-family: monospace;">{{ $record->product_code }}</code>
                        </div>
                        @if($record->category)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <span style="font-size: 13px; color: #9ca3af;">Kategori</span>
                            <span style="font-size: 13px; color: #d1d5db;">{{ $record->category }}</span>
                        </div>
                        @endif
                        @if($record->target && $record->target !== '-')
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <span style="font-size: 13px; color: #9ca3af;">Target</span>
                            <span style="font-size: 13px; color: #d1d5db; font-family: monospace;">{{ $record->target }}</span>
                        </div>
                        @endif
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px;">
                            <span style="font-size: 13px; color: #9ca3af;">Jumlah</span>
                            <span style="font-size: 13px; font-weight: 700; color: #f3f4f6;">{{ $record->quantity }}x</span>
                        </div>
                    </div>
                </div>

                {{-- ─── RINCIAN HARGA ─── --}}
                <div style="border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
                    <div style="padding: 12px 20px; background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.08);">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #9ca3af;">Rincian Harga</span>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <span style="font-size: 13px; color: #9ca3af;">Harga Dasar</span>
                            <span style="font-size: 13px; color: #d1d5db;">Rp {{ number_format($record->base_price, 0, ',', '.') }}</span>
                        </div>
                        @if((float)$record->markup > 0)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <span style="font-size: 13px; color: #9ca3af;">Markup</span>
                            <span style="font-size: 13px; color: #fbbf24; font-weight: 500;">+ Rp {{ number_format($record->markup, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <span style="font-size: 13px; color: #9ca3af;">Harga Jual</span>
                            <span style="font-size: 13px; font-weight: 600; color: #d1d5db;">Rp {{ number_format($record->sell_price, 0, ',', '.') }}</span>
                        </div>
                        @if($record->quantity > 1)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <span style="font-size: 13px; color: #9ca3af;">Subtotal ({{ $record->quantity }}x)</span>
                            <span style="font-size: 13px; color: #d1d5db;">Rp {{ number_format($record->sell_price * $record->quantity, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if((float)$record->payment_fee > 0)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <span style="font-size: 13px; color: #9ca3af;">Biaya Pembayaran</span>
                            <span style="font-size: 13px; color: #d1d5db;">Rp {{ number_format($record->payment_fee, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if((float)$record->profit > 0)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <span style="font-size: 13px; color: #9ca3af;">Profit</span>
                            <span style="font-size: 13px; font-weight: 600; color: #6ee7b7;">+ Rp {{ number_format($record->profit, 0, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>
                    {{-- Total --}}
                    <div style="padding: 20px; background: linear-gradient(135deg, rgba(99,102,241,0.1) 0%, rgba(139,92,246,0.1) 100%); border-top: 1px solid rgba(99,102,241,0.2);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 15px; font-weight: 700; color: #e5e7eb;">Total Bayar</span>
                            <span style="font-size: 24px; font-weight: 900; color: #a5b4fc; font-family: monospace; letter-spacing: -1px;">Rp {{ number_format($record->total_pay, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- ─── SN / DELIVERY ─── --}}
                @if($record->sn)
                <div style="border: 2px solid rgba(16,185,129,0.3); border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
                    <div style="padding: 12px 20px; background: rgba(16,185,129,0.08); border-bottom: 1px solid rgba(16,185,129,0.2); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #6ee7b7;">SN / Delivery</span>
                        <button
                            onclick="
                                var text = document.getElementById('sn-content').innerText;
                                navigator.clipboard.writeText(text);
                                this.innerText = 'Tersalin!';
                                var btn = this;
                                setTimeout(function(){ btn.innerText = 'Copy'; }, 2000);
                            "
                            style="padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; background: #059669; color: #fff; border: none; cursor: pointer;"
                        >Copy</button>
                    </div>
                    <div style="padding: 20px; background: rgba(16,185,129,0.03);">
                        <pre id="sn-content" style="font-size: 14px; font-family: monospace; color: #a7f3d0; white-space: pre-wrap; word-break: break-all; margin: 0; line-height: 1.6;">{{ $record->sn }}</pre>
                    </div>
                </div>
                @endif

                {{-- ─── NOTES ─── --}}
                @if($record->notes)
                <div style="border: 1px solid rgba(245,158,11,0.3); border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
                    <div style="padding: 12px 20px; background: rgba(245,158,11,0.08); border-bottom: 1px solid rgba(245,158,11,0.2);">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #fbbf24;">Catatan</span>
                    </div>
                    <div style="padding: 20px; background: rgba(245,158,11,0.03);">
                        <p style="font-size: 13px; color: #fcd34d; margin: 0; line-height: 1.6;">{{ $record->notes }}</p>
                    </div>
                </div>
                @endif

            </div>

            {{-- ═══ FOOTER ═══ --}}
            <div style="padding: 16px 32px; background: rgba(255,255,255,0.02); border-top: 1px solid rgba(255,255,255,0.05); text-align: center;">
                <span style="font-size: 11px; color: #6b7280;">Invoice dibuat otomatis &bull; {{ $record->created_at->format('d M Y, H:i') }} WIB</span>
            </div>

        </div>
    </div>
</x-filament-panels::page>
