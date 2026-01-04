<?php
/**
 * LLTool Entry Point
 * 
 * Upload dit bestand en de hele PHP/ map naar je WebReus hosting.
 * Ga dan naar: https://jouw-domein.nl/index.php
 */

// Check if vendor directory exists
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    // Try to install dependencies automatically if composer.phar exists
    if (file_exists(__DIR__ . '/composer.phar')) {
        // Try to run composer install
        $output = [];
        $returnVar = 0;
        $command = 'php ' . escapeshellarg(__DIR__ . '/composer.phar') . ' install --no-dev --no-interaction 2>&1';
        
        @exec($command, $output, $returnVar);
        
        // Check if it succeeded
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            // Success, continue
        } else {
            // Still failed, show error
            showDependenciesError();
        }
    } else {
        showDependenciesError();
    }
}

function showDependenciesError() {
    $composerPharPath = __DIR__ . '/composer.phar';
    $hasComposerPhar = file_exists($composerPharPath);
    
    die('
    <!DOCTYPE html>
    <html lang="nl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>LLTool - Dependencies Missing</title>
        <style>
            body { 
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif; 
                max-width: 700px; 
                margin: 50px auto; 
                padding: 20px; 
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            h1 { color: #333; margin-top: 0; }
            .error { 
                background: #fee; 
                border: 1px solid #fcc; 
                padding: 15px; 
                border-radius: 5px; 
                margin: 20px 0;
            }
            .info {
                background: #e7f3ff;
                border: 1px solid #b3d9ff;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            code { 
                background: #f5f5f5; 
                padding: 2px 6px; 
                border-radius: 3px;
                font-family: "Courier New", monospace;
            }
            pre {
                background: #f5f5f5;
                padding: 15px;
                border-radius: 5px;
                overflow-x: auto;
                border-left: 4px solid #0066cc;
            }
            .step {
                margin: 15px 0;
                padding: 10px;
                background: #f9f9f9;
                border-left: 3px solid #0066cc;
            }
            .step-number {
                font-weight: bold;
                color: #0066cc;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>LLTool - Dependencies Missing</h1>
            
            <div class="error">
                <p><strong>Fout:</strong> De <code>vendor/</code> directory ontbreekt.</p>
                <p>De applicatie heeft Composer dependencies nodig om te werken.</p>
            </div>

            <h2>Oplossing 1: Lokaal installeren (Aanbevolen)</h2>
            <div class="step">
                <span class="step-number">Stap 1:</span> Open een terminal/command prompt
            </div>
            <div class="step">
                <span class="step-number">Stap 2:</span> Ga naar de PHP map:
                <pre><code>cd "' . htmlspecialchars(str_replace('\\', '/', __DIR__)) . '"</code></pre>
            </div>
            <div class="step">
                <span class="step-number">Stap 3:</span> Installeer dependencies:
                <pre><code>composer install --no-dev</code></pre>
            </div>
            <div class="step">
                <span class="step-number">Stap 4:</span> Upload de <code>vendor/</code> map naar je server
            </div>

            <h2>Oplossing 2: Via SSH op server</h2>
            <div class="info">
                <p>Als je SSH toegang hebt tot je server:</p>
                <pre><code>cd /path/to/your/website
composer install --no-dev</code></pre>
            </div>

            ' . ($hasComposerPhar ? '
            <h2>Oplossing 3: Composer.phar gevonden</h2>
            <div class="info">
                <p>Er is een <code>composer.phar</code> bestand gevonden. Probeer handmatig uit te voeren:</p>
                <pre><code>php composer.phar install --no-dev</code></pre>
                <p>Of via SSH op je server in de website directory.</p>
            </div>
            ' : '
            <h2>Composer.phar downloaden</h2>
            <div class="info">
                <p>Je kunt composer.phar downloaden en uploaden naar je server:</p>
                <pre><code>wget https://getcomposer.org/download/latest-stable/composer.phar
# Of download handmatig van: https://getcomposer.org/download/</code></pre>
                <p>Upload <code>composer.phar</code> naar de PHP directory en voer dan uit:</p>
                <pre><code>php composer.phar install --no-dev</code></pre>
            </div>
            ') . '

            <h2>Hulp nodig?</h2>
            <div class="info">
                <p>Zorg ervoor dat:</p>
                <ul>
                    <li>PHP 8.2+ geïnstalleerd is</li>
                    <li>Composer geïnstalleerd is (of composer.phar aanwezig is)</li>
                    <li>Je schrijfrechten hebt in de directory</li>
                </ul>
            </div>
        </div>
    </body>
    </html>
    ');
}

require_once __DIR__ . '/vendor/autoload.php';

// Custom autoloader for root files and app/ directory
// This handles the mixed structure where some classes are in root and some in app/
spl_autoload_register(function ($class) {
    // Remove LLTool\ prefix
    if (strpos($class, 'LLTool\\') !== 0) {
        return false;
    }
    
    $relativeClass = substr($class, 7); // Remove 'LLTool\'
    $parts = explode('\\', $relativeClass);
    $className = end($parts);
    
    // List of root files that have subnamespaces
    $rootFiles = [
        'Config' => 'Config.php',
        'Router' => 'Router.php',
        'Bootstrap' => 'Bootstrap.php',
        'HomeController' => 'HomeController.php',
        'InstallController' => 'InstallController.php',
        'SystemCheck' => 'SystemCheck.php',
        'DatabaseConnectionTester' => 'DatabaseConnectionTester.php',
        'InstallerState' => 'InstallerState.php',
    ];
    
    // Check if it's a root file with subnamespace
    if (isset($rootFiles[$className])) {
        $file = __DIR__ . '/' . $rootFiles[$className];
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    
    // Try app/ directory with full namespace path
    $relativeClass = str_replace('\\', '/', $relativeClass);
    $file = __DIR__ . '/app/' . $relativeClass . '.php';
    if (file_exists($file)) {
        require $file;
        return true;
    }
    
    // Try root with simple class name (fallback)
    $file = __DIR__ . '/' . $className . '.php';
    if (file_exists($file)) {
        require $file;
        return true;
    }
    
    return false;
}, true, true); // Prepend to autoload stack and throw on error

use LLTool\Bootstrap;

Bootstrap::run();

