<h2><?php echo isset($student) ? 'Edit Student' : 'New Student'; ?></h2>
<?php if (!empty($errors)): ?>
  <ul style="color:red">
  <?php foreach ($errors as $e): ?>
    <li><?php echo htmlspecialchars($e); ?></li>
  <?php endforeach; ?>
  </ul>
<?php endif; ?>
<form method="post" action="/?page=students&action=save">
  <?php echo \App\csrf_field(); ?>
  <?php if (!empty($student['id'])): ?>
    <input type="hidden" name="id" value="<?php echo intval($student['id']); ?>">
  <?php endif; ?>
  <input type="hidden" name="cohort_id" value="<?php echo intval($cohort_id ?? ($old['cohort_id'] ?? 0)); ?>">
  <label>First name<br><input type="text" name="first_name" value="<?php echo htmlspecialchars($student['first_name'] ?? ($old['first_name'] ?? '')); ?>" required></label><br><br>
  <label>Last name<br><input type="text" name="last_name" value="<?php echo htmlspecialchars($student['last_name'] ?? ($old['last_name'] ?? '')); ?>" required></label><br><br>
  <label>Email<br><input type="email" name="email" value="<?php echo htmlspecialchars($student['email'] ?? ($old['email'] ?? '')); ?>"></label><br><br>
  <button type="submit">Save</button>
  <a href="/?page=students&cohort_id=<?php echo intval($cohort_id ?? ($old['cohort_id'] ?? 0)); ?>">Cancel</a>
</form>
<?php if (!empty($student['id'])): ?>
  <form method="post" action="/?page=students&action=delete&id=<?php echo intval($student['id']); ?>&cohort_id=<?php echo intval($cohort_id); ?>" onsubmit="return confirm('Delete this student?');">
    <?php echo \App\csrf_field(); ?>
    <button type="submit" style="margin-top:8px;color:red">Delete Student</button>
  </form>
<?php endif; ?>
