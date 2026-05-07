<!doctype html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nutri Nova - Smart Sport & Nutrition Platform</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top nn-navbar">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="index.php?route=dashboard/index">
      <img src="assets/images/nutri-nova-logo.svg" alt="Nutri Nova" class="nn-logo" onerror="this.style.display='none';document.getElementById('logoFallback').classList.remove('d-none');">
      <span id="logoFallback" class="d-none fw-bold"><i class="fa-solid fa-dumbbell me-1"></i>Nutri Nova</span>
    </a>
    <span class="text-muted small d-none d-md-inline">Smart Sport & Nutrition Platform</span>
    <div class="d-flex align-items-center gap-2 ms-auto">
      <button id="themeToggle" class="btn btn-outline-light btn-sm"><i class="fa-regular fa-moon"></i></button>
      <?php if (!empty($_SESSION['user'])): ?>
        <a class="btn btn-outline-light btn-sm" href="index.php?route=programme/front">Front Programmes</a>
        <a class="btn btn-outline-light btn-sm" href="index.php?route=coach/front">Front Coachs</a>
        <a class="btn btn-outline-light btn-sm" href="index.php?route=programme/index">Back Programmes</a>
        <a class="btn btn-outline-light btn-sm" href="index.php?route=coach/index">Back Coachs</a>
        <a class="btn btn-outline-light btn-sm" href="index.php?route=seance/history">Mes seances</a>
        <a class="btn btn-warning btn-sm" href="index.php?route=user/logout">Logout</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<div class="container-fluid py-4">
<?php if (!empty($_SESSION['flash'])): ?>
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
    <div class="toast show text-bg-<?= htmlspecialchars($_SESSION['flash']['type']) ?> border-0">
      <div class="d-flex">
        <div class="toast-body"><?= htmlspecialchars($_SESSION['flash']['message']) ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>
<?php unset($_SESSION['flash']); endif; ?>
