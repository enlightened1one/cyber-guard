<?php
$user = $user ?? null;
$flashSuccess = Session::getFlash('success');
$flashError = Session::getFlash('error');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberGuard LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; color: #10233d; }
        .navbar { background: #091b30; }
        .navbar-brand { font-weight: 700; }
        .hero { background: linear-gradient(135deg, #091b30, #0d3b66); color: white; }
        .badge-soft { background: rgba(38,166,154,0.12); color: #0d6efd; }
        .card { border: none; box-shadow: 0 6px 18px rgba(7, 23, 41, 0.06); }
        .section-title { font-weight: 700; color: #0b1f38; }
        .btn-primary { background: #0d6efd; border: none; }
        .btn-success { background: #198754; }
        .page-shell { min-height: 78vh; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/">CyberGuard LMS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbars">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbars">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/catalog">Catalog</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/exercises">Exercises</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact">Contact</a></li>
        <?php if ($user): ?>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assignments">Assignments</a></li>
            <li class="nav-item"><form method="post" action="<?= BASE_URL ?>/logout"><?= Csrf::token() ?><button class="btn btn-outline-light btn-sm" type="submit">Logout</button></form></li>
        <?php else: ?>
            <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>/login">Login</a></li>
            <li class="nav-item"><a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/register">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container page-shell py-4">
    <?php if ($flashSuccess): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?= $content ?? '' ?>
</div>
<footer class="bg-dark text-light py-4 mt-4">
  <div class="container d-flex justify-content-between flex-wrap gap-3">
    <div>© <?= date('Y') ?> CyberGuard LMS</div>
    <div>
      <a class="text-light text-decoration-none me-3" href="<?= BASE_URL ?>/privacy">Privacy</a>
      <a class="text-light text-decoration-none me-3" href="<?= BASE_URL ?>/terms">Terms</a>
      <a class="text-light text-decoration-none" href="<?= BASE_URL ?>/contact">Help</a>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
