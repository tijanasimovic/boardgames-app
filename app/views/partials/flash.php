<?php
require_once __DIR__ . '/../../lib/flash.php';
$flashes = flash_get_all();
if ($flashes):
?>
<div style="margin:10px 0;">
  <?php foreach($flashes as $f): ?>
    <div style="padding:8px 10px;border-radius:6px;margin-bottom:6px;
                <?= $f['type']==='ok' ? 'background:#e6ffed;border:1px solid #10b981;' : 'background:#ffecec;border:1px solid #ef4444;' ?>">
      <?=htmlspecialchars($f['msg'], ENT_QUOTES, 'UTF-8')?>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
