<?php

return [
    'provider' => env('TRANSLATION_PROVIDER', 'libretranslate'),
    'base_url' => env('TRANSLATION_BASE_URL', 'http://192.168.1.123:8000'),
    'api_key' => env('TRANSLATION_API_KEY'),
    'timeout' => (int) env('TRANSLATION_TIMEOUT', 20),
    'cache_store' => env('TRANSLATION_CACHE_STORE', 'file'),
    'cache_ttl' => (int) env('TRANSLATION_CACHE_TTL', 2592000),

    'libretranslate' => [
        'base_url' => env('TRANSLATION_BASE_URL', 'http://192.168.1.123:8000'),
        'api_key' => env('TRANSLATION_API_KEY'),
    ],

    'google' => [
        'base_url' => env('GOOGLE_TRANSLATE_BASE_URL', 'https://translation.googleapis.com'),
        'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
        'endpoint' => env('GOOGLE_TRANSLATE_ENDPOINT', '/language/translate/v2'),
    ],

    'glossary' => [
        'en' => [
            ['pattern' => '/(?<!\pL)FREZI(?!\pL)/iu', 'replace' => 'MILLING CUTTERS'],
            ['pattern' => '/(?<!\pL)ФРЕЗИ(?!\pL)/iu', 'replace' => 'MILLING CUTTERS'],
            ['pattern' => '/(?<!\pL)МИЛ(?!\pL)/iu', 'replace' => 'MILL'],
            ['pattern' => '/(?<!\pL)ПЕРА(?!\pL)/iu', 'replace' => 'FLUTES'],
            ['pattern' => '/(?<!\pL)ПЕРО(?!\pL)/iu', 'replace' => 'FLUTE'],
            ['pattern' => '/(?<!\pL)ЦАНГА(?!\pL)/iu', 'replace' => 'COLLET'],
            ['pattern' => '/(?<!\pL)КЕЧЕ(?!\pL)/iu', 'replace' => 'FELT'],
            ['pattern' => '/(?<!\pL)ЗЪРН\.?(?!\pL)/iu', 'replace' => 'GRIT'],
            ['pattern' => '/(?<!\pL)ЕЛ\s+ДВИГАТЕЛ(?!\pL)/iu', 'replace' => 'MOTOR'],
            ['pattern' => '/(?<!\pL)РАЗВЕРТКА(?!\pL)/iu', 'replace' => 'REAMER'],
            ['pattern' => '/(?<!\pL)УДЪЛЖЕН(?!\pL)/iu', 'replace' => 'EXTENDED'],
            ['pattern' => '/(?<!\pL)ПРАВОТОКОВ(?!\pL)/iu', 'replace' => 'DC'],
            ['pattern' => '/(?<!\pL)САЧМЕН(?!\pL)/iu', 'replace' => 'BALL'],
            ['pattern' => '/(?<!\pL)РАЗДВИЖЕН(?!\pL)/iu', 'replace' => 'ADJUSTABLE'],
            ['pattern' => '/(?<!\pL)ДОРНИКОВ(?!\pL)/iu', 'replace' => 'ARBOR'],
            ['pattern' => '/(?<!\pL)ГАЕЧЕН(?!\pL)/iu', 'replace' => 'WRENCH'],
            ['pattern' => '/(?<!\pL)ШЕСТОСТЕН(?!\pL)/iu', 'replace' => 'HEX'],
            ['pattern' => '/(?<!\pL)СЕКТОРЕН(?!\pL)/iu', 'replace' => 'SECTOR'],
            ['pattern' => '/(?<!\pL)СЛЯП(?!\pL)/iu', 'replace' => 'BLIND'],
            ['pattern' => '/(?<!\pL)КЛЕЩИ(?!\pL)/iu', 'replace' => 'PLIERS'],
            ['pattern' => '/(?<!\pL)СЕКАЧКИ(?!\pL)/iu', 'replace' => 'CUTTERS'],
            ['pattern' => '/(?<!\pL)КОМБИН(?!\pL)/iu', 'replace' => 'COMBINATION'],
            ['pattern' => '/(?<!\pL)КОНТАКТОР(?!\pL)/iu', 'replace' => 'CONTACTOR'],
            ['pattern' => '/(?<!\pL)ЗАМБА(?!\pL)/iu', 'replace' => 'PUNCH'],
            ['pattern' => '/(?<!\pL)ЗЪБОЛЕКАРСКИ\s+ИНСТРУМЕНТИ(?!\pL)/iu', 'replace' => 'DENTAL INSTRUMENTS'],
            ['pattern' => '/(?<!\pL)ЗВЕЗДА(?!\pL)/iu', 'replace' => 'STAR'],
            ['pattern' => '/(?<!\pL)ЛУЛА(?!\pL)/iu', 'replace' => 'BOX'],
            ['pattern' => '/(?<!\pL)ТРИСТР(?!\pL)/iu', 'replace' => 'THREE-SIDED'],
            ['pattern' => '/(?<!\pL)ЪГЛОВА(?!\pL)/iu', 'replace' => 'ANGLE'],
            ['pattern' => '/(?<!\pL)КВАДР(?!\pL)/iu', 'replace' => 'SQUARE'],
            ['pattern' => '/(?<!\pL)ТРИАГ(?!\pL)/iu', 'replace' => 'TRIANGULAR'],
            ['pattern' => '/(?<!\pL)СТОЙКА(?!\pL)/iu', 'replace' => 'STAND'],
            ['pattern' => '/(?<!\pL)МАГНИТНА(?!\pL)/iu', 'replace' => 'MAGNETIC'],
            ['pattern' => '/(?<!\pL)МАСА(?!\pL)/iu', 'replace' => 'TABLE'],
            ['pattern' => '/(?<!\pL)МЕНГЕМЕ(?!\pL)/iu', 'replace' => 'VISE'],
            ['pattern' => '/(?<!\pL)ШЛОСЕРСКО(?!\pL)/iu', 'replace' => 'BENCH'],
            ['pattern' => '/(?<!\pL)ТРЪБНО(?!\pL)/iu', 'replace' => 'PIPE'],
            ['pattern' => '/(?<!\pL)ЕЛЕКТР(?!\pL)/iu', 'replace' => 'ELECTRIC'],
            ['pattern' => '/(?<!\pL)НАКАТКИ(?!\pL)/iu', 'replace' => 'KNURLS'],
            ['pattern' => '/(?<!\pL)НАКАТКА(?!\pL)/iu', 'replace' => 'KNURL'],
            ['pattern' => '/(?<!\pL)КРЪСТОСАНИ?(?!\pL)/iu', 'replace' => 'CROSSED'],
            ['pattern' => '/(?<!\pL)НАКЛОН(?:ЕН|ЕНИ)?(?!\pL)/iu', 'replace' => 'ANGLED'],
            ['pattern' => '/(?<!\pL)РОЛКИ(?!\pL)/iu', 'replace' => 'ROLLERS'],
            ['pattern' => '/(?<!\pL)ОТВЕРКА(?!\pL)/iu', 'replace' => 'SCREWDRIVER'],
            ['pattern' => '/(?<!\pL)УДАРНА(?!\pL)/iu', 'replace' => 'IMPACT'],
            ['pattern' => '/(?<!\pL)ГОЛЯМА(?!\pL)/iu', 'replace' => 'LARGE'],
            ['pattern' => '/(?<!\pL)МАЛКА(?!\pL)/iu', 'replace' => 'SMALL'],
            ['pattern' => '/(?<!\pL)ВТУЛКА(?!\pL)/iu', 'replace' => 'BUSHING'],
            ['pattern' => '/(?<!\pL)ПРЕХОДНА(?!\pL)/iu', 'replace' => 'ADAPTER'],
            ['pattern' => '/(?<!\pL)ВИНТ(?!\pL)/iu', 'replace' => 'SCREW'],
            ['pattern' => '/(?<!\pL)КРЪСТАТ(?!\pL)/iu', 'replace' => 'CROSS-HEAD'],
            ['pattern' => '/(?<!\pL)ТРИЪГЪЛНА(?!\pL)/iu', 'replace' => 'TRIANGULAR'],
            ['pattern' => '/(?<!\pL)КВАДРАТНА(?!\pL)/iu', 'replace' => 'SQUARE'],
            ['pattern' => '/(?<!\pL)ПОЛУОБЛА(?!\pL)/iu', 'replace' => 'HALF-ROUND'],
            ['pattern' => '/(?<!\pL)ОБЛА(?!\pL)/iu', 'replace' => 'ROUND'],
            ['pattern' => '/(?<!\pL)ПРЕСОСТАТ(?!\pL)/iu', 'replace' => 'PRESSURE SWITCH'],
            ['pattern' => '/(?<!\pL)РЪКАВИЦИ(?!\pL)/iu', 'replace' => 'GLOVES'],
            ['pattern' => '/(?<!\pL)ЗАВАРКА(?!\pL)/iu', 'replace' => 'WELDING'],
            ['pattern' => '/(?<!\pL)ЧЕРВЕНИ(?!\pL)/iu', 'replace' => 'RED'],
            ['pattern' => '/(?<!\pL)ДЪЛГИ(?!\pL)/iu', 'replace' => 'LONG'],
            ['pattern' => '/(?<!\pL)СИНИ(?!\pL)/iu', 'replace' => 'BLUE'],
            ['pattern' => '/(?<!\pL)ЗЕЛЕНИ(?!\pL)/iu', 'replace' => 'GREEN'],
            ['pattern' => '/(?<!\pL)СИВИ(?!\pL)/iu', 'replace' => 'GRAY'],
            ['pattern' => '/(?<!\pL)КОЖЕНИ(?!\pL)/iu', 'replace' => 'LEATHER'],
            ['pattern' => '/(?<!\pL)ГУМИРАНИ(?!\pL)/iu', 'replace' => 'RUBBERIZED'],
            ['pattern' => '/(?<!\pL)ПРОМАЗАНИ(?!\pL)/iu', 'replace' => 'COATED'],
            ['pattern' => '/(?<!\pL)ЖЪЛТИ(?!\pL)/iu', 'replace' => 'YELLOW'],
            ['pattern' => '/(?<!\pL)СТРУЙНИК(?!\pL)/iu', 'replace' => 'NOZZLE'],
            ['pattern' => '/(?<!\pL)УДЪЛЖИТЕЛ(?!\pL)/iu', 'replace' => 'EXTENSION'],
            ['pattern' => '/(?<!\pL)СЪЕДИНИТЕЛ(?!\pL)/iu', 'replace' => 'CONNECTOR'],
            ['pattern' => '/(?<!\pL)СТЪКЛОРЕЗ(?!\pL)/iu', 'replace' => 'GLASS CUTTER'],
            ['pattern' => '/(?<!\pL)СТЪКЛОТЕКСТОЛИТ(?!\pL)/iu', 'replace' => 'TEXTOLITE'],
            ['pattern' => '/(?<!\pL)ТЕКСТОЛИТ(?!\pL)/iu', 'replace' => 'TEXTOLITE'],
            ['pattern' => '/(?<!\pL)ПЛАНШАЙБА(?!\pL)/iu', 'replace' => 'FACEPLATE'],
            ['pattern' => '/(?<!\pL)ПОДВИЖНА(?!\pL)/iu', 'replace' => 'MOVABLE'],
            ['pattern' => '/(?<!\pL)ЛАМПА(?!\pL)/iu', 'replace' => 'LAMP'],
            ['pattern' => '/(?<!\pL)ПРОЖЕКТОР(?!\pL)/iu', 'replace' => 'FLOODLIGHT'],
            ['pattern' => '/(?<!\pL)ХАЛОГЕН(?!\pL)/iu', 'replace' => 'HALOGEN'],
            ['pattern' => '/(?<!\pL)ПАНТА(?!\pL)/iu', 'replace' => 'HINGE'],
            ['pattern' => '/(?<!\pL)РОЛЕТКА(?!\pL)/iu', 'replace' => 'TAPE MEASURE'],
            ['pattern' => '/(?<!\pL)ФЛАНЕЦ(?!\pL)/iu', 'replace' => 'FLANGE'],
            ['pattern' => '/(?<!\pL)РЕЗБОНАКАТНИ(?!\pL)/iu', 'replace' => 'THREAD-ROLLING'],
            ['pattern' => '/(?<!\pL)РЕЗБОНАРЕЗЕН(?!\pL)/iu', 'replace' => 'TAPPING'],
            ['pattern' => '/(?<!\pL)ЧЕРТИЛКА(?!\pL)/iu', 'replace' => 'SCRIBER'],
            ['pattern' => '/(?<!\pL)ЧЕЛЮСТИ(?!\pL)/iu', 'replace' => 'JAWS'],
            ['pattern' => '/(?<!\pL)ПРУЖИНКИ(?!\pL)/iu', 'replace' => 'SPRINGS'],
            ['pattern' => '/(?<!\pL)КРАЧЕ(?!\pL)/iu', 'replace' => 'FOOT'],
            ['pattern' => '/(?<!\pL)ШАБЪР(?!\pL)/iu', 'replace' => 'SCRAPER'],
            ['pattern' => '/(?<!\pL)ФИБРОСТЪКЛО(?!\pL)/iu', 'replace' => 'FIBERGLASS'],
            ['pattern' => '/(?<!\pL)ЦИФРИ(?!\pL)/iu', 'replace' => 'DIGITS'],
            ['pattern' => '/(?<!\pL)ПРИТИСКАЧИ(?!\pL)/iu', 'replace' => 'CLAMPS'],
            ['pattern' => '/(?<!\pL)ПАЛЦОВА(?!\pL)/iu', 'replace' => 'FINGER'],
            ['pattern' => '/(?<!\pL)ЕЛ\s+ТАБЛО(?!\pL)/iu', 'replace' => 'ELECTRIC PANEL'],
            ['pattern' => '/(?<!\pL)ДЪЛГО(?!\pL)/iu', 'replace' => 'LONG'],
            ['pattern' => '/(?<!\pL)ДЯСНО(?!\pL)/iu', 'replace' => 'RIGHT'],
            ['pattern' => '/(?<!\pL)ТАБЛО(?!\pL)/iu', 'replace' => 'PANEL'],
            ['pattern' => '/(?<!\pL)ПРАВИ(?!\pL)/iu', 'replace' => 'STRAIGHT'],
            ['pattern' => '/(?<!\pL)ОБРАТНИ(?!\pL)/iu', 'replace' => 'REVERSE'],
            ['pattern' => '/(?<!\pL)ЛЯВО(?!\pL)/iu', 'replace' => 'LEFT'],
            ['pattern' => '/(?<!\pL)ЧЕРВЯЧНА(?!\pL)/iu', 'replace' => 'WORM'],
            ['pattern' => '/(?<!\pL)МОДУЛНА(?!\pL)/iu', 'replace' => 'MODULE'],
            ['pattern' => '/(?<!\pL)РАЗЛИЧНИ(?!\pL)/iu', 'replace' => 'VARIOUS'],
            ['pattern' => '/(?<!\pL)INSERT\s+VATR\s+THREAD\s+ST(?!\pL)/iu', 'replace' => 'INSERT INTERNAL THREAD ST'],
            ['pattern' => '/(?<!\pL)INSERT\s+VATR\s+THREAD(?!\pL)/iu', 'replace' => 'INSERT INTERNAL THREAD'],
            ['pattern' => '/(?<!\pL)VATR\s+THREAD\s+ST(?!\pL)/iu', 'replace' => 'INTERNAL THREAD ST'],
            ['pattern' => '/(?<!\pL)VATR\s+THREAD(?!\pL)/iu', 'replace' => 'INTERNAL THREAD'],
            ['pattern' => '/(?<!\pL)INSERT\s+PETAOGALNA(?!\pL)/iu', 'replace' => 'INSERT PENTAGONAL'],
            ['pattern' => '/(?<!\pL)PETAOGALNA(?!\pL)/iu', 'replace' => 'PENTAGONAL'],
            ['pattern' => '/(?<!\pL)SHESTOAGALNA(?!\pL)/iu', 'replace' => 'HEXAGONAL'],
            ['pattern' => '/(?<!\pL)ЗА\s+ВЪТР\.?\s*Р-?БА(?!\pL)/iu', 'replace' => 'FOR INTERNAL THREAD'],
            ['pattern' => '/(?<!\pL)ЗАГОТОВКА\s+КОБАЛТ(?:ОВА)?(?!\pL)/iu', 'replace' => 'COBALT BLANK'],
            ['pattern' => '/(?<!\pL)НОЖ\s+ТВЪРД\s+ЗА\s+КАНАЛ(?!\pL)/iu', 'replace' => 'HARD CUTTER FOR GROOVE'],
            ['pattern' => '/(?<!\pL)ВЪТР\.?\s*Р-?БА(?!\pL)/iu', 'replace' => 'INTERNAL THREAD'],
            ['pattern' => '/(?<!\pL)ВЪНШ\.?\s*Р-?БА(?!\pL)/iu', 'replace' => 'EXTERNAL THREAD'],
            ['pattern' => '/(?<!\pL)КР[ЪА]Г[ЪА]Л\s+КАНАЛ(?!\pL)/iu', 'replace' => 'ROUND GROOVE'],
            ['pattern' => '/(?<!\pL)КАЛИБРИ(?!\pL)/iu', 'replace' => 'CALIBRES'],
            ['pattern' => '/(?<!\pL)КАЛИБЪР(?!\pL)/iu', 'replace' => 'CALIBER'],
            ['pattern' => '/(?<!\pL)ПЕТ[ОЪ]АГАЛНА(?!\pL)/iu', 'replace' => 'PENTAGONAL'],
            ['pattern' => '/(?<!\pL)ШЕСТОАГАЛНА(?!\pL)/iu', 'replace' => 'HEXAGONAL'],
            ['pattern' => '/(?<!\pL)ПЛАШКИ(?!\pL)/iu', 'replace' => 'DIES'],
            ['pattern' => '/(?<!\pL)ПЛАШКА(?!\pL)/iu', 'replace' => 'DIE'],
            ['pattern' => '/(?<!\pL)ЛЯВА(?!\pL)/iu', 'replace' => 'LEFT'],
            ['pattern' => '/(?<!\pL)ЦОЛОВА(?!\pL)/iu', 'replace' => 'INCH'],
            ['pattern' => '/(?<!\pL)ЦОЛА?(?!\pL)/iu', 'replace' => 'INCH'],
            ['pattern' => '/(?<!\pL)НАВИВКИ(?!\pL)/iu', 'replace' => 'THREADS'],
            ['pattern' => '/(?<!\pL)ЗАГОТОВКА(?!\pL)/iu', 'replace' => 'BLANK'],
            ['pattern' => '/(?<!\pL)КОБАЛТОВА(?!\pL)/iu', 'replace' => 'COBALT'],
            ['pattern' => '/(?<!\pL)ТВЪРД(?!\pL)/iu', 'replace' => 'HARD'],
            ['pattern' => '/(?<!\pL)ТВЪРДОСПЛАВНА(?!\pL)/iu', 'replace' => 'CARBIDE'],
            ['pattern' => '/(?<!\pL)РЕЗБОВА(?!\pL)/iu', 'replace' => 'THREADED'],
            ['pattern' => '/(?<!\pL)ЗЕГЕРОВ(?!\pL)/iu', 'replace' => 'CIRCLIP'],
            ['pattern' => '/(?<!\pL)ЗАПОЯЕМА(?!\pL)/iu', 'replace' => 'BRAZED'],
            ['pattern' => '/(?<!\pL)КАНАЛНА(?!\pL)/iu', 'replace' => 'GROOVING'],
            ['pattern' => '/(?<!\pL)НОЖ\s+ЗА\s+ВЪТРЕШЕН\s+КАНАЛ(?!\pL)/iu', 'replace' => 'CUTTER FOR INTERNAL GROOVE'],
            ['pattern' => '/(?<!\pL)НОЖ\s+ЗА\s+ГЛУХ\s+ОТВОР(?!\pL)/iu', 'replace' => 'CUTTER FOR BLIND HOLE'],
            ['pattern' => '/(?<!\pL)ПРОРЕЗЕН\s+НОЖ(?!\pL)/iu', 'replace' => 'SLOTTING CUTTER'],
            ['pattern' => '/(?<!\pL)НОЖ\s+ЧИСТ(?!\pL)/iu', 'replace' => 'FINISHING CUTTER'],
            ['pattern' => '/(?<!\pL)НОЖОВКА\s+ЛИСТ(?!\pL)/iu', 'replace' => 'HACKSAW BLADE'],
            ['pattern' => '/(?<!\pL)НОЖ\s+ГИЛОТИНА(?!\pL)/iu', 'replace' => 'GUILLOTINE CUTTER'],
            ['pattern' => '/(?<!\pL)ШИНА(?!\pL)/iu', 'replace' => 'RAIL'],
            ['pattern' => '/(?<!\pL)ОТРЕЗНА(?!\pL)/iu', 'replace' => 'CUT-OFF'],
            ['pattern' => '/(?<!\pL)ОТРЕЗ(?!\pL)/iu', 'replace' => 'CUT-OFF'],
            ['pattern' => '/(?<!\pL)ЛЯВ(?!\pL)/iu', 'replace' => 'LEFT'],
            ['pattern' => '/(?<!\pL)ДЕСЕН(?!\pL)/iu', 'replace' => 'RIGHT'],
            ['pattern' => '/(?<!\pL)КЕРАМИЧНА(?!\pL)/iu', 'replace' => 'CERAMIC'],
            ['pattern' => '/(?<!\pL)ГЛУХ(?!\pL)/iu', 'replace' => 'BLIND'],
            ['pattern' => '/(?<!\pL)ОТВОР(?!\pL)/iu', 'replace' => 'HOLE'],
            ['pattern' => '/(?<!\pL)ВЪТРЕШЕН(?!\pL)/iu', 'replace' => 'INTERNAL'],
            ['pattern' => '/(?<!\pL)ВЪНШНО(?!\pL)/iu', 'replace' => 'EXTERNAL'],
            ['pattern' => '/(?<!\pL)КАНАЛ(?!\pL)/iu', 'replace' => 'GROOVE'],
            ['pattern' => '/(?<!\pL)ПРОРЕЗЕН(?!\pL)/iu', 'replace' => 'SLOTTING'],
            ['pattern' => '/(?<!\pL)БОРЩАНГА(?!\pL)/iu', 'replace' => 'BORING BAR'],
            ['pattern' => '/(?<!\pL)ЧИСТ(?!\pL)/iu', 'replace' => 'FINISHING'],
            ['pattern' => '/(?<!\pL)НОЖОВКА(?!\pL)/iu', 'replace' => 'HACKSAW'],
            ['pattern' => '/(?<!\pL)ГИЛОТИНА(?!\pL)/iu', 'replace' => 'GUILLOTINE'],
            ['pattern' => '/(?<!\pL)ГРАДУСА(?!\pL)/iu', 'replace' => 'DEGREES'],
            ['pattern' => '/(?<!\pL)ГРАДУС(?!\pL)/iu', 'replace' => 'DEG'],
            ['pattern' => '/(?<!\pL)РЕЗБОВИ(?!\pL)/iu', 'replace' => 'THREADED'],
            ['pattern' => '/(?<!\pL)РЕЗБА(?!\pL)/iu', 'replace' => 'THREAD'],
            ['pattern' => '/(?<!\pL)РЕЗБ(?!\pL)/iu', 'replace' => 'THREAD'],
            ['pattern' => '/(?<!\pL)ГРИВНА(?!\pL)/iu', 'replace' => 'BRACELET'],
            ['pattern' => '/(?<!\pL)ПРОБКА(?!\pL)/iu', 'replace' => 'PROBE'],
            ['pattern' => '/(?<!\pL)ГЛАДАК(?!\pL)/iu', 'replace' => 'SMOOTH'],
            ['pattern' => '/(?<!\pL)КЕЧАНА\s+ШАЙБА(?!\pL)/iu', 'replace' => 'KECHANA PUCK'],
            ['pattern' => '/(?<!\pL)ДИАМАНТЕН\s+ИЗРАВНИТЕЛ\s+ЦО(?!\pL)/iu', 'replace' => 'DIAMOND DRESSER TSO'],
            ['pattern' => '/(?<!\pL)ДИАМАНТЕН\s+ИЗРАВНИТЕЛ(?!\pL)/iu', 'replace' => 'DIAMOND DRESSER'],
            ['pattern' => '/(?<!\pL)ДИАМАНТЕН\s+ДИСК\s+ОТРЕЗЕН(?!\pL)/iu', 'replace' => 'DIAMOND CUT-OFF DISC'],
            ['pattern' => '/(?<!\pL)ДИАМАНТЕНА\s+ТАРЕЛКА(?!\pL)/iu', 'replace' => 'DIAMOND PLATE'],
            ['pattern' => '/(?<!\pL)ДИАМАНТЕНА\s+ЧАША(?!\pL)/iu', 'replace' => 'DIAMOND CUP'],
            ['pattern' => '/(?<!\pL)ДИАМАНТЕН\s+АБРАЗИВ(?!\pL)/iu', 'replace' => 'DIAMOND ABRASIVE'],
            ['pattern' => '/(?<!\pL)ДИСК\s+ОТРЕЗЕН(?!\pL)/iu', 'replace' => 'CUT-OFF DISC'],
            ['pattern' => '/(?<!\pL)ДИСК\s+ЗА\s+ШЛАЙФ(?!\pL)/iu', 'replace' => 'DISC FOR GRINDER'],
            ['pattern' => '/(?<!\pL)ШКУРКА\s+НА\s+ЛИСТ(?!\pL)/iu', 'replace' => 'ABRASIVE SHEET'],
            ['pattern' => '/(?<!\pL)ШКУРКА\s+-\s+РОЛО(?!\pL)/iu', 'replace' => 'ABRASIVE ROLL'],
            ['pattern' => '/(?<!\pL)ШКУРКА\s+КРАГЛА(?!\pL)/iu', 'replace' => 'ABRASIVE DISC'],
            ['pattern' => '/(?<!\pL)ШКУРКА\s+НА\s+МЕТАР(?!\pL)/iu', 'replace' => 'ABRASIVE PAPER PER METER'],
            ['pattern' => '/(?<!\pL)ДИСК(?!\pL)/iu', 'replace' => 'DISC'],
            ['pattern' => '/(?<!\pL)ШАЙБА(?!\pL)/iu', 'replace' => 'DISC'],
            ['pattern' => '/(?<!\pL)ШКУРКА(?!\pL)/iu', 'replace' => 'ABRASIVE PAPER'],
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
            ['pattern' => '/(?<!\pL)МЕТЧИК\s+МАШ(?:ИНЕН)?(?!\pL)/iu', 'replace' => 'MACHINE TAP'],
            ['pattern' => '/(?<!\pL)МЕТЧИК\s+РЪЧЕН(?!\pL)/iu', 'replace' => 'HAND TAP'],
            ['pattern' => '/(?<!\pL)МЕТЧИЦИ(?!\pL)/iu', 'replace' => 'TAPS'],
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
            ['pattern' => '/(?<!\pL)ПЛОСЪК(?!\pL)/iu', 'replace' => 'FLAT'],
            ['pattern' => '/(?<!\pL)ПЛОСАК(?!\pL)/iu', 'replace' => 'FLAT'],
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
            ['pattern' => '/(?<!\pL)ЧАШКА(?!\pL)/iu', 'replace' => 'CUP'],
            ['pattern' => '/(?<!\pL)ЕБ(?!\pL)/iu', 'replace' => 'EB'],
            ['pattern' => '/(?<!\pL)ДИАМАНТЕНА(?!\pL)/iu', 'replace' => 'DIAMOND'],
            ['pattern' => '/(?<!\pL)МЕТЧИК(?!\pL)/iu', 'replace' => 'TAP'],
        ],
        'de' => [],
    ],

    'rules' => [
        'en' => [
            [
                'pattern' => '/(?<!\pL)FREZI(?!\pL)/iu',
                'replace' => 'MILLING CUTTERS',
            ],
            [
                'pattern' => '/(?<!\pL)ФРЕЗИ(?!\pL)/iu',
                'replace' => 'MILLING CUTTERS',
            ],
            [
                'pattern' => '/(?<!\pL)МИЛ(?!\pL)/iu',
                'replace' => 'MILL',
            ],
            [
                'pattern' => '/(?<!\pL)ПЕРА(?!\pL)/iu',
                'replace' => 'FLUTES',
            ],
            [
                'pattern' => '/(?<!\pL)ПЕРО(?!\pL)/iu',
                'replace' => 'FLUTE',
            ],
            [
                'pattern' => '/(?<!\pL)ЦАНГА(?!\pL)/iu',
                'replace' => 'COLLET',
            ],
            [
                'pattern' => '/(?<!\pL)КЕЧЕ(?!\pL)/iu',
                'replace' => 'FELT',
            ],
            [
                'pattern' => '/(?<!\pL)ЗЪРН\.?(?!\pL)/iu',
                'replace' => 'GRIT',
            ],
            [
                'pattern' => '/(?<!\pL)ЕЛ\s+ДВИГАТЕЛ(?!\pL)/iu',
                'replace' => 'MOTOR',
            ],
            [
                'pattern' => '/(?<!\pL)РАЗВЕРТКА(?!\pL)/iu',
                'replace' => 'REAMER',
            ],
            [
                'pattern' => '/(?<!\pL)УДЪЛЖЕН(?!\pL)/iu',
                'replace' => 'EXTENDED',
            ],
            [
                'pattern' => '/(?<!\pL)ПРАВОТОКОВ(?!\pL)/iu',
                'replace' => 'DC',
            ],
            [
                'pattern' => '/(?<!\pL)САЧМЕН(?!\pL)/iu',
                'replace' => 'BALL',
            ],
            [
                'pattern' => '/(?<!\pL)РАЗДВИЖЕН(?!\pL)/iu',
                'replace' => 'ADJUSTABLE',
            ],
            [
                'pattern' => '/(?<!\pL)ДОРНИКОВ(?!\pL)/iu',
                'replace' => 'ARBOR',
            ],
            [
                'pattern' => '/(?<!\pL)ГАЕЧЕН(?!\pL)/iu',
                'replace' => 'WRENCH',
            ],
            [
                'pattern' => '/(?<!\pL)ШЕСТОСТЕН(?!\pL)/iu',
                'replace' => 'HEX',
            ],
            [
                'pattern' => '/(?<!\pL)СЕКТОРЕН(?!\pL)/iu',
                'replace' => 'SECTOR',
            ],
            [
                'pattern' => '/(?<!\pL)СЛЯП(?!\pL)/iu',
                'replace' => 'BLIND',
            ],
            [
                'pattern' => '/(?<!\pL)КЛЕЩИ(?!\pL)/iu',
                'replace' => 'PLIERS',
            ],
            [
                'pattern' => '/(?<!\pL)СЕКАЧКИ(?!\pL)/iu',
                'replace' => 'CUTTERS',
            ],
            [
                'pattern' => '/(?<!\pL)КОМБИН(?!\pL)/iu',
                'replace' => 'COMBINATION',
            ],
            [
                'pattern' => '/(?<!\pL)КОНТАКТОР(?!\pL)/iu',
                'replace' => 'CONTACTOR',
            ],
            [
                'pattern' => '/(?<!\pL)ЗАМБА(?!\pL)/iu',
                'replace' => 'PUNCH',
            ],
            [
                'pattern' => '/(?<!\pL)ЗЪБОЛЕКАРСКИ\s+ИНСТРУМЕНТИ(?!\pL)/iu',
                'replace' => 'DENTAL INSTRUMENTS',
            ],
            [
                'pattern' => '/(?<!\pL)ЗВЕЗДА(?!\pL)/iu',
                'replace' => 'STAR',
            ],
            [
                'pattern' => '/(?<!\pL)ЛУЛА(?!\pL)/iu',
                'replace' => 'BOX',
            ],
            [
                'pattern' => '/(?<!\pL)ТРИСТР(?!\pL)/iu',
                'replace' => 'THREE-SIDED',
            ],
            [
                'pattern' => '/(?<!\pL)ЪГЛОВА(?!\pL)/iu',
                'replace' => 'ANGLE',
            ],
            [
                'pattern' => '/(?<!\pL)КВАДР(?!\pL)/iu',
                'replace' => 'SQUARE',
            ],
            [
                'pattern' => '/(?<!\pL)ТРИАГ(?!\pL)/iu',
                'replace' => 'TRIANGULAR',
            ],
            [
                'pattern' => '/(?<!\pL)СТОЙКА(?!\pL)/iu',
                'replace' => 'STAND',
            ],
            [
                'pattern' => '/(?<!\pL)МАГНИТНА(?!\pL)/iu',
                'replace' => 'MAGNETIC',
            ],
            [
                'pattern' => '/(?<!\pL)МАСА(?!\pL)/iu',
                'replace' => 'TABLE',
            ],
            [
                'pattern' => '/(?<!\pL)МЕНГЕМЕ(?!\pL)/iu',
                'replace' => 'VISE',
            ],
            [
                'pattern' => '/(?<!\pL)ШЛОСЕРСКО(?!\pL)/iu',
                'replace' => 'BENCH',
            ],
            [
                'pattern' => '/(?<!\pL)ТРЪБНО(?!\pL)/iu',
                'replace' => 'PIPE',
            ],
            [
                'pattern' => '/(?<!\pL)ЕЛЕКТР(?!\pL)/iu',
                'replace' => 'ELECTRIC',
            ],
            [
                'pattern' => '/(?<!\pL)НАКАТКИ(?!\pL)/iu',
                'replace' => 'KNURLS',
            ],
            [
                'pattern' => '/(?<!\pL)НАКАТКА(?!\pL)/iu',
                'replace' => 'KNURL',
            ],
            [
                'pattern' => '/(?<!\pL)КРЪСТОСАНИ?(?!\pL)/iu',
                'replace' => 'CROSSED',
            ],
            [
                'pattern' => '/(?<!\pL)НАКЛОН(?:ЕН|ЕНИ)?(?!\pL)/iu',
                'replace' => 'ANGLED',
            ],
            [
                'pattern' => '/(?<!\pL)РОЛКИ(?!\pL)/iu',
                'replace' => 'ROLLERS',
            ],
            [
                'pattern' => '/(?<!\pL)ОТВЕРКА(?!\pL)/iu',
                'replace' => 'SCREWDRIVER',
            ],
            [
                'pattern' => '/(?<!\pL)УДАРНА(?!\pL)/iu',
                'replace' => 'IMPACT',
            ],
            [
                'pattern' => '/(?<!\pL)ГОЛЯМА(?!\pL)/iu',
                'replace' => 'LARGE',
            ],
            [
                'pattern' => '/(?<!\pL)МАЛКА(?!\pL)/iu',
                'replace' => 'SMALL',
            ],
            [
                'pattern' => '/(?<!\pL)ВТУЛКА(?!\pL)/iu',
                'replace' => 'BUSHING',
            ],
            [
                'pattern' => '/(?<!\pL)ПРЕХОДНА(?!\pL)/iu',
                'replace' => 'ADAPTER',
            ],
            [
                'pattern' => '/(?<!\pL)ВИНТ(?!\pL)/iu',
                'replace' => 'SCREW',
            ],
            [
                'pattern' => '/(?<!\pL)КРЪСТАТ(?!\pL)/iu',
                'replace' => 'CROSS-HEAD',
            ],
            [
                'pattern' => '/(?<!\pL)ТРИЪГЪЛНА(?!\pL)/iu',
                'replace' => 'TRIANGULAR',
            ],
            [
                'pattern' => '/(?<!\pL)КВАДРАТНА(?!\pL)/iu',
                'replace' => 'SQUARE',
            ],
            [
                'pattern' => '/(?<!\pL)ПОЛУОБЛА(?!\pL)/iu',
                'replace' => 'HALF-ROUND',
            ],
            [
                'pattern' => '/(?<!\pL)ОБЛА(?!\pL)/iu',
                'replace' => 'ROUND',
            ],
            [
                'pattern' => '/(?<!\pL)ПРЕСОСТАТ(?!\pL)/iu',
                'replace' => 'PRESSURE SWITCH',
            ],
            [
                'pattern' => '/(?<!\pL)РЪКАВИЦИ(?!\pL)/iu',
                'replace' => 'GLOVES',
            ],
            [
                'pattern' => '/(?<!\pL)ЗАВАРКА(?!\pL)/iu',
                'replace' => 'WELDING',
            ],
            [
                'pattern' => '/(?<!\pL)ЧЕРВЕНИ(?!\pL)/iu',
                'replace' => 'RED',
            ],
            [
                'pattern' => '/(?<!\pL)ДЪЛГИ(?!\pL)/iu',
                'replace' => 'LONG',
            ],
            [
                'pattern' => '/(?<!\pL)СИНИ(?!\pL)/iu',
                'replace' => 'BLUE',
            ],
            [
                'pattern' => '/(?<!\pL)ЗЕЛЕНИ(?!\pL)/iu',
                'replace' => 'GREEN',
            ],
            [
                'pattern' => '/(?<!\pL)СИВИ(?!\pL)/iu',
                'replace' => 'GRAY',
            ],
            [
                'pattern' => '/(?<!\pL)КОЖЕНИ(?!\pL)/iu',
                'replace' => 'LEATHER',
            ],
            [
                'pattern' => '/(?<!\pL)ГУМИРАНИ(?!\pL)/iu',
                'replace' => 'RUBBERIZED',
            ],
            [
                'pattern' => '/(?<!\pL)ПРОМАЗАНИ(?!\pL)/iu',
                'replace' => 'COATED',
            ],
            [
                'pattern' => '/(?<!\pL)ЖЪЛТИ(?!\pL)/iu',
                'replace' => 'YELLOW',
            ],
            [
                'pattern' => '/(?<!\pL)СТРУЙНИК(?!\pL)/iu',
                'replace' => 'NOZZLE',
            ],
            [
                'pattern' => '/(?<!\pL)УДЪЛЖИТЕЛ(?!\pL)/iu',
                'replace' => 'EXTENSION',
            ],
            [
                'pattern' => '/(?<!\pL)СЪЕДИНИТЕЛ(?!\pL)/iu',
                'replace' => 'CONNECTOR',
            ],
            [
                'pattern' => '/(?<!\pL)СТЪКЛОРЕЗ(?!\pL)/iu',
                'replace' => 'GLASS CUTTER',
            ],
            [
                'pattern' => '/(?<!\pL)СТЪКЛОТЕКСТОЛИТ(?!\pL)/iu',
                'replace' => 'TEXTOLITE',
            ],
            [
                'pattern' => '/(?<!\pL)ТЕКСТОЛИТ(?!\pL)/iu',
                'replace' => 'TEXTOLITE',
            ],
            [
                'pattern' => '/(?<!\pL)ПЛАНШАЙБА(?!\pL)/iu',
                'replace' => 'FACEPLATE',
            ],
            [
                'pattern' => '/(?<!\pL)ПОДВИЖНА(?!\pL)/iu',
                'replace' => 'MOVABLE',
            ],
            [
                'pattern' => '/(?<!\pL)ЛАМПА(?!\pL)/iu',
                'replace' => 'LAMP',
            ],
            [
                'pattern' => '/(?<!\pL)ПРОЖЕКТОР(?!\pL)/iu',
                'replace' => 'FLOODLIGHT',
            ],
            [
                'pattern' => '/(?<!\pL)ХАЛОГЕН(?!\pL)/iu',
                'replace' => 'HALOGEN',
            ],
            [
                'pattern' => '/(?<!\pL)ПАНТА(?!\pL)/iu',
                'replace' => 'HINGE',
            ],
            [
                'pattern' => '/(?<!\pL)РОЛЕТКА(?!\pL)/iu',
                'replace' => 'TAPE MEASURE',
            ],
            [
                'pattern' => '/(?<!\pL)ФЛАНЕЦ(?!\pL)/iu',
                'replace' => 'FLANGE',
            ],
            [
                'pattern' => '/(?<!\pL)РЕЗБОНАКАТНИ(?!\pL)/iu',
                'replace' => 'THREAD-ROLLING',
            ],
            [
                'pattern' => '/(?<!\pL)РЕЗБОНАРЕЗЕН(?!\pL)/iu',
                'replace' => 'TAPPING',
            ],
            [
                'pattern' => '/(?<!\pL)ЧЕРТИЛКА(?!\pL)/iu',
                'replace' => 'SCRIBER',
            ],
            [
                'pattern' => '/(?<!\pL)ЧЕЛЮСТИ(?!\pL)/iu',
                'replace' => 'JAWS',
            ],
            [
                'pattern' => '/(?<!\pL)ПРУЖИНКИ(?!\pL)/iu',
                'replace' => 'SPRINGS',
            ],
            [
                'pattern' => '/(?<!\pL)КРАЧЕ(?!\pL)/iu',
                'replace' => 'FOOT',
            ],
            [
                'pattern' => '/(?<!\pL)ШАБЪР(?!\pL)/iu',
                'replace' => 'SCRAPER',
            ],
            [
                'pattern' => '/(?<!\pL)ФИБРОСТЪКЛО(?!\pL)/iu',
                'replace' => 'FIBERGLASS',
            ],
            [
                'pattern' => '/(?<!\pL)ЦИФРИ(?!\pL)/iu',
                'replace' => 'DIGITS',
            ],
            [
                'pattern' => '/(?<!\pL)ПРИТИСКАЧИ(?!\pL)/iu',
                'replace' => 'CLAMPS',
            ],
            [
                'pattern' => '/(?<!\pL)ПАЛЦОВА(?!\pL)/iu',
                'replace' => 'FINGER',
            ],
            [
                'pattern' => '/(?<!\pL)ЕЛ\s+ТАБЛО(?!\pL)/iu',
                'replace' => 'ELECTRIC PANEL',
            ],
            [
                'pattern' => '/(?<!\pL)ДЪЛГО(?!\pL)/iu',
                'replace' => 'LONG',
            ],
            [
                'pattern' => '/(?<!\pL)ДЯСНО(?!\pL)/iu',
                'replace' => 'RIGHT',
            ],
            [
                'pattern' => '/(?<!\pL)ТАБЛО(?!\pL)/iu',
                'replace' => 'PANEL',
            ],
            [
                'pattern' => '/(?<!\pL)ПРАВИ(?!\pL)/iu',
                'replace' => 'STRAIGHT',
            ],
            [
                'pattern' => '/(?<!\pL)ОБРАТНИ(?!\pL)/iu',
                'replace' => 'REVERSE',
            ],
            [
                'pattern' => '/(?<!\pL)ЛЯВО(?!\pL)/iu',
                'replace' => 'LEFT',
            ],
            [
                'pattern' => '/(?<!\pL)ЧЕРВЯЧНА(?!\pL)/iu',
                'replace' => 'WORM',
            ],
            [
                'pattern' => '/(?<!\pL)МОДУЛНА(?!\pL)/iu',
                'replace' => 'MODULE',
            ],
            [
                'pattern' => '/(?<!\pL)РАЗЛИЧНИ(?!\pL)/iu',
                'replace' => 'VARIOUS',
            ],
            [
                'pattern' => '/(?<!\pL)INSERT\s+VATR\s+THREAD\s+ST(?!\pL)/iu',
                'replace' => 'INSERT INTERNAL THREAD ST',
            ],
            [
                'pattern' => '/(?<!\pL)INSERT\s+VATR\s+THREAD(?!\pL)/iu',
                'replace' => 'INSERT INTERNAL THREAD',
            ],
            [
                'pattern' => '/(?<!\pL)VATR\s+THREAD\s+ST(?!\pL)/iu',
                'replace' => 'INTERNAL THREAD ST',
            ],
            [
                'pattern' => '/(?<!\pL)VATR\s+THREAD(?!\pL)/iu',
                'replace' => 'INTERNAL THREAD',
            ],
            [
                'pattern' => '/(?<!\pL)INSERT\s+PETAOGALNA(?!\pL)/iu',
                'replace' => 'INSERT PENTAGONAL',
            ],
            [
                'pattern' => '/(?<!\pL)PETAOGALNA(?!\pL)/iu',
                'replace' => 'PENTAGONAL',
            ],
            [
                'pattern' => '/(?<!\pL)SHESTOAGALNA(?!\pL)/iu',
                'replace' => 'HEXAGONAL',
            ],
            [
                'pattern' => '/(?<!\pL)ЗА\s+ВЪТР\.?\s*Р-?БА(?!\pL)/iu',
                'replace' => 'FOR INTERNAL THREAD',
            ],
            [
                'pattern' => '/(?<!\pL)ЗАГОТОВКА\s+КОБАЛТ(?:ОВА)?(?!\pL)/iu',
                'replace' => 'COBALT BLANK',
            ],
            [
                'pattern' => '/(?<!\pL)НОЖ\s+ТВЪРД\s+ЗА\s+КАНАЛ(?!\pL)/iu',
                'replace' => 'HARD CUTTER FOR GROOVE',
            ],
            [
                'pattern' => '/(?<!\pL)ВЪТР\.?\s*Р-?БА(?!\pL)/iu',
                'replace' => 'INTERNAL THREAD',
            ],
            [
                'pattern' => '/(?<!\pL)ВЪНШ\.?\s*Р-?БА(?!\pL)/iu',
                'replace' => 'EXTERNAL THREAD',
            ],
            [
                'pattern' => '/(?<!\pL)КР[ЪА]Г[ЪА]Л\s+КАНАЛ(?!\pL)/iu',
                'replace' => 'ROUND GROOVE',
            ],
            [
                'pattern' => '/(?<!\pL)КАЛИБРИ(?!\pL)/iu',
                'replace' => 'CALIBRES',
            ],
            [
                'pattern' => '/(?<!\pL)КАЛИБЪР(?!\pL)/iu',
                'replace' => 'CALIBER',
            ],
            [
                'pattern' => '/(?<!\pL)ПЕТ[ОЪ]АГАЛНА(?!\pL)/iu',
                'replace' => 'PENTAGONAL',
            ],
            [
                'pattern' => '/(?<!\pL)ШЕСТОАГАЛНА(?!\pL)/iu',
                'replace' => 'HEXAGONAL',
            ],
            [
                'pattern' => '/(?<!\pL)ПЛАШКИ(?!\pL)/iu',
                'replace' => 'DIES',
            ],
            [
                'pattern' => '/(?<!\pL)ПЛАШКА(?!\pL)/iu',
                'replace' => 'DIE',
            ],
            [
                'pattern' => '/(?<!\pL)ЛЯВА(?!\pL)/iu',
                'replace' => 'LEFT',
            ],
            [
                'pattern' => '/(?<!\pL)ЦОЛОВА(?!\pL)/iu',
                'replace' => 'INCH',
            ],
            [
                'pattern' => '/(?<!\pL)ЦОЛА?(?!\pL)/iu',
                'replace' => 'INCH',
            ],
            [
                'pattern' => '/(?<!\pL)НАВИВКИ(?!\pL)/iu',
                'replace' => 'THREADS',
            ],
            [
                'pattern' => '/(?<!\pL)ЗАГОТОВКА(?!\pL)/iu',
                'replace' => 'BLANK',
            ],
            [
                'pattern' => '/(?<!\pL)КОБАЛТОВА(?!\pL)/iu',
                'replace' => 'COBALT',
            ],
            [
                'pattern' => '/(?<!\pL)ТВЪРД(?!\pL)/iu',
                'replace' => 'HARD',
            ],
            [
                'pattern' => '/(?<!\pL)ТВЪРДОСПЛАВНА(?!\pL)/iu',
                'replace' => 'CARBIDE',
            ],
            [
                'pattern' => '/(?<!\pL)РЕЗБОВА(?!\pL)/iu',
                'replace' => 'THREADED',
            ],
            [
                'pattern' => '/(?<!\pL)ЗЕГЕРОВ(?!\pL)/iu',
                'replace' => 'CIRCLIP',
            ],
            [
                'pattern' => '/(?<!\pL)ЗАПОЯЕМА(?!\pL)/iu',
                'replace' => 'BRAZED',
            ],
            [
                'pattern' => '/(?<!\pL)КАНАЛНА(?!\pL)/iu',
                'replace' => 'GROOVING',
            ],
            [
                'pattern' => '/(?<!\pL)НОЖ\s+ЗА\s+ВЪТРЕШЕН\s+КАНАЛ(?!\pL)/iu',
                'replace' => 'CUTTER FOR INTERNAL GROOVE',
            ],
            [
                'pattern' => '/(?<!\pL)НОЖ\s+ЗА\s+ГЛУХ\s+ОТВОР(?!\pL)/iu',
                'replace' => 'CUTTER FOR BLIND HOLE',
            ],
            [
                'pattern' => '/(?<!\pL)ПРОРЕЗЕН\s+НОЖ(?!\pL)/iu',
                'replace' => 'SLOTTING CUTTER',
            ],
            [
                'pattern' => '/(?<!\pL)НОЖ\s+ЧИСТ(?!\pL)/iu',
                'replace' => 'FINISHING CUTTER',
            ],
            [
                'pattern' => '/(?<!\pL)НОЖОВКА\s+ЛИСТ(?!\pL)/iu',
                'replace' => 'HACKSAW BLADE',
            ],
            [
                'pattern' => '/(?<!\pL)НОЖ\s+ГИЛОТИНА(?!\pL)/iu',
                'replace' => 'GUILLOTINE CUTTER',
            ],
            [
                'pattern' => '/(?<!\pL)ШИНА(?!\pL)/iu',
                'replace' => 'RAIL',
            ],
            [
                'pattern' => '/(?<!\pL)ОТРЕЗНА(?!\pL)/iu',
                'replace' => 'CUT-OFF',
            ],
            [
                'pattern' => '/(?<!\pL)ОТРЕЗ(?!\pL)/iu',
                'replace' => 'CUT-OFF',
            ],
            [
                'pattern' => '/(?<!\pL)ЛЯВ(?!\pL)/iu',
                'replace' => 'LEFT',
            ],
            [
                'pattern' => '/(?<!\pL)ДЕСЕН(?!\pL)/iu',
                'replace' => 'RIGHT',
            ],
            [
                'pattern' => '/(?<!\pL)КЕРАМИЧНА(?!\pL)/iu',
                'replace' => 'CERAMIC',
            ],
            [
                'pattern' => '/(?<!\pL)ГЛУХ(?!\pL)/iu',
                'replace' => 'BLIND',
            ],
            [
                'pattern' => '/(?<!\pL)ОТВОР(?!\pL)/iu',
                'replace' => 'HOLE',
            ],
            [
                'pattern' => '/(?<!\pL)ВЪТРЕШЕН(?!\pL)/iu',
                'replace' => 'INTERNAL',
            ],
            [
                'pattern' => '/(?<!\pL)ВЪНШНО(?!\pL)/iu',
                'replace' => 'EXTERNAL',
            ],
            [
                'pattern' => '/(?<!\pL)КАНАЛ(?!\pL)/iu',
                'replace' => 'GROOVE',
            ],
            [
                'pattern' => '/(?<!\pL)ПРОРЕЗЕН(?!\pL)/iu',
                'replace' => 'SLOTTING',
            ],
            [
                'pattern' => '/(?<!\pL)БОРЩАНГА(?!\pL)/iu',
                'replace' => 'BORING BAR',
            ],
            [
                'pattern' => '/(?<!\pL)ЧИСТ(?!\pL)/iu',
                'replace' => 'FINISHING',
            ],
            [
                'pattern' => '/(?<!\pL)НОЖОВКА(?!\pL)/iu',
                'replace' => 'HACKSAW',
            ],
            [
                'pattern' => '/(?<!\pL)ГИЛОТИНА(?!\pL)/iu',
                'replace' => 'GUILLOTINE',
            ],
            [
                'pattern' => '/(?<!\pL)ГРАДУСА(?!\pL)/iu',
                'replace' => 'DEGREES',
            ],
            [
                'pattern' => '/(?<!\pL)ГРАДУС(?!\pL)/iu',
                'replace' => 'DEG',
            ],
            [
                'pattern' => '/(?<!\pL)РЕЗБОВИ(?!\pL)/iu',
                'replace' => 'THREADED',
            ],
            [
                'pattern' => '/(?<!\pL)РЕЗБА(?!\pL)/iu',
                'replace' => 'THREAD',
            ],
            [
                'pattern' => '/(?<!\pL)РЕЗБ(?!\pL)/iu',
                'replace' => 'THREAD',
            ],
            [
                'pattern' => '/(?<!\pL)ГРИВНА(?!\pL)/iu',
                'replace' => 'BRACELET',
            ],
            [
                'pattern' => '/(?<!\pL)ПРОБКА(?!\pL)/iu',
                'replace' => 'PROBE',
            ],
            [
                'pattern' => '/(?<!\pL)ГЛАДАК(?!\pL)/iu',
                'replace' => 'SMOOTH',
            ],
            [
                'pattern' => '/(?<!\pL)КЕЧАНА\s+ШАЙБА(?!\pL)/iu',
                'replace' => 'KECHANA PUCK',
            ],
            [
                'pattern' => '/(?<!\pL)ДИАМАНТЕН\s+ИЗРАВНИТЕЛ\s+ЦО(?!\pL)/iu',
                'replace' => 'DIAMOND DRESSER TSO',
            ],
            [
                'pattern' => '/(?<!\pL)ДИАМАНТЕН\s+ИЗРАВНИТЕЛ(?!\pL)/iu',
                'replace' => 'DIAMOND DRESSER',
            ],
            [
                'pattern' => '/(?<!\pL)ДИАМАНТЕН\s+ДИСК\s+ОТРЕЗЕН(?!\pL)/iu',
                'replace' => 'DIAMOND CUT-OFF DISC',
            ],
            [
                'pattern' => '/(?<!\pL)ДИАМАНТЕНА\s+ТАРЕЛКА(?!\pL)/iu',
                'replace' => 'DIAMOND PLATE',
            ],
            [
                'pattern' => '/(?<!\pL)ДИАМАНТЕНА\s+ЧАША(?!\pL)/iu',
                'replace' => 'DIAMOND CUP',
            ],
            [
                'pattern' => '/(?<!\pL)ДИАМАНТЕН\s+АБРАЗИВ(?!\pL)/iu',
                'replace' => 'DIAMOND ABRASIVE',
            ],
            [
                'pattern' => '/(?<!\pL)ДИСК\s+ОТРЕЗЕН(?!\pL)/iu',
                'replace' => 'CUT-OFF DISC',
            ],
            [
                'pattern' => '/(?<!\pL)ДИСК\s+ЗА\s+ШЛАЙФ(?!\pL)/iu',
                'replace' => 'DISC FOR GRINDER',
            ],
            [
                'pattern' => '/(?<!\pL)ШКУРКА\s+НА\s+ЛИСТ(?!\pL)/iu',
                'replace' => 'ABRASIVE SHEET',
            ],
            [
                'pattern' => '/(?<!\pL)ШКУРКА\s+-\s+РОЛО(?!\pL)/iu',
                'replace' => 'ABRASIVE ROLL',
            ],
            [
                'pattern' => '/(?<!\pL)ШКУРКА\s+КРАГЛА(?!\pL)/iu',
                'replace' => 'ABRASIVE DISC',
            ],
            [
                'pattern' => '/(?<!\pL)ШКУРКА\s+НА\s+МЕТАР(?!\pL)/iu',
                'replace' => 'ABRASIVE PAPER PER METER',
            ],
            [
                'pattern' => '/(?<!\pL)ДИСК(?!\pL)/iu',
                'replace' => 'DISC',
            ],
            [
                'pattern' => '/(?<!\pL)ШАЙБА(?!\pL)/iu',
                'replace' => 'DISC',
            ],
            [
                'pattern' => '/(?<!\pL)ШКУРКА(?!\pL)/iu',
                'replace' => 'ABRASIVE PAPER',
            ],
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
                'pattern' => '/(?<!\pL)МЕТЧИК\s+МАШ(?:ИНЕН)?(?!\pL)/iu',
                'replace' => 'MACHINE TAP',
            ],
            [
                'pattern' => '/(?<!\pL)МЕТЧИК\s+РЪЧЕН(?!\pL)/iu',
                'replace' => 'HAND TAP',
            ],
            [
                'pattern' => '/(?<!\pL)МЕТЧИЦИ(?!\pL)/iu',
                'replace' => 'TAPS',
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
                'pattern' => '/(?<!\pL)ПЛОСЪК(?!\pL)/iu',
                'replace' => 'FLAT',
            ],
            [
                'pattern' => '/(?<!\pL)ПЛОСАК(?!\pL)/iu',
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
            ['pattern' => '/(?<!\pL)КРЪГЛА(?!\pL)/iu',
             'replace' => 'ROUND'
            ],
            ['pattern' => '/(?<!\pL)ШЛАЙ(?!\pL)/iu', 'replace' => 'GRINDER'],
            ['pattern' => '/(?<!\pL)НА\s+МЕТЪР(?!\pL)/iu', 'replace' => 'BY THE METER'],
            ['pattern' => '/(?<!\pL)НА\s+МЕТАР(?!\pL)/iu', 'replace' => 'BY THE METER'],
            ['pattern' => '/(?<!\pL)РАЙБЕР(?!\pL)/iu', 'replace' => 'REAMER'],
            ['pattern' => '/(?<!\pL)ПРОТЯЖКА(?!\pL)/iu', 'replace' => 'BROACH'],
            ['pattern' => '/(?<!\pL)ЗТП(?!\pL)/iu', 'replace' => 'ZTP'],
            ['pattern' => '/(?<!\pL)ЦО(?!\pL)/iu', 'replace' => 'TSO'],
            ['pattern' => '/(?<!\pL)DALGO(?!\pL)/iu', 'replace' => 'LONG'],
            ['pattern' => '/(?<!\pL)LYAVO(?!\pL)/iu', 'replace' => 'LEFT'],
            ['pattern' => '/(?<!\pL)TSOLA?(?!\pL)/iu', 'replace' => 'INCH'],
            ['pattern' => '/(?<!\pL)NAVIVKI(?!\pL)/iu', 'replace' => 'THREADS'],
            ['pattern' => '/(?<!\pL)КО(?!\pL)/iu', 'replace' => 'KO'],
            ['pattern' => '/(?<!\pL)ЧАШКА(?!\pL)/iu', 'replace' => 'CUP'],
            ['pattern' => '/(?<!\pL)ЕБ(?!\pL)/iu', 'replace' => 'EB'],
            ['pattern' => '/(?<!\pL)ДИАМАНТЕНА(?!\pL)/iu', 'replace' => 'DIAMOND'],
            ['pattern' => '/(?<!\pL)МЕТЧИК(?!\pL)/iu', 'replace' => 'TAP'],
            ['pattern' => '/(?<!\pL)БОРФРЕЗА(?!\pL)/iu', 'replace' => 'BURR'],
            ['pattern' => '/(?<!\pL)ФРЕЗОВА(?!\pL)/iu', 'replace' => 'MILLING'],
            ['pattern' => '/(?<!\pL)ГЛАВА(?!\pL)/iu', 'replace' => 'HEAD'],
            ['pattern' => '/(?<!\pL)ДРЪЖКА(?!\pL)/iu', 'replace' => 'HANDLE'],
            ['pattern' => '/(?<!\pL)ДИАМАНТЕН(?!\pL)/iu', 'replace' => 'DIAMOND'],
            ['pattern' => '/(?<!\pL)ДИНАМОМЕТРИЧЕН(?!\pL)/iu', 'replace' => 'DYNAMOMETRIC'],
            ['pattern' => '/(?<!\pL)УПОТР.(?!\pL)/iu', 'replace' => 'USED'],
            ['pattern' => '/(?<!\pL)ДЕЛИТЕЛЕН(?!\pL)/iu', 'replace' => 'DIVIDING'],
            ['pattern' => '/(?<!\pL)АПАРАТ(?!\pL)/iu', 'replace' => 'DEVICE'],
            ['pattern' => '/(?<!\pL)КЛЮЧ(?!\pL)/iu', 'replace' => 'WRENCH'],
            ['pattern' => '/(?<!\pL)Гладък(?!\pL)/iu', 'replace' => 'SMOOTH'],

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
            ['pattern' => '/(?<!\pL)ШЛАЙ(?!\pL)/iu', 'replace' => 'SCHLEIFER'],
            ['pattern' => '/(?<!\pL)НА\s+МЕТЪР(?!\pL)/iu', 'replace' => 'PRO METER'],
            ['pattern' => '/(?<!\pL)НА\s+МЕТАР(?!\pL)/iu', 'replace' => 'PRO METER'],
            ['pattern' => '/(?<!\pL)РАЙБЕР(?!\pL)/iu', 'replace' => 'REIBAHLE'],
            ['pattern' => '/(?<!\pL)ПРОТЯЖКА(?!\pL)/iu', 'replace' => 'RÄUMER'],
            ['pattern' => '/(?<!\pL)ЗТП(?!\pL)/iu', 'replace' => 'ZTP'],
            ['pattern' => '/(?<!\pL)ЦО(?!\pL)/iu', 'replace' => 'TSO'],
            ['pattern' => '/(?<!\pL)КО(?!\pL)/iu', 'replace' => 'KO'],
        ],
    ],
];
