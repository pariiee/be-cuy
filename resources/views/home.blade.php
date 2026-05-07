@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-5">
        {{-- Main Content (3 cols) --}}
        <div class="xl:col-span-3 space-y-5">

            {{-- Hero Banner --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#3B82F6] to-[#60A5FA] p-6 sm:p-8 min-h-[220px] flex items-center">
                <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4"></div>
                <div class="absolute bottom-0 left-1/2 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
                <div class="relative z-10 max-w-lg">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm mb-4">
                        <svg class="w-4 h-4 text-[#FCD34D]" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span class="text-xs font-semibold text-white">Top Up Instan</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-[Poppins] font-extrabold text-white leading-tight mb-3">
                        Top Up Game<br>Favorit Kamu
                    </h1>
                    <p class="text-sm text-blue-100 mb-5 max-w-sm leading-relaxed">Nikmati top up cepat, aman, dan harga termurah untuk semua game populer.</p>
                    <a href="#games" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#FCD34D] text-gray-900 text-sm font-bold hover:bg-[#FBBF24] transition-all shadow-lg shadow-yellow-500/20">
                        Top Up Sekarang
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
                {{-- Decorative game icons --}}
                <div class="hidden sm:flex absolute right-6 top-1/2 -translate-y-1/2 gap-3">
                    <div class="w-24 h-32 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 overflow-hidden rotate-3 shadow-xl">
                        <img src="https://placehold.co/200x280/3B82F6/FCD34D?text=FF" alt="" class="w-full h-full object-cover opacity-80">
                    </div>
                    <div class="w-24 h-32 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 overflow-hidden -rotate-2 shadow-xl mt-4">
                        <img src="https://placehold.co/200x280/2563EB/FCD34D?text=ML" alt="" class="w-full h-full object-cover opacity-80">
                    </div>
                    <div class="hidden lg:block w-24 h-32 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 overflow-hidden rotate-6 shadow-xl">
                        <img src="https://placehold.co/200x280/1D4ED8/FCD34D?text=GI" alt="" class="w-full h-full object-cover opacity-80">
                    </div>
                </div>
            </div>

            {{-- Game Populer --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-[Poppins] font-bold text-gray-800">Game Populer</h2>
                    <a href="#games" class="text-xs font-semibold text-[#3B82F6] hover:underline">Lihat Semua &rarr;</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach($popularGames as $game)
                    <a href="{{ route('topup.show', $game['slug']) }}" class="group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:border-[#3B82F6]/30 hover:shadow-lg hover:shadow-blue-100 transition-all duration-300 hover:-translate-y-1">
                        <div class="aspect-[4/3] relative overflow-hidden bg-gradient-to-br from-blue-50 to-blue-100">
                            <img src="{{ $game['image'] }}" alt="{{ $game['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute top-2 right-2">
                                <span class="px-2 py-0.5 rounded-md bg-[#3B82F6] text-[9px] font-bold text-white">{{ $game['category'] }}</span>
                            </div>
                        </div>
                        <div class="p-3">
                            <h3 class="text-sm font-bold text-gray-800 truncate">{{ $game['name'] }}</h3>
                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $game['developer'] }}</p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs font-bold text-[#3B82F6]">Rp {{ number_format($game['items'][0]['price'], 0, ',', '.') }}</span>
                                <span class="w-6 h-6 rounded-full bg-blue-50 flex items-center justify-center group-hover:bg-[#3B82F6] transition-colors">
                                    <svg class="w-3 h-3 text-[#3B82F6] group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Promo Banner --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#FCD34D] to-[#F59E0B] p-5 flex items-center">
                    <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-white/10 rounded-full"></div>
                    <div class="relative z-10">
                        <span class="text-xs font-bold text-yellow-900/60 uppercase tracking-wider">Promo Spesial</span>
                        <h3 class="text-lg font-[Poppins] font-extrabold text-gray-900 mt-1">Bonus 20%</h3>
                        <p class="text-xs text-yellow-800 mt-1 mb-3">Top up pertama kamu dapat bonus diamond ekstra!</p>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white text-xs font-bold text-gray-900 shadow-sm">Klaim Sekarang</span>
                    </div>
                    <div class="ml-auto flex-shrink-0">
                        <div class="w-20 h-20 rounded-2xl bg-white/20 flex items-center justify-center">
                            <svg class="w-10 h-10 text-yellow-900/40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#3B82F6] to-[#6366F1] p-5 flex items-center">
                    <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-white/10 rounded-full"></div>
                    <div class="relative z-10">
                        <span class="text-xs font-bold text-blue-200 uppercase tracking-wider">Flash Sale</span>
                        <h3 class="text-lg font-[Poppins] font-extrabold text-white mt-1">Diskon 15%</h3>
                        <p class="text-xs text-blue-200 mt-1 mb-3">Berlaku untuk semua game. Waktu terbatas!</p>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white text-xs font-bold text-[#3B82F6] shadow-sm">Lihat Promo</span>
                    </div>
                    <div class="ml-auto flex-shrink-0">
                        <div class="w-20 h-20 rounded-2xl bg-white/20 flex items-center justify-center">
                            <svg class="w-10 h-10 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats Row --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @php
                $stats = [
                    ['game' => 'Free Fire', 'slug' => 'free-fire', 'reward' => '+38', 'orders' => '+86', 'color' => 'from-orange-400 to-orange-500'],
                    ['game' => 'Mobile Legends', 'slug' => 'mobile-legends', 'reward' => '+38', 'orders' => '+86', 'color' => 'from-blue-500 to-blue-600'],
                    ['game' => 'Genshin Impact', 'slug' => 'genshin-impact', 'reward' => '+38', 'orders' => '+86', 'color' => 'from-purple-500 to-purple-600'],
                    ['game' => 'Valorant', 'slug' => 'valorant', 'reward' => '+38', 'orders' => '+86', 'color' => 'from-red-400 to-red-500'],
                ];
                @endphp
                @foreach($stats as $stat)
                <a href="{{ route('topup.show', $stat['slug']) }}" class="bg-white rounded-2xl p-4 border border-gray-100 hover:border-[#3B82F6]/20 hover:shadow-md transition-all group">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br {{ $stat['color'] }} flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-800 truncate">{{ $stat['game'] }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div>
                            <span class="text-lg font-extrabold text-[#3B82F6]">{{ $stat['reward'] }}</span>
                            <div class="text-[10px] text-gray-400 font-medium">REWARD</div>
                        </div>
                        <div>
                            <span class="text-lg font-extrabold text-[#F59E0B]">{{ $stat['orders'] }}</span>
                            <div class="text-[10px] text-gray-400 font-medium">ORDERS</div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            {{-- All Games Section --}}
            <div id="games">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-[Poppins] font-bold text-gray-800">Semua Game</h2>
                </div>

                {{-- Category Filter --}}
                <div class="flex flex-wrap items-center gap-2 mb-5">
                    <button onclick="filterGames('all')" class="filter-btn active px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-[#3B82F6] text-white transition-all" data-filter="all">Semua</button>
                    <button onclick="filterGames('MOBA')" class="filter-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white text-gray-500 border border-gray-200 hover:border-[#3B82F6]/30 hover:text-[#3B82F6] transition-all" data-filter="MOBA">MOBA</button>
                    <button onclick="filterGames('Battle Royale')" class="filter-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white text-gray-500 border border-gray-200 hover:border-[#3B82F6]/30 hover:text-[#3B82F6] transition-all" data-filter="Battle Royale">Battle Royale</button>
                    <button onclick="filterGames('RPG')" class="filter-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white text-gray-500 border border-gray-200 hover:border-[#3B82F6]/30 hover:text-[#3B82F6] transition-all" data-filter="RPG">RPG</button>
                    <button onclick="filterGames('FPS')" class="filter-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white text-gray-500 border border-gray-200 hover:border-[#3B82F6]/30 hover:text-[#3B82F6] transition-all" data-filter="FPS">FPS</button>
                    <button onclick="filterGames('Strategy')" class="filter-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white text-gray-500 border border-gray-200 hover:border-[#3B82F6]/30 hover:text-[#3B82F6] transition-all" data-filter="Strategy">Strategy</button>
                </div>

                <div id="gamesGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($games as $game)
                    <a href="{{ route('topup.show', $game['slug']) }}" class="game-card group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:border-[#3B82F6]/30 hover:shadow-lg hover:shadow-blue-100 transition-all duration-300 hover:-translate-y-1" data-category="{{ $game['category'] }}" data-name="{{ strtolower($game['name']) }}">
                        <div class="aspect-[4/3] relative overflow-hidden bg-gradient-to-br from-blue-50 to-gray-100">
                            <img src="{{ $game['image'] }}" alt="{{ $game['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                            <div class="absolute top-2 right-2">
                                <span class="px-2 py-0.5 rounded-md bg-[#3B82F6] text-[9px] font-bold text-white shadow">{{ $game['category'] }}</span>
                            </div>
                        </div>
                        <div class="p-3">
                            <h3 class="text-sm font-bold text-gray-800 truncate">{{ $game['name'] }}</h3>
                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $game['developer'] }}</p>
                            <div class="flex items-center justify-between mt-2">
                                <div>
                                    <span class="text-[10px] text-gray-400">Mulai dari</span>
                                    <div class="text-xs font-bold text-[#3B82F6]">Rp {{ number_format($game['items'][0]['price'], 0, ',', '.') }}</div>
                                </div>
                                <span class="w-6 h-6 rounded-full bg-blue-50 flex items-center justify-center group-hover:bg-[#3B82F6] transition-colors">
                                    <svg class="w-3 h-3 text-[#3B82F6] group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- CTA Banner --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#FCD34D] via-[#F59E0B] to-[#FCD34D] p-6 sm:p-8">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full"></div>
                <div class="absolute -left-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
                    <div class="flex-1">
                        <h2 class="text-xl sm:text-2xl font-[Poppins] font-extrabold text-gray-900">Top Up Game Sekarang, Dapat <span class="text-[#3B82F6]">Bonus!</span></h2>
                        <p class="text-sm text-yellow-800 mt-2">Gabung dengan 500K+ gamer lainnya. Dapatkan promo menarik setiap hari!</p>
                    </div>
                    <a href="#games" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#3B82F6] text-white text-sm font-bold hover:bg-[#2563EB] transition-all shadow-lg shadow-blue-500/20 flex-shrink-0">
                        Mulai Top Up
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- Right Sidebar (1 col) --}}
        <div class="xl:col-span-1 space-y-5">

            {{-- Game Categories --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Kategori Game</h3>
                <div class="space-y-2">
                    @php
                    $categories = [
                        ['name' => 'Battle Royale', 'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z', 'count' => 2, 'color' => 'bg-red-50 text-red-500'],
                        ['name' => 'MOBA', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'count' => 2, 'color' => 'bg-blue-50 text-blue-500'],
                        ['name' => 'RPG', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'count' => 2, 'color' => 'bg-purple-50 text-purple-500'],
                        ['name' => 'FPS', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'count' => 1, 'color' => 'bg-green-50 text-green-500'],
                        ['name' => 'Strategy', 'icon' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z', 'count' => 1, 'color' => 'bg-amber-50 text-amber-500'],
                    ];
                    @endphp
                    @foreach($categories as $cat)
                    <button onclick="filterGames('{{ $cat['name'] }}')" class="w-full flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition-all group text-left">
                        <div class="w-9 h-9 rounded-lg {{ $cat['color'] }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $cat['icon'] }}"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-semibold text-gray-700 group-hover:text-[#3B82F6] transition-colors">{{ $cat['name'] }}</span>
                            <span class="block text-[10px] text-gray-400">{{ $cat['count'] }} games</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-[#3B82F6] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    @endforeach
                </div>
                <a href="#games" class="mt-3 block text-center text-xs font-semibold text-[#3B82F6] py-2 rounded-xl bg-blue-50 hover:bg-blue-100 transition-colors">Semua Kategori</a>
            </div>

            {{-- Transaksi Terbaru --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Transaksi Terbaru</h3>
                <div class="space-y-3">
                    @php
                    $transactions = [
                        ['game' => 'Mobile Legends', 'item' => '172 Diamonds', 'time' => '2 menit lalu', 'color' => 'from-blue-500 to-blue-600'],
                        ['game' => 'Free Fire', 'item' => '355 Diamonds', 'time' => '5 menit lalu', 'color' => 'from-orange-400 to-orange-500'],
                        ['game' => 'Genshin Impact', 'item' => '300 Crystals', 'time' => '8 menit lalu', 'color' => 'from-purple-500 to-purple-600'],
                        ['game' => 'Valorant', 'item' => '700 VP', 'time' => '12 menit lalu', 'color' => 'from-red-400 to-red-500'],
                    ];
                    @endphp
                    @foreach($transactions as $trx)
                    <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition-colors">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br {{ $trx['color'] }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-semibold text-gray-700 block truncate">{{ $trx['game'] }}</span>
                            <span class="text-[10px] text-gray-400">{{ $trx['item'] }}</span>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="text-[10px] text-gray-400">{{ $trx['time'] }}</span>
                            <span class="block w-2 h-2 rounded-full bg-green-400 ml-auto mt-1"></span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <a href="#" class="mt-3 block text-center text-xs font-semibold text-[#3B82F6] py-2 rounded-xl bg-blue-50 hover:bg-blue-100 transition-colors">Semua Transaksi</a>
            </div>

            {{-- Kenapa Pilih Kami --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Kenapa Pilih Kami?</h3>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-[#3B82F6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-700">Proses Instan</span>
                            <p class="text-[10px] text-gray-400 mt-0.5 leading-relaxed">Diamond masuk dalam hitungan detik</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-700">Aman & Terpercaya</span>
                            <p class="text-[10px] text-gray-400 mt-0.5 leading-relaxed">Transaksi terenkripsi & aman</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-700">Harga Termurah</span>
                            <p class="text-[10px] text-gray-400 mt-0.5 leading-relaxed">Harga paling kompetitif</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-700">Support 24/7</span>
                            <p class="text-[10px] text-gray-400 mt-0.5 leading-relaxed">Tim CS siap bantu kapan saja</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Search from navbar
    document.getElementById('globalSearch')?.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('.game-card').forEach(card => {
            const name = card.dataset.name;
            card.style.display = name.includes(query) ? '' : 'none';
        });
        // Scroll to games section
        if (query.length > 0) {
            document.getElementById('games')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    // Filter functionality
    function filterGames(category) {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            if (btn.dataset.filter === category || (category === 'all' && btn.dataset.filter === 'all')) {
                btn.classList.remove('bg-white', 'text-gray-500', 'border', 'border-gray-200');
                btn.classList.add('bg-[#3B82F6]', 'text-white');
            } else {
                btn.classList.remove('bg-[#3B82F6]', 'text-white');
                btn.classList.add('bg-white', 'text-gray-500', 'border', 'border-gray-200');
            }
        });
        document.querySelectorAll('.game-card').forEach(card => {
            card.style.display = (category === 'all' || card.dataset.category === category) ? '' : 'none';
        });
        document.getElementById('games')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
</script>
@endsection
