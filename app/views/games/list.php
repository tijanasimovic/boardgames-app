<style>

  h1.page-title{
    font-family: inherit;        
    font-weight: 700;            
    font-size: 36px;             
    line-height: 1.2;
    text-align: center;          
    margin: 0 0 16px 0;        
  }

  
</style>

<?php
include __DIR__ . '/../partials/header.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }


function asset_url(?string $path): string {
  $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
  if (!$path || trim($path) === '') return $baseUrl . '/assets/images/placeholder.jpg';
  if (preg_match('~^https?://~i', $path)) return $path;
  if ($path[0] === '/') return $path;
  return $baseUrl . '/' . ltrim($path, '/');
}

$filters = [
  'q'          => $_GET['q']          ?? '',
  // sada očekujemo niz ID-eva; ako nije niz, pretvori
  'genre_id'   => (function(){
      $v = $_GET['genre_id'] ?? [];
      if (!is_array($v)) $v = $v!=='' ? [$v] : [];
      return array_values(array_unique(array_map('strval', $v)));
  })(),
  'rating_min' => $_GET['rating_min'] ?? '',
  'sort'       => $_GET['sort']       ?? '',
];

$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$selectedGenres = $filters['genre_id'];
?>
<style>
  /* thumbnail da se slika vidi */
  .card .thumb{ width:100%; height:180px; background-size:cover; background-position:center; background-color:#eee; border-top-left-radius:12px; border-top-right-radius:12px; }
  .card .stars{ font-size:18px; line-height:1; color:#f5a623; }
  .card .stars .muted{ color:#999; font-size:13px; margin-left:6px; }

  
  .genre-dropdown { position: relative; display:inline-block; width: 100%; }
  .genre-button { width:100%; display:flex; align-items:center; justify-content:space-between; gap:8px;
                  padding:10px 12px; border:1px solid #ddd; border-radius:12px; background:#fff; cursor:pointer; min-height:44px; }
  .genre-button span.badge { display:inline-block; background:#f5f5f5; border:1px solid #e5e5e5; border-radius:999px; padding:2px 8px; font-size:12px; margin-left:6px; }
  .genre-menu { position:absolute; z-index:1000; inset:auto 0 auto 0; margin-top:6px; background:#fff; border:1px solid #ddd; border-radius:12px;
                box-shadow:0 10px 25px rgba(0,0,0,.07); padding:8px; display:none; max-height:260px; overflow:auto; }
  .genre-menu.open { display:block; }
  .genre-menu .row { display:flex; align-items:center; gap:8px; padding:6px 8px; border-radius:8px; }
  .genre-menu .row:hover { background:#f7f7f7; }
  .genre-actions { display:flex; justify-content:space-between; align-items:center; padding:8px; border-top:1px solid #eee; }
  .link-reset { background:none; border:none; color:#555; font-size:13px; cursor:pointer; text-decoration:underline; padding:4px 6px; }
  .filters .input, .filters .select, .genre-button { min-width: 180px; }
  @media (min-width: 700px){ .filters{ display:grid; grid-template-columns:1fr 1fr 1fr 1fr auto; gap:16px; } }
</style>

<div class="container">
  <h1 class="page-title">Sve igre</h1>

  <form class="filters" method="get" action="<?= $baseUrl ?>/">
    <input type="hidden" name="page" value="games">

    <input class="input" type="text" name="q" placeholder="Pretraži igre..." value="<?= h($filters['q']) ?>">


    <div class="genre-dropdown" data-role="genre-dropdown">
      <button class="genre-button" type="button" aria-haspopup="listbox" aria-expanded="false">
        <div>
          <span class="label">Žanrovi</span>
          <?php if (!empty($selectedGenres)): ?>
            <?php
              $labels = [];
              if (!empty($genres)) {
                
                $map = [];
                foreach ($genres as $gg) { $map[(string)$gg['id']] = $gg['name']; }
                foreach ($selectedGenres as $gid) {
                  if (isset($map[$gid])) $labels[] = $map[$gid];
                }
              }
              $shown = array_slice($labels, 0, 2);
            ?>
            <?php foreach ($shown as $lab): ?><span class="badge"><?= h($lab) ?></span><?php endforeach; ?>
            <?php if (count($labels) > 2): ?><span class="badge">+<?= count($labels)-2 ?></span><?php endif; ?>
          <?php else: ?>
            <span class="badge">Sve kategorije</span>
          <?php endif; ?>
        </div>
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10l5 5 5-5H7z"></path></svg>
      </button>

      <div class="genre-menu" role="listbox" aria-multiselectable="true">
        <?php if (!empty($genres)): ?>
          <?php foreach ($genres as $g): 
            $gid = (string)$g['id'];
            $checked = in_array($gid, $selectedGenres, true) ? 'checked' : '';
          ?>
            <label class="row">
              <input type="checkbox" value="<?= (int)$g['id'] ?>" <?= $checked ?> data-genre-checkbox>
              <span><?= h($g['name']) ?></span>
            </label>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="row"><em>Nema žanrova</em></div>
        <?php endif; ?>
        <div class="genre-actions">
          <button type="button" class="link-reset" data-genre-clear>Očisti</button>
         
          <input type="hidden" name="genre_id[]" value="" disabled data-genre-hidden-prototype>
        </div>
      </div>
    </div>

    <select class="select" name="rating_min">
      <?php
        $opts = ['' => 'Sve ocene', '4'=>'Ocena 4+','3'=>'Ocena 3+','2'=>'Ocena 2+','1'=>'Ocena 1+'];
        foreach($opts as $val=>$label):
      ?>
        <option value="<?= h($val) ?>" <?= (string)$filters['rating_min']===(string)$val?'selected':'' ?>>
          <?= h($label) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <select class="select" name="sort">
      <?php
        $sorts = [
          '' => 'Sortiraj',
          'rating_desc' => 'Ocena ↓',
          'rating_asc'  => 'Ocena ↑',
          'name_asc'    => 'Naziv A-Z',
          'name_desc'   => 'Naziv Z-A'
        ];
        foreach($sorts as $val=>$label):
      ?>
        <option value="<?= h($val) ?>" <?= (string)$filters['sort']===$val?'selected':'' ?>>
          <?= h($label) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <button class="btn" type="submit">Primeni</button>
  </form>

  <?php if(empty($games)): ?>
    <p style="text-align:center;color:#666;margin-top:24px">Nema rezultata za zadate filtere.</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach($games as $g): ?>
        <?php
          $href   = $baseUrl . "/?page=game&id=".(int)$g['id'];
          $img    = asset_url($g['image_url'] ?? null);
          $rating = isset($g['rating']) ? (float)$g['rating'] : 0.0;
          $stars  = (int)round(max(0.0, min(5.0, $rating)));
          $desc   = $g['short_desc'] ?? ($g['description'] ?? '');
          if (function_exists('mb_strlen') && mb_strlen($desc) > 110) {
            $desc = mb_substr($desc,0,110).'…';
          } elseif (strlen($desc) > 110) {
            $desc = substr($desc,0,110).'…';
          }
        ?>
        <article class="card" data-href="<?= $href ?>" tabindex="0" role="link" aria-label="Otvori detalje za <?= h($g['title']) ?>">
          <a href="<?= $href ?>" aria-label="Detalji za <?= h($g['title']) ?>">
            <div class="thumb" style="background-image:url('<?= h($img) ?>')"></div>
          </a>
          <div class="body">
            <div class="title"><a href="<?= $href ?>"><?= h($g['title']) ?></a></div>
            <div class="stars" aria-label="Ocena <?= (int)$stars ?> od 5">
              <?php for($i=1;$i<=5;$i++): ?><?= $i <= $stars ? '★' : '☆' ?><?php endfor; ?>
              <span class="muted">(<?= number_format($rating, 1) ?>)</span>
            </div>
            <p class="desc"><?= h($desc) ?></p>
            <div class="actions"><a class="btn btn--primary" href="<?= $href ?>">Detalji</a></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
 
  document.querySelectorAll('.card').forEach(c=>{
    c.addEventListener('click', e=>{
      if(e.target.closest('a,button')) return;
      const href = c.getAttribute('data-href');
      if(href) location.href = href;
    });
    c.addEventListener('keydown', e=>{
      if((e.key === 'Enter' || e.key === ' ') && !e.target.closest('a,button')) {
        const href = c.getAttribute('data-href');
        if(href){ e.preventDefault(); location.href = href; }
      }
    });
  });

  
  const dd = document.querySelector('[data-role="genre-dropdown"]');
  if (dd){
    const btn   = dd.querySelector('.genre-button');
    const menu  = dd.querySelector('.genre-menu');
    const proto = dd.querySelector('[data-genre-hidden-prototype]');

    
    const form = dd.closest('form');
    form.addEventListener('submit', () => {
      form.querySelectorAll('input[type="hidden"][name="genre_id[]"]').forEach(h=>{
        if (h !== proto) h.remove();
      });
      
      dd.querySelectorAll('[data-genre-checkbox]:checked').forEach(ch=>{
        const hid = proto.cloneNode(true);
        hid.disabled = false;
        hid.value = ch.value;
        form.appendChild(hid);
      });
    });


    btn.addEventListener('click', (e)=>{
      e.preventDefault();
      const open = menu.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('click', (e)=>{
      if (!dd.contains(e.target)) {
        menu.classList.remove('open');
        btn.setAttribute('aria-expanded','false');
      }
    });

    
    const clearBtn = dd.querySelector('[data-genre-clear]');
    clearBtn.addEventListener('click', ()=>{
      dd.querySelectorAll('[data-genre-checkbox]').forEach(ch=> ch.checked=false);
    });
  }
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
