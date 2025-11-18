<h2>Login</h2>
<form method="post" action="/?page=auth/do_login">
  <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
  <label>Username or Email<br><input name="username"></label><br>
  <label>Password<br><input type="password" name="password"></label><br>
  <button type="submit">Login</button>
</form>
<p>Belum punya akun? <a href="/?page=auth/register">Register</a></p>
