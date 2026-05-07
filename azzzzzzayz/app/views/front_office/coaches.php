<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Front Office - Coachs</h3>
  <form class="d-flex gap-2" method="get" action="index.php">
    <input type="hidden" name="route" value="coach/front">
    <input type="text" name="q" class="form-control" placeholder="Recherche coach" value="<?= htmlspecialchars($search ?? '') ?>">
    <button class="btn btn-outline-primary">Rechercher</button>
  </form>
</div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-hover">
  <thead><tr><th>Nom</th><th>Email</th><th>Telephone</th><th>Specialite</th></tr></thead>
  <tbody>
  <?php foreach($coaches as $c): ?>
    <tr>
      <td><?= htmlspecialchars($c['nom']) ?></td>
      <td><?= htmlspecialchars($c['email']) ?></td>
      <td><?= htmlspecialchars($c['telephone']) ?></td>
      <td><?= htmlspecialchars($c['specialite']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
