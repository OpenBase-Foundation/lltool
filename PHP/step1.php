<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LLTool Installation – Step 1</title>
    <style>
        body { font-family: sans-serif; background: #f5f5f5; }
        .box { max-width: 700px; margin: 40px auto; background: #fff; padding: 24px; }
        .ok { color: green; }
        .fail { color: red; }
    </style>
</head>
<body>

<div class="box">
    <h1>LLTool Installation</h1>
    <h2>Step 1 – System check</h2>

    <h3>PHP</h3>
    <p>
        PHP version: <?= htmlspecialchars($checks['php_version']['current']) ?>
        —
        <strong class="<?= $checks['php_version']['ok'] ? 'ok' : 'fail' ?>">
            <?= $checks['php_version']['ok'] ? 'OK' : 'FAIL' ?>
        </strong>
    </p>

    <h3>Extensions</h3>
    <ul>
        <?php foreach ($checks['extensions'] as $ext => $ok): ?>
            <li>
                <?= $ext ?>:
                <strong class="<?= $ok ? 'ok' : ($ext === 'imagick' ? 'ok' : 'fail') ?>">
                    <?= $ok ? 'OK' : ($ext === 'imagick' ? 'OPTIONAL (GD is sufficient)' : 'MISSING') ?>
                </strong>
            </li>
        <?php endforeach; ?>
    </ul>
    <p><small><em>Note: Imagick is optional. GD extension is sufficient for photo processing.</em></small></p>

    <h3>Permissions</h3>
    <ul>
        <?php foreach ($checks['permissions'] as $dir => $ok): ?>
            <li>
                <?= $dir ?> writable:
                <strong class="<?= $ok ? 'ok' : 'fail' ?>">
                    <?= $ok ? 'YES' : 'NO' ?>
                </strong>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if (in_array(false, $checks['permissions'] ?? [], true)): ?>
        <div style="background: #ffe; border: 1px solid #fcc; padding: 12px; border-radius: 4px; margin-top: 10px;">
            <strong>Permissions need to be fixed!</strong><br>
            <a href="fix-permissions.php" style="color: #0066cc;">Click here to fix permissions automatically</a><br>
            <small>Or run manually in Docker: <code>docker-compose exec -u root web chmod -R 777 /var/www/html/storage /var/www/html/config</code></small><br>
            <small>Or on server: <code>chmod -R 775 storage config</code></small>
        </div>
    <?php endif; ?>

    <p>
        <?php 
        // GD is sufficient, imagick is optional
        $requiredExtensions = ['pdo', 'curl', 'openssl', 'json', 'gd'];
        $extensionsOk = true;
        foreach ($requiredExtensions as $ext) {
            if (!($checks['extensions'][$ext] ?? false)) {
                $extensionsOk = false;
                break;
            }
        }
        
        if (
            $checks['php_version']['ok']
            && $extensionsOk
            && !in_array(false, $checks['permissions'], true)
        ): ?>
            <strong class="ok">System OK. Ready to continue.</strong>
        <?php else: ?>
            <strong class="fail">Fix the issues above before continuing.</strong>
        <?php endif; ?>
    </p>
</div>

</body>
</html>
