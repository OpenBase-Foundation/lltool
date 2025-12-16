<h2>Admin Dashboard</h2>

<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
  <div style="background: #f0f0f0; padding: 20px; border-radius: 4px; text-align: center;">
    <div style="font-size: 32px; font-weight: bold; color: #667eea;"><?php echo $userCount; ?></div>
    <div style="color: #666; margin-top: 5px;">Users</div>
  </div>
  <div style="background: #f0f0f0; padding: 20px; border-radius: 4px; text-align: center;">
    <div style="font-size: 32px; font-weight: bold; color: #667eea;"><?php echo $cohortCount; ?></div>
    <div style="color: #666; margin-top: 5px;">Cohorts</div>
  </div>
  <div style="background: #f0f0f0; padding: 20px; border-radius: 4px; text-align: center;">
    <div style="font-size: 32px; font-weight: bold; color: #667eea;"><?php echo $studentCount; ?></div>
    <div style="color: #666; margin-top: 5px;">Students</div>
  </div>
</div>

<nav style="margin-bottom: 20px;">
  <a href="/?page=admin&action=dashboard" style="margin-right: 15px; color: #667eea; text-decoration: none; font-weight: bold;">Dashboard</a>
  <a href="/?page=admin&action=settings" style="margin-right: 15px; color: #667eea; text-decoration: none;">Settings</a>
  <a href="/?page=admin&action=users" style="color: #667eea; text-decoration: none;">Users</a>
</nav>

<p style="color: #999; font-size: 14px;">Welcome to the admin panel. Use the tabs above to manage settings, users, and view system statistics.</p>
