<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="nn-skeleton mb-3"></div>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card stat-card"><div class="card-body"><small>Utilisateurs actifs</small><h3><?= (int)$stats['usersActifs'] ?></h3></div></div></div>
  <div class="col-md-3"><div class="card stat-card"><div class="card-body"><small>Programmes</small><h3><?= (int)$stats['programmes'] ?></h3></div></div></div>
  <div class="col-md-3"><div class="card stat-card"><div class="card-body"><small>Coachs</small><h3><?= (int)$stats['coachs'] ?></h3></div></div></div>
  <div class="col-md-3"><div class="card stat-card"><div class="card-body"><small>Taux completion</small><h3><?= (int)$stats['completionRate'] ?>%</h3></div></div></div>
</div>
<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="card stat-card"><div class="card-body"><small>Seances (semaine)</small><h4><?= (int)$stats['seancesSemaine'] ?></h4></div></div></div>
  <div class="col-md-4"><div class="card stat-card"><div class="card-body"><small>Seances (mois)</small><h4><?= (int)$stats['seancesMois'] ?></h4></div></div></div>
  <div class="col-md-4"><div class="card stat-card"><div class="card-body"><small>Calories total / moyenne</small><h4><?= (int)$stats['caloriesTotal'] ?> / <?= (int)$stats['caloriesAvg'] ?></h4></div></div></div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-6"><div class="card"><div class="card-header">Evolution calories</div><div class="card-body"><canvas id="chartCalories"></canvas></div></div></div>
  <div class="col-lg-3"><div class="card"><div class="card-header">Difficulte programmes</div><div class="card-body"><canvas id="chartDifficulty"></canvas></div></div></div>
  <div class="col-lg-3"><div class="card"><div class="card-header">Seances / semaine</div><div class="card-body"><canvas id="chartWeekly"></canvas></div></div></div>
</div>

<?php if (!empty($recommended)): ?>
<div class="alert alert-info">Programme recommande pour vous: <strong><?= htmlspecialchars($recommended['nom']) ?></strong> (<?= htmlspecialchars($recommended['difficulte']) ?>)</div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-8"><div class="card"><div class="card-header">Calendrier sportif</div><div class="card-body"><div id="calendar"></div></div></div></div>
  <div class="col-lg-4"><div class="card"><div class="card-header">Actions rapides</div><div class="card-body d-grid gap-2">
    <a class="btn btn-success" href="index.php?route=programme/index">Gerer Programmes</a>
    <a class="btn btn-primary" href="index.php?route=coach/index">Gerer Coachs</a>
    <a class="btn btn-dark" href="index.php?route=seance/start&programme_id=1">Lancer une seance</a>
  </div></div></div>
</div>

<script>
window.dashboardData = {
  difficultyLabels: <?= json_encode($chart['difficultyLabels']) ?>,
  difficultyValues: <?= json_encode($chart['difficultyValues']) ?>,
  weeklyLabels: <?= json_encode($chart['weeklyLabels']) ?>,
  weeklyValues: <?= json_encode($chart['weeklyValues']) ?>,
  caloriesLabels: <?= json_encode($chart['caloriesLabels']) ?>,
  caloriesValues: <?= json_encode($chart['caloriesValues']) ?>
};
</script>
<script src="../back_office/test1.js"></script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
