<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="row g-3">
  <div class="col-lg-4"><div class="card"><div class="card-header">Ajouter coach</div><div class="card-body">
    <form method="post" action="index.php?route=coach/store" id="coachForm">
      <input name="nom" class="form-control mb-2" placeholder="Nom" required>
      <input name="email" type="email" class="form-control mb-2" placeholder="Email" required>
      <input name="telephone" class="form-control mb-2" placeholder="Telephone" required>
      <input name="specialite" class="form-control mb-2" placeholder="Specialite" required>
      <button class="btn btn-success w-100">Ajouter</button>
    </form>
  </div></div></div>
  <div class="col-lg-8"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><span>Liste coachs</span>
      <form class="d-flex gap-2" method="get" action="index.php">
        <input type="hidden" name="route" value="coach/index">
        <input id="tableCoachFilter" type="text" name="q" class="form-control form-control-sm" placeholder="Recherche..." value="<?= htmlspecialchars($search ?? '') ?>">
        <button class="btn btn-outline-secondary btn-sm">OK</button>
      </form>
  </div><div class="card-body table-responsive">
    <table class="table table-hover" id="tableCoach"><thead><tr><th>#</th><th>Nom</th><th>Email</th><th>Telephone</th><th>Specialite</th><th>Actions</th></tr></thead><tbody>
    <?php foreach($coaches as $c): ?>
      <tr>
        <td><?= (int)$c['id'] ?></td><td><?= htmlspecialchars($c['nom']) ?></td><td><?= htmlspecialchars($c['email']) ?></td><td><?= htmlspecialchars($c['telephone']) ?></td><td><?= htmlspecialchars($c['specialite']) ?></td>
        <td>
          <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#editC<?= (int)$c['id'] ?>">Modifier</button>
          <a class="btn btn-sm btn-danger" href="index.php?route=coach/delete&id=<?= (int)$c['id'] ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
          <div id="editC<?= (int)$c['id'] ?>" class="collapse mt-2">
            <form method="post" action="index.php?route=coach/update" class="row g-2 p-2 bg-light rounded-3">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <div class="col-md-6"><input name="nom" class="form-control form-control-sm" value="<?= htmlspecialchars($c['nom']) ?>" required></div>
              <div class="col-md-6"><input name="email" type="email" class="form-control form-control-sm" value="<?= htmlspecialchars($c['email']) ?>" required></div>
              <div class="col-md-6"><input name="telephone" class="form-control form-control-sm" value="<?= htmlspecialchars($c['telephone']) ?>" required></div>
              <div class="col-md-6"><input name="specialite" class="form-control form-control-sm" value="<?= htmlspecialchars($c['specialite']) ?>" required></div>
              <div class="col-12"><button class="btn btn-primary btn-sm">Enregistrer</button></div>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?></tbody></table>
  </div></div></div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
