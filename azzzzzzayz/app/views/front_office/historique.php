<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="card mb-3"><div class="card-body">
  <form class="d-flex gap-2" method="get" action="index.php">
    <input type="hidden" name="route" value="seance/history">
    <input type="text" name="q" value="<?= htmlspecialchars($query ?? '') ?>" class="form-control" placeholder="Recherche programme">
    <button class="btn btn-outline-primary">Filtrer</button>
  </form>
</div></div>
<div class="card mb-3"><div class="card-header">Mes seances</div><div class="card-body table-responsive">
<table class="table"><thead><tr><th>Programme</th><th>Duree</th><th>Calories</th><th>Date</th><th>Progression</th></tr></thead><tbody>
<?php foreach($seances as $s): ?>
<tr><td><?= htmlspecialchars($s['programme_nom'] ?? 'N/A') ?></td><td><?= (int)$s['duree_effectuee'] ?> min</td><td><?= (int)$s['calories_brulees'] ?></td><td><?= htmlspecialchars($s['date_seance']) ?></td><td><?= (int)$s['progression'] ?>%</td></tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
<div class="card"><div class="card-header">Badges / Recompenses</div><div class="card-body">
<?php if(empty($badges)): ?>Aucun badge pour le moment.<?php else: foreach($badges as $b): ?>
<span class="badge text-bg-success me-2 mb-1"><?= htmlspecialchars($b['label_badge']) ?></span>
<?php endforeach; endif; ?>
</div></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
