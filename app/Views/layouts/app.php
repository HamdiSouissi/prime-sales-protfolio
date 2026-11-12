<?php
$user = $GLOBALS['auth']->user() ?? [];
$settings = $GLOBALS['view_settings'] ?? [];
$company = (string) (($settings['company_name'] ?? '') ?: ($GLOBALS['app_config']['name'] ?? 'Prime Sales'));
$primary = (string) ($settings['primary_color'] ?? '#4F8CFF');
$accent = (string) ($settings['accent_color'] ?? '#8B5CF6');
$isAdmin = ($user['role'] ?? '') === 'ADMIN';
$flashes = consume_flash();
$navItems = [
    ['label' => 'Vue d’ensemble', 'href' => '/dashboard', 'icon' => 'grid', 'key' => 'dashboard'],
    ['label' => 'Bibliothèque', 'href' => '/prototypes', 'icon' => 'layers', 'key' => 'prototypes'],
    ['label' => 'Catégories', 'href' => '/categories', 'icon' => 'shapes', 'key' => 'categories'],
    ['label' => 'Domaines', 'href' => '/domains', 'icon' => 'globe', 'key' => 'domains'],
];
if ($isAdmin) {
    $navItems[] = ['label' => 'Équipe commerciale', 'href' => '/admin/users', 'icon' => 'users', 'key' => 'users'];
    $navItems[] = ['label' => 'Paramètres', 'href' => '/settings', 'icon' => 'settings', 'key' => 'settings'];
}
?>
<!doctype html>
<html lang="fr" style="--brand-primary: <?= e($primary) ?>; --brand-accent: <?= e($accent) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B1020">
    <title><?= e($pageTitle ?? 'Espace') ?> · <?= e($company) ?></title>
    <link rel="icon" href="<?= e(url('/media/brand/favicon')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body class="app-body">
<div class="ambient ambient--one"></div><div class="ambient ambient--two"></div><div class="ambient ambient--three"></div>
<div class="app-shell">
    <div class="sidebar-scrim" data-sidebar-close></div>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="<?= e(url('/dashboard')) ?>" aria-label="<?= e($company) ?> — accueil">
            <img class="brand__image" src="<?= e(url('/media/brand/logo')) ?>" alt="Logo <?= e($company) ?>">
            <span class="brand__type"><strong><?= e($company) ?></strong><small>SALES PORTFOLIO</small></span>
        </a>
        <div class="workspace-chip"><span class="workspace-chip__dot"></span><span>ESPACE COMMERCIAL</span><?= icon('chevron-down') ?></div>
        <nav class="main-nav" aria-label="Navigation principale">
            <span class="nav-section-label">ESPACE</span>
            <?php foreach ($navItems as $item): ?>
                <a class="nav-link <?= ($activeNav ?? '') === $item['key'] ? 'is-active' : '' ?>" href="<?= e(url($item['href'])) ?>">
                    <?= icon($item['icon']) ?><span><?= e($item['label']) ?></span>
                    <?php if ($item['key'] === 'prototypes'): ?><span class="nav-link__dot"></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-spacer"></div>
        <div class="sidebar-callout">
            <div class="sidebar-callout__glow"></div>
            <span class="sidebar-callout__icon"><?= icon('sparkle') ?></span>
            <strong>Une belle histoire<br>à présenter ?</strong>
            <p>Retrouvez la réalisation idéale pour votre prochain rendez-vous.</p>
            <a href="<?= e(url('/prototypes')) ?>">Explorer la bibliothèque <?= icon('arrow-right') ?></a>
        </div>
        <div class="sidebar-bottom">
            <a class="profile-mini" href="<?= e(url('/profile')) ?>">
                <span class="avatar"><?= e(initials((string) ($user['name'] ?? 'Prime Sales'))) ?></span>
                <span class="profile-mini__info"><strong><?= e($user['name'] ?? 'Utilisateur') ?></strong><small><?= $isAdmin ? 'Administrateur' : 'Commercial' ?></small></span>
                <?= icon('chevron-right', 'profile-mini__arrow') ?>
            </a>
            <form method="post" action="<?= e(url('/logout')) ?>" class="logout-form"><?= csrf_field() ?><button type="submit" class="logout-link"><?= icon('logout') ?><span>Déconnexion</span></button></form>
        </div>
    </aside>
    <div class="app-main">
        <header class="topbar">
            <button class="icon-button mobile-menu-button" type="button" aria-label="Ouvrir le menu" data-sidebar-toggle><?= icon('menu') ?></button>
            <div class="topbar__context"><span class="topbar__eyebrow">PRIME SALES <span>/</span> ESPACE</span><h1><?= e($pageTitle ?? '') ?></h1></div>
            <div class="topbar__actions">
                <span class="topbar-status"><span class="status-dot status-dot--green"></span>Votre espace est à jour</span>
                <a class="topbar-profile" href="<?= e(url('/profile')) ?>" title="Mon profil"><span class="avatar avatar--small"><?= e(initials((string) ($user['name'] ?? 'PS'))) ?></span></a>
            </div>
        </header>
        <main class="page-content" id="main-content">
            <?php foreach ($flashes as $message): ?>
                <div class="toast toast--<?= e($message['type']) ?>" role="status" data-toast><?= e($message['message']) ?><button type="button" aria-label="Fermer" data-toast-close>×</button></div>
            <?php endforeach; ?>
            <?= $content ?>
        </main>
        <footer class="app-footer"><span>© <?= date('Y') ?> <?= e($company) ?></span><span>Conçu pour mieux raconter vos réalisations <span class="footer-sparkle">✦</span></span></footer>
    </div>
</div>
</body>
</html>
