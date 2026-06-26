<section class="page-title">
  <h1>Login</h1>
  <p>Don't have an account? <a class="text-link" href="?page=create-account">Sign up here.</a></p>
</section>
<section class="form-panel">
  <div class="panel-body">
    <h2>Login</h2>
    <p>Masuk untuk melihat status pesanan Anda dan simpan detail checkout lebih cepat.</p>
    <form id="login-form" method="post" action="api.php?action=login">
      <div class="field"><label for="login-email">Email</label><input id="login-email" name="email" type="email" placeholder="Email"></div>
      <div class="field"><label for="login-password">Password</label><input id="login-password" name="password" type="password" placeholder="Password"></div>
      <div class="form-actions">
        <button class="wide-button primary" type="button">Sign in</button>
        <a class="text-link" href="?page=reset-password">Forgot your password?</a>
      </div>
      <div style="margin-top:24px;"><a class="button-secondary" href="?page=home" style="display:inline-flex;">Return to store</a></div>
    </form>
  </div>
</section>
