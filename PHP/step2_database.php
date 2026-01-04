<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LLTool Installation – Database</title>
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
        }
        .error-box ul { margin: 0; padding-left: 20px; }
        .error-box li { margin: 4px 0; }
        
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
        button:active { background: #003d7a; }
        
        .step-info {
            background: #f9f9f9;
            border-left: 4px solid #0066cc;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>LLTool Installation</h1>
    <h2>Step 2 – Database Configuration</h2>

    <div class="step-info">
        Please provide your database connection details. The system will test the connection before saving.
    </div>

    <?php if ($success): ?>
        <div class="success-box">
            <strong>✓ Database configuration saved successfully!</strong><br>
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
            <label for="driver">Database Driver</label>
            <select name="driver" id="driver" required>
                <option value="mysql" <?= $formData['driver'] === 'mysql' ? 'selected' : '' ?>>MySQL / MariaDB</option>
                <option value="pgsql" <?= $formData['driver'] === 'pgsql' ? 'selected' : '' ?>>PostgreSQL</option>
            </select>
        </div>

        <div class="form-group">
            <label for="host">Host</label>
            <input 
                type="text" 
                name="host" 
                id="host" 
                value="<?= htmlspecialchars($formData['host']) ?>"
                placeholder="localhost or 127.0.0.1"
                required
            >
            <?php if (isset($errors['host'])): ?>
                <div class="field-error-text"><?= htmlspecialchars($errors['host']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="port">Port</label>
            <input 
                type="text" 
                name="port" 
                id="port" 
                value="<?= htmlspecialchars($formData['port']) ?>"
                placeholder="3306 for MySQL, 5432 for PostgreSQL"
                required
            >
            <?php if (isset($errors['port'])): ?>
                <div class="field-error-text"><?= htmlspecialchars($errors['port']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="database">Database Name</label>
            <input 
                type="text" 
                name="database" 
                id="database" 
                value="<?= htmlspecialchars($formData['database']) ?>"
                placeholder="e.g., lltool"
                required
            >
            <?php if (isset($errors['database'])): ?>
                <div class="field-error-text"><?= htmlspecialchars($errors['database']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input 
                type="text" 
                name="username" 
                id="username" 
                value="<?= htmlspecialchars($formData['username']) ?>"
                placeholder="Database user"
                required
            >
            <?php if (isset($errors['username'])): ?>
                <div class="field-error-text"><?= htmlspecialchars($errors['username']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Password (optional)</label>
            <input 
                type="password" 
                name="password" 
                id="password"
                placeholder="Leave blank if no password"
            >
            <?php if (isset($errors['password'])): ?>
                <div class="field-error-text"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit">Test Connection & Save</button>
    </form>
</div>

</body>
</html>
