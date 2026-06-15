<?php

namespace Tests\Unit;

use App\Console\Commands\ImportInventory;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ImportInventoryStandaloneNTest extends TestCase
{
    public function test_n_rows_remain_standalone_products(): void
    {
        $command = new ImportInventory();
        $splitMethod = new ReflectionMethod($command, 'splitProductAndVariant');
        $parentMethod = new ReflectionMethod($command, 'canBeVariantParent');

        $this->assertSame(
            ['N 151.2 6008 SANDVIK', null],
            $splitMethod->invoke($command, 'N 151.2 6008 SANDVIK'),
        );

        $this->assertFalse($parentMethod->invoke($command, ['name' => 'N']));
    }

    public function test_r_rows_still_split_into_variants(): void
    {
        $command = new ImportInventory();
        $splitMethod = new ReflectionMethod($command, 'splitProductAndVariant');
        $parentMethod = new ReflectionMethod($command, 'canBeVariantParent');

        $this->assertSame(
            ['R', '166 L 2AA 150 GERMANY'],
            $splitMethod->invoke($command, 'R 166 L 2AA 150 GERMANY'),
        );

        $this->assertTrue($parentMethod->invoke($command, ['name' => 'R']));
    }
}
