<?php

namespace Tests\Unit;

use App\Console\Commands\ImportInventory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ImportInventoryProductSplitTest extends TestCase
{
    #[DataProvider('inventoryVariants')]
    public function test_inventory_names_are_split_into_product_and_variant(
        string $rawName,
        string $expectedProduct,
        ?string $expectedVariant,
    ): void {
        $command = new ImportInventory();
        $method = new ReflectionMethod($command, 'splitProductAndVariant');

        $this->assertSame(
            [$expectedProduct, $expectedVariant],
            $method->invoke($command, $rawName),
        );
    }

    public static function inventoryVariants(): array
    {
        return [
            'compact millimeter size without space' => ['ОТВЕРКА /FORCE/3мм', 'ОТВЕРКА /FORCE/', '3ММ'],
            'compact millimeter size' => ['ОТВЕРКА /FORCE/ 3мм', 'ОТВЕРКА /FORCE/', '3ММ'],
            'compact decimal millimeter size' => ['ОТВЕРКА /FORCE/ 3.5мм', 'ОТВЕРКА /FORCE/', '3.5ММ'],
            'pozidriv profile' => ['ОТВЕРКА /FORCE/ PZ1', 'ОТВЕРКА /FORCE/', 'PZ1'],
            'lowercase iso arbor' => ['дорник исо40 на', 'ДОРНИК ИСО40', 'НА'],
            'round file length' => ['ПИЛА ОБЛА L100', 'ПИЛА ОБЛА', 'L100'],
            'iso arbor available' => ['ДОРНИК ИСО40 НА', 'ДОРНИК ИСО40', 'НА'],
            'iso arbor purpose' => ['ДОРНИК ИСО40 ЗА ФРЕЗА', 'ДОРНИК ИСО40', 'ЗА ФРЕЗА'],
            'iso arbor reordered purpose' => ['ДОРНИК ЗА ФРЕЗА ИСО50', 'ДОРНИК ИСО50', 'ЗА ФРЕЗА'],
            'iso arbor rv' => ['ДОРНИК ИСО40 ЗА RV НА', 'ДОРНИК ИСО40', 'ЗА RV НА'],
            'iso arbor fu' => ['ДОРНИК ИСО50 ЗА ФУ320 НА', 'ДОРНИК ИСО50', 'ЗА ФУ320 НА'],
            'iso arbor fu extended' => ['ДОРНИК ИСО50 ЗА ФУ320 УДЪЛЖЕНИ НА', 'ДОРНИК ИСО50', 'ЗА ФУ320 УДЪЛЖЕНИ НА'],
            'instrument mill arbor' => ['ДОРНИК ЗА ИНСТР ФРЕЗА МК2 НА', 'ДОРНИК ЗА ИНСТР ФРЕЗА', 'МК2 НА'],
            'surface plate arbor' => ['ДОРНИК ЗА ФРЕЗОВА ГЛАВА СМ.ПЛ.', 'ДОРНИК ЗА ФРЕЗОВА ГЛАВА', 'СМ.ПЛ.'],
            'flange arbor' => ['ДОРНИК С ФЛАНЕЦ ЗА УНИВЕРСАЛ', 'ДОРНИК С ФЛАНЕЦ', 'ЗА УНИВЕРСАЛ'],
            'standalone collet arbor' => ['ДОРНИК ЦАНГОВ', 'ДОРНИК ЦАНГОВ', null],
            'collet arbor iso' => ['ДОРНИК ЦАНГОВ ИСО30', 'ДОРНИК ЦАНГОВ', 'ИСО30'],
            'collet arbor iso with diameter' => ['ДОРНИК ЦАНГОВ ИСО40 Ф3-Ф8', 'ДОРНИК ЦАНГОВ', 'ИСО40 Ф3-Ф8'],
            'collet arbor machine center' => ['ДОРНИК ЦАНГОВ ИСО40 ЗА МАШ ЦЕНТЪР', 'ДОРНИК ЦАНГОВ', 'ИСО40 ЗА МАШ ЦЕНТЪР'],
            'file handle size' => ['ДРЪЖКА ЗА ПИЛА МАЛКА', 'ДРЪЖКА ЗА ПИЛА', 'МАЛКА'],
            'drill chuck arbor tail' => ['ОПАШКА ЗА ПАТР B12', 'ОПАШКА ЗА ПАТР', 'B12'],
            'cross connector for arbor' => ['СЪЕДИН КРЪСТАТ ЗА ДОРН DC16', 'СЪЕДИН КРЪСТАТ ЗА ДОРН', 'DC16'],
            'textolite sheet thickness' => ['ТЕКСТОЛИТ НА ЛИСТ 0.8мм', 'ТЕКСТОЛИТ НА ЛИСТ', '0.8ММ'],
            'advance guillotine knife size' => ['АВАНС. ПЛАЩАНЕ 50% НОЖ ЗА ИЛИОТИНА 710X78X20 ПО ФАКТУРА', 'АВАНС. ПЛАЩАНЕ 50% НОЖ ЗА ИЛИОТИНА', '710X78X20 ПО ФАКТУРА'],
            'external turning knife size' => ['НОЖ ЗА ВЪНШНО ПРЕСТЪРГВАНЕ 20X20', 'НОЖ ЗА ВЪНШНО ПРЕСТЪРГВАНЕ', '20X20'],
            'internal turning knife size' => ['НОЖ ЗА ВЪТРЕШНО ПРЕСТЪРГВАНЕ 32X32', 'НОЖ ЗА ВЪТРЕШНО ПРЕСТЪРГВАНЕ', '32X32'],
            'face turning knife size' => ['НОЖ ЗА ЧЕЛНО ПРЕСТ. 45 ГРАДУСА 25X25', 'НОЖ ЗА ЧЕЛНО ПРЕСТ. 45 ГРАДУСА', '25X25'],
            'parting knife size' => ['НОЖ ОТРЕЗЕН 20X12', 'НОЖ ОТРЕЗЕН', '20X12'],
            'arbor cutter grade' => ['ФРЕЗА ДОРН 3ТП Р30', 'ФРЕЗА ДОРН 3ТП', 'Р30'],
            'three sided cutter grade' => ['ФРЕЗА ТРИСТР K30', 'ФРЕЗА ТРИСТР', 'K30'],
            'three sided cutter type' => ['ФРЕЗА ТРИСТР T N', 'ФРЕЗА ТРИСТР', 'T N'],
            'three sided cutter hard type' => ['ФРЕЗА ТРИСТР H', 'ФРЕЗА ТРИСТР', 'H'],
            'cylindrical face cutter type' => ['ФРЕЗА ЧЕЛ ЦИЛ T H', 'ФРЕЗА ЧЕЛ ЦИЛ', 'T H'],
            'cylindrical face cutter rough type' => ['ФРЕЗА ЧЕЛ ЦИЛ T R', 'ФРЕЗА ЧЕЛ ЦИЛ', 'T R'],
            'disc slotting cutter module' => ['ДЪЛБЯК ДИСКОВ M2.5', 'ДЪЛБЯК ДИСКОВ', 'M2.5'],
            'worm shank cutter module' => ['ФРЕЗА ЧЕРВЯЧНА ОПАШКОВА A20 M1.25', 'ФРЕЗА ЧЕРВЯЧНА ОПАШКОВА A20', 'M1.25'],
            'worm shank cutter module integer' => ['ФРЕЗА ЧЕРВЯЧНА ОПАШКОВА A20 M2', 'ФРЕЗА ЧЕРВЯЧНА ОПАШКОВА A20', 'M2'],
            'worm shank cutter cyrillic module' => ['ФРЕЗА ЧЕРВЯЧНА ОПАШКОВА А20 М2.5', 'ФРЕЗА ЧЕРВЯЧНА ОПАШКОВА А20', 'М2.5'],
            'cup slotting cutter module' => ['ДЪЛБЯК ЧАШКОВИД M3 A20', 'ДЪЛБЯК ЧАШКОВИД', 'M3 A20'],
            'hand tap threaded variant' => ['МЕТЧИК РЪЧЕН ТР PX2 G1', 'МЕТЧИК РЪЧЕН ТР', 'PX2 G1'],
            'hand tap variant' => ['МЕТЧИК РЪЧЕН PX2', 'МЕТЧИК РЪЧЕН', 'PX2'],
            'thread die variant' => ['ПЛАШКА ТР 1', 'ПЛАШКА ТР', '1'],
            'bent turning knife grade' => ['НОЖ ПРОХОДНО ИЗВИТ 45ГР ISO2 P20', 'НОЖ ПРОХОДНО ИЗВИТ 45ГР', 'ISO2 P20'],
            'external thread knife grade' => ['НОЖ ЗА ВЪНШНА РЕЗБА ISO13 P30', 'НОЖ ЗА ВЪНШНА РЕЗБА', 'ISO13 P30'],
            'internal thread knife standalone' => ['НОЖ ЗА ВЪТРЕШНА РЕЗБА', 'НОЖ ЗА ВЪТРЕШНА РЕЗБА', null],
            'internal thread knife grade' => ['НОЖ ЗА ВЪТРЕШНА РЕЗБА ISO14 P10', 'НОЖ ЗА ВЪТРЕШНА РЕЗБА', 'ISO14 P10'],
            'internal thread knife hard grade' => ['НОЖ ЗА ВЪТРЕШНА РЕЗБА ISO14 K30', 'НОЖ ЗА ВЪТРЕШНА РЕЗБА', 'ISO14 K30'],
            'internal thread knife grade without iso' => ['НОЖ ЗА ВЪТРЕШНА РЕЗБА Р30', 'НОЖ ЗА ВЪТРЕШНА РЕЗБА', 'Р30'],
            'blind hole knife grade' => ['НОЖ ЗА ГЛУХ ОТВОР ISO9 P20', 'НОЖ ЗА ГЛУХ ОТВОР', 'ISO9 P20'],
            'blind hole knife cyrillic iso grade' => ['НОЖ ЗА ГЛУХ ОТВОР ИСО9 М20', 'НОЖ ЗА ГЛУХ ОТВОР', 'ИСО9 М20'],
            'through hole knife grade' => ['НОЖ ЗА ПРОХОДЕН ОТВОР ISO8 P30', 'НОЖ ЗА ПРОХОДЕН ОТВОР', 'ISO8 P30'],
            'straight knife grade' => ['НОЖ ПРАВ ISO1 P20', 'НОЖ ПРАВ', 'ISO1 P20'],
            'slotting knife iso grade' => ['НОЖ ПРОРЕЗЕН ISO7 P10', 'НОЖ ПРОРЕЗЕН', 'ISO7 P10'],
            'slotting knife grade' => ['НОЖ ПРОРЕЗЕН P30', 'НОЖ ПРОРЕЗЕН', 'P30'],
            'slotting knife cyrillic iso' => ['НОЖ ПРОРЕЗЕН ИСО7', 'НОЖ ПРОРЕЗЕН', 'ИСО7'],
            'finishing knife grade' => ['НОЖ ЧИСТ ISO4 P01', 'НОЖ ЧИСТ', 'ISO4 P01'],
            'finishing knife cyrillic iso' => ['НОЖ ЧИСТ ИСО4', 'НОЖ ЧИСТ', 'ИСО4'],
            'support knife iso grade' => ['НОЖ УПОРЕН ISO6 K20', 'НОЖ УПОРЕН', 'ISO6 K20'],
            'support knife cyrillic grade' => ['НОЖ УПОРЕН К20', 'НОЖ УПОРЕН', 'К20'],
            'trapezoid channel knife grade' => ['НОЖ ЗА ТРАП КАНАЛ ISO20 P30', 'НОЖ ЗА ТРАП КАНАЛ', 'ISO20 P30'],
            'trapezoid dotted channel knife grade' => ['НОЖ ЗА ТРАП. КАНАЛ ISO20 K20', 'НОЖ ЗА ТРАП. КАНАЛ', 'ISO20 K20'],
            'mechanical hacksaw blade dimension' => ['ЛИСТ МЕХАНИЧНА НОЖОВКА L450/40/2.5 ПИЛАНА', 'ЛИСТ МЕХАНИЧНА НОЖОВКА', 'L450/40/2.5 ПИЛАНА'],
            'bent knife 30 degrees grade' => ['НОЖ ИЗВИТ 30ГР P20', 'НОЖ ИЗВИТ 30ГР', 'P20'],
            'holder for n150 variant' => ['ДЪРЖАЧ ЗА N150.2 - 500', 'ДЪРЖАЧ ЗА N150.2', '500'],
        ];
    }
}
