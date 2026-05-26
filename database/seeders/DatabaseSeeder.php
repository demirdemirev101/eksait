<?php

namespace Database\Seeders;

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
        | Settings
        |--------------------------------------------------------------------------
        */
        Setting::firstOrCreate(
            ['id' => 1],
            [
                'delivery_enabled' => true,
                'stripe_enabled' => false,
                'free_delivery_over' => 100,
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Other seeders
        |--------------------------------------------------------------------------
        */
        $this->call([
            CategorySeeder::class,
            HomeBannerSeeder::class,
        ]);
    }
}
