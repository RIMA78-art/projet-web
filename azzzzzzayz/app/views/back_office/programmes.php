<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card"><div class="card-header">Ajouter programme</div><div class="card-body">
      <form method="post" action="index.php?route=programme/store" id="programmeForm">
        <input name="nom" class="form-control mb-2" placeholder="Nom" required>
        <input name="duree_semaines" type="number" class="form-control mb-2" placeholder="Duree (semaines)" required>
        <input name="jours_semaine" type="number" class="form-control mb-2" placeholder="Jours / semaine" required>
        <select name="difficulte" class="form-select mb-2" required><option>debutant</option><option>intermediaire</option><option>avance</option></select>
        <textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>
        <select name="coach_id" class="form-select mb-2"><option value="">Coach</option><?php foreach($coaches as $c): ?><option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option><?php endforeach; ?></select>
        <input name="popularite" type="number" class="form-control mb-2" value="1" min="1" max="100">
        <button class="btn btn-success w-100">Ajouter</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-8">
    <div class="card"><div class="card-header d-flex justify-content-between align-items-center"><span>Lister / Modifier / Supprimer</span>
      <form class="d-flex gap-2" method="get" action="index.php">
        <input type="hidden" name="route" value="programme/index">
        <input id="tableProgrammeFilter" type="text" name="q" class="form-control form-control-sm" placeholder="Recherche..." value="<?= htmlspecialchars($search ?? '') ?>">
        <button class="btn btn-outline-secondary btn-sm">OK</button>
      </form>
    </div><div class="card-body table-responsive">
      <table class="table table-striped" id="tableProgramme"><thead><tr><th>ID</th><th>Nom</th><th>Difficulte</th><th>Duree</th><th>Actions</th></tr></thead><tbody>
      <?php foreach($programmes as $p): ?>
      <tr>
        <td><?= (int)$p['id'] ?></td>
        <td><?= htmlspecialchars($p['nom']) ?></td>
        <td><?= htmlspecialchars($p['difficulte']) ?></td>
        <td><?= (int)$p['duree_semaines'] ?> sem.</td>
        <td>
          <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#editP<?= (int)$p['id'] ?>">Modifier</button>
          <a class="btn btn-sm btn-danger" href="index.php?route=programme/delete&id=<?= (int)$p['id'] ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
          <a class="btn btn-sm btn-dark" href="index.php?route=programme/exportPdf&id=<?= (int)$p['id'] ?>">Export PDF</a>
          <a class="btn btn-sm btn-outline-success" href="index.php?route=seance/start&programme_id=<?= (int)$p['id'] ?>">Commencer</a>

          <div id="editP<?= (int)$p['id'] ?>" class="collapse mt-2">
            <form method="post" action="index.php?route=programme/update" class="row g-2 p-2 bg-light rounded-3">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <div class="col-md-6"><input name="nom" class="form-control form-control-sm" value="<?= htmlspecialchars($p['nom']) ?>" required></div>
              <div class="col-md-3"><input name="duree_semaines" type="number" class="form-control form-control-sm" value="<?= (int)$p['duree_semaines'] ?>" required></div>
              <div class="col-md-3"><input name="jours_semaine" type="number" class="form-control form-control-sm" value="<?= (int)$p['jours_semaine'] ?>" required></div>
              <div class="col-md-4"><select name="difficulte" class="form-select form-select-sm"><option <?= $p['difficulte']==='debutant'?'selected':'' ?>>debutant</option><option <?= $p['difficulte']==='intermediaire'?'selected':'' ?>>intermediaire</option><option <?= $p['difficulte']==='avance'?'selected':'' ?>>avance</option></select></div>
              <div class="col-md-4"><input name="popularite" type="number" class="form-control form-control-sm" value="<?= (int)($p['popularite'] ?? 1) ?>"></div>
              <div class="col-md-4"><input name="coach_id" type="number" class="form-control form-control-sm" value="<?= (int)($p['coach_id'] ?? 0) ?>"></div>
              <div class="col-12"><textarea name="description" class="form-control form-control-sm"><?= htmlspecialchars($p['description'] ?? '') ?></textarea></div>
              <div class="col-12"><button class="btn btn-primary btn-sm">Enregistrer</button></div>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?></tbody></table>
    </div></div>
  </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
