<h2>Students</h2>
<?php if (empty($students)): ?>
  <p>No students found for this cohort.</p>
<?php else: ?>
  <a href="/?page=students&action=new&cohort_id=<?php echo intval($_GET['cohort_id'] ?? 0); ?>">+ New Student</a>
  <ul>
    <?php foreach ($students as $s): ?>
      <li>
        <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?> (<?php echo htmlspecialchars($s['email']); ?>)
        — <a href="/?page=students&action=edit&id=<?php echo intval($s['id']); ?>&cohort_id=<?php echo intval($_GET['cohort_id'] ?? 0); ?>">Edit</a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
