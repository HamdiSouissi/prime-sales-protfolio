<?php
$firstName = explode(' ', trim((string) ($user['name'] ?? 'Bonjour')))[0] ?: 'Bonjour';
?>
<section class="welcome-banner">
    <div class="welcome-banner__shine"></div>
    <div class="welcome-banner__content"><span class="eyebrow"><span class="status-dot status-dot--green"></span> VOTRE ESPACE, VOTRE HISTOIRE</span><h2>Bonjour <?= e($firstName) ?><span class="welcome-wave">✦</span></h2><p><?= $isAdmin ? 'Tout est prêt pour faire découvrir le meilleur de votre savoir-faire.' : 'Votre prochaine belle présentation commence ici.' ?></p><a class="button button-light" href="<?= e(url('/prototypes')) ?>">Explorer la bibliothèque <?= icon('arrow-right') ?></a></div>
    <div class="welcome-visual" aria-hidden="true"><div class="welcome-visual__ring welcome-visual__ring--one"></div><div class="welcome-visual__ring welcome-visual__ring--two"></div><div class="welcome-visual__core"><?= icon('sparkle') ?></div><span class="welcome-float welcome-float--one">01 <small>CATALOGUE</small></span><span class="welcome-float welcome-float--two"><?= icon('check') ?> Prêt à présenter</span></div>
</section>

<?php if ($isAdmin): ?>
<section class="stats-grid" aria-label="Statistiques de l’espace">
    <?php foreach ($stats as $index => $stat): ?>
        <article class="stat-card stat-card--<?= e($stat['tone']) ?>" style="--card-index: <?= (int) $index ?>"><div class="stat-card__top"><span><?= e($stat['label']) ?></span><span class="stat-card__icon"><?= icon($stat['icon']) ?></span></div><strong class="stat-card__value"><?= number_format((int) $stat['value'], 0, ',', ' ') ?></strong><div class="stat-card__foot"><span class="stat-card__pulse"></span><?= e($stat['hint']) ?></div><div class="stat-card__shine"></div></article>
    <?php endforeach; ?>
</section>
<?php else: ?>
<section class="commercial-note glass-panel"><span class="commercial-note__icon"><?= icon('sparkle') ?></span><div><strong>Votre prochaine démo commence ici</strong><p>Filtrez par prestation et secteur pour trouver le projet qui fera écho aux besoins de votre client.</p></div><a class="button button-secondary" href="<?= e(url('/prototypes')) ?>">Trouver une réalisation <?= icon('arrow-right') ?></a></section>
<?php endif; ?>

<div class="dashboard-columns">
    <section class="dashboard-section dashboard-section--wide">
        <div class="section-heading"><div><span class="eyebrow">LA BIBLIOTHÈQUE</span><h3>Ajouts récents</h3></div><a class="text-link" href="<?= e(url('/prototypes')) ?>">Tout voir <?= icon('arrow') ?></a></div>
        <?php if ($latest): ?>
            <div class="recent-grid">
                <?php foreach ($latest as $prototype): ?>
                    <article class="recent-card"><a class="recent-card__cover" href="<?= e($prototype['url']) ?>" target="_blank" rel="noopener noreferrer"><img src="<?= e($prototype['cover_url']) ?>" alt="Aperçu de <?= e($prototype['name']) ?>" loading="lazy"><span class="recent-card__open"><?= icon('arrow') ?></span></a><div class="recent-card__body"><div class="tag-row"><span class="tag tag--blue"><?= e($prototype['category_name']) ?></span><span class="tag tag--subtle"><?= e($prototype['domain_name']) ?></span></div><a class="recent-card__title" href="<?= e($prototype['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($prototype['name']) ?></a><span class="recent-card__date"><?= icon('clock') ?> Ajouté le <?= e(format_date($prototype['created_at'])) ?></span></div></article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-inline"><span class="empty-inline__icon"><?= icon('layers') ?></span><div><strong>La bibliothèque attend sa première réalisation.</strong><p>Les nouveaux prototypes apparaîtront ici, prêts à être présentés.</p></div><?php if ($isAdmin): ?><a class="button button-secondary button-small" href="<?= e(url('/prototypes/create')) ?>"><?= icon('plus') ?> Ajouter un prototype</a><?php endif; ?></div>
        <?php endif; ?>
    </section>
    <aside class="dashboard-aside">
        <section class="dashboard-side-card glass-panel">
            <div class="section-heading section-heading--small"><div><span class="eyebrow">RACCOURCIS</span><h3>Accès rapides</h3></div></div>
            <a class="quick-link" href="<?= e(url('/prototypes')) ?>"><span class="quick-link__icon quick-link__icon--blue"><?= icon('search') ?></span><span><strong>Rechercher un prototype</strong><small>Trouvez le bon exemple</small></span><?= icon('arrow') ?></a>
            <a class="quick-link" href="<?= e(url('/categories')) ?>"><span class="quick-link__icon quick-link__icon--purple"><?= icon('layers') ?></span><span><strong>Parcourir les catégories</strong><small>Par type de prestation</small></span><?= icon('arrow') ?></a>
            <a class="quick-link" href="<?= e(url('/domains')) ?>"><span class="quick-link__icon quick-link__icon--mint"><?= icon('globe') ?></span><span><strong>Explorer les domaines</strong><small>Par secteur d’activité</small></span><?= icon('arrow') ?></a>
        </section>
        <section class="insight-card"><span class="insight-card__badge"><?= icon('sparkle') ?> CONSEIL DU JOUR</span><h3>La bonne démo<br>commence par une question.</h3><p>Identifiez le défi de votre client, puis choisissez une réalisation qui lui ressemble.</p><span class="insight-card__decoration">✦</span></section>
    </aside>
</div>

<?php if ($isAdmin): ?>
<section class="dashboard-section login-activity-section"><div class="section-heading"><div><span class="eyebrow">SÉCURITÉ & ACCÈS</span><h3>Dernières connexions</h3></div><span class="activity-pill"><?= icon('shield') ?> Journal des accès</span></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Compte</th><th>Adresse e-mail</th><th>Adresse IP</th><th>Résultat</th><th>Date</th></tr></thead><tbody>
        <?php if ($latestLogins): foreach ($latestLogins as $entry): ?><tr><td><span class="table-person"><span class="avatar avatar--tiny"><?= e(initials((string) ($entry['user_name'] ?? $entry['email']))) ?></span><strong><?= e($entry['user_name'] ?: 'Utilisateur inconnu') ?></strong></span></td><td><?= e($entry['email']) ?></td><td class="mono-cell"><?= e($entry['ip_address']) ?></td><td><span class="status-badge <?= (int) $entry['success'] === 1 ? 'status-badge--success' : 'status-badge--danger' ?>"><i></i><?= (int) $entry['success'] === 1 ? 'Réussie' : 'Échouée' ?></span></td><td><?= e(format_datetime($entry['created_at'])) ?></td></tr><?php endforeach; else: ?><tr><td colspan="5" class="table-empty">Aucune connexion enregistrée pour le moment.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php endif; ?>
