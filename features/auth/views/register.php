<h2>Register</h2>
<form method="post" action="/?page=auth/do_register">
  <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
  <label>Username<br><input name="username"></label><br>
  <label>Email<br><input name="email"></label><br>
  <label>Password<br><input type="password" name="password"></label><br>
  <button type="submit">Register</button>
</form>
<p>Sudah punya akun? <a href="/?page=auth/login">Login</a></p>
