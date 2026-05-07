<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TopUp Game') - TopUp Game Store</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&family=poppins:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F0F4F8] min-h-screen font-[Inter] text-gray-800">

    <div class="flex min-h-screen">
        {{-- Left Sidebar --}}
        <aside id="sidebar" class="fixed top-0 left-0 z-40 w-[220px] h-screen bg-white border-r border-gray-100 flex flex-col transition-transform -translate-x-full lg:translate-x-0">
            {{-- Logo --}}
            <div class="flex items-center gap-2.5 px-5 h-16 border-b border-gray-100 flex-shrink-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#3B82F6] to-[#60A5FA] flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="text-lg font-[Poppins] font-extrabold text-[#3B82F6]">TopUp</span>
            </div>

            {{-- Nav Links --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('home') }}" class="sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Home</span>
                </a>
                <a href="#" class="sidebar-link">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Cari Game</span>
                </a>
                <a href="#" class="sidebar-link">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span>Notifikasi</span>
                    <span class="ml-auto bg-red-500 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center">3</span>
                </a>

                <div class="pt-4 pb-2 px-3">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Menu</span>
                </div>

                <a href="{{ route('home') }}#games" class="sidebar-link">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Games</span>
                </a>
                <a href="#" class="sidebar-link">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Transaksi</span>
                </a>
                <a href="#" class="sidebar-link">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span>Favorit</span>
                </a>
                <a href="#" class="sidebar-link">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span>Saldo</span>
                </a>

                <div class="pt-4 pb-2 px-3">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Lainnya</span>
                </div>

                <a href="#" class="sidebar-link">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Pengaturan</span>
                </a>
                <a href="#" class="sidebar-link">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Bantuan</span>
                </a>
            </nav>
        </aside>

        {{-- Main Area --}}
        <div class="flex-1 lg:ml-[220px]">
            {{-- Top Navbar --}}
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-lg border-b border-gray-100 h-16 flex items-center px-4 lg:px-6">
                {{-- Mobile menu toggle --}}
                <button id="sidebarToggle" class="lg:hidden mr-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                {{-- Search Bar --}}
                <div class="flex-1 max-w-md relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="globalSearch" placeholder="Cari game..." class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#F0F4F8] border-none text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#3B82F6]/30 transition-all">
                </div>

                {{-- Nav Tabs --}}
                <div class="hidden md:flex items-center gap-1 ml-6">
                    <a href="{{ route('home') }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('home') ? 'text-[#3B82F6] bg-blue-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }} transition-all">Top Up</a>
                    <a href="#" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-all">Games</a>
                    <a href="#" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-all">Promo</a>
                </div>

                {{-- Right Side --}}
                <div class="flex items-center gap-3 ml-auto">
                    <button class="relative p-2 rounded-xl hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500"></span>
                    </button>
                    <a href="#" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#3B82F6] to-[#60A5FA] text-white text-sm font-semibold hover:opacity-90 transition-all shadow-md shadow-blue-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Wallet
                    </a>
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#FCD34D] to-[#F59E0B] flex items-center justify-center text-white text-sm font-bold cursor-pointer">
                        U
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="p-4 lg:p-6">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="border-t border-gray-200 bg-white mt-8">
                <div class="px-4 lg:px-6 py-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="col-span-1 md:col-span-2">
                            <div class="flex items-center gap-2.5 mb-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#3B82F6] to-[#60A5FA] flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <span class="text-lg font-[Poppins] font-extrabold text-[#3B82F6]">TopUp Store</span>
                            </div>
                            <p class="text-sm text-gray-500 max-w-sm leading-relaxed">Platform top up game terpercaya di Indonesia. Proses cepat, harga murah, dan pelayanan 24/7.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-3 text-sm">Menu</h4>
                            <ul class="space-y-2">
                                <li><a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-[#3B82F6] transition-colors">Home</a></li>
                                <li><a href="#" class="text-sm text-gray-500 hover:text-[#3B82F6] transition-colors">Cek Transaksi</a></li>
                                <li><a href="#" class="text-sm text-gray-500 hover:text-[#3B82F6] transition-colors">Bantuan</a></li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-3 text-sm">Pembayaran</h4>
                            <ul class="space-y-2">
                                <li class="text-sm text-gray-500">GoPay / OVO / DANA</li>
                                <li class="text-sm text-gray-500">Bank Transfer</li>
                                <li class="text-sm text-gray-500">Indomaret / Alfamart</li>
                            </ul>
                        </div>
                    </div>
                    <div class="border-t border-gray-100 mt-6 pt-4 text-center">
                        <p class="text-xs text-gray-400">&copy; {{ date('Y') }} TopUp Store. All rights reserved. (Dummy Website)</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    {{-- Mobile sidebar overlay --}}
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/30 z-30 hidden lg:hidden" onclick="closeSidebar()"></div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });
        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    </script>
    @yield('scripts')
</body>
</html>
