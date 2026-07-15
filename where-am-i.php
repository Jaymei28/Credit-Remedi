<?php
// This file will tell you exactly where it is on the server
echo "<h1>Server Path Information</h1>";
echo "<p><strong>This file is located at:</strong><br>" . __FILE__ . "</p>";
echo "<p><strong>Current directory:</strong><br>" . __DIR__ . "</p>";
echo "<p><strong>Document root:</strong><br>" . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script name:</strong><br>" . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<hr>";
echo "<h2>Instructions:</h2>";
echo "<ol>";
echo "<li>Note the 'This file is located at' path above</li>";
echo "<li>Upload your debug-check.php to the SAME directory</li>";
echo "<li>Upload AllyAI.png to the SAME directory</li>";
echo "</ol>";
