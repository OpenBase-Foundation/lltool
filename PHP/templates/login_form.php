<h2>Login</h2>
<?php if (!empty($error)): ?><p style="color:red"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
<form method="post" action="/?page=login">
  <?php echo \App\csrf_field(); ?>
  <label>Email<br><input type="email" name="email" required></label><br><br>
  <label>Password<br><input type="password" name="password" required></label><br><br>
  <button type="submit">Login</button>
</form>

<?php if (isset($show_register_link) && $show_register_link): ?>
<p style="text-align:center;margin-top:20px">
  <a href="/?page=auth&action=register">Create an account</a>
</p>
<?php endif; ?>

