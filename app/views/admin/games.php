<?php
include __DIR__ . '/../partials/header.php';
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }


function asset_url_admin(string $baseUrl, ?string $path): string {
  $p = (string)($path ?? '');
  if ($p !== '' && preg_match('~^https?://~i', $p)) return $p;                 // već pun URL
  if ($p !== '') return $baseUrl . '/' . ltrim($p, '/');                      
  return $baseUrl . '/assets/default_game.png';                                    
}
?>
<style>
  /* thumbnail u tabeli */
  .thumb-xs{
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 8px;
    background: #eee;
  }
</style>

<div class="container">

  <h1 class="page-title" style="font-family: inherit;
    font-weight: 700;
    font-size: 36px;
    line-height: 1.2;
    text-align: center;
    margin: 8px 0 16px 0;">Admin — Igre</h1>

  <div class="admin-wrap">
    <!-- Forma -->
    <form method="post" class="card admin-form">
      <h3 class="admin-form-title">Dodaj / Izmeni igru</h3>

      <input type="hidden" name="id" id="id">

      <div class="form-group">
        <label for="title">Naslov</label>
        <input class="input" type="text" id="title" name="title" required>
      </div>

      <div class="form-group">
        <label for="description">Opis</label>
        <textarea class="input" id="description" name="description" rows="4"></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="min_players">Min igrača</label>
          <input class="input" type="number" id="min_players" name="min_players" value="1">
        </div>
        <div class="form-group">
          <label for="max_players">Maks igrača</label>
          <input class="input" type="number" id="max_players" name="max_players" value="4">
        </div>
        <div class="form-group">
          <label for="play_time">Trajanje (min)</label>
          <input class="input" type="number" id="play_time" name="play_time" value="60">
        </div>
        <div class="form-group">
          <label for="year">Uzrast</label>
          <input class="input" type="number" id="year" name="year" value="0">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="image_path">Putanja do slike (npr. <code>uploads/box.jpg</code> ili puni URL)</label>
          <input class="input" type="text" id="image_path" name="image_path" placeholder="uploads/moja-slika.jpg">
        </div>

        <div class="form-group">
          <label for="genres">Žanrovi (Ctrl/klik za više)</label>
          <select class="input" id="genres" name="genres[]" multiple size="6">
            <?php foreach($allGenres as $gen): ?>
              <option value="<?= (int)$gen['id'] ?>"><?= h($gen['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn" type="submit">Sačuvaj</button>
        <button class="btn btn-secondary" type="reset">Poništi</button>
      </div>
    </form>

    <!-- Lista igara -->
    <div class="card admin-list">
      <h3 class="admin-form-title">Sve igre</h3>

      <?php if(empty($games)): ?>
        <p class="muted">Nema unosa.</p>
      <?php else: ?>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Slika</th>
              <th>Naslov</th>
              <th>Igrači</th>
              <th>Vreme</th>
              <th>Uzrast</th>
              <th>Žanrovi</th>
              <th style="width:160px">Akcije</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($games as $g): ?>
            <?php
              $genreIds = implode(',', array_map(fn($x)=>$x['id'], $g['_genres'] ?? []));
              $imgSrc   = asset_url_admin($baseUrl, $g['image_path'] ?? null);
            ?>
            <tr>
              <td><img src="<?= h($imgSrc) ?>" alt="" class="thumb-xs"></td>
              <td><?= h($g['title']) ?></td>
              <td><?= (int)$g['min_players'] ?>–<?= (int)$g['max_players'] ?></td>
              <td><?= (int)$g['play_time'] ?> min</td>
              <td><?= (int)$g['year'] ?>+</td>
              <td class="tags">
                <?php if(!empty($g['_genres'])): ?>
                  <?php foreach($g['_genres'] as $gen): ?>
                    <span class="tag"><?= h($gen['name']) ?></span>
                  <?php endforeach; ?>
                <?php else: ?>
                  — 
                <?php endif; ?>
              </td>
              <td class="actions">
                <a class="btn btn-sm danger"
                   href="<?= $baseUrl ?>/?page=admin_games&delete=<?= (int)$g['id'] ?>"
                   onclick="return confirm('Obrisati igru #<?= (int)$g['id'] ?>?')">Obriši</a>

                <!-- EDIT bez inline JS: podaci idu u data-* atribute -->
                <a class="btn btn-sm edit-btn"
                   href="#"
                   data-id="<?= (int)$g['id'] ?>"
                   data-title="<?= h($g['title']) ?>"
                   data-description="<?= h($g['description']) ?>"
                   data-min="<?= (int)$g['min_players'] ?>"
                   data-max="<?= (int)$g['max_players'] ?>"
                   data-time="<?= (int)$g['play_time'] ?>"
                   data-year="<?= (int)$g['year'] ?>"
                   data-image="<?= h($g['image_path'] ?? '') ?>"
                   data-genres="<?= h($genreIds) ?>"
                >Uredi</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>

document.addEventListener('click', function(e){
  const btn = e.target.closest('.edit-btn');
  if (!btn) return;

  e.preventDefault();
  const f = document.querySelector('.admin-form');
  if (!f) return;

  f.querySelector('#id').value           = btn.dataset.id || '';
  f.querySelector('#title').value        = btn.dataset.title || '';
  f.querySelector('#description').value  = btn.dataset.description || '';
  f.querySelector('#min_players').value  = btn.dataset.min || '';
  f.querySelector('#max_players').value  = btn.dataset.max || '';
  f.querySelector('#play_time').value    = btn.dataset.time || '';
  f.querySelector('#year').value         = btn.dataset.year || '';

  const img = f.querySelector('#image_path');
  if (img) img.value = btn.dataset.image || '';

  const sel = f.querySelector('#genres');
  if (sel) {
    const ids = (btn.dataset.genres || '').split(',').filter(Boolean);
    for (const opt of sel.options) opt.selected = ids.includes(String(opt.value));
  }

  f.scrollIntoView({behavior:'smooth', block:'start'});
});
</script>


<?php include __DIR__ . '/../partials/footer.php'; ?>


<style>
.admin-table {
  border-collapse: collapse;   
  border-spacing: 0;           
}

.admin-table th,
.admin-table td {
  border-bottom: 1px solid #e9e9ef;
  padding: 10px 12px;
  vertical-align: middle;
  text-align: left;
}


.admin-table td.actions {
  display: table-cell !important;
  white-space: nowrap;         
  padding: 10px 12px;
}


.admin-table td.actions .btn,
.admin-table td.actions .btn-sm {
  margin-top: 0;
  margin-bottom: 0;
}


.admin-table tbody tr:last-child th,
.admin-table tbody tr:last-child td {
  border-bottom: 0;
}
</style>