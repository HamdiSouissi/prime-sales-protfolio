<?php
$flashes = consume_flash();
$company = (string) (($GLOBALS['view_settings']['company_name'] ?? '') ?: ($GLOBALS['app_config']['name'] ?? 'Prime Sales'));
$primary = (string) ($GLOBALS['view_settings']['primary_color'] ?? '#4F8CFF');
$accent = (string) ($GLOBALS['view_settings']['accent_color'] ?? '#8B5CF6');
?>
<!doctype html>
<html lang="fr" style="--brand-primary: <?= e($primary) ?>; --brand-accent: <?= e($accent) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F4F7FF">
    <title><?= e($pageTitle ?? 'Connexion') ?> · <?= e($company) ?></title>
    <link rel="icon" href="<?= e(url('/media/brand/favicon')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/light-theme.css')) ?>">
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body class="guest-body">
<div class="ambient ambient--one"></div><div class="ambient ambient--two"></div><div class="ambient ambient--three"></div>
<?php foreach ($flashes as $message): ?>
    <div class="toast toast--<?= e($message['type']) ?>" role="status"><?= e($message['message']) ?></div>
<?php endforeach; ?>
<?= $content ?>
</body>
</html>
