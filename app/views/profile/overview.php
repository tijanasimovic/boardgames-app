<?php
include __DIR__ . '/../partials/header.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>

<main>
  <div class="container">

  
    <div class="card card--pad" style="margin-bottom:16px">
      <h1 style="margin:0 0 8px">Moj profil</h1>
      <div><strong><?= h($_SESSION['user']['username']) ?></strong> — uloga: <?= h($_SESSION['user']['role']) ?></div>
      <div class="meta" style="margin-top:8px">
        <span class="badge">📝 Recenzije: <strong><?= (int)($stats['reviews_count'] ?? 0) ?></strong></span>
        <?php if(isset($stats['avg_rating']) && $stats['avg_rating'] !== null): ?>
          <span class="badge">⭐ Prosek: <strong><?= h($stats['avg_rating']) ?></strong></span>
        <?php endif; ?>
        <span class="badge">🧡 Wishlist: <strong><?= (int)($stats['wishlist_count'] ?? 0) ?></strong></span>
      </div>

      <?php if (!empty($achievements)): ?>
        <div class="achievements">
          <h4 class="ach-title">Bedževi</h4>
          <ul class="ach-list">
            <?php foreach ($achievements as $a): ?>
              <li class="ach" title="<?= h($a['tip']) ?>">
                <span class="ach-ico"><?= $a['icon'] ?></span>
                <span class="ach-txt"><?= h($a['text']) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>

    <!-- Dve kolone -->
    <div class="profile-grid">
      <!-- Moje recenzije -->
      <section class="card card--pad">
        <h3 class="admin-form-title" style="margin-bottom:10px">Moje recenzije</h3>

        <?php if (empty($myReviews)): ?>
          <p>Nema recenzija.</p>
        <?php else: ?>
          <ul class="review-list">
            <?php foreach ($myReviews as $r): ?>
              <?php
                $title   = $r['title'] ?? ($r['game_title'] ?? ('Igra #' . (int)($r['game_id'] ?? 0)));
                $rating  = (int)($r['rating'] ?? 0);
                $comment = (string)($r['comment'] ?? '');
                $created = isset($r['created_at']) && $r['created_at'] !== null
                           ? date('d.m.Y H:i', strtotime($r['created_at'])) : '';
                $gid     = (int)($r['game_id'] ?? 0);
              ?>
              <li class="review-item">
                <div class="stars">
                  <?php for($i=1;$i<=5;$i++) echo $i <= $rating ? '★' : '☆'; ?>
                </div>
                <div>
                  <strong><a href="<?= $baseUrl ?>/?page=game&id=<?= $gid ?>"><?= h($title) ?></a></strong>
                  <?php if ($created): ?> · <small><?= h($created) ?></small><?php endif; ?>
                </div>
                <p><?= nl2br(h($comment)) ?></p>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <!-- Moja lista želja -->
      <section class="card card--pad">
        <h3 class="admin-form-title" style="margin-bottom:10px">Moja lista želja</h3>

        <?php if (empty($myWishlist)): ?>
          <p>Nema igara na listi želja.</p>
        <?php else: ?>
          <ul class="review-list">
            <?php foreach ($myWishlist as $g): ?>
              <?php
                $gid   = (int)($g['id'] ?? 0);
                $title = $g['title'] ?? ('Igra #' . $gid);
                // players badge
                $min = $g['min_players'] ?? null; $max = $g['max_players'] ?? null;
                $playersLabel = '';
                if ($min && $max)        $playersLabel = "{$min} do {$max} igrača";
                elseif ($min && !$max)   $playersLabel = "{$min}+ igrača";
                elseif (!$min && $max)   $playersLabel = "do {$max} igrača";
                elseif (!empty($g['players'])) $playersLabel = (int)$g['players'] . ' igrača';
                $time = isset($g['play_time']) ? ((int)$g['play_time'].' min') : '';
                $year = $g['year'] ?? '';
              ?>
              <li class="review-item">
                <div>
                  <strong><a href="<?= $baseUrl ?>/?page=game&id=<?= $gid ?>"><?= h($title) ?></a></strong>
                </div>
                <div class="meta" style="margin:6px 0">
                  <?php if($playersLabel): ?><span class="badge">👥 <?= h($playersLabel) ?></span><?php endif; ?>
                  <?php if($time): ?><span class="badge">⏱ <?= h($time) ?></span><?php endif; ?>
                  <?php if($year): ?><span class="badge">📅 <?= h($year) ?>+</span><?php endif; ?>
                </div>
                <form method="post" action="<?= $baseUrl ?>/?page=wishlist_toggle" style="display:inline-block">
                  <input type="hidden" name="game_id" value="<?= $gid ?>">
                  <button class="btn" type="submit">Ukloni</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    </div>
  </div>
</main>

<style>
 
  .profile-grid{ display:grid; gap:16px; grid-template-columns:1fr; }
  @media (min-width: 980px){ .profile-grid{ grid-template-columns:1fr 1fr; } }

  .achievements { margin-top: 10px; }
  .ach-title { margin: 0 0 8px; font-size: 14px; color: #333; }

  .ach-list {
    display:flex; flex-wrap:wrap; gap:8px;
    list-style:none; margin:0; padding:0;
  }
  .ach {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 10px; border-radius:999px;
    background:rgba(99,102,241,.10);
    border:1px solid rgba(99,102,241,.22);
    color:#2f2e7a; font-size:13px; cursor:default;
    transition:transform .08s ease, box-shadow .12s ease;
  }
  .ach:hover{ transform:translateY(-1px); box-shadow:0 6px 16px rgba(0,0,0,.08); }
  .ach-ico { font-size:15px; line-height:1; }
  .ach-txt { white-space:nowrap; }
</style>

<?php include __DIR__ . '/../partials/footer.php'; ?>
