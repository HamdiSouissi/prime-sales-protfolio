<main class="login-layout">
    <section class="login-showcase">
        <div class="login-showcase__top">
            <a class="brand brand--guest" href="<?= e(url('/login')) ?>"><img class="brand__image" src="<?= e(url('/media/brand/logo')) ?>" alt="Logo"><span class="brand__type"><strong><?= e(($GLOBALS['view_settings']['company_name'] ?? '') ?: 'Prime Sales') ?></strong><small>SALES PORTFOLIO</small></span></a>
            <span class="showcase-badge"><span class="status-dot status-dot--green"></span> Votre espace de démonstration</span>
        </div>
        <div class="login-showcase__content">
            <div class="showcase-orbit showcase-orbit--one"></div><div class="showcase-orbit showcase-orbit--two"></div>
            <span class="eyebrow"><span class="eyebrow-line"></span> DÉMONTREZ. INSPIREZ. CONCLUEZ.</span>
            <h1>Chaque projet<br>mérite sa <em>scène.</em></h1>
            <p>Retrouvez, organisez et présentez les réalisations qui font la différence. Toute votre expertise, au même endroit.</p>
            <div class="showcase-stack">
                <div class="showcase-stack__card showcase-stack__card--back"><div class="mini-screen mini-screen--mint"></div><span>Application mobile</span></div>
                <div class="showcase-stack__card showcase-stack__card--middle"><div class="mini-screen mini-screen--purple"></div><span>Expérience digitale</span></div>
                <div class="showcase-stack__card showcase-stack__card--front"><div class="mini-screen mini-screen--blue"><i></i><i></i><i></i></div><span>Site web · Restaurant</span><b><?= icon('arrow') ?></b></div>
            </div>
        </div>
        <div class="login-showcase__bottom"><span>Une bibliothèque créée pour vos rendez-vous client.</span><span>01 <i></i> 03</span></div>
    </section>
    <section class="login-panel-wrap">
        <div class="login-panel glass-panel">
            <div class="login-panel__mobile-brand"><img src="<?= e(url('/media/brand/logo')) ?>" alt="Logo"></div>
            <span class="eyebrow">BON RETOUR PARMI NOUS</span>
            <h2>Connectez-vous<span>.</span></h2>
            <p class="login-intro">Accédez à votre espace de présentation.</p>
            <form class="form-stack login-form" action="<?= e(url('/login')) ?>" method="post" autocomplete="on">
                <?= csrf_field() ?>
                <label class="field"><span>Adresse e-mail</span><div class="input-with-icon"><?= icon('mail') ?><input type="email" name="email" value="<?= e($oldEmail ?? '') ?>" placeholder="vous@entreprise.fr" autocomplete="username" maxlength="190" required autofocus></div></label>
                <label class="field"><span>Mot de passe</span><div class="input-with-icon"><?= icon('lock') ?><input type="password" name="password" placeholder="Votre mot de passe" autocomplete="current-password" required><button class="password-toggle" type="button" aria-label="Afficher le mot de passe" data-password-toggle><?= icon('eye') ?></button></div></label>
                <div class="login-helper"><span><span class="status-dot status-dot--blue"></span> Connexion sécurisée</span><span>Besoin d’aide ? <a href="mailto:administration@entreprise.fr">Contactez l’administrateur</a></span></div>
                <button class="button button-primary button-wide" type="submit"><span>Accéder à mon espace</span><?= icon('arrow-right') ?></button>
            </form>
            <div class="login-separator"><span></span><small>UN ESPACE, TOUTES VOS RÉALISATIONS</small><span></span></div>
            <div class="login-trust"><span><?= icon('shield') ?></span><div><strong>Vos accès, en toute confiance</strong><small>Les données de votre espace restent protégées.</small></div></div>
        </div>
        <div class="login-panel-footer"><span>© <?= date('Y') ?> Prime Sales</span><span>Propulsé avec <i>✦</i> par votre équipe</span></div>
    </section>
</main>
