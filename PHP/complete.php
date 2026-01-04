<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LLTool Installation – Complete</title>
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
        
        .success-box { 
            background: #efe; 
            border: 1px solid #cfc; 
            border-radius: 4px; 
            padding: 12px 16px; 
            margin-bottom: 24px; 
            color: #3a3; 
        }
        
        .error-box { 
            background: #fee; 
            border: 1px solid #fcc; 
            border-radius: 4px; 
            padding: 12px 16px; 
            margin-bottom: 24px; 
            color: #c33; 
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
        }
        button:hover { background: #0052a3; }
    </style>
</head>
<body>

<div class="box">
    <h1>LLTool Installation</h1>
    <h2>Installation Complete</h2>

    <?php
    use LLTool\Install\InstallerState;
    ?>
    
    <?php if (InstallerState::isInstalled()): ?>
        <div class="success-box">
            <strong>✓ Installation completed successfully!</strong><br>
            You can now start using LLTool.
        </div>
        <button onclick="window.location.href='/auth/login'">Go to Login</button>
    <?php else: ?>
        <div class="error-box">
            <strong>Installation incomplete.</strong><br>
            Please go back and complete all installation steps.
        </div>
        <button onclick="window.location.href='/install'">Back to Installation</button>
    <?php endif; ?>
</div>

</body>
</html>

