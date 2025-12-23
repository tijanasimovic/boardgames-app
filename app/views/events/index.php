<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container events-page">

  <!-- Header -->
  <div class="events-head">
    <h1>Događaji</h1>
    <a class="btn btn--primary" href="?page=event_new">+ Novi događaj</a>
  </div>

  <?php if(empty($events)): ?>
    <div class="empty">
      <p>Još uvek nema događaja.</p>
      <a class="btn" href="?page=event_new">Kreiraj prvi događaj</a>
    </div>
  <?php else: ?>

    <?php
      // grupisanje po datumu (YYYY-MM-DD)
      $byDate = [];
      foreach($events as $e){
        $key = substr($e['starts_at'],0,10);
        $byDate[$key][] = $e;
      }
    ?>

    <?php foreach($byDate as $date => $items): ?>
      <div class="date-sep">
        <span><?= htmlspecialchars($date) ?></span>
        <hr>
      </div>

      <ul class="cards">
        <?php foreach($items as $e): ?>
          <?php
            $time = date('H:i', strtotime($e['starts_at']));
            $isOnline = (int)$e['is_online'] === 1;
          ?>
          <li class="card event-card">
            <a class="card-link" href="?page=event&id=<?= (int)$e['id'] ?>">
              <div class="card-title-row">
                <strong class="card-title"><?= htmlspecialchars($e['title']) ?></strong>
                <?php if(!empty($e['game_title'])): ?>
                  <span class="chip"><?= htmlspecialchars($e['game_title']) ?></span>
                <?php endif; ?>
              </div>

              <div class="meta">
                <span class="meta-item">👤 <?= htmlspecialchars($e['organizer_name']) ?></span>
                <span class="dot">•</span>
                <?php if ($isOnline): ?>
                  <span class="badge badge-online">Online</span>
                <?php else: ?>
                  <span class="badge badge-place"><?= htmlspecialchars($e['location'] ?: 'Lokacija nije navedena') ?></span>
                <?php endif; ?>
              </div>

              <div class="time">
                <span class="clock">🕒</span> Početak: <?= htmlspecialchars($e['starts_at']) ?><?php /* ili samo vreme: $time */ ?>
              </div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endforeach; ?>

  <?php endif; ?>
</div>

<style>
  .events-page { max-width: 920px; }
  .events-head {
    display:flex; align-items:center; justify-content:space-between;
    gap:12px; margin: 8px 0 18px;
  }
  .events-head h1 { margin:0; }

  .empty { text-align:center; padding:32px 0; }
  .empty .btn { margin-top:8px; }

  .date-sep { display:flex; align-items:center; gap:12px; margin:18px 0 10px; }
  .date-sep span { font-weight:700; color:#333; white-space:nowrap; }
  .date-sep hr { flex:1; border:none; height:1px; background:rgba(0,0,0,.12); }

 
  .cards {
    list-style:none; padding:0; margin:0 0 16px 0;
    display:grid; grid-template-columns:1fr; gap:12px;
  }
  @media (min-width: 820px) {
    .cards { grid-template-columns:1fr 1fr; }
  }

  .event-card {
    border-radius:14px; box-shadow:0 1px 0 rgba(0,0,0,.04);
    background:#fff; transition:transform .08s ease, box-shadow .12s ease;
  }
  .event-card:hover { transform:translateY(-1px); box-shadow:0 8px 20px rgba(0,0,0,.08); }

  .card-link { display:block; text-decoration:none; color:inherit; padding:14px 16px; }
  .card-title-row { display:flex; align-items:center; gap:8px; margin-bottom:6px; }
  .card-title { font-size:16px; }

  .chip {
    font-size:12px; padding:2px 8px; border-radius:999px;
    background:rgba(99,102,241,.12); color:#3f40bf; border:1px solid rgba(99,102,241,.25);
  }

  .meta { display:flex; align-items:center; flex-wrap:wrap; gap:6px; color:#555; font-size:13px; margin-bottom:6px; }
  .meta .dot { opacity:.5; }
  .meta-item { display:flex; align-items:center; gap:6px; }

  .badge {
    display:inline-block; padding:2px 8px; font-size:12px; border-radius:999px; border:1px solid transparent;
    white-space:nowrap; max-width: 100%; overflow: hidden; text-overflow: ellipsis;
  }
  .badge-online { background:rgba(16,185,129,.12); color:#087d57; border-color:rgba(16,185,129,.25); }
  .badge-place  { background:rgba(59,130,246,.10); color:#215a9a; border-color:rgba(59,130,246,.22); }

  .time { font-size:13px; color:#333; }
  .clock { margin-right:6px; }
</style>
<?php include __DIR__ . '/../partials/footer.php'; ?>
