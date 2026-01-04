<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LLTool Installation – Migrations</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; 
            background: #f5f5f5; 
            margin: 0; 
        }
        .box { 
            max-width: 700px; 
            margin: 40px auto; 
            background: #fff; 
            padding: 32px; 
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 { margin: 0 0 8px 0; color: #333; font-size: 28px; }
        h2 { margin: 0 0 24px 0; color: #666; font-size: 18px; font-weight: normal; }
        
        .error-box { 
            background: #fee; 
            border: 1px solid #fcc; 
            border-radius: 4px; 
            padding: 12px 16px; 
            margin-bottom: 24px; 
            color: #c33; 
            text-align: left;
        }
        
        .success-box { 
            background: #efe; 
            border: 1px solid #cfc; 
            border-radius: 4px; 
            padding: 12px 16px; 
            margin-bottom: 24px; 
            color: #3a3; 
        }
        
        .migration-list {
            text-align: left;
            margin: 20px 0;
        }
        
        .migration-item {
            padding: 8px;
            margin: 4px 0;
            border-radius: 4px;
        }
        
        .migration-success {
            background: #efe;
            color: #3a3;
        }
        
        .migration-error {
            background: #fee;
            color: #c33;
        }
        
        .step-info {
            background: #f9f9f9;
            border-left: 4px solid #0066cc;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>LLTool Installation</h1>
    <h2>Step 5 – Database Migrations</h2>

    <div class="step-info">
        The system will now create the necessary database tables. This may take a few moments.
    </div>

    <?php if (isset($error)): ?>
        <div class="error-box">
            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($migrationsRun) && $migrationsRun): ?>
        <?php if (!empty($migrationResults)): ?>
            <div class="migration-list">
                <h3>Running Migrations:</h3>
                <?php foreach ($migrationResults as $migration => $result): ?>
                    <div class="migration-item <?= $result['status'] === 'success' ? 'migration-success' : 'migration-error' ?>">
                        <strong><?= htmlspecialchars($migration) ?>:</strong>
                        <?php if ($result['status'] === 'success'): ?>
                            ✓ Success
                        <?php else: ?>
                            ✗ Error: <?= htmlspecialchars($result['error'] ?? 'Unknown error') ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (InstallerState::isStepCompleted('migrations')): ?>
            <div class="success-box">
                <strong>✓ All migrations completed successfully!</strong><br>
                Redirecting to final step...
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="step-info">
            Click the button below to run database migrations.
        </div>
        <form method="post">
            <button type="submit">Run Migrations</button>
        </form>
    <?php endif; ?>
    
    <?php
    use LLTool\Install\InstallerState;
    ?>
</div>

</body>
</html>

