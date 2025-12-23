<?php
include __DIR__ . '/../partials/header.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

$img   = $game['image_path'] ?? ($baseUrl.'/assets/images/placeholder.jpg');


$avg   = isset($agg['avg_rating']) ? (float)$agg['avg_rating'] : 0.0;
$stars = (int)round($avg);


$playersLabel = '';
if (!empty($game['min_players']) || !empty($game['max_players'])) {
    $min = isset($game['min_players']) && $game['min_players'] !== '' ? (int)$game['min_players'] : null;
    $max = isset($game['max_players']) && $game['max_players'] !== '' ? (int)$game['max_players'] : null;
    if ($min && $max)        $playersLabel = "$min do $max igrača";
    elseif ($min && !$max)   $playersLabel = "$min+ igrača";
    elseif (!$min && $max)   $playersLabel = "do $max igrača";
} elseif (!empty($game['players'])) {
    $playersLabel = (int)$game['players'] . ' igrača';
}


$timeLabel = '';
if (isset($game['play_time']) && $game['play_time'] !== '' && $game['play_time'] !== null) {
    $timeLabel = ((int)$game['play_time']).' min';
}


$genreBadges = [];
if (!empty($game['_genres']) && is_array($game['_genres'])) {
    foreach ($game['_genres'] as $g) {
        $name = is_string($g) ? $g
             : (is_array($g) ? ($g['name'] ?? $g['genre_name'] ?? (is_string(($g[0] ?? null)) ? $g[0] : '')) : '');
        $name = trim((string)$name);
        if ($name !== '') $genreBadges[] = $name;
    }
}
$genreBadges = array_slice(array_unique($genreBadges), 0, 6);


$reviews   = $reviews   ?? [];
$revCount  = isset($revCount) ? (int)$revCount : count($reviews);


$myReview   = $myReview   ?? null;
$myRating   = isset($myReview['rating'])  ? (int)$myReview['rating']  : 5;
$myComment  = isset($myReview['comment']) ? (string)$myReview['comment'] : '';

// URL iste strane za fallback redirect (bez #)
$selfUrl     = strtok($_SERVER['REQUEST_URI'] ?? ('?page=game&id='.(int)$game['id']), '#');
$redirectUrl = $selfUrl . '#reviews';
?>

<style>
  .detail .poster { border-radius: 6px; }
  .reviews-section { max-width: 920px; margin: 48px auto 0; padding-bottom: 64px; }
  .reviews-card {
    margin: 0 auto;
    padding: 20px 24px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,.10);
  }
  .reviews-title { margin: 0 0 12px; }
  .review-list { list-style: disc; margin: 0; padding-left: 22px; }
  .review-item { margin: 12px 0 20px; }

  .review-form label { display:block; font-weight:600; margin:6px 0 4px; }
  .review-form select, .review-form textarea {
    width: 100%; border: 1px solid #ddd; border-radius: 8px; padding: 10px 12px;
  }
  .alert.ok    { background:#e9f9ef; border-left:4px solid #2bbb5d; padding:8px 10px; margin-bottom:8px; }
  .alert.error { background:#fff2f2; border-left:4px solid #e74c3c; padding:8px 10px; margin-bottom:8px; }
  main { padding-bottom: 40px; }
</style>

<main>
  <div class="container">
    <div class="detail">
      <img class="poster" src="<?= h($img) ?>" alt="<?= h($game['title']) ?>">
      <div>
        <h1><?= h($game['title']) ?></h1>

        <div class="stars">
          <?php for($i=1;$i<=5;$i++): ?><?= $i <= $stars ? '★' : '☆' ?><?php endfor; ?>
          <span class="badge"><?= number_format($avg, 1) ?></span>
        </div>

        <div class="meta">
          <?php foreach ($genreBadges as $gn): ?>
            <span class="badge">🎭 <?= h($gn) ?></span>
          <?php endforeach; ?>
          <?php if($playersLabel): ?><span class="badge">👥 <?= h($playersLabel) ?></span><?php endif; ?>
          <?php if($timeLabel): ?><span class="badge">⏱ <?= h($timeLabel) ?></span><?php endif; ?>
          <?php if(!empty($game['year'])): ?><span class="badge">🔞 <?= h($game['year']) ?>+</span><?php endif; ?>
        </div>

        <p class="lead"><?= nl2br(h($game['description'] ?? '')) ?></p>

        <div class="section" style="display:flex;gap:10px;flex-wrap:wrap">
          <?php if(isset($_SESSION['user'])): ?>
            <form method="post" action="<?= $baseUrl ?>/?page=wishlist_toggle">
              <input type="hidden" name="game_id" value="<?= (int)$game['id'] ?>">
              <button class="btn" type="submit"><?= !empty($inWishlist) ? 'Ukloni iz želja' : 'Dodaj u želje' ?></button>
            </form>
            <a class="btn" href="#review-form">Napiši recenziju</a>
          <?php else: ?>
            <a class="btn" href="<?= $baseUrl ?>/?page=login">Prijavi se</a>
          <?php endif; ?>
          <a class="btn" href="<?= $baseUrl ?>/?page=games">← Nazad na listu</a>
        </div>
      </div>
    </div>
  </div>

  <section id="reviews" class="reviews-section">
    <?php if(isset($_SESSION['user'])): ?>
      <div class="reviews-card" style="margin-bottom:18px">
        <h3 class="reviews-title" id="review-form">Dodaj / izmeni svoju recenziju</h3>

        <div id="review-flash" style="display:none"></div>

        <form method="post" action="<?= $baseUrl ?>/?page=review_add" class="review-form" id="reviewForm">
          <input type="hidden" name="game_id" value="<?= (int)$game['id'] ?>">
          <input type="hidden" name="redirect" value="<?= h($redirectUrl) ?>">

          <label for="rating">Ocena (1-5)</label>
          <select id="rating" name="rating" required>
            <?php for ($i=5; $i>=1; $i--): ?>
              <option value="<?= $i ?>" <?= $i===$myRating ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
          </select>

          <label for="comment">Komentar</label>
          <textarea id="comment" name="comment" rows="3" maxlength="1000" placeholder="Podeli svoje utiske..." required><?= h($myComment) ?></textarea>

          <button class="btn" type="submit" style="margin-top:10px">Sačuvaj recenziju</button>
        </form>
      </div>
    <?php else: ?>
      <div class="reviews-card" style="margin-bottom:18px">
        <p>Za pisanje recenzije potrebno je da se <a class="btn" href="<?= $baseUrl ?>/?page=login">prijaviš</a>.</p>
      </div>
    <?php endif; ?>

    <div class="reviews-card">
      <h3 class="reviews-title">Recenzije (<?= $revCount ?>)</h3>
      <?php if (empty($reviews)): ?>
        <p>Nema recenzija za ovu igru.</p>
      <?php else: ?>
        <ul class="review-list">
          <?php foreach ($reviews as $r): ?>
            <li class="review-item">
              <div class="stars">
                <?php $rt = (int)$r['rating']; for ($i=1; $i<=5; $i++) echo $i <= $rt ? '★' : '☆'; ?>
              </div>
              <div>
                <strong><?= h($r['username'] ?? 'Korisnik') ?></strong> ·
                <small><?= h(date('d.m.Y H:i', strtotime($r['created_at']))) ?></small>
              </div>
              <p><?= nl2br(h($r['comment'])) ?></p>

              <?php if (isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'admin')): ?>
                <form method="post" action="<?= $baseUrl ?>/?page=review_delete" style="display:inline">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <input type="hidden" name="redirect" value="<?= h($redirectUrl) ?>">
                  <button type="submit" class="btn btn-sm danger" onclick="return confirm('Obrisati ovu recenziju?')">Obriši</button>
                </form>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('reviewForm');
  if (!form) return;

  const flashBox = document.getElementById('review-flash');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    btn && (btn.disabled = true);

    try {
      const resp = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });

      let data = null;
      try { data = await resp.json(); } catch (_) {}

      
      if (resp.ok && data && data.ok) {
        if (location.hash !== '#reviews') location.hash = '#reviews';
        location.reload();
        return;
      }

      const msg = (data && data.errors) ? data.errors.join(' ') : ('Neuspešan odgovor ('+resp.status+').');
      if (flashBox) {
        flashBox.className = 'alert error';
        flashBox.textContent = msg;
        flashBox.style.display = 'block';
      } else {
        alert(msg);
      }
    } catch (err) {
      console.error(err);
      if (flashBox) {
        flashBox.className = 'alert error';
        flashBox.textContent = 'Greška pri slanju zahteva.';
        flashBox.style.display = 'block';
      } else {
        alert('Greška pri slanju zahteva.');
      }
    } finally {
      btn && (btn.disabled = false);
    }
  });
});
</script>


<?php include __DIR__ . '/../partials/footer.php'; ?>