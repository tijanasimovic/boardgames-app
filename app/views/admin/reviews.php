
<?php include __DIR__ . '/../partials/flash.php'; ?>
<h2>Admin — Moderacija komentara</h2>

<form method="get" style="margin-bottom:10px;">
  <input type="hidden" name="page" value="admin_reviews">
  <input type="text" name="q" placeholder="Traži igru ili korisnika" value="<?=htmlspecialchars($filters['q']??'', ENT_QUOTES, 'UTF-8')?>">
  <select name="status">
    <?php $s = $filters['status'] ?? 'all'; ?>
    <option value="all"    <?=$s==='all'?'selected':''?>>Svi</option>
    <option value="visible"<?=$s==='visible'?'selected':''?>>Samo vidljivi</option>
    <option value="hidden" <?=$s==='hidden'?'selected':''?>>Samo sakriveni</option>
  </select>
  <button>Primeni</button>
</form>

<table border="1" cellpadding="6" cellspacing="0">
  <tr>
    <th>ID</th>
    <th>Igra</th>
    <th>Korisnik</th>
    <th>Ocena</th>
    <th>Komentar</th>
    <th>Vreme</th>
    <th>Status</th>
    <th>Akcija</th>
  </tr>
  <?php foreach($rows as $r): ?>
  <tr>
    <td><?=$r['id']?></td>
    <td><?=htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8')?></td>
    <td><?=htmlspecialchars($r['username'], ENT_QUOTES, 'UTF-8')?></td>
    <td><?=$r['rating']?></td>
    <td><?=nl2br(htmlspecialchars($r['comment'], ENT_QUOTES, 'UTF-8'))?></td>
    <td><?=$r['created_at']?></td>
    <td><?=$r['is_deleted'] ? 'Sakriven' : 'Vidljiv'?></td>
    <td>
      <?php if(!$r['is_deleted']): ?>
        <form method="post" action="?page=admin_reviews_hide" style="display:inline;">
          <input type="hidden" name="id" value="<?=$r['id']?>">
          <button>Sakrij</button>
        </form>
      <?php else: ?>
        <form method="post" action="?page=admin_reviews_restore" style="display:inline;">
          <input type="hidden" name="id" value="<?=$r['id']?>">
          <button>Vrati</button>
        </form>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php if(($filters['pages']??1) > 1): ?>
  <p>
    Strana <?=$filters['page']?> / <?=$filters['pages']?> (ukupno: <?=$filters['total']?>)
    <?php if($filters['page']>1): ?>
      <a href="?page=admin_reviews&q=<?=urlencode($filters['q'])?>&status=<?=$filters['status']?>&p=<?=($filters['page']-1)?>">← Prethodna</a>
    <?php endif; ?>
    <?php if($filters['page']<$filters['pages']): ?>
      <a href="?page=admin_reviews&q=<?=urlencode($filters['q'])?>&status=<?=$filters['status']?>&p=<?=($filters['page']+1)?>">Sledeća →</a>
    <?php endif; ?>
  </p>
<?php endif; ?>

