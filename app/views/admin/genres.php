
<?php include __DIR__ . '/../partials/flash.php'; ?>
<h2>Admin — Žanrovi</h2>

<form method="post" style="max-width:420px;margin-bottom:14px;">
  <label>Novi žanr</label>
  <input type="text" name="name" required>
  <button>Dodaj</button>
</form>

<table border="1" cellpadding="6" cellspacing="0">
  <tr><th>ID</th><th>Naziv</th><th>Akcije</th></tr>
  <?php foreach($genres as $g): ?>
  <tr>
    <td><?=$g['id']?></td>
    <td><?=htmlspecialchars($g['name'], ENT_QUOTES, 'UTF-8')?></td>
    <td>
      <a href="?page=admin_genres&delete=<?=$g['id']?>" onclick="return confirm('Obrisati žanr?')">Obriši</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
