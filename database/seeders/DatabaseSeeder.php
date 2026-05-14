<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
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
        ];

        foreach ($products as $productData) {
            $product = Product::updateOrCreate(
                ['name' => $productData['data']['name']],
                $productData['data'],
            );

            $product->categories()->syncWithoutDetaching([
                $categories[$productData['category']]->id,
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
    }
}
