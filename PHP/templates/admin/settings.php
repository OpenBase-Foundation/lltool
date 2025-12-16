<h2>Settings</h2>

<nav style="margin-bottom: 20px;">
  <a href="/?page=admin&action=dashboard" style="margin-right: 15px; color: #667eea; text-decoration: none;">Dashboard</a>
  <a href="/?page=admin&action=settings" style="margin-right: 15px; color: #667eea; text-decoration: none; font-weight: bold;">Settings</a>
  <a href="/?page=admin&action=users" style="color: #667eea; text-decoration: none;">Users</a>
</nav>

<?php if (!empty($errors)): ?>
  <div style="background: #fee; color: #c33; padding: 12px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #c33;">
    <ul style="margin: 0; padding-left: 20px;">
      <?php foreach ($errors as $e): ?>
        <li><?php echo htmlspecialchars($e); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" action="/?page=admin&action=update_settings">
  <?php echo \App\csrf_field(); ?>

  <label for="organization_name" style="display: block; margin-bottom: 10px; font-weight: bold;">
    Organization Name
  </label>
  <input type="text" id="organization_name" name="organization_name" value="<?php echo htmlspecialchars($org_name ?? ''); ?>" required style="width: 100%; max-width: 400px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">

  <div style="margin-top: 20px;">
    <label style="display: flex; align-items: center;">
      <input type="checkbox" name="allow_user_registration" value="1" <?php echo $allow_registration ? 'checked' : ''; ?> style="margin-right: 10px; width: 18px; height: 18px;">
      <span>Allow users to self-register</span>
    </label>
    <p style="color: #999; font-size: 12px; margin-top: 5px;">If unchecked, only admins can create user accounts</p>
  </div>

  <button type="submit" style="margin-top: 20px; padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer;">Save Settings</button>
</form>
