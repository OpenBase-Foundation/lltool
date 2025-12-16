<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register — LLTool</title>
  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      margin: 0;
      padding: 0;
      background: #f3f4f6;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    .container {
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      max-width: 400px;
      width: 100%;
      padding: 30px;
      margin: 20px;
    }
    h2 {
      color: #333;
      margin-top: 0;
      text-align: center;
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
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }
    input[type="email"]:focus,
    input[type="password"]:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
    }
    .help-text {
      color: #999;
      font-size: 12px;
      margin-top: 5px;
    }
    button {
      width: 100%;
      padding: 10px;
      margin-top: 20px;
      background: #667eea;
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
    }
    button:hover {
      background: #5568d3;
    }
    .login-link {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }
    .login-link a {
      color: #667eea;
      text-decoration: none;
    }
    .login-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>Create Account</h2>

  <?php if (!empty($errors)): ?>
    <div class="errors">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="/?page=auth&action=register">
    <?php echo \App\csrf_field(); ?>

    <label>Email<br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required></label>

    <label>Password<br>
    <input type="password" name="password" required></label>
    <div class="help-text">8+ characters, uppercase, lowercase, digit</div>

    <label>Confirm Password<br>
    <input type="password" name="password_confirm" required></label>

    <button type="submit">Register</button>
  </form>

  <div class="login-link">
    Already have an account? <a href="/?page=login">Login here</a>
  </div>
</div>
</body>
</html>
