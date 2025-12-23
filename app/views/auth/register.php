<?php
include __DIR__ . '/../partials/header.php';
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<div class="container">
  <div class="auth-wrap">
    <h1 class="auth-title">Registracija</h1>
    <form method="post" action="<?= $baseUrl ?>/?page=register" class="auth-form" novalidate>
      <div class="form-group">
        <label for="username">Korisničko ime</label>
        <input class="input" type="text" id="username" name="username" required>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input class="input" type="email" id="email" name="email" required>
      </div>

      <div class="form-group">
        <label for="password">Lozinka</label>
        <input class="input" type="password" id="password" name="password" required>
      </div>

      <button class="btn btn-primary w-full" type="submit">Kreiraj nalog</button>

      <p class="auth-switch">
        Već imaš nalog?
        <a href="<?= $baseUrl ?>/?page=login">Prijava</a>
      </p>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
