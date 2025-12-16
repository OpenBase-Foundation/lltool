<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LLTool — Setup</title>
  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .setup-container {
      background: white;
      border-radius: 8px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      max-width: 500px;
      width: 100%;
      padding: 40px;
      margin: 20px;
    }
    h1 {
      color: #333;
      margin-top: 0;
      text-align: center;
    }
    .subtitle {
      color: #666;
      text-align: center;
      margin-bottom: 30px;
      font-size: 14px;
    }
    .errors {
      background: #fee;
      color: #c33;
      padding: 12px;
      border-radius: 4px;
      margin-bottom: 20px;
      border-left: 4px solid #c33;
    }
    .errors ul {
      margin: 0;
      padding-left: 20px;
    }
    .errors li {
      margin: 4px 0;
    }
    label {
      display: block;
      margin-top: 15px;
      color: #333;
      font-weight: bold;
      font-size: 14px;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"],
    textarea {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
      font-family: inherit;
    }
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    textarea:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
    }
    .checkbox-group {
      display: flex;
      align-items: center;
      margin-top: 15px;
    }
    input[type="checkbox"] {
      margin-right: 10px;
      width: 18px;
      height: 18px;
      cursor: pointer;
    }
    .checkbox-group label {
      margin: 0;
      font-weight: normal;
      font-size: 14px;
    }
    .help-text {
      color: #999;
      font-size: 12px;
      margin-top: 5px;
      display: block;
    }
    button {
      width: 100%;
      padding: 12px;
      margin-top: 25px;
      background: #667eea;
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover {
      background: #5568d3;
    }
    .password-requirements {
      background: #f0f0f0;
      border-left: 4px solid #667eea;
      padding: 12px;
      margin-top: 15px;
      border-radius: 4px;
      font-size: 12px;
      color: #666;
    }
    .password-requirements ul {
      margin: 5px 0;
      padding-left: 20px;
    }
    .password-requirements li {
      margin: 3px 0;
    }
  </style>
</head>
<body>
<div class="setup-container">
  <h1>🚀 LLTool Setup</h1>
  <div class="subtitle">Configure your application on first run</div>

  <?php if (!empty($errors)): ?>
    <div class="errors">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="/?page=setup&action=process">
    <?php echo \App\csrf_field(); ?>

    <label for="organization_name">Organization Name</label>
    <input type="text" id="organization_name" name="organization_name" value="<?php echo htmlspecialchars($_POST['organization_name'] ?? 'My School'); ?>" required>
    <span class="help-text">The name of your organization (e.g., school, company, or institution)</span>

    <label for="admin_email">Admin Email</label>
    <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($_POST['admin_email'] ?? 'admin@example.com'); ?>" required>
    <span class="help-text">Primary administrator email address</span>

    <label for="admin_password">Admin Password</label>
    <input type="password" id="admin_password" name="admin_password" required>
    <div class="password-requirements">
      <strong>Password must contain:</strong>
      <ul>
        <li>At least 8 characters</li>
        <li>One uppercase letter (A-Z)</li>
        <li>One lowercase letter (a-z)</li>
        <li>One digit (0-9)</li>
      </ul>
    </div>

    <label for="admin_password_confirm">Confirm Password</label>
    <input type="password" id="admin_password_confirm" name="admin_password_confirm" required>

    <div class="checkbox-group">
      <input type="checkbox" id="allow_registration" name="allow_user_registration" value="1">
      <label for="allow_registration">Allow users to self-register</label>
    </div>
    <span class="help-text">If unchecked, only admins can create user accounts</span>

    <button type="submit">Complete Setup</button>
  </form>
</div>
</body>
</html>
