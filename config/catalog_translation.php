<?php

return [
    'provider' => env('TRANSLATION_PROVIDER', 'libretranslate'),
    'base_url' => env('TRANSLATION_BASE_URL', 'http://192.168.1.123:8000'),
    'api_key' => env('TRANSLATION_API_KEY'),
    'timeout' => (int) env('TRANSLATION_TIMEOUT', 20),
    'cache_store' => env('TRANSLATION_CACHE_STORE', 'file'),
    'cache_ttl' => (int) env('TRANSLATION_CACHE_TTL', 2592000),

    'glossary' => [
        'en' => [
            ['pattern' => '/(?<!\pL)ДИСК\s+ЛАМЕЛЕН(?!\pL)/iu', 'replace' => 'FLAP DISC'],
            ['pattern' => '/(?<!\pL)ЛАМЕЛЕН\s+ШЛАЙФГРИФЕР(?!\pL)/iu', 'replace' => 'FLAP MOUNTED POINT'],
            ['pattern' => '/(?<!\pL)ДИАМАНТЕН\s+ШЛАЙФГРИФЕР(?!\pL)/iu', 'replace' => 'DIAMOND MOUNTED POINT'],
            ['pattern' => '/(?<!\pL)ЛАМАРИНА(?!\pL)/iu', 'replace' => 'SHEET METAL'],
            ['pattern' => '/(?<!\pL)СВРЕДЛО\s+ЗА\s+МЕТАЛ(?!\pL)/iu', 'replace' => 'DRILL FOR METAL'],
            ['pattern' => '/(?<!\pL)СВРЕДЛО\s+ЗА\s+ЛАМАРИНА(?!\pL)/iu', 'replace' => 'DRILL FOR SHEET METAL'],
            ['pattern' => '/(?<!\pL)СВРЕДЛА\s+ЗА\s+МЕТАЛ(?!\pL)/iu', 'replace' => 'DRILLS FOR METAL'],
            ['pattern' => '/(?<!\pL)СВРЕДЛА\s+ЗА\s+ЛАМАРИНА(?!\pL)/iu', 'replace' => 'DRILLS FOR SHEET METAL'],
            ['pattern' => '/(?<!\pL)ШЛАЙФГРИФЕР(?!\pL)/iu', 'replace' => 'MOUNTED POINT'],
            ['pattern' => '/(?<!\pL)ШЛАЙФРИФЕР(?!\pL)/iu', 'replace' => 'MOUNTED POINT'],
            ['pattern' => '/(?<!\pL)ШЛАЙФПИЛА(?!\pL)/iu', 'replace' => 'GRINDING FILE'],
            ['pattern' => '/(?<!\pL)ШЛАЙФШАЙБА(?!\pL)/iu', 'replace' => 'GRINDING WHEEL'],
            ['pattern' => '/(?<!\pL)ПНЕВМОШЛАЙФ(?!\pL)/iu', 'replace' => 'PNEUMATIC GRINDER'],
            ['pattern' => '/(?<!\pL)ПРЕДШЛАЙФ(?!\pL)/iu', 'replace' => 'PRE-GRINDING'],
            ['pattern' => '/(?<!\pL)ЗА\s+ПЛОСК\s+ШЛАЙФ(?!\pL)/iu', 'replace' => 'FOR FLAT GRINDER'],
            ['pattern' => '/(?<!\pL)ПЛОСК\s+ШЛАЙФ(?!\pL)/iu', 'replace' => 'FLAT GRINDER'],
            ['pattern' => '/(?<!\pL)АБРАЗИВ\s+ЕР\s+ЗА\s+ПЛОСК\s+ШЛАЙФ(?!\pL)/iu', 'replace' => 'ABRASIVE ER FOR FLAT GRINDER'],
            ['pattern' => '/(?<!\pL)ИНДИКАТОРЕН\s+ЧАСОВНИК(?!\pL)/iu', 'replace' => 'DIAL INDICATOR'],
            ['pattern' => '/(?<!\pL)МЕТЧИК\s+РЪЧЕН(?!\pL)/iu', 'replace' => 'HAND TAP'],
            ['pattern' => '/(?<!\pL)ЗЕНКЕР\s+КОНУСЕН(?!\pL)/iu', 'replace' => 'CONICAL COUNTERSINK'],
            ['pattern' => '/(?<!\pL)ФРЕЗА\s+ТВЪРДОСПЛАВНА(?!\pL)/iu', 'replace' => 'CARBIDE MILLING CUTTER'],
            ['pattern' => '/(?<!\pL)ФРЕЗА\s+РАДИУСНА(?!\pL)/iu', 'replace' => 'RADIUS MILLING CUTTER'],
            ['pattern' => '/(?<!\pL)ФРЕЗА\s+ДОРН(?!\pL)/iu', 'replace' => 'ARBOR MILLING CUTTER'],
            ['pattern' => '/(?<!\pL)СВРЕДЛА\s+ЗА(?!\pL)/iu', 'replace' => 'DRILLS FOR'],
            ['pattern' => '/(?<!\pL)СВРЕДЛО\s+ЗА(?!\pL)/iu', 'replace' => 'DRILL FOR'],
            ['pattern' => '/(?<!\pL)ЗА\s+ФРЕЗА(?!\pL)/iu', 'replace' => 'FOR MILLING CUTTER'],
            ['pattern' => '/(?<!\pL)ЗА\s+МЕТЧИК(?!\pL)/iu', 'replace' => 'FOR TAP'],
            ['pattern' => '/(?<!\pL)ЗА\s+ПАТРОННИК(?!\pL)/iu', 'replace' => 'FOR CHUCK'],
            ['pattern' => '/(?<!\pL)ЗА\s+МЕТАЛ(?!\pL)/iu', 'replace' => 'FOR METAL'],
            ['pattern' => '/(?<!\pL)ЗА\s+ЛАМАРИНА(?!\pL)/iu', 'replace' => 'FOR SHEET METAL'],
            ['pattern' => '/(?<!\pL)ЗА\s+БЕТОН(?!\pL)/iu', 'replace' => 'FOR CONCRETE'],
            ['pattern' => '/(?<!\pL)ЗА\s+ДЪРВО(?!\pL)/iu', 'replace' => 'FOR WOOD'],
            ['pattern' => '/(?<!\pL)ЗА\s+ХИЛТИ(?!\pL)/iu', 'replace' => 'FOR HILTI'],
            ['pattern' => '/(?<!\pL)СВРЕДЛА(?!\pL)/iu', 'replace' => 'DRILLS'],
            ['pattern' => '/(?<!\pL)СВРЕДЛО(?!\pL)/iu', 'replace' => 'DRILL'],
            ['pattern' => '/(?<!\pL)БЪРЗОПРОБИВНО(?!\pL)/iu', 'replace' => 'RAPID DRILLING'],
            ['pattern' => '/(?<!\pL)СПИРАЛОВИДНО(?!\pL)/iu', 'replace' => 'SPIRAL'],
            ['pattern' => '/(?<!\pL)АБРАЗИВ(?!\pL)/iu', 'replace' => 'ABRASIVE'],
            ['pattern' => '/(?<!\pL)ПЛАСТИНИ(?!\pL)/iu', 'replace' => 'INSERTS'],
            ['pattern' => '/(?<!\pL)ПЛАСТИНА(?!\pL)/iu', 'replace' => 'INSERT'],
            ['pattern' => '/(?<!\pL)НОЖОВЕ(?!\pL)/iu', 'replace' => 'CUTTERS'],
            ['pattern' => '/(?<!\pL)НОЖ(?!\pL)/iu', 'replace' => 'CUTTER'],
            ['pattern' => '/(?<!\pL)ДЪРЖАЧИ(?!\pL)/iu', 'replace' => 'HOLDERS'],
            ['pattern' => '/(?<!\pL)ДЪРЖАЧ(?!\pL)/iu', 'replace' => 'HOLDER'],
            ['pattern' => '/(?<!\pL)ШУБЛЕР(?!\pL)/iu', 'replace' => 'CALIPER'],
            ['pattern' => '/(?<!\pL)ЗЕНКЕР(?!\pL)/iu', 'replace' => 'COUNTERSINK'],
            ['pattern' => '/(?<!\pL)ЗЕНКОВКА(?!\pL)/iu', 'replace' => 'COUNTERSINK'],
            ['pattern' => '/(?<!\pL)ЩАНГА(?!\pL)/iu', 'replace' => 'BAR'],
            ['pattern' => '/(?<!\pL)ЧАША(?!\pL)/iu', 'replace' => 'CUP'],
            ['pattern' => '/(?<!\pL)РЕЗБА(?!\pL)/iu', 'replace' => 'THREAD'],
            ['pattern' => '/(?<!\pL)ДЪЛБЯК(?!\pL)/iu', 'replace' => 'BROACH'],
            ['pattern' => '/(?<!\pL)ОПАШКА(?!\pL)/iu', 'replace' => 'SHANK'],
            ['pattern' => '/(?<!\pL)ПЛОСКА(?!\pL)/iu', 'replace' => 'FLAT'],
            ['pattern' => '/(?<!\pL)ПЛОСК(?!\pL)/iu', 'replace' => 'FLAT'],
            ['pattern' => '/(?<!\pL)КОНУСЕН(?!\pL)/iu', 'replace' => 'CONICAL'],
            ['pattern' => '/(?<!\pL)КОНУСОВИДНА(?!\pL)/iu', 'replace' => 'CONE-SHAPED'],
            ['pattern' => '/(?<!\pL)ОБЪЛ(?!\pL)/iu', 'replace' => 'ROUND'],
            ['pattern' => '/(?<!\pL)КОНУС(?!\pL)/iu', 'replace' => 'CONE'],
            ['pattern' => '/(?<!\pL)ЦИЛИНДРИЧЕН(?!\pL)/iu', 'replace' => 'CYLINDRICAL'],
            ['pattern' => '/(?<!\pL)ВЪНШНА(?!\pL)/iu', 'replace' => 'EXTERNAL'],
            ['pattern' => '/(?<!\pL)ВЪТРЕШНА(?!\pL)/iu', 'replace' => 'INTERNAL'],
            ['pattern' => '/(?<!\pL)ЧЕЛЕН(?!\pL)/iu', 'replace' => 'FACE'],
            ['pattern' => '/(?<!\pL)РАДИУСНА(?!\pL)/iu', 'replace' => 'RADIUS'],
            ['pattern' => '/(?<!\pL)РАДИУСЕН(?!\pL)/iu', 'replace' => 'RADIUS'],
            ['pattern' => '/(?<!\pL)ПРОХОДНО(?!\pL)/iu', 'replace' => 'THROUGH'],
            ['pattern' => '/(?<!\pL)ПРАВ(?!\pL)/iu', 'replace' => 'STRAIGHT'],
            ['pattern' => '/(?<!\pL)ИЗВИТ(?!\pL)/iu', 'replace' => 'CURVED'],
            ['pattern' => '/(?<!\pL)УПОРЕН(?!\pL)/iu', 'replace' => 'THRUST'],
            ['pattern' => '/(?<!\pL)РЪЧЕН(?!\pL)/iu', 'replace' => 'HAND'],
            ['pattern' => '/(?<!\pL)МАШ(?!\pL)/iu', 'replace' => 'MACHINE'],
            ['pattern' => '/(?<!\pL)НОВО(?!\pL)/iu', 'replace' => 'NEW'],
            ['pattern' => '/(?<!\pL)БАНЕР(?!\pL)/iu', 'replace' => 'BANNER'],
            ['pattern' => '/(?<!\pL)ПОДЗАГЛАВИЕ(?!\pL)/iu', 'replace' => 'SUBTITLE'],
            ['pattern' => '/(?<!\pL)ВИЖ(?!\pL)/iu', 'replace' => 'VIEW'],
        ],
        'de' => [],
    ],

    'rules' => [
        'en' => [
            [
                'pattern' => '/(?<!\pL)ЛАМЕЛЕН\s+ШЛАЙФГРИФЕР(?!\pL)/iu',
                'replace' => 'FLAP MOUNTED POINT',
            ],
            [
                'pattern' => '/(?<!\pL)ДИАМАНТЕН\s+ШЛАЙФГРИФЕР(?!\pL)/iu',
                'replace' => 'DIAMOND MOUNTED POINT',
            ],
            [
                'pattern' => '/(?<!\pL)ШЛАЙФГРИФЕР(?!\pL)/iu',
                'replace' => 'MOUNTED POINT',
            ],
            [
                'pattern' => '/(?<!\pL)ШЛАЙФРИФЕР(?!\pL)/iu',
                'replace' => 'MOUNTED POINT',
            ],
            [
                'pattern' => '/(?<!\pL)ШЛАЙФ(?!\pL)/iu',
                'replace' => 'GRINDER',
            ],
            [
                'pattern' => '/(?<!\pL)ЕН(?!\pL)/iu',
                'replace' => 'EN',
            ],
            [
                'pattern' => '/(?<!\pL)ЕР(?!\pL)/iu',
                'replace' => 'ER',
            ],
            [
                'pattern' => '/(?<!\pL)ЕБ(?!\pL)/iu',
                'replace' => 'EB',
            ],
            [
                'pattern' => '/(?<!\pL)ШЛАЙФПИЛА(?!\pL)/iu',
                'replace' => 'GRINDING FILE',
            ],
            [
                'pattern' => '/(?<!\pL)ШЛАЙФШАЙБА(?!\pL)/iu',
                'replace' => 'GRINDING WHEEL',
            ],
            [
                'pattern' => '/(?<!\pL)ПНЕВМОШЛАЙФ(?!\pL)/iu',
                'replace' => 'PNEUMATIC GRINDER',
            ],
            [
                'pattern' => '/(?<!\pL)ПРЕДШЛАЙФ(?!\pL)/iu',
                'replace' => 'PRE-GRINDING',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+ПЛОСК\s+ШЛАЙФ(?!\pL)/iu',
                'replace' => 'FOR FLAT GRINDER',
            ],
            [
                'pattern' => '/(?<!\pL)ПЛОСК\s+ШЛАЙФ(?!\pL)/iu',
                'replace' => 'FLAT GRINDER',
            ],
            [
                'pattern' => '/(?<!\pL)ИНДИКАТОРЕН\s+ЧАСОВНИК(?!\pL)/iu',
                'replace' => 'DIAL INDICATOR',
            ],
            [
                'pattern' => '/(?<!\pL)МЕТЧИК\s+РЪЧЕН(?!\pL)/iu',
                'replace' => 'HAND TAP',
            ],
            [
                'pattern' => '/(?<!\pL)ЗЕНКЕР\s+КОНУСЕН(?!\pL)/iu',
                'replace' => 'CONICAL COUNTERSINK',
            ],
            [
                'pattern' => '/(?<!\pL)ФРЕЗА\s+ТВЪРДОСПЛАВНА(?!\pL)/iu',
                'replace' => 'CARBIDE MILLING CUTTER',
            ],
            [
                'pattern' => '/(?<!\pL)ФРЕЗА\s+РАДИУСНА(?!\pL)/iu',
                'replace' => 'RADIUS MILLING CUTTER',
            ],
            [
                'pattern' => '/(?<!\pL)ФРЕЗА\s+ДОРН(?!\pL)/iu',
                'replace' => 'ARBOR MILLING CUTTER',
            ],
            [
                'pattern' => '/(?<!\pL)ДЪРЖАЧ\s+ЗА(?!\pL)/iu',
                'replace' => 'HOLDER FOR',
            ],
            [
                'pattern' => '/(?<!\pL)ПОДЛОЖКА\s+ЗА(?!\pL)/iu',
                'replace' => 'PAD FOR',
            ],
            [
                'pattern' => '/(?<!\pL)СВРЕДЛА\s+ЗА(?!\pL)/iu',
                'replace' => 'DRILLS FOR',
            ],
            [
                'pattern' => '/(?<!\pL)СВРЕДЛО\s+ЗА(?!\pL)/iu',
                'replace' => 'DRILL FOR',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+ФРЕЗА(?!\pL)/iu',
                'replace' => 'FOR MILLING CUTTER',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+МЕТЧИК(?!\pL)/iu',
                'replace' => 'FOR TAP',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+ПАТРОННИК(?!\pL)/iu',
                'replace' => 'FOR CHUCK',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+МЕТАЛ(?!\pL)/iu',
                'replace' => 'FOR METAL',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+ЛАМАРИНА(?!\pL)/iu',
                'replace' => 'FOR SHEET METAL',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+БЕТОН(?!\pL)/iu',
                'replace' => 'FOR CONCRETE',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+ДЪРВО(?!\pL)/iu',
                'replace' => 'FOR WOOD',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+ХИЛТИ(?!\pL)/iu',
                'replace' => 'FOR HILTI',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА(?!\pL)/iu',
                'replace' => 'FOR',
            ],
            [
                'pattern' => '/(?<!\pL)ПЛАСТИНИ(?!\pL)/iu',
                'replace' => 'INSERTS',
            ],
            [
                'pattern' => '/(?<!\pL)ПЛАСТИНА(?!\pL)/iu',
                'replace' => 'INSERT',
            ],
            [
                'pattern' => '/(?<!\pL)НОЖОВЕ(?!\pL)/iu',
                'replace' => 'CUTTERS',
            ],
            [
                'pattern' => '/(?<!\pL)НОЖ(?!\pL)/iu',
                'replace' => 'CUTTER',
            ],
            [
                'pattern' => '/(?<!\pL)ДЪРЖАЧИ(?!\pL)/iu',
                'replace' => 'HOLDERS',
            ],
            [
                'pattern' => '/(?<!\pL)ДЪРЖАЧ(?!\pL)/iu',
                'replace' => 'HOLDER',
            ],
            [
                'pattern' => '/(?<!\pL)ШУБЛЕР(?!\pL)/iu',
                'replace' => 'CALIPER',
            ],
            [
                'pattern' => '/(?<!\pL)ЗЕНКЕР(?!\pL)/iu',
                'replace' => 'COUNTERSINK',
            ],
            [
                'pattern' => '/(?<!\pL)ЗЕНКОВКА(?!\pL)/iu',
                'replace' => 'COUNTERSINK',
            ],
            [
                'pattern' => '/(?<!\pL)АБРАЗИВ(?!\pL)/iu',
                'replace' => 'ABRASIVE',
            ],
            [
                'pattern' => '/(?<!\pL)ЩАНГА(?!\pL)/iu',
                'replace' => 'BAR',
            ],
            [
                'pattern' => '/(?<!\pL)ЧАША(?!\pL)/iu',
                'replace' => 'CUP',
            ],
            [
                'pattern' => '/(?<!\pL)РЕЗБА(?!\pL)/iu',
                'replace' => 'THREAD',
            ],
            [
                'pattern' => '/(?<!\pL)ДЪЛБЯК(?!\pL)/iu',
                'replace' => 'BROACH',
            ],
            [
                'pattern' => '/(?<!\pL)ОПАШКА(?!\pL)/iu',
                'replace' => 'SHANK',
            ],
            [
                'pattern' => '/(?<!\pL)ПЛОСКА(?!\pL)/iu',
                'replace' => 'FLAT',
            ],
            [
                'pattern' => '/(?<!\pL)ПЛОСК(?!\pL)/iu',
                'replace' => 'FLAT',
            ],
            [
                'pattern' => '/(?<!\pL)КОНУСЕН(?!\pL)/iu',
                'replace' => 'CONICAL',
            ],
            [
                'pattern' => '/(?<!\pL)КОНУСОВИДНА(?!\pL)/iu',
                'replace' => 'CONE-SHAPED',
            ],
            [
                'pattern' => '/(?<!\pL)ОБЪЛ(?!\pL)/iu',
                'replace' => 'ROUND',
            ],
            [
                'pattern' => '/(?<!\pL)КОНУС(?!\pL)/iu',
                'replace' => 'CONE',
            ],
            [
                'pattern' => '/(?<!\pL)ЦИЛИНДРИЧЕН(?!\pL)/iu',
                'replace' => 'CYLINDRICAL',
            ],
            [
                'pattern' => '/(?<!\pL)ВЪНШНА(?!\pL)/iu',
                'replace' => 'EXTERNAL',
            ],
            [
                'pattern' => '/(?<!\pL)ВЪТРЕШНА(?!\pL)/iu',
                'replace' => 'INTERNAL',
            ],
            [
                'pattern' => '/(?<!\pL)ЧЕЛЕН(?!\pL)/iu',
                'replace' => 'FACE',
            ],
            [
                'pattern' => '/(?<!\pL)РАДИУСНА(?!\pL)/iu',
                'replace' => 'RADIUS',
            ],
            [
                'pattern' => '/(?<!\pL)РАДИУСЕН(?!\pL)/iu',
                'replace' => 'RADIUS',
            ],
            [
                'pattern' => '/(?<!\pL)ПРОХОДНО(?!\pL)/iu',
                'replace' => 'THROUGH',
            ],
            [
                'pattern' => '/(?<!\pL)ПРАВ(?!\pL)/iu',
                'replace' => 'STRAIGHT',
            ],
            [
                'pattern' => '/(?<!\pL)ИЗВИТ(?!\pL)/iu',
                'replace' => 'CURVED',
            ],
            [
                'pattern' => '/(?<!\pL)УПОРЕН(?!\pL)/iu',
                'replace' => 'THRUST',
            ],
            [
                'pattern' => '/(?<!\pL)РЪЧЕН(?!\pL)/iu',
                'replace' => 'HAND',
            ],
            [
                'pattern' => '/(?<!\pL)МАШ(?!\pL)/iu',
                'replace' => 'MACHINE',
            ],
            [
                'pattern' => '/(?<!\pL)СВРЕДЛА(?!\pL)/iu',
                'replace' => 'DRILLS',
            ],
            [
                'pattern' => '/(?<!\pL)СВРЕДЛО(?!\pL)/iu',
                'replace' => 'DRILL',
            ],
            [
                'pattern' => '/(?<!\pL)БЪРЗОПРОБИВНО(?!\pL)/iu',
                'replace' => 'RAPID DRILLING',
            ],
            [
                'pattern' => '/(?<!\pL)СПИРАЛОВИДНО(?!\pL)/iu',
                'replace' => 'SPIRAL',
            ],
            [
                'pattern' => '/(?<!\pL)МЕТАЛ(?!\pL)/iu',
                'replace' => 'METAL',
            ],
            [
                'pattern' => '/(?<!\pL)ЛАМАРИНА(?!\pL)/iu',
                'replace' => 'SHEET METAL',
            ],
            [
                'pattern' => '/(?<!\pL)БЕТОН(?!\pL)/iu',
                'replace' => 'CONCRETE',
            ],
            [
                'pattern' => '/(?<!\pL)ДЪРВО(?!\pL)/iu',
                'replace' => 'WOOD',
            ],
            [
                'pattern' => '/(?<!\pL)ХИЛТИ(?!\pL)/iu',
                'replace' => 'HILTI',
            ],
            [
                'pattern' => '/(?<!\pL)ЦЕНТРОВО(?!\pL)/iu',
                'replace' => 'CENTER',
            ],
            [
                'pattern' => '/(?<!\pL)ЦЕНТЪРНО(?!\pL)/iu',
                'replace' => 'CENTER',
            ],
            [
                'pattern' => '/(?<!\pL)ТВЪРДОСПЛ(?!\pL)/iu',
                'replace' => 'CARBIDE',
            ],
            [
                'pattern' => '/(?<!\pL)КОБАЛТ(?!\pL)/iu',
                'replace' => 'COBALT',
            ],
            [
                'pattern' => '/(?<!\pL)ДОРНИК\s+ЦАНГОВ(?!\pL)/iu',
                'replace' => 'COLLET ARBOR',
            ],
            [
                'pattern' => '/(?<!\pL)ДОРНИК\s+ЗА\s+ФРЕЗА(?!\pL)/iu',
                'replace' => 'ARBOR FOR MILLING CUTTER',
            ],
            [
                'pattern' => '/(?<!\pL)ДОРНИК(?!\pL)/iu',
                'replace' => 'ARBOR',
            ],
            [
                'pattern' => '/(?<!\pL)ФРЕЗА(?!\pL)/iu',
                'replace' => 'MILL',
            ],
            [
                'pattern' => '/(?<!\pL)ПИЛА(?!\pL)/iu',
                'replace' => 'SAW',
            ],
            [
                'pattern' => '/(?<!\pL)НОВО(?!\pL)/iu',
                'replace' => 'NEW',
            ],
            [
                'pattern' => '/(?<!\pL)БАНЕР(?!\pL)/iu',
                'replace' => 'BANNER',
            ],
            [
                'pattern' => '/(?<!\pL)ПОДЗАГЛАВИЕ(?!\pL)/iu',
                'replace' => 'SUBTITLE',
            ],
            [
                'pattern' => '/(?<!\pL)ВИЖ(?!\pL)/iu',
                'replace' => 'VIEW',
            ],
            [
                'pattern' => '/Ф(?=\s*\d)/u',
                'replace' => 'Ø',
            ],
        ],
        'de' => [
            [
                'pattern' => '/(?<!\pL)СВРЕДЛА(?!\pL)/iu',
                'replace' => 'BOHRER',
            ],
            [
                'pattern' => '/(?<!\pL)СВРЕДЛО(?!\pL)/iu',
                'replace' => 'BOHRER',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+ЛАМАРИНА(?!\pL)/iu',
                'replace' => 'FÜR BLECH',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+МЕТАЛ(?!\pL)/iu',
                'replace' => 'FÜR METALL',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+БЕТОН(?!\pL)/iu',
                'replace' => 'FÜR BETON',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+ДЪРВО(?!\pL)/iu',
                'replace' => 'FÜR HOLZ',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+ХИЛТИ(?!\pL)/iu',
                'replace' => 'FÜR HILTI',
            ],
            [
                'pattern' => '/(?<!\pL)БЪРЗОПРОБИВНО(?!\pL)/iu',
                'replace' => 'SCHNELLBOHREN',
            ],
            [
                'pattern' => '/(?<!\pL)СПИРАЛОВИДНО(?!\pL)/iu',
                'replace' => 'SPIRAL',
            ],
            [
                'pattern' => '/(?<!\pL)МЕТАЛ(?!\pL)/iu',
                'replace' => 'METALL',
            ],
            [
                'pattern' => '/(?<!\pL)ЛАМАРИНА(?!\pL)/iu',
                'replace' => 'BLECH',
            ],
            [
                'pattern' => '/(?<!\pL)БЕТОН(?!\pL)/iu',
                'replace' => 'BETON',
            ],
            [
                'pattern' => '/(?<!\pL)ДЪРВО(?!\pL)/iu',
                'replace' => 'HOLZ',
            ],
            [
                'pattern' => '/(?<!\pL)ХИЛТИ(?!\pL)/iu',
                'replace' => 'HILTI',
            ],
            [
                'pattern' => '/(?<!\pL)ЦЕНТРОВО(?!\pL)/iu',
                'replace' => 'ZENTRIER',
            ],
            [
                'pattern' => '/(?<!\pL)ЦЕНТЪРНО(?!\pL)/iu',
                'replace' => 'ZENTRIER',
            ],
            [
                'pattern' => '/(?<!\pL)ТВЪРДОСПЛ(?!\pL)/iu',
                'replace' => 'HARTMETALL',
            ],
            [
                'pattern' => '/(?<!\pL)КОБАЛТ(?!\pL)/iu',
                'replace' => 'KOBALT',
            ],
            [
                'pattern' => '/(?<!\pL)ДОРНИК\s+ЦАНГОВ(?!\pL)/iu',
                'replace' => 'SPANNDORN',
            ],
            [
                'pattern' => '/(?<!\pL)ДОРНИК\s+ЗА\s+ФРЕЗА(?!\pL)/iu',
                'replace' => 'DORN FÜR FRÄSER',
            ],
            [
                'pattern' => '/(?<!\pL)ДОРНИК(?!\pL)/iu',
                'replace' => 'DORN',
            ],
            [
                'pattern' => '/(?<!\pL)ФРЕЗА(?!\pL)/iu',
                'replace' => 'FRÄSER',
            ],
            [
                'pattern' => '/(?<!\pL)ПИЛА(?!\pL)/iu',
                'replace' => 'SÄGE',
            ],
            [
                'pattern' => '/(?<!\pL)ДРЪЖКА(?!\pL)/iu',
                'replace' => 'GRIFF',
            ],
            [
                'pattern' => '/(?<!\pL)ДЪРЖАЧ(?!\pL)/iu',
                'replace' => 'HALTER',
            ],
            [
                'pattern' => '/(?<!\pL)МЕТЧИК(?!\pL)/iu',
                'replace' => 'GEWINDSCHNEIDER',
            ],
            [
                'pattern' => '/(?<!\pL)ПЛАШКА(?!\pL)/iu',
                'replace' => 'SCHNEIDEISEN',
            ],
            [
                'pattern' => '/(?<!\pL)ОПАШКА(?!\pL)/iu',
                'replace' => 'SCHAFT',
            ],
            [
                'pattern' => '/(?<!\pL)ДЪЛБЯК(?!\pL)/iu',
                'replace' => 'RÄUMER',
            ],
            [
                'pattern' => '/(?<!\pL)ТЕКСТОЛИТ(?!\pL)/iu',
                'replace' => 'TEXTOLIT',
            ],
            [
                'pattern' => '/(?<!\pL)ФЛАНЕЦ(?!\pL)/iu',
                'replace' => 'FLANSCH',
            ],
            [
                'pattern' => '/(?<!\pL)ПАТРОННИК(?!\pL)/iu',
                'replace' => 'FUTTER',
            ],
            [
                'pattern' => '/(?<!\pL)СЪЕДИН(?!\pL)/iu',
                'replace' => 'VERBINDER',
            ],
            [
                'pattern' => '/(?<!\pL)НОВО(?!\pL)/iu',
                'replace' => 'NEU',
            ],
            [
                'pattern' => '/(?<!\pL)БАНЕР(?!\pL)/iu',
                'replace' => 'BANNER',
            ],
            [
                'pattern' => '/(?<!\pL)ПОДЗАГЛАВИЕ(?!\pL)/iu',
                'replace' => 'UNTERTITEL',
            ],
            [
                'pattern' => '/(?<!\pL)ВИЖ(?!\pL)/iu',
                'replace' => 'ANSEHEN',
            ],
            [
                'pattern' => '/Ф(?=\s*\d)/u',
                'replace' => 'Ø',
            ],
        ],
    ],
];
