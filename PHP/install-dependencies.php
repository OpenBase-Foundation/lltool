<?php
/**
 * Install Dependencies Script
 * 
 * Dit script installeert automatisch Composer dependencies.
 * Voer uit via browser of command line.
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

function runCommand($command) {
    global $isCli;
    
    if ($isCli) {
        passthru($command, $returnVar);
        return $returnVar === 0;
    } else {
        $output = [];
        $returnVar = 0;
        exec($command . ' 2>&1', $output, $returnVar);
        return [
            'success' => $returnVar === 0,
            'output' => implode("\n", $output)
        ];
    }
}

if (!$isCli) {
    echo '<!DOCTYPE html>
    <html lang="nl">
    <head>
        <meta charset="UTF-8">
        <title>LLTool - Install Dependencies</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
            .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            h1 { color: #333; }
            pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        </style>
    </head>
    <body>
    <div class="container">
    <h1>LLTool - Install Dependencies</h1>';
}

output("Checking for composer.phar...", 'info');

$composerPhar = __DIR__ . '/composer.phar';
$hasComposerPhar = file_exists($composerPhar);

if (!$hasComposerPhar) {
    output("composer.phar not found. Downloading...", 'warning');
    
    $composerUrl = 'https://getcomposer.org/download/latest-stable/composer.phar';
    $composerContent = @file_get_contents($composerUrl);
    
    if ($composerContent === false) {
        output("ERROR: Could not download composer.phar. Please download manually from https://getcomposer.org/download/", 'error');
        if (!$isCli) {
            echo '</div></body></html>';
        }
        exit(1);
    }
    
    file_put_contents($composerPhar, $composerContent);
    chmod($composerPhar, 0755);
    output("composer.phar downloaded successfully!", 'success');
} else {
    output("composer.phar found!", 'success');
}

output("Running composer install --no-dev...", 'info');

$command = 'php ' . escapeshellarg($composerPhar) . ' install --no-dev --no-interaction';

if ($isCli) {
    $success = runCommand($command);
    if ($success) {
        output("Dependencies installed successfully!", 'success');
        output("You can now access the application.", 'success');
    } else {
        output("ERROR: Failed to install dependencies. Check the output above.", 'error');
        exit(1);
    }
} else {
    $result = runCommand($command);
    
    echo '<h2>Output:</h2>';
    echo '<pre>' . htmlspecialchars($result['output']) . '</pre>';
    
    if ($result['success']) {
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            output("Dependencies installed successfully!", 'success');
            output('<a href="index.php">Go to application</a>', 'success');
        } else {
            output("Installation completed but vendor/autoload.php not found. Please check the output above.", 'warning');
        }
    } else {
        output("ERROR: Failed to install dependencies. Check the output above.", 'error');
    }
}

if (!$isCli) {
    echo '</div></body></html>';
}

