<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'customer']);

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        $admin = User::firstOrCreate(
            ['email' => 'demir@abv.bg'],
            [
                'name' => 'Demir Demirev',
                'phone' => '0888123456',
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles(['admin']);

        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'John Doe',
                'phone' => '0888123456',
                'password' => Hash::make('password'),
            ]
        );
        $customer->syncRoles(['customer']);

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */
        $categories = collect([
            'Свредла',
            'Фрези',
            'Пластини',
            'Метчици',
            'Плашки',
            'Ножове',
            'Кобалт',
            'Държачи',
            'Щанги',
            'Измервателни',
            'Калибри',
        ])->mapWithKeys(fn (string $name) => [
            $name => Category::firstOrCreate(['name' => $name]),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */
        $products = [
            [
                'category' => 'Фрези',
                'image_path' => 'product-images/01KRJQ1X37PR0T7F40KGBYZQGD.png',
                'data' => [
                    'name' => 'Фреза тристранна',
                    'price' => '17.12',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 15,
                    'description' => '<p>Фрези Наличностите които са изброени по-долу може да претърпят моментни промени, както по отношение на количеството, така и по отношение на предлаганите размери. Ексайт Къмпани ООД си запазва правото да прави това едностранно, като за по-подробна информация и цени, може да използвате формата за контакти както и телефони за връзка.</p>',
                    'extra_information' => '<p></p>',
                ],
            ],
            [
                'category' => 'Пластини',
                'image_path' => 'product-images/01KRJQ509NMTGV517GHXM8QK2E.jpg',
                'data' => [
                    'name' => 'Пластина с диамантен връх',
                    'price' => '2.40',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 10,
                    'description' => '<p></p>',
                    'extra_information' => '<p></p>',
                ],
            ],
            [
                'category' => 'Ð¡Ð²Ñ€ÐµÐ´Ð»Ð°',
                'image_path' => 'product-images/01KRJQ1X37PR0T7F40KGBYZQGD.png',
                'data' => [
                    'name' => 'Свредло HSS DIN 338',
                    'price' => '4.80',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 60,
                    'weight' => 0.05,
                    'description' => '<p>Универсално HSS свредло за стомана и цветни метали.</p>',
                    'extra_information' => '<p>Подходящо за ежедневна работа в сервиз и производство.</p>',
                ],
            ],
            [
                'category' => 'Ð¡Ð²Ñ€ÐµÐ´Ð»Ð°',
                'image_path' => 'product-images/01KRJQ1X37PR0T7F40KGBYZQGD.png',
                'data' => [
                    'name' => 'Свредло центрово 60 градуса',
                    'price' => '7.20',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 35,
                    'weight' => 0.06,
                    'description' => '<p>Центрово свредло за точно позициониране преди пробиване.</p>',
                    'extra_information' => '<p>Предлага стабилен старт и намалява отклонението.</p>',
                ],
            ],
            [
                'category' => 'Ð¤Ñ€ÐµÐ·Ð¸',
                'image_path' => 'product-images/01KRJQ1X37PR0T7F40KGBYZQGD.png',
                'data' => [
                    'name' => 'Фреза челна четиризъба',
                    'price' => '28.50',
                    'sale_price' => '25.90',
                    'stock' => true,
                    'quantity' => 18,
                    'weight' => 0.12,
                    'description' => '<p>Челна фреза за чисто фрезоване и стабилна работа при стомани.</p>',
                    'extra_information' => '<p>Подходяща за груба и довършителна обработка.</p>',
                ],
            ],
            [
                'category' => 'ÐŸÐ»Ð°ÑÑ‚Ð¸Ð½Ð¸',
                'image_path' => 'product-images/01KRJQ509NMTGV517GHXM8QK2E.jpg',
                'data' => [
                    'name' => 'Пластина карбидна за струговане',
                    'price' => '5.40',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 80,
                    'weight' => 0.02,
                    'description' => '<p>Карбидна пластина за надеждно струговане на конструкционни стомани.</p>',
                    'extra_information' => '<p>Подходяща за държачи със стандартно закрепване.</p>',
                ],
            ],
            [
                'category' => 'ÐœÐµÑ‚Ñ‡Ð¸Ñ†Ð¸',
                'image_path' => 'product-images/01KRJQ1X37PR0T7F40KGBYZQGD.png',
                'data' => [
                    'name' => 'Метчик машинен M6',
                    'price' => '9.90',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 42,
                    'weight' => 0.04,
                    'description' => '<p>Машинен метчик M6 за метрична резба.</p>',
                    'extra_information' => '<p>Подходящ за проходни отвори и серийна работа.</p>',
                ],
            ],
            [
                'category' => 'ÐŸÐ»Ð°ÑˆÐºÐ¸',
                'image_path' => 'product-images/01KRJQ509NMTGV517GHXM8QK2E.jpg',
                'data' => [
                    'name' => 'Плашка кръгла M8',
                    'price' => '12.60',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 24,
                    'weight' => 0.08,
                    'description' => '<p>Кръгла плашка M8 за външна метрична резба.</p>',
                    'extra_information' => '<p>Използва се с подходящ държач за плашки.</p>',
                ],
            ],
            [
                'category' => 'ÐÐ¾Ð¶Ð¾Ð²Ðµ',
                'image_path' => 'product-images/01KRJQ509NMTGV517GHXM8QK2E.jpg',
                'data' => [
                    'name' => 'Стругарски нож десен',
                    'price' => '18.30',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 16,
                    'weight' => 0.20,
                    'description' => '<p>Десен стругарски нож за външно струговане.</p>',
                    'extra_information' => '<p>Съвместим с твърдосплавни пластини.</p>',
                ],
            ],
            [
                'category' => 'ÐšÐ¾Ð±Ð°Ð»Ñ‚',
                'image_path' => 'product-images/01KRJQ1X37PR0T7F40KGBYZQGD.png',
                'data' => [
                    'name' => 'Кобалтово свредло HSS-Co',
                    'price' => '8.70',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 55,
                    'weight' => 0.05,
                    'description' => '<p>Кобалтово свредло за неръждаема стомана и по-твърди материали.</p>',
                    'extra_information' => '<p>Повишена устойчивост на нагряване при пробиване.</p>',
                ],
            ],
            [
                'category' => 'Ð”ÑŠÑ€Ð¶Ð°Ñ‡Ð¸',
                'image_path' => 'product-images/01KRJQ509NMTGV517GHXM8QK2E.jpg',
                'data' => [
                    'name' => 'Държач за стругарска пластина',
                    'price' => '34.00',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 12,
                    'weight' => 0.35,
                    'description' => '<p>Държач за външно струговане с твърдосплавни пластини.</p>',
                    'extra_information' => '<p>Подходящ за работа на универсални и CNC стругове.</p>',
                ],
            ],
            [
                'category' => 'Ð©Ð°Ð½Ð³Ð¸',
                'image_path' => 'product-images/01KRJQ1X37PR0T7F40KGBYZQGD.png',
                'data' => [
                    'name' => 'Шанга ER32 10 мм',
                    'price' => '11.40',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 30,
                    'weight' => 0.09,
                    'description' => '<p>Шанга ER32 за точно захващане на инструмент с диаметър 10 мм.</p>',
                    'extra_information' => '<p>Предназначена за фрезови патронници ER32.</p>',
                ],
            ],
            [
                'category' => 'Ð˜Ð·Ð¼ÐµÑ€Ð²Ð°Ñ‚ÐµÐ»Ð½Ð¸',
                'image_path' => 'product-images/01KRJQ509NMTGV517GHXM8QK2E.jpg',
                'data' => [
                    'name' => 'Шублер дигитален 150 мм',
                    'price' => '39.90',
                    'sale_price' => '35.90',
                    'stock' => true,
                    'quantity' => 14,
                    'weight' => 0.25,
                    'description' => '<p>Дигитален шублер за вътрешни, външни и дълбочинни измервания.</p>',
                    'extra_information' => '<p>Диапазон на измерване до 150 мм.</p>',
                ],
            ],
            [
                'category' => 'ÐšÐ°Ð»Ð¸Ð±Ñ€Ð¸',
                'image_path' => 'product-images/01KRJQ509NMTGV517GHXM8QK2E.jpg',
                'data' => [
                    'name' => 'Калибър резбови M10',
                    'price' => '22.80',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 10,
                    'weight' => 0.15,
                    'description' => '<p>Резбови калибър за контрол на метрична резба M10.</p>',
                    'extra_information' => '<p>Подходящ за входящ и производствен контрол.</p>',
                ],
            ],
            [
                'category' => 'ÐœÐµÑ‚Ñ‡Ð¸Ñ†Ð¸',
                'image_path' => 'product-images/01KRJQ1X37PR0T7F40KGBYZQGD.png',
                'data' => [
                    'name' => 'Метчик комплект M3-M12',
                    'price' => '68.00',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 8,
                    'weight' => 0.80,
                    'description' => '<p>Комплект метчици за основни метрични резби.</p>',
                    'extra_information' => '<p>Практичен избор за ремонтна дейност и малки серии.</p>',
                ],
            ],
            [
                'category' => 'Ð”ÑŠÑ€Ð¶Ð°Ñ‡Ð¸',
                'image_path' => 'product-images/01KRJQ509NMTGV517GHXM8QK2E.jpg',
                'data' => [
                    'name' => 'Държач за плашки 25 мм',
                    'price' => '16.20',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 22,
                    'weight' => 0.18,
                    'description' => '<p>Ръчен държач за кръгли плашки с диаметър 25 мм.</p>',
                    'extra_information' => '<p>Осигурява стабилно водене при нарязване на външна резба.</p>',
                ],
            ],
            [
                'category' => 'Ð˜Ð·Ð¼ÐµÑ€Ð²Ð°Ñ‚ÐµÐ»Ð½Ð¸',
                'image_path' => 'product-images/01KRJQ509NMTGV517GHXM8QK2E.jpg',
                'data' => [
                    'name' => 'Микрометър 0-25 мм',
                    'price' => '52.50',
                    'sale_price' => null,
                    'stock' => true,
                    'quantity' => 9,
                    'weight' => 0.40,
                    'description' => '<p>Микрометър за прецизно външно измерване в диапазон 0-25 мм.</p>',
                    'extra_information' => '<p>Подходящ за контрол на детайли след механична обработка.</p>',
                ],
            ],
        ];

        $categoryList = $categories->values();
        $productCategoryIndexes = [
            1, 2, 0, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 3, 7, 9,
        ];

        foreach ($products as $productIndex => $productData) {
            $product = Product::updateOrCreate(
                ['name' => $productData['data']['name']],
                $productData['data'],
            );

            $category = $categories[$productData['category']]
                ?? $categoryList[$productCategoryIndexes[$productIndex]];

            $product->categories()->syncWithoutDetaching([
                $category->id,
            ]);

            ProductImage::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sort_order' => 1,
                ],
                [
                    'image_path' => $productData['image_path'],
                    'is_primary' => true,
                ],
            );
        }

        $variantCategory = Category::firstOrCreate(['name' => 'Test variants']);
        $variantProduct = Product::updateOrCreate(
            ['name' => 'Cobalt drill TITEX'],
            [
                'price' => null,
                'sale_price' => null,
                'stock' => true,
                'quantity' => 0,
                'description' => '<p>Variant product sample.</p>',
                'extra_information' => '<p></p>',
            ],
        );
        $variantProduct->categories()->syncWithoutDetaching([$variantCategory->id]);

        foreach ([
            ['size' => 'F0.35', 'quantity' => 20, 'price' => 3],
            ['size' => 'F0.50', 'quantity' => 20, 'price' => 2],
            ['size' => 'F0.60', 'quantity' => 29, 'price' => 2],
        ] as $variantData) {
            ProductVariant::updateOrCreate(
                [
                    'product_id' => $variantProduct->id,
                    'size' => $variantData['size'],
                ],
                [
                    'price' => $variantData['price'],
                    'sale_price' => null,
                    'quantity' => $variantData['quantity'],
                    'stock' => true,
                    'weight' => 0.1,
                ],
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Other seeders
        |--------------------------------------------------------------------------
        */
        Setting::firstOrCreate(
            ['id' => 1],
            [
                'delivery_enabled' => true,
                'free_delivery_over' => 100,
            ],
        );

        $this->call([
            HomeBannerSeeder::class,
        ]);
    }
}
