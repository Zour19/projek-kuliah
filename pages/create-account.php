<section class="page-title">
  <h1>Create Account</h1>
  <p>Sudah punya akun? <a class="text-link" href="?page=login">Sign in here.</a></p>
</section>
<section class="form-panel">
  <div class="panel-body">
    <h2>Create Account</h2>
    <p>Daftar sekarang untuk menikmati layanan pengiriman bunga cepat dan personalisasi ucapan gratis.</p>
    <form id="create-account-form" method="post" action="api.php?action=create_account">
      <div class="field"><label for="account-username">Username</label><input id="account-username" name="username" type="text" placeholder="Username" required></div>
      <div class="field"><label for="account-email">Email</label><input id="account-email" name="email" type="email" placeholder="Email" required></div>
      <div class="field"><label for="account-password">Password</label><input id="account-password" name="password" type="password" placeholder="Password" required></div>
      <button class="wide-button primary" type="submit">Create Account</button>
      <div id="create-account-message" style="margin-top:18px;"></div>
    </form>
  </div>
</section>
