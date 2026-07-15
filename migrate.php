<?php
// Security: Only allow access with secret key
$secret = 'CreditRemedi704'; // Change this to something secure
if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    die('Unauthorized');
}

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<!DOCTYPE html>";
echo "<html><head><title>Database Migration</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}pre{background:#fff;padding:15px;border-radius:5px;overflow:auto;}</style>";
echo "</head><body>";
echo "<h2>🚀 Running Database Migrations...</h2>";
echo "<pre>";

// Run migrations
$exitCode = $kernel->call('migrate', [
    '--force' => true, // Required for production
]);

echo "</pre>";

if ($exitCode === 0) {
    echo "<h3 style='color:green;'>✅ Migration completed successfully!</h3>";
} else {
    echo "<h3 style='color:red;'>❌ Migration failed with exit code: " . $exitCode . "</h3>";
}

echo "<p style='background:yellow;padding:10px;border-radius:5px;'><strong>⚠️ IMPORTANT: Delete this file immediately for security!</strong></p>";
echo "<p>Once you've confirmed everything works, delete <code>migrate.php</code> from your server.</p>";
echo "</body></html>";
?>
