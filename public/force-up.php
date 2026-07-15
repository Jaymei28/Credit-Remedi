<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
echo "Attempting to bring site up...<br>";
try {
    Illuminate\Support\Facades\Artisan::call('up');
    echo "Artisan UP executed successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
