<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Front Office - Programmes</h3>
  <form class="d-flex gap-2" method="get" action="index.php">
    <input type="hidden" name="route" value="programme/front">
    <input type="text" name="q" class="form-control" placeholder="Recherche programme" value="<?= htmlspecialchars($search ?? '') ?>">
    <button class="btn btn-outline-primary">Rechercher</button>
  </form>
</div>
<div class="row g-3">
<?php foreach($programmes as $p): ?>
  <div class="col-md-6 col-lg-4">
    <div class="card h-100"><div class="card-body d-flex flex-column">
      <h5><?= htmlspecialchars($p['nom']) ?></h5>
      <p class="small text-muted mb-1">Difficulte: <?= htmlspecialchars($p['difficulte']) ?></p>
      <p class="small text-muted mb-2">Duree: <?= (int)$p['duree_semaines'] ?> semaines</p>
      <p class="small flex-grow-1"><?= htmlspecialchars($p['description'] ?? 'Description non disponible') ?></p>
      <div class="d-flex gap-2">
        <a class="btn btn-success btn-sm" href="index.php?route=seance/start&programme_id=<?= (int)$p['id'] ?>">Commencer</a>
        <a class="btn btn-dark btn-sm" href="index.php?route=programme/exportPdf&id=<?= (int)$p['id'] ?>">PDF</a>
      </div>
    </div></div>
  </div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
