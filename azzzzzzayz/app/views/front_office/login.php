<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="row justify-content-center"><div class="col-md-5"><div class="card shadow-sm"><div class="card-body p-4">
<div class="text-center mb-3">
  <img src="assets/images/nutri-nova-icon.svg" class="nn-icon" alt="Nutri Nova" onerror="this.style.display='none'">
  <h4 class="mt-2 mb-0">Nutri Nova</h4>
  <small class="text-muted">Smart Sport & Nutrition Platform</small>
</div>
<?php if(!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post" action="index.php?route=user/login">
  <input name="email" type="email" class="form-control mb-2" placeholder="Email" required>
  <input name="password" type="password" class="form-control mb-2" placeholder="Mot de passe" required>
  <button class="btn btn-dark w-100">Se connecter</button>
</form>
<a href="index.php?route=user/register" class="d-block mt-3 text-center">Creer un compte</a>
</div></div></div></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
