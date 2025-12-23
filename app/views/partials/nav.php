<nav>
  <a href="?page=home">Početna</a>
  <a href="?page=games">Igre</a>
  <?php if(isset($_SESSION['user'])): ?>
    <span>👤 <?=htmlspecialchars($_SESSION['user']['username'], ENT_QUOTES, 'UTF-8')?> (<?= $_SESSION['user']['role']?>)</span>
    <?php if($_SESSION['user']['role']==='admin'): ?>
      <a href="?page=admin">Admin</a>
    <?php endif; ?>
    <a href="?page=logout">Odjava</a>
  <?php else: ?>
    <a href="?page=login">Prijava</a>
    <a href="?page=register">Registracija</a>
    <a href="?page=games">Igre</a>
    <a href="?page=top&by=rating">Top ocenjene</a>
    <a href="?page=top&by=comments">Najkomentarisanije</a>

  <?php endif; ?>
</nav>
