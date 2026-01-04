<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LLTool Installation – Sentry</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; 
            background: #f5f5f5; 
            margin: 0; 
        }
        .box { 
            max-width: 600px; 
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
        
        .form-group { 
            margin-bottom: 16px; 
            text-align: left;
        }
        label { 
            display: block; 
            margin-bottom: 6px; 
            font-weight: 500; 
            color: #333;
        }
        input, select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.1);
        }
        
        .field-error {
            border-color: #cc3333 !important;
        }
        .field-error-text {
            color: #c33;
            font-size: 12px;
            margin-top: 4px;
        }
        
        button { 
            background: #0066cc; 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 16px;
            width: 100%;
        }
        button:hover { background: #0052a3; }
        
        .step-info {
            background: #f9f9f9;
            border-left: 4px solid #0066cc;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
            text-align: left;
        }
        
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>LLTool Installation</h1>
    <h2>Step 4 – Sentry Configuration</h2>

    <div class="step-info">
        Configure Sentry for error tracking. This is optional - you can leave the DSN empty and configure it later.
        You can find your DSN in your <a href="https://sentry.io" target="_blank">Sentry project settings</a>.
    </div>

    <?php if ($success): ?>
        <div class="success-box">
            <strong>✓ Sentry configuration saved successfully!</strong><br>
            Redirecting to next step...
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Please fix the following issues:</strong>
            <ul>
                <?php foreach ($errors as $field => $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="form-group">
            <label for="dsn">Sentry DSN (optional)</label>
            <input 
                type="text" 
                name="dsn" 
                id="dsn" 
                value="<?= htmlspecialchars($formData['dsn']) ?>"
                placeholder="https://xxx@xxx.ingest.sentry.io/xxx"
            >
            <div class="help-text">Leave empty to skip Sentry setup for now</div>
            <?php if (isset($errors['dsn'])): ?>
                <div class="field-error-text"><?= htmlspecialchars($errors['dsn']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="environment">Environment</label>
            <select id="environment" name="environment">
                <option value="production" <?= $formData['environment'] === 'production' ? 'selected' : '' ?>>Production</option>
                <option value="staging" <?= $formData['environment'] === 'staging' ? 'selected' : '' ?>>Staging</option>
                <option value="development" <?= $formData['environment'] === 'development' ? 'selected' : '' ?>>Development</option>
            </select>
        </div>

        <button type="submit">Save & Continue</button>
    </form>
</div>

</body>
</html>

