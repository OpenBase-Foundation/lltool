<h2><?php echo $cohort ? 'Edit Cohort' : 'New Cohort'; ?></h2>
<?php if (!empty($errors)): ?>
  <ul style="color:red">
  <?php foreach ($errors as $e): ?>
    <li><?php echo htmlspecialchars($e); ?></li>
  <?php endforeach; ?>
  </ul>
<?php endif; ?>
<form method="post" action="/?page=cohorts&action=save">
  <?php echo \App\csrf_field(); ?>
  <?php if (!empty($cohort['id'])): ?>
    <input type="hidden" name="id" value="<?php echo intval($cohort['id']); ?>">
  <?php endif; ?>
  <label>Name<br><input type="text" name="name" value="<?php echo htmlspecialchars($cohort['name'] ?? ($old['name'] ?? '')); ?>" required></label><br><br>
  <label>Description<br><textarea name="description"><?php echo htmlspecialchars($cohort['description'] ?? ($old['description'] ?? '')); ?></textarea></label><br><br>
  <button type="submit">Save</button>
  <a href="/?page=cohorts">Cancel</a>
</form>
<?php if (!empty($cohort['id'])): ?>
  <form method="post" action="/?page=cohorts&action=delete&id=<?php echo intval($cohort['id']); ?>" onsubmit="return confirm('Delete this cohort?');">
    <?php echo \App\csrf_field(); ?>
    <button type="submit" style="margin-top:8px;color:red">Delete Cohort</button>
  </form>
<?php endif; ?>
