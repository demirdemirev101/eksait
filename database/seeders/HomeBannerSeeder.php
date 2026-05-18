<?php

namespace Database\Seeders;

use App\Models\HomeBanner;
use Illuminate\Database\Seeder;

class HomeBannerSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'eyebrow' => 'EXCITE COMPANY',
                'title' => 'Професионални инструменти',
                'subtitle' => 'Решения за прецизна обработка, производство и сервиз.',
                'button_text' => 'РАЗГЛЕДАЙ ПРОДУКТИ',
                'button_url' => '/products',
                'image' => 'banners/01KRX1MWBV4NGQRZ6JFKJ4S9Y9.jpg',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'eyebrow' => 'EXCITE COMPANY',
                'title' => 'Оборудване за работилници',
                'subtitle' => 'Подбрани продукти за надеждна ежедневна работа.',
                'button_text' => 'РАЗГЛЕДАЙ ПРОДУКТИ',
                'button_url' => '/products',
                'image' => 'banners/01KRX1JDCCZNWAVF5KTGMKPEMW.jpg',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'eyebrow' => 'EXCITE COMPANY',
                'title' => 'Технически консумативи',
                'subtitle' => 'Практически предложения за металообработка и монтаж.',
                'button_text' => 'РАЗГЛЕДАЙ ПРОДУКТИ',
                'button_url' => '/products',
                'image' => 'banners/01KRX1FCZTEQ9ER0VB2XJXW7RR.jpg',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'eyebrow' => 'EXCITE COMPANY',
                'title' => 'Инструменти за резбонарязване',
                'subtitle' => 'Метчици, свредла и аксесоари за точен резултат.',
                'button_text' => 'РАЗГЛЕДАЙ ПРОДУКТИ',
                'button_url' => '/products',
                'image' => 'banners/01KRX17G3YA9YBZ8Q0M2M8JXNN.jpg',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'eyebrow' => 'EXCITE COMPANY',
                'title' => 'Индустриални решения',
                'subtitle' => 'Оборудване и инструменти според нуждите на клиента.',
                'button_text' => 'РАЗГЛЕДАЙ ПРОДУКТИ',
                'button_url' => '/products',
                'image' => 'banners/01KRX1AT2NS4APEF1ZYJVFJQ8N.jpg',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        HomeBanner::upsert(
            $rows,
            ['sort_order'],
            ['eyebrow', 'title', 'subtitle', 'button_text', 'button_url', 'image', 'is_active']
        );
    }
}

