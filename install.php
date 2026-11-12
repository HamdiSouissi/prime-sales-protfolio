<?php

declare(strict_types=1);

use PrimeSales\Core\Csrf;
use PrimeSales\Core\Database;
use PrimeSales\Services\Installer;

require __DIR__ . '/app/bootstrap.php';

$error = '';
$success = '';
$alreadyInstalled = false;
$dbReady = false;

try {
    $db = Database::connection();
    $dbReady = true;
    try {
        $alreadyInstalled = Installer::hasAdmin($db);
    } catch (Throwable) {
        // The installer will create the schema on the first successful submission.
    }
} catch (Throwable $exception) {
    error_log('[Prime Sales] Installer database error: ' . $exception->getMessage());
    $error = 'Connexion MySQL impossible. Vérifiez les identifiants dans app/Config/database.php et assurez-vous que la base existe.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyInstalled) {
    if (!Csrf::verify(is_string($_POST['_token'] ?? null) ? $_POST['_token'] : null)) {
        $error = 'Votre session a expiré. Actualisez cette page et réessayez.';
    } elseif (!$dbReady) {
        $error = 'La base MySQL n’est pas accessible. Corrigez d’abord la configuration.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')), 'UTF-8');
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');
        $company = trim((string) ($_POST['company_name'] ?? 'Prime Sales'));

        if (mb_strlen($name, 'UTF-8') < 2 || mb_strlen($name, 'UTF-8') > 120) {
            $error = 'Indiquez un nom compris entre 2 et 120 caractères.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email, 'UTF-8') > 190) {
            $error = 'Adresse e-mail invalide.';
        } elseif (mb_strlen($password, 'UTF-8') < 12 || strlen($password) > 72) {
            $error = 'Le mot de passe doit contenir au moins 12 caractères et rester compatible avec la limite sécurisée de 72 octets.';
        } elseif (!hash_equals($password, $confirmation)) {
            $error = 'Les deux mots de passe ne correspondent pas.';
        } elseif (mb_strlen($company, 'UTF-8') < 2 || mb_strlen($company, 'UTF-8') > 120) {
            $error = 'Le nom de l’entreprise doit contenir entre 2 et 120 caractères.';
        } else {
            try {
                Installer::install($db, $name, $email, $password, $company);
                $success = 'Installation terminée. Vous pouvez maintenant vous connecter.';
                redirect('/login');
            } catch (Throwable $exception) {
                error_log('[Prime Sales] Installer failed: ' . $exception->getMessage());
                $error = $exception->getMessage() === 'An administrator account already exists.'
                    ? 'Un administrateur existe déjà. Connectez-vous à votre espace.'
                    : 'L’installation a échoué. Vérifiez les droits SQL et consultez le journal d’erreurs de l’hébergement.';
                try {
                    $alreadyInstalled = Installer::hasAdmin($db);
                } catch (Throwable) {
                    $alreadyInstalled = false;
                }
            }
        }
    }
}

if ($alreadyInstalled) {
    redirect('/login');
}

$brand = e(asset('images/brand-mark.svg'));
$css = e(asset('css/app.css'));
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B1020">
    <title>Installation · Prime Sales</title>
    <link rel="icon" href="<?= e(asset('images/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= $css ?>">
</head>
<body class="guest-body install-body">
<div class="ambient ambient--one"></div><div class="ambient ambient--two"></div>
<main class="install-wrap">
    <section class="install-card glass-panel">
        <div class="install-brand"><img src="<?= $brand ?>" alt="Prime Sales"><span>PRIME <b>SALES</b></span></div>
        <div class="install-progress"><span class="install-progress__dot is-active"></span><i></i><span class="install-progress__dot"></span><i></i><span class="install-progress__dot"></span></div>
        <span class="eyebrow">MISE EN PLACE · 01 / 03</span>
        <h1>Votre espace,<br><em>prêt à rayonner.</em></h1>
        <p class="muted">Créez le compte administrateur. Les tables MySQL et le catalogue de départ seront préparés automatiquement.</p>

        <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success !== ''): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

        <form method="post" action="" class="form-stack">
            <?= csrf_field() ?>
            <label class="field"><span>Nom de l’entreprise</span><input type="text" name="company_name" value="<?= e($_POST['company_name'] ?? 'Prime Sales') ?>" maxlength="120" required autocomplete="organization"></label>
            <div class="field-row">
                <label class="field"><span>Votre nom</span><input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" maxlength="120" required autocomplete="name"></label>
                <label class="field"><span>E-mail administrateur</span><input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" maxlength="190" required autocomplete="email"></label>
            </div>
            <div class="field-row">
                <label class="field"><span>Mot de passe <small>12 caractères min.</small></span><input type="password" name="password" minlength="12" maxlength="72" required autocomplete="new-password"></label>
                <label class="field"><span>Confirmation</span><input type="password" name="password_confirmation" minlength="12" maxlength="72" required autocomplete="new-password"></label>
            </div>
            <button class="button button-primary button-wide" type="submit" <?= !$dbReady ? 'disabled' : '' ?>>Créer mon espace <?= icon('arrow-right') ?></button>
        </form>
        <p class="install-footnote"><span class="status-dot <?= $dbReady ? 'status-dot--green' : 'status-dot--amber' ?>"></span><?= $dbReady ? 'Base MySQL connectée · prête pour l’installation' : 'En attente de la configuration MySQL' ?></p>
    </section>
    <aside class="install-aside">
        <div class="install-aside__orb"></div>
        <span class="eyebrow">UNE BIBLIOTHÈQUE. TOUT UN SAVOIR-FAIRE.</span>
        <h2>La bonne réalisation.<br><em>Au bon moment.</em></h2>
        <div class="install-feature"><span class="install-feature__icon"><?= icon('layers') ?></span><div><strong>Un catalogue vivant</strong><p>Vos réalisations triées par prestation et secteur.</p></div></div>
        <div class="install-feature"><span class="install-feature__icon install-feature__icon--mint"><?= icon('shield') ?></span><div><strong>Un espace maîtrisé</strong><p>Des accès dédiés aux administrateurs et commerciaux.</p></div></div>
        <div class="install-feature"><span class="install-feature__icon install-feature__icon--purple"><?= icon('sparkle') ?></span><div><strong>Une présentation premium</strong><p>Une expérience pensée pour vos rendez-vous client.</p></div></div>
        <span class="install-aside__note">PHP natif · MySQL · aucune compilation requise</span>
    </aside>
</main>
</body>
</html>
