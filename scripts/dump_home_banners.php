<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = App\Models\HomeBanner::orderBy('sort_order')->get([
    'eyebrow','title','subtitle','button_text','button_url','image','is_active','sort_order'
])->toArray();

echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
