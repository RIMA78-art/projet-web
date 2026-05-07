<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="row justify-content-center"><div class="col-md-7"><div class="card shadow-sm"><div class="card-body p-4">
<div class="text-center mb-3"><h4>Inscription Nutri Nova</h4><small class="text-muted">Smart Sport & Nutrition Platform</small></div>
<form method="post" action="index.php?route=user/register" id="registerForm">
  <div class="row g-2">
    <div class="col-md-6"><input name="nom" class="form-control" placeholder="Nom" required></div>
    <div class="col-md-6"><input name="email" type="email" class="form-control" placeholder="Email" required></div>
    <div class="col-md-6"><input name="password" type="password" class="form-control" placeholder="Mot de passe" required></div>
    <div class="col-md-6"><select name="role" class="form-select"><option value="utilisateur">utilisateur</option><option value="admin">admin</option></select></div>
    <div class="col-md-4"><input name="age" type="number" class="form-control" placeholder="Age" required></div>
    <div class="col-md-4"><input name="objectif" class="form-control" placeholder="Objectif" required></div>
    <div class="col-md-4"><select name="niveau" class="form-select"><option>debutant</option><option>intermediaire</option><option>avance</option></select></div>
  </div>
  <button class="btn btn-success mt-3 w-100">Inscription</button>
</form>
</div></div></div></div>
<script src="../front_office/js/test.js"></script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
