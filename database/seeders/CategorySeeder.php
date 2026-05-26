<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $parent = Category::query()->updateOrCreate(
            ['name' => 'ИНСТРУМЕНТИ'],
            ['parent_id' => null],
        );

        foreach ([
            'ДЪРЖАЧИ',
            'ИЗМЕРВАТЕЛНИ',
            'КАЛИБРИ',
            'КОБАЛТ',
            'МЕТЧИЦИ',
            'НОЖОВЕ',
            'ПЛАСТИНИ',
            'ПЛАШКИ',
            'СВРЕДЛА',
            'ФРЕЗИ',
            'ЩАНГИ',
        ] as $name) {
            Category::query()->updateOrCreate(
                ['name' => $name],
                ['parent_id' => $parent->id],
            );
        }

        Category::query()->updateOrCreate(
            ['name' => 'ОБОРУДВАНЕ'],
            ['parent_id' => null],
        );
    }
}
