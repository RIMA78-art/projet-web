<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="card"><div class="card-header d-flex justify-content-between"><span>Seance dynamique</span><span id="remaining">Temps restant: 30:00</span></div>
<div class="card-body">
  <div class="mb-3 d-flex gap-2">
    <button class="btn btn-success" id="startBtn">Start</button>
    <button class="btn btn-primary" id="pauseBtn">Pause/Reprendre</button>
    <button class="btn btn-danger" id="finishBtn">Terminer</button>
  </div>
  <p>Timer: <strong id="timer">00:00</strong></p>
  <p>Calories estimees: <strong id="calories">0</strong></p>
  <div class="progress mb-3"><div id="progressBar" class="progress-bar bg-success" style="width:0%">0%</div></div>
  <ul class="list-group mb-3"><li class="list-group-item">Echauffement 5 min</li><li class="list-group-item">Cardio 15 min</li><li class="list-group-item">Renforcement 10 min</li></ul>
  <form id="finishForm" method="post" action="index.php?route=seance/finish">
    <input type="hidden" name="programme_id" value="<?= (int)$programmeId ?>">
    <input type="hidden" name="duree_effectuee" id="duree_effectuee">
    <input type="hidden" name="calories_brulees" id="calories_brulees">
    <input type="hidden" name="progression" id="progression">
  </form>
</div></div>
<script src="../front_office/js/test.js"></script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
