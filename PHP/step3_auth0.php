<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LLTool Installation – Auth0</title>
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
        .error-box ul { margin: 0; padding-left: 20px; }
        
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
        input { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus {
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
    <h2>Step 3 – Auth0 Configuration</h2>

    <div class="step-info">
        Configure your Auth0 application. You can find these values in your <a href="https://manage.auth0.com" target="_blank">Auth0 Dashboard</a> under Applications.
    </div>

    <?php if ($success): ?>
        <div class="success-box">
            <strong>✓ Auth0 configuration saved successfully!</strong><br>
            Redirecting to next step...
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Please fix the following issues:</strong>
            <ul>
                <?php foreach ($errors as $field => $error): ?>
                    <li>
                        <?php if (is_string($field) && $field !== 'connection'): ?>
                            <strong><?= htmlspecialchars($field) ?>:</strong>
                        <?php endif; ?>
                        <?= htmlspecialchars($error) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="form-group">
            <label for="domain">Auth0 Domain</label>
            <input 
                type="text" 
                name="domain" 
                id="domain" 
                value="<?= htmlspecialchars($formData['domain']) ?>"
                placeholder="your-tenant.auth0.com"
                required
            >
            <div class="help-text">Your Auth0 tenant domain (e.g., myapp.auth0.com)</div>
            <?php if (isset($errors['domain'])): ?>
                <div class="field-error-text"><?= htmlspecialchars($errors['domain']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="client_id">Client ID</label>
            <input 
                type="text" 
                name="client_id" 
                id="client_id" 
                value="<?= htmlspecialchars($formData['client_id']) ?>"
                placeholder="Your Auth0 Client ID"
                required
            >
            <?php if (isset($errors['client_id'])): ?>
                <div class="field-error-text"><?= htmlspecialchars($errors['client_id']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="client_secret">Client Secret</label>
            <input 
                type="password" 
                name="client_secret" 
                id="client_secret" 
                value="<?= htmlspecialchars($formData['client_secret']) ?>"
                placeholder="Your Auth0 Client Secret"
                required
            >
            <?php if (isset($errors['client_secret'])): ?>
                <div class="field-error-text"><?= htmlspecialchars($errors['client_secret']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="audience">API Audience (optional)</label>
            <input 
                type="text" 
                name="audience" 
                id="audience" 
                value="<?= htmlspecialchars($formData['audience']) ?>"
                placeholder="https://your-api.com"
            >
            <div class="help-text">Leave empty if not using API authentication</div>
        </div>

        <div class="step-info">
            <strong>Callback URL:</strong> <?php
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                echo htmlspecialchars("{$protocol}://{$host}/auth/callback");
            ?><br>
            Make sure to add this URL to your Auth0 Application settings.
        </div>

        <button type="submit">Save & Continue</button>
    </form>
</div>

</body>
</html>

