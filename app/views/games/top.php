<?php
include __DIR__ . '/../partials/header.php';
include __DIR__ . '/../partials/flash.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// helper za linkove paginacije / promenu kriterijuma
function topLink($overrides=[]){
  $params = [
    'page' => 'top',
    'by'   => $_GET['by'] ?? 'rating',
    'p'    => $_GET['p']  ?? 1,
  ];
  foreach($overrides as $k=>$v){ $params[$k]=$v; }
  return '?'.http_build_query($params);
}

$by = $_GET['by'] ?? 'rating';
$startIndex = (($filters['page']-1) * $filters['per']) + 1;
?>

<main>
  <div class="container">

 
    <div  style="margin-bottom:16px; text-align:center">
      <h1 style="margin:0 0 10px"><?= h($byTitle) ?></h1>
     
    </div>

    <?php if(empty($games)): ?>
      <div class="card card--pad">
        <p>Nema podataka.</p>
      </div>
    <?php else: ?>

      <!-- Lista top igara -->
      <div class="card card--pad">
        <ol start="<?= (int)$startIndex ?>" class="review-list">
          <?php foreach($games as $g): ?>
            <?php
               $avg = (float)($g['avg_rating'] ?? $g['rating'] ?? 0);
            $stars = (int)round($avg);
              $min   = $g['min_players'] ?? null;
                $max   = $g['max_players'] ?? null;
              $playersLabel = '';
              if ($min && $max)        $playersLabel = "{$min} do {$max} igrača";
              elseif ($min && !$max)   $playersLabel = "{$min}+ igrača";
              elseif (!$min && $max)   $playersLabel = "do {$max} igrača";
            ?>
            <li class="review-item" style="display:flex; gap:12px; align-items:flex-start; flex-wrap:wrap">
              <div class="stars" style="min-width:80px">
                <?php for($i=1;$i<=5;$i++) echo $i <= $stars ? '★' : '☆'; ?>
                <span class="badge"><?= number_format($avg, 1) ?></span>
              </div>

              <div style="flex:1 1 auto">
                <div style="margin-bottom:6px">
                  <strong><a href="<?= $baseUrl ?>/?page=game&id=<?= (int)$g['id'] ?>"><?= h($g['title']) ?></a></strong>
                </div>
                <div class="meta" style="display:flex; gap:6px; flex-wrap:wrap">
                  <span class="badge">📝 <?= (int)($g['reviews_count'] ?? 0) ?></span>
                  <?php if($playersLabel): ?><span class="badge">👥 <?= h($playersLabel) ?></span><?php endif; ?>
                  <?php if(!empty($g['play_time'])): ?><span class="badge">⏱ <?= (int)$g['play_time'] ?> min</span><?php endif; ?>
                  <?php if(!empty($g['year'])): ?><span class="badge">📅 <?= h($g['year']) ?>+</span><?php endif; ?>
                </div>
              </div>

              <div>
                <a class="btn" href="<?= $baseUrl ?>/?page=game&id=<?= (int)$g['id'] ?>">Detalji</a>
              </div>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>

      <!-- Paginacija -->
      <?php if (($filters['pages'] ?? 1) > 1): ?>
        <div class="card card--pad" style="display:flex; justify-content:space-between; align-items:center">
          <div>
            Strana <?= (int)$filters['page'] ?> / <?= (int)$filters['pages'] ?>
            <span class="badge">ukupno: <?= (int)$filters['total'] ?></span>
          </div>
          <div style="display:flex; gap:8px">
            <?php if($filters['page'] > 1): ?>
              <a class="btn" href="<?= topLink(['p'=>$filters['page']-1]) ?>">← Prethodna</a>
            <?php endif; ?>
            <?php if($filters['page'] < $filters['pages']): ?>
              <a class="btn" href="<?= topLink(['p'=>$filters['page']+1]) ?>">Sledeća →</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

    <?php endif; ?>

    <p><a class="btn" href="<?= $baseUrl ?>/?page=games">← Nazad na listu</a></p>
  </div>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
