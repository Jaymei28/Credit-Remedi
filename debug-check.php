<?php
// Debug script to check server configuration
echo "<h1>Server Debug Information</h1>";

echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";

echo "<h2>2. File Existence Check</h2>";
$files = [
    'AllyAI.png' => __DIR__ . '/AllyAI.png',
    'Build Manifest' => __DIR__ . '/build/manifest.json',
    'App CSS' => __DIR__ . '/build/assets/app-CXUZ7LWH.css',
];

foreach ($files as $name => $path) {
    $exists = file_exists($path) ? '✅ EXISTS' : '❌ MISSING';
    echo "$name: $exists<br>";
    if (file_exists($path)) {
        echo "  → Path: $path<br>";
        echo "  → Size: " . filesize($path) . " bytes<br>";
    }
}

echo "<h2>3. Laravel Check</h2>";
$laravelPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($laravelPath)) {
    echo "✅ Laravel installed<br>";
    require $laravelPath;
    
    $app = require_once dirname(__DIR__) . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "Laravel Version: " . app()->version() . "<br>";
    echo "Environment: " . app()->environment() . "<br>";
    echo "Debug Mode: " . (config('app.debug') ? 'ON' : 'OFF') . "<br>";
} else {
    echo "❌ Laravel not found<br>";
}

echo "<h2>4. Storage Permissions</h2>";
$storagePath = dirname(__DIR__) . '/storage';
echo "Storage Path: $storagePath<br>";
echo "Writable: " . (is_writable($storagePath) ? '✅ YES' : '❌ NO') . "<br>";

echo "<h2>5. Error Log Check</h2>";
$logPath = dirname(__DIR__) . '/storage/logs/laravel.log';
if (file_exists($logPath)) {
    echo "✅ Log file exists<br>";
    echo "<h3>Last 20 lines of error log:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 400px;'>";
    $lines = file($logPath);
    $lastLines = array_slice($lines, -20);
    echo htmlspecialchars(implode('', $lastLines));
    echo "</pre>";
} else {
    echo "❌ No log file found<br>";
}

echo "<h2>6. Recommended Actions</h2>";
echo "<ol>";
echo "<li>If AllyAI.png is missing, upload it to the public folder</li>";
echo "<li>If build files are missing, run 'npm run build' locally and upload the public/build folder</li>";
echo "<li>If storage is not writable, run: chmod -R 775 storage bootstrap/cache</li>";
echo "<li>Clear caches: php artisan config:clear && php artisan cache:clear && php artisan view:clear</li>";
echo "</ol>";

echo "<p><strong>After fixing issues, delete this file for security!</strong></p>";
