<?php
echo "Clearing cache...<br>";

// Clear config cache
$configPath = __DIR__ . '/bootstrap/cache/config.php';
if (file_exists($configPath)) {
    unlink($configPath);
    echo "Config cache cleared!<br>";
}

// Clear view cache
$viewPath = __DIR__ . '/storage/framework/views';
if (is_dir($viewPath)) {
    $files = glob($viewPath . '/*');
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }
    echo "View cache cleared!<br>";
}

echo "Done! You can delete this file now.";
?>