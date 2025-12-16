<h2>Cohorts</h2>
<a href="/?page=cohorts&action=new">+ New Cohort</a>
<ul>
<?php foreach ($cohorts as $c): ?>
  <li>
    <a href="/?page=students&cohort_id=<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></a>
    — <a href="/?page=cohorts&action=edit&id=<?php echo $c['id']; ?>">Edit</a>
  </li>
<?php endforeach; ?>
</ul>
