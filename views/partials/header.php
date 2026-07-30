<?php
$pageTitle = $pageTitle ?? setting('site_name', app_config('app.name'));
$pageDescription = $pageDescription ?? setting('site_description', 'Portal resmi Pemilihan Bujang Dayang Kepulauan Bangka Belitung.');
$current = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$event = $event ?? active_event();
$flashes = pull_flashes();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#047857">
    <title><?= e($pageTitle) ?> | <?= e(setting('site_name', app_config('app.name'))) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<div class="topbar py-2">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2 small">
        <span><i class="bi bi-shield-check me-1"></i> Portal resmi dan pembayaran terlindungi</span>
        <span><?= $event ? e($event['name']) . ' • ' . e($event['year']) : 'Bujang Dayang Bangka Belitung' ?></span>
    </div>
</div>
<nav class="navbar navbar-expand-lg navbar-light sticky-top main-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-3" href="<?= url('index.php') ?>">
            <img src="<?= asset('images/logo.svg') ?>" alt="Logo" width="54" height="54">
            <span><strong>Bujang Dayang</strong><small>Bangka Belitung</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Buka navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link <?= $current === 'index.php' ? 'active' : '' ?>" href="<?= url('index.php') ?>">Beranda</a></li>
                <li class="nav-item"><a class="nav-link <?= $current === 'winners.php' ? 'active' : '' ?>" href="<?= url('winners.php') ?>">Nama Pemenang</a></li>
                <li class="nav-item"><a class="nav-link <?= in_array($current, ['evoting.php','finalist.php'], true) ? 'active' : '' ?>" href="<?= url('evoting.php') ?>">E-Voting</a></li>
                <li class="nav-item"><a class="nav-link <?= $current === 'leaderboard.php' ? 'active' : '' ?>" href="<?= url('leaderboard.php') ?>">Leaderboard</a></li>
                <li class="nav-item ms-lg-2"><a class="btn btn-gold rounded-pill px-4" href="<?= url('evoting.php') ?>"><i class="bi bi-hand-index-thumb me-2"></i>Vote Sekarang</a></li>
            </ul>
        </div>
    </div>
</nav>
<?php foreach ($flashes as $flash): ?>
<div class="container mt-3"><div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm" role="alert"><?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
<?php endforeach; ?>
<main>
