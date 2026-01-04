<?php
/**
 * Fix Permissions Script
 * 
 * Dit script maakt de benodigde directories aan en zet de juiste permissions.
 */

$isCli = php_sapi_name() === 'cli';

function output($message, $type = 'info') {
    global $isCli;
    
    if ($isCli) {
        echo $message . "\n";
    } else {
        $color = match($type) {
            'error' => '#fee',
            'success' => '#efe',
            'warning' => '#ffe',
            default => '#e7f3ff'
        };
        echo "<div style='background: {$color}; padding: 10px; margin: 5px 0; border-radius: 4px;'>{$message}</div>";
    }
}

if (!$isCli) {
    echo '<!DOCTYPE html>
    <html lang="nl">
    <head>
        <meta charset="UTF-8">
        <title>LLTool - Fix Permissions</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
            .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            h1 { color: #333; }
        </style>
    </head>
    <body>
    <div class="container">
    <h1>LLTool - Fix Permissions</h1>';
}

// Get base directory (where index.php is located)
$baseDir = dirname(__FILE__);
$directories = [
    'storage',
    'storage/photos',
    'config',
];

$success = true;

// Try to get web server user (www-data in most cases)
$webUser = 'www-data';
if (function_exists('posix_getpwuid')) {
    $processUser = posix_getpwuid(posix_geteuid());
    $webUser = $processUser['name'] ?? 'www-data';
}

foreach ($directories as $dir) {
    $path = $baseDir . '/' . $dir;
    
    // Create directory if it doesn't exist
    if (!is_dir($path)) {
        if (@mkdir($path, 0777, true)) {
            output("✓ Created directory: {$dir}", 'success');
        } else {
            output("✗ Failed to create directory: {$dir}", 'error');
            $success = false;
            continue;
        }
    }
    
    // Try to change ownership (may fail if not root, that's OK)
    if (function_exists('chown')) {
        @chown($path, $webUser);
        @chgrp($path, $webUser);
    }
    
    // Set permissions - try 777 first, then 775
    $permissionsSet = false;
    if (@chmod($path, 0777)) {
        output("✓ Set permissions 777 on: {$dir}", 'success');
        $permissionsSet = true;
    } elseif (@chmod($path, 0775)) {
        output("✓ Set permissions 775 on: {$dir}", 'success');
        $permissionsSet = true;
    } else {
        output("⚠ Could not set permissions on: {$dir}", 'warning');
    }
    
    // Also set permissions on parent if needed
    $parent = dirname($path);
    if ($parent !== $baseDir && is_dir($parent)) {
        @chmod($parent, 0775);
    }
    
    // Check if writable
    if (is_writable($path)) {
        output("✓ {$dir} is writable", 'success');
    } else {
        output("✗ {$dir} is NOT writable", 'error');
        output("Current permissions: " . substr(sprintf('%o', fileperms($path)), -4), 'info');
        output("Try running as root: chown -R {$webUser}:{$webUser} {$dir} && chmod -R 775 {$dir}", 'info');
        $success = false;
    }
}

if ($success) {
    output("All permissions fixed successfully!", 'success');
    if (!$isCli) {
        output('<a href="index.php">Go to application</a>', 'success');
    }
} else {
    output("Some permissions could not be fixed automatically. You may need to run manually:", 'warning');
    output("chmod -R 775 storage config", 'info');
    if (!$isCli) {
        output('<p>Or via SSH:</p><pre>chmod -R 775 storage config</pre>', 'info');
    }
}

if (!$isCli) {
    echo '</div></body></html>';
}

