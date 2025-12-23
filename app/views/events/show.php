<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container event-onecard">

  <a class="link back" href="?page=events">← Nazad</a>

  <?php if(!empty($_SESSION['flash_warning'])): ?>
    <div class="alert alert-warning"><?=$_SESSION['flash_warning']?></div>
    <?php unset($_SESSION['flash_warning']); ?>
  <?php endif; ?>

  <?php
    // $goingCount dolazi iz kontrolera (broji samo status='going')
    $isOrganizer = (int)($_SESSION['user']['id'] ?? 0) === (int)$event['organizer_id'];
    $isFull = !empty($event['capacity']) && $goingCount >= (int)$event['capacity'];
  ?>

  <div class="card big-card">
    <!-- Header kartice -->
    <div class="card-head">
      <h1 class="title"><?= htmlspecialchars($event['title']) ?></h1>
      <div class="meta">
        <span class="meta-item">👤 <?= htmlspecialchars($event['organizer_name']) ?></span>
        <?php if(!empty($event['game_title'])): ?>
          <span class="chip"><?= htmlspecialchars($event['game_title']) ?></span>
        <?php endif; ?>
      </div>
      <?php if (!empty($event['description'])): ?>
        <p class="desc"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
      <?php endif; ?>
    </div>

    
    <div class="card-body grid2">
      <!-- Detalji -->
      <section class="block">
        <ul class="details">
          <li>
            <span class="icon">📍</span>
            <span class="label">Gde:</span>
            <span class="value">
              <?php if ((int)$event['is_online'] === 1): ?>
                <span class="badge badge-online">Online</span>
                <?= htmlspecialchars($event['online_url'] ?: '') ?>
              <?php else: ?>
                <span class="badge badge-place"><?= htmlspecialchars($event['location'] ?: 'Nije navedeno') ?></span>
              <?php endif; ?>
            </span>
          </li>
          <li>
            <span class="icon">🕒</span>
            <span class="label">Početak:</span>
            <span class="value"><?= htmlspecialchars($event['starts_at']) ?></span>
          </li>
          <?php if(!empty($event['ends_at'])): ?>
          <li>
            <span class="icon">⏱</span>
            <span class="label">Kraj:</span>
            <span class="value"><?= htmlspecialchars($event['ends_at']) ?></span>
          </li>
          <?php endif; ?>
          <?php if(!empty($event['capacity'])): ?>
          <li>
            <span class="icon">👥</span>
            <span class="label">Kapacitet:</span>
            <span class="value">
              <?= (int)$event['capacity'] ?>
              <span class="muted">(prijavljenih: <?= (int)$goingCount ?>)</span>
              <?php if ($isFull): ?><span class="tag tag-full">Popunjeno</span><?php endif; ?>
            </span>
          </li>
          <?php endif; ?>
        </ul>
      </section>

      <!-- RSVP (samo "Dolazim") -->
      <section class="block rsvp" id="rsvp">
        <h3>Prijava</h3>
        <?php if ($isOrganizer): ?>
          <p class="muted">Ti si organizator ovog događaja.</p>
        <?php else: ?>
          <?php if ($isFull): ?>
            <button class="btn btn--primary" disabled>✅ Dolazim</button>
            <p class="muted" style="margin-top:6px">Događaj je popunjen.</p>
          <?php else: ?>
            <a class="btn btn--primary" href="?page=event_rsvp&id=<?= $event['id'] ?>">✅ Dolazim</a>
          <?php endif; ?>
        <?php endif; ?>
      </section>
    </div>

    <!-- Prijavljeni -->
    <div class="card-foot">
      <h3>Prijavljeni</h3>
      <?php if(empty($attendees)): ?>
        <p class="muted">Nema prijavljenih.</p>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr><th>Korisnik</th><th>Status</th><th>Check-in</th></tr>
          </thead>
          <tbody>
          <?php foreach($attendees as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['username']) ?></td>
              <td>
                <?php if (trim(strtolower($a['status'])) === 'going'): ?>
                  <span class="tag tag-going">Dolazim</span>
                <?php else: ?>
                  <span class="tag">Možda</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if($a['checked_in_at']): ?>
                  ✅ <?= htmlspecialchars($a['checked_in_at']) ?>
                <?php elseif(($_SESSION['user']['role'] ?? '') === 'admin' || (int)$_SESSION['user']['id'] === (int)$event['organizer_id']): ?>
                  <form method="post" action="?page=event_checkin&id=<?= $event['id'] ?>&user=<?= $a['user_id'] ?>" style="display:inline">
                    <button class="btn">Check-in</button>
                  </form>
                <?php else: ?>—<?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>
  .event-onecard { max-width: 860px; }
  .back { display:inline-block; margin: 6px 0 12px; }

  .big-card { padding: 10px 14px; }
  .card-head { margin-bottom: 8px; }
  .title { margin:0 0 4px; }
  .desc { margin: 6px 0 0; color:#444; }
  .meta { display:flex; gap:8px; align-items:center; color:#666; font-size:14px; flex-wrap:wrap; }
  .chip { font-size:12px; padding:2px 8px; border-radius:999px; background:rgba(99,102,241,.12); color:#3f40bf; border:1px solid rgba(99,102,241,.25); }

  .grid2 { display:grid; gap:16px; grid-template-columns:1fr; }
  @media (min-width: 820px){ .grid2 { grid-template-columns:2fr 1fr; } }
  .block h3 { margin:0 0 8px; }

  .details { list-style:none; margin:0; padding:0; display:grid; gap:8px; }
  .details li { display:flex; gap:8px; align-items:center; }
  .icon { width:18px; text-align:center; opacity:.85; }
  .label { font-weight:600; min-width:90px; }
  .value { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }

  .badge { display:inline-block; padding:2px 8px; font-size:12px; border-radius:999px; border:1px solid transparent; }
  .badge-online { background:rgba(16,185,129,.12); color:#087d57; border-color:rgba(16,185,129,.25); }
  .badge-place  { background:rgba(59,130,246,.10); color:#215a9a; border-color:rgba(59,130,246,.22); }

  .tag { font-size:12px; padding:2px 8px; border-radius:999px; background:rgba(0,0,0,.06); color:#333; }
  .tag-going { background:rgba(34,197,94,.16); color:#166534; }
  .tag-full { background:rgba(239,68,68,.12); color:#b3261e; margin-left:8px; }
  .muted { color:#666; }

  .btn[disabled]{ opacity:.5; cursor:not-allowed; pointer-events:none; }

  .card-foot { margin-top: 14px; }
  .table th, .table td { vertical-align: middle;}
</style>


<?php include __DIR__ . '/../partials/footer.php'; ?>
