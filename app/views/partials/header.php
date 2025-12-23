<?php

$loggedIn = isset($_SESSION['user']);
$username = $loggedIn ? ($_SESSION['user']['username'] ?? $_SESSION['user']['name'] ?? 'Korisnik') : null;


$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// Admin detekcija
$isAdmin = $loggedIn && (
  (($_SESSION['user']['role'] ?? '') === 'admin') ||
  !empty($_SESSION['user']['is_admin'])
);
?>
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/styles.css?v=footerfix1">

<nav class="nav" role="navigation" aria-label="Glavna navigacija">
  <div class="inner">
    <!-- Hamburger -->
    <button class="burger" id="burgerBtn" aria-label="Otvori meni" aria-controls="sideDrawer" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

    
    <a class="brand" href="<?= $baseUrl ?>/?page=games">
      <span class="dice" aria-hidden="true">🎲</span>
      <span class="brand-text">Društvene igre</span>
    </a>

   
    <div class="auth">
      <?php if(!$loggedIn): ?>
        <a class="btn" href="<?= $baseUrl ?>/?page=login">Prijava</a>
        <a class="btn solid" href="<?= $baseUrl ?>/?page=register">Registracija</a>
      <?php else: ?>
        <a class="badge" href="<?= $baseUrl ?>/?page=profile">
          👤 <?= htmlspecialchars($username) ?>
        </a>
        <a class="btn solid" href="<?= $baseUrl ?>/?page=logout">Odjava</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<?php include __DIR__ . '/flash.php'; ?>


<div class="drawer-backdrop" id="drawerBackdrop" hidden></div>
<aside class="drawer" id="sideDrawer" aria-hidden="true">
  <div class="drawer-head">
    <div class="brand">
      <span class="dice" aria-hidden="true">🎲</span>
      <span class="brand-text">Društvene igre</span>
    </div>
    <button class="drawer-close" id="drawerCloseBtn" aria-label="Zatvori meni">✕</button>
  </div>

  <nav class="drawer-nav">
    <a href="<?= $baseUrl ?>/?page=games">Sve igre</a>
    <a href="<?= $baseUrl ?>/?page=top">Recenzije</a>

    <?php if ($loggedIn): ?>
      <a href="<?= $baseUrl ?>/?page=wishlist">Lista želja</a>
      <a href="<?= $baseUrl ?>/?page=events">Događaji</a>
      <?php if ($isAdmin): ?>
        <a href="<?= $baseUrl ?>/?page=admin_games">Dodaj igru</a>
      <?php endif; ?>
    <?php endif; ?>
  </nav>
</aside>

<style>
 
  .nav .inner { display:flex; align-items:center; justify-content:space-between; gap:12px; }
  .brand { display:flex; align-items:center; gap:8px; color:#fff; text-decoration:none; white-space:nowrap; }
  .brand .dice { display:inline-grid; place-items:center; width:28px; height:28px; border-radius:8px; background:#fff; color:#120e3b; font-weight:800; }
  .auth { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }

  
  @media (max-width: 560px){
    .brand .brand-text { display:none; }
  }
 
  @media (max-width: 440px){
    .auth .user-name { display:none; }
  }
 
  @media (max-width: 360px){
    .auth { gap:6px; }
  }
</style>

<script>
(function(){
  const burger   = document.getElementById('burgerBtn');
  const drawer   = document.getElementById('sideDrawer');
  const backdrop = document.getElementById('drawerBackdrop');
  const closeBtn = document.getElementById('drawerCloseBtn');

  function openDrawer(){
    drawer.classList.add('open');
    backdrop.hidden = false;
    document.body.classList.add('no-scroll');
    burger && burger.setAttribute('aria-expanded','true');
    drawer.setAttribute('aria-hidden','false');
  }
  function closeDrawer(){
    drawer.classList.remove('open');
    backdrop.hidden = true;
    document.body.classList.remove('no-scroll');
    burger && burger.setAttribute('aria-expanded','false');
    drawer.setAttribute('aria-hidden','true');
  }

  burger   && burger.addEventListener('click', openDrawer);
  closeBtn && closeBtn.addEventListener('click', closeDrawer);
  backdrop && backdrop.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closeDrawer(); });
})();
</script>
