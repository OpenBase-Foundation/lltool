<h2>Users</h2>

<nav style="margin-bottom: 20px;">
  <a href="/?page=admin&action=dashboard" style="margin-right: 15px; color: #667eea; text-decoration: none;">Dashboard</a>
  <a href="/?page=admin&action=settings" style="margin-right: 15px; color: #667eea; text-decoration: none;">Settings</a>
  <a href="/?page=admin&action=users" style="color: #667eea; text-decoration: none; font-weight: bold;">Users</a>
</nav>

<?php if (empty($users)): ?>
  <p>No users found.</p>
<?php else: ?>
  <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <thead style="background: #f0f0f0;">
      <tr>
        <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Email</th>
        <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Created</th>
        <th style="padding: 10px; text-align: center; border-bottom: 2px solid #ddd;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr style="border-bottom: 1px solid #eee;">
          <td style="padding: 10px;"><?php echo htmlspecialchars($u['email']); ?></td>
          <td style="padding: 10px;"><?php echo htmlspecialchars($u['created_at']); ?></td>
          <td style="padding: 10px; text-align: center;">
            <?php if ($u['id'] != $_SESSION['user_id']): ?>
              <form method="post" action="/?page=admin&action=delete_user&id=<?php echo $u['id']; ?>" style="display: inline; margin: 0; padding: 0;" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                <?php echo \App\csrf_field(); ?>
                <button type="submit" style="background: none; border: none; color: #c33; cursor: pointer; text-decoration: underline; font-size: 12px;">Delete</button>
              </form>
            <?php else: ?>
              <span style="color: #999; font-size: 12px;">(You)</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
