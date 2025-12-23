<?php
include __DIR__ . '/../partials/header.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>

<main>
  <div class="container">

    <div  style="margin-bottom:16px; text-align:center">
      <h1 style="margin:0">Moja lista želja</h1>
    </div>

    <?php if (empty($games)): ?>
      <div class="card card--pad">
        <p>Nema igara na listi želja.</p>
        <p style="margin-top:8px"><a class="btn--wishlist" href="<?= $baseUrl ?>/?page=games">← Nazad na listu igara</a></p>
      </div>
    <?php else: ?>
      <div class="card card--pad">
        <ul class="review-list">
          <?php foreach ($games as $g): ?>
            <?php
              $gid   = (int)($g['id'] ?? 0);
              $title = $g['title'] ?? ('Igra #' . $gid);

              
              $min = $g['min_players'] ?? null;
              $max = $g['max_players'] ?? null;
              $playersLabel = '';
              if ($min && $max)        $playersLabel = "{$min} do {$max} igrača";
              elseif ($min && !$max)   $playersLabel = "{$min}+ igrača";
              elseif (!$min && $max)   $playersLabel = "do {$max} igrača";
              elseif (!empty($g['players'])) $playersLabel = (int)$g['players'] . ' igrača';

             
              $time = isset($g['play_time']) ? ((int)$g['play_time'] . ' min') : '';
              $year = $g['year'] ?? '';
            ?>
            <li class="review-item" style="display:flex;gap:12px;align-items:flex-start;flex-wrap:wrap">
              <div style="flex:1 1 auto">
                <div style="margin-bottom:6px">
                  <strong><a href="<?= $baseUrl ?>/?page=game&id=<?= $gid ?>"><?= h($title) ?></a></strong>
                </div>
                <div class="meta" style="display:flex;gap:6px;flex-wrap:wrap">
                  <?php if ($playersLabel): ?><span class="badge">👥 <?= h($playersLabel) ?></span><?php endif; ?>
                  <?php if ($time): ?><span class="badge">⏱ <?= h($time) ?></span><?php endif; ?>
                  <?php if ($year): ?><span class="badge">📅 <?= h($year) ?>+</span><?php endif; ?>
                </div>
              </div>

              <div style="display:flex;gap:8px">
                <a class="btn--wishlist" href="<?= $baseUrl ?>/?page=game&id=<?= $gid ?>">Detalji</a>
                <form method="post" action="<?= $baseUrl ?>/?page=wishlist_toggle">
                  <input type="hidden" name="game_id" value="<?= $gid ?>">
                  <button class="btn--wishlist" type="submit">Ukloni</button>
                </form>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <p style="margin-top:12px"><a class="btn--wishlist" href="<?= $baseUrl ?>/?page=games">← Nazad na listu igara</a></p>
    <?php endif; ?>

  </div>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
