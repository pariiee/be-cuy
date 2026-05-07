<?php

namespace Database\Seeders;

use App\Models\DigitalProduct;
use App\Models\DigitalProductCategory;
use App\Models\RedeemCode;
use Illuminate\Database\Seeder;

class DigitalProductSeeder extends Seeder
{
    /**
     * Seed categories and sample redeem codes.
     * Products are added manually via admin panel.
     */
    public function run(): void
    {
        // ═══════════════════════════════════════════════════════
        //  CATEGORIES (sesuai platform SMM)
        // ═══════════════════════════════════════════════════════

        $categories = [
            // Social Media
            ['nama_kategori' => 'Instagram',  'slug' => 'instagram',  'platform' => 'social_media',  'icon' => 'instagram',  'sort_order' => 1],
            ['nama_kategori' => 'TikTok',     'slug' => 'tiktok',     'platform' => 'social_media',  'icon' => 'tiktok',     'sort_order' => 2],
            ['nama_kategori' => 'Facebook',   'slug' => 'facebook',   'platform' => 'social_media',  'icon' => 'facebook',   'sort_order' => 3],
            ['nama_kategori' => 'YouTube',    'slug' => 'youtube',    'platform' => 'social_media',  'icon' => 'youtube',    'sort_order' => 4],
            ['nama_kategori' => 'Twitter (X)', 'slug' => 'twitter',  'platform' => 'social_media',  'icon' => 'twitter',    'sort_order' => 5],

            // E-commerce
            ['nama_kategori' => 'Shopee',     'slug' => 'shopee',     'platform' => 'ecommerce',     'icon' => 'shopping-bag', 'sort_order' => 1],
            ['nama_kategori' => 'Tokopedia',  'slug' => 'tokopedia',  'platform' => 'ecommerce',     'icon' => 'store',        'sort_order' => 2],

            // Entertainment
            ['nama_kategori' => 'Twitch',     'slug' => 'twitch',     'platform' => 'entertainment', 'icon' => 'twitch',     'sort_order' => 1],
            ['nama_kategori' => 'Pinterest',  'slug' => 'pinterest',  'platform' => 'entertainment', 'icon' => 'pin',        'sort_order' => 2],
            ['nama_kategori' => 'Spotify',    'slug' => 'spotify',    'platform' => 'entertainment', 'icon' => 'music',      'sort_order' => 3],
            ['nama_kategori' => 'Telegram',   'slug' => 'telegram',   'platform' => 'entertainment', 'icon' => 'send',       'sort_order' => 4],
            ['nama_kategori' => 'LinkedIn',   'slug' => 'linkedin',   'platform' => 'entertainment', 'icon' => 'linkedin',   'sort_order' => 5],
        ];

        foreach ($categories as $cat) {
            DigitalProductCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        // ═══════════════════════════════════════════════════════
        //  SAMPLE REDEEM CODES
        // ═══════════════════════════════════════════════════════

        $redeemCodes = [
            [
                'code'               => 'DISKONHEMAT',
                'type'               => 'discount',
                'discount_value'     => 5000,
                'applicable_products' => null,
                'is_active'          => true,
                'max_usage'          => 100,
            ],
            [
                'code'               => 'WELCOMEBONUS',
                'type'               => 'custom_text',
                'discount_value'     => 0,
                'custom_text'        => '🎉 Selamat! Anda mendapatkan bonus setelah pembelian pertama!',
                'applicable_products' => null,
                'is_active'          => true,
                'max_usage'          => 0,
            ],
        ];

        foreach ($redeemCodes as $rc) {
            RedeemCode::updateOrCreate(
                ['code' => $rc['code']],
                $rc
            );
        }
    }
}
