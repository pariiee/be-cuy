<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TopUpController extends Controller
{
    private function getGames()
    {
        return [
            [
                'slug' => 'free-fire',
                'name' => 'Free Fire',
                'developer' => 'Garena',
                'image' => 'https://placehold.co/400x500/001D39/7BBDE8?text=Free+Fire',
                'banner' => 'https://placehold.co/1200x400/001D39/7BBDE8?text=Free+Fire+Banner',
                'category' => 'Battle Royale',
                'description' => 'Top up diamond Free Fire dengan harga termurah dan proses tercepat.',
                'items' => [
                    ['name' => '70 Diamonds', 'price' => 15000],
                    ['name' => '140 Diamonds', 'price' => 28000],
                    ['name' => '355 Diamonds', 'price' => 70000],
                    ['name' => '720 Diamonds', 'price' => 140000],
                    ['name' => '1450 Diamonds', 'price' => 280000],
                    ['name' => '2180 Diamonds', 'price' => 400000],
                ],
            ],
            [
                'slug' => 'mobile-legends',
                'name' => 'Mobile Legends',
                'developer' => 'Moonton',
                'image' => 'https://placehold.co/400x500/0A4174/BDD8E9?text=Mobile+Legends',
                'banner' => 'https://placehold.co/1200x400/0A4174/BDD8E9?text=Mobile+Legends+Banner',
                'category' => 'MOBA',
                'description' => 'Top up diamond Mobile Legends Bang Bang murah, cepat, dan aman.',
                'items' => [
                    ['name' => '86 Diamonds', 'price' => 19000],
                    ['name' => '172 Diamonds', 'price' => 38000],
                    ['name' => '257 Diamonds', 'price' => 57000],
                    ['name' => '344 Diamonds', 'price' => 76000],
                    ['name' => '514 Diamonds', 'price' => 114000],
                    ['name' => '1050 Diamonds', 'price' => 228000],
                ],
            ],
            [
                'slug' => 'genshin-impact',
                'name' => 'Genshin Impact',
                'developer' => 'miHoYo',
                'image' => 'https://placehold.co/400x500/49769F/BDD8E9?text=Genshin+Impact',
                'banner' => 'https://placehold.co/1200x400/49769F/BDD8E9?text=Genshin+Impact+Banner',
                'category' => 'RPG',
                'description' => 'Top up Genesis Crystal Genshin Impact dengan harga terbaik.',
                'items' => [
                    ['name' => '60 Genesis Crystals', 'price' => 16000],
                    ['name' => '300 Genesis Crystals', 'price' => 79000],
                    ['name' => '980 Genesis Crystals', 'price' => 249000],
                    ['name' => '1980 Genesis Crystals', 'price' => 479000],
                    ['name' => '3280 Genesis Crystals', 'price' => 799000],
                    ['name' => '6480 Genesis Crystals', 'price' => 1599000],
                ],
            ],
            [
                'slug' => 'pubg-mobile',
                'name' => 'PUBG Mobile',
                'developer' => 'Tencent',
                'image' => 'https://placehold.co/400x500/4E8EA2/001D39?text=PUBG+Mobile',
                'banner' => 'https://placehold.co/1200x400/4E8EA2/001D39?text=PUBG+Mobile+Banner',
                'category' => 'Battle Royale',
                'description' => 'Top up UC PUBG Mobile harga murah dan proses instan.',
                'items' => [
                    ['name' => '60 UC', 'price' => 15000],
                    ['name' => '325 UC', 'price' => 75000],
                    ['name' => '660 UC', 'price' => 149000],
                    ['name' => '1800 UC', 'price' => 379000],
                    ['name' => '3850 UC', 'price' => 759000],
                    ['name' => '8100 UC', 'price' => 1519000],
                ],
            ],
            [
                'slug' => 'valorant',
                'name' => 'Valorant',
                'developer' => 'Riot Games',
                'image' => 'https://placehold.co/400x500/6EA2B3/001D39?text=Valorant',
                'banner' => 'https://placehold.co/1200x400/6EA2B3/001D39?text=Valorant+Banner',
                'category' => 'FPS',
                'description' => 'Top up Valorant Points murah dan proses cepat.',
                'items' => [
                    ['name' => '125 VP', 'price' => 15000],
                    ['name' => '420 VP', 'price' => 50000],
                    ['name' => '700 VP', 'price' => 80000],
                    ['name' => '1375 VP', 'price' => 150000],
                    ['name' => '2400 VP', 'price' => 250000],
                    ['name' => '4000 VP', 'price' => 400000],
                ],
            ],
            [
                'slug' => 'honkai-star-rail',
                'name' => 'Honkai Star Rail',
                'developer' => 'miHoYo',
                'image' => 'https://placehold.co/400x500/7BBDE8/001D39?text=Honkai+Star+Rail',
                'banner' => 'https://placehold.co/1200x400/7BBDE8/001D39?text=Honkai+Star+Rail+Banner',
                'category' => 'RPG',
                'description' => 'Top up Oneiric Shard Honkai Star Rail termurah.',
                'items' => [
                    ['name' => '60 Oneiric Shard', 'price' => 16000],
                    ['name' => '300 Oneiric Shard', 'price' => 79000],
                    ['name' => '980 Oneiric Shard', 'price' => 249000],
                    ['name' => '1980 Oneiric Shard', 'price' => 479000],
                    ['name' => '3280 Oneiric Shard', 'price' => 799000],
                    ['name' => '6480 Oneiric Shard', 'price' => 1599000],
                ],
            ],
            [
                'slug' => 'clash-of-clans',
                'name' => 'Clash of Clans',
                'developer' => 'Supercell',
                'image' => 'https://placehold.co/400x500/001D39/6EA2B3?text=Clash+of+Clans',
                'banner' => 'https://placehold.co/1200x400/001D39/6EA2B3?text=Clash+of+Clans+Banner',
                'category' => 'Strategy',
                'description' => 'Top up Gems Clash of Clans harga terjangkau.',
                'items' => [
                    ['name' => '80 Gems', 'price' => 16000],
                    ['name' => '500 Gems', 'price' => 79000],
                    ['name' => '1200 Gems', 'price' => 159000],
                    ['name' => '2500 Gems', 'price' => 319000],
                    ['name' => '6500 Gems', 'price' => 799000],
                    ['name' => '14000 Gems', 'price' => 1599000],
                ],
            ],
            [
                'slug' => 'arena-of-valor',
                'name' => 'Arena of Valor',
                'developer' => 'Tencent',
                'image' => 'https://placehold.co/400x500/0A4174/7BBDE8?text=Arena+of+Valor',
                'banner' => 'https://placehold.co/1200x400/0A4174/7BBDE8?text=Arena+of+Valor+Banner',
                'category' => 'MOBA',
                'description' => 'Top up Voucher Arena of Valor (AOV) termurah.',
                'items' => [
                    ['name' => '90 Vouchers', 'price' => 19000],
                    ['name' => '200 Vouchers', 'price' => 38000],
                    ['name' => '530 Vouchers', 'price' => 95000],
                    ['name' => '1060 Vouchers', 'price' => 190000],
                    ['name' => '2120 Vouchers', 'price' => 380000],
                    ['name' => '5300 Vouchers', 'price' => 950000],
                ],
            ],
        ];
    }

    public function index()
    {
        $games = $this->getGames();
        $popularGames = array_slice($games, 0, 4);
        return view('home', compact('games', 'popularGames'));
    }

    public function show($slug)
    {
        $games = $this->getGames();
        $game = collect($games)->firstWhere('slug', $slug);

        if (!$game) {
            abort(404);
        }

        return view('topup', compact('game'));
    }

    public function process(Request $request, $slug)
    {
        $request->validate([
            'user_id' => 'required|string',
            'server_id' => 'nullable|string',
            'item' => 'required|string',
            'payment' => 'required|string',
        ]);

        $games = $this->getGames();
        $game = collect($games)->firstWhere('slug', $slug);

        if (!$game) {
            abort(404);
        }

        $selectedItem = collect($game['items'])->firstWhere('name', $request->item);

        return view('invoice', [
            'game' => $game,
            'user_id' => $request->user_id,
            'server_id' => $request->server_id,
            'item' => $selectedItem,
            'payment' => $request->payment,
            'invoice_id' => 'INV-' . strtoupper(uniqid()),
        ]);
    }
}
