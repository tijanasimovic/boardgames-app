<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container-2">
    
  <form method="post" action="?page=event_save" class="card form-card">
    <div class="form-head">
      <h1 class="form-title">Novi događaj</h1>
      <?php if(!empty($error)): ?>
        <div class="alert alert-danger" role="alert"><?=$error?></div>
      <?php endif; ?>
    </div>

    <div class="form-body form-grid">
      <!-- Naslov -->
      <div class="form-group span-2">
        <label for="title">Naslov <span class="req">*</span></label>
        <input id="title" class="input" name="title" required autofocus placeholder="Kratak, prepoznatljiv opis">

      </div>

      <!-- Opis -->
      <div class="form-group span-2">
        <label for="description">Opis</label>
        <textarea id="description" class="input" name="description" rows="3" placeholder="Detalji: format partije, šta doneti, napomene…"></textarea>
      </div>

      <!-- Online toggle -->
      <div class="form-group span-2 inline">
        <input type="checkbox" id="is_online" name="is_online">
        <label for="is_online" class="inline-label">Online događaj</label>
      </div>

      <!-- Lokacija / Online URL -->
      <div class=".form-group" id="grp_location">
        <label for="location"><span>Lokacija</span></label>
        <input id="location" class="input" name="location" placeholder="Adresa / mesto">
  
      </div>

      <div class="form-group" id="grp_online">
        <label for="online_url">Online URL</label>
        <input id="online_url" class="input" name="online_url" placeholder="https://…">
     
      </div>
            <!-- Kapacitet / Igra -->
      <div class=".form-group">
        <label for="capacity">Kapacitet</label>
        <input id="capacity" class="input" type="number" name="capacity" min="1" placeholder="npr. 4">

      </div>
      <!-- Vreme -->
      <div class="form-group">
        <label for="starts_at">Početak <span class="req">*</span></label>
        <input id="starts_at" class="input" type="datetime-local" name="starts_at" required>
      </div>

      <div class="form-group">
        <label for="ends_at">Kraj</label>
        <input id="ends_at" class="input" type="datetime-local" name="ends_at">
      </div>

  

      <div class="form-group">
        <label for="game_id">Igra iz baze</label>
        <select id="game_id" class="input" name="game_id">
          <option value="">— Izaberi igru —</option>
          <?php foreach($games as $g): ?>
            <option value="<?=$g['id']?>"><?=htmlspecialchars($g['title'])?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-foot">
      <a class="btn btn--ghost" href="?page=events">Otkaži</a>
      <button class="btn btn--primary">Sačuvaj</button>
    </div>
  </form>
</div>

<style>
  
  .container-2{max-width:900px;margin:0 auto;padding:24px}

  .form-card { padding: 0; overflow: hidden; }
  .form-head { padding: 18px 20px 0; }
  .form-title { margin: 0; }
  .form-body { padding: 16px 20px; }
  .form-foot { display:flex; gap:12px; justify-content:flex-end; padding:12px 20px 18px; border-top:1px solid rgba(0,0,0,.08); }

  .form-grid { display:grid; grid-template-columns: 1fr; gap: 14px 16px; }
  .form-group { display:flex; flex-direction:column; gap:6px; }
  .form-group.inline { flex-direction:row; align-items:center; gap:10px; }
  .inline-label { margin: 0; }
  .req { color:#d33; }


  @media (min-width: 820px) {
    .form-grid { grid-template-columns: 1fr 1fr; }
    .form-group.span-2 { grid-column: span 2; }
  }
  #location {width:400px; margin: 6px 0 0 0}
  #online_url {width:400px; margin: 6px 0 0 0}
  #capacity {width:400px; margin: 6px 0 0 0}
  
  .btn.btn--ghost { background:transparent; border:1px solid rgba(0,0,0,.15); }
</style>

<script>
  (function(){
    const cb = document.getElementById('is_online');
    const grpLoc = document.getElementById('grp_location');
    const grpOn  = document.getElementById('grp_online');
    function sync(){ if(cb.checked){ grpLoc.style.display='none'; grpOn.style.display='block'; }
    else  { grpLoc.style.display='block'; grpOn.style.display='none'; } }
    cb.addEventListener('change', sync);
    sync();
  })();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
