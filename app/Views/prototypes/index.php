<?php
$page = (int) $result['page'];
$pages = (int) $result['pages'];
?>
<section class="page-intro library-intro">
    <div><span class="eyebrow"><span class="status-dot status-dot--blue"></span> VOTRE SAVOIR-FAIRE, EN UN CLIC</span><h2>Trouvez la bonne <em>inspiration.</em></h2><p>Des réalisations concrètes pour ouvrir la conversation et donner vie aux idées.</p></div>
    <?php if ($isAdmin): ?><a class="button button-primary" href="<?= e(url('/prototypes/create')) ?>"><?= icon('plus') ?> Ajouter un prototype</a><?php endif; ?>
</section>
<section class="library-panel glass-panel" data-library data-api-url="<?= e(url('/api/prototypes')) ?>" data-current-page="<?= $page ?>" data-admin="<?= $isAdmin ? '1' : '0' ?>" data-csrf-token="<?= e(\PrimeSales\Core\Csrf::token()) ?>">
    <form class="library-toolbar" method="get" action="<?= e(url('/prototypes')) ?>" data-library-form>
        <label class="library-search"><span class="sr-only">Rechercher un prototype</span><?= icon('search') ?><input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Un nom, un secteur, une idée…" autocomplete="off" data-library-search><kbd>⌘ K</kbd></label>
        <label class="select-control"><span class="sr-only">Filtrer par catégorie</span><?= icon('layers') ?><select name="category" data-library-filter><option value="">Toutes les catégories</option><?php foreach ($categoriesList as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (string) $filters['category'] === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select><?= icon('chevron-down', 'select-control__chevron') ?></label>
        <label class="select-control"><span class="sr-only">Filtrer par domaine</span><?= icon('globe') ?><select name="domain" data-library-filter><option value="">Tous les domaines</option><?php foreach ($domainsList as $domain): ?><option value="<?= (int) $domain['id'] ?>" <?= (string) $filters['domain'] === (string) $domain['id'] ? 'selected' : '' ?>><?= e($domain['name']) ?></option><?php endforeach; ?></select><?= icon('chevron-down', 'select-control__chevron') ?></label>
        <label class="select-control select-control--sort"><span class="sr-only">Trier les prototypes</span><?= icon('filter') ?><select name="sort" data-library-filter><option value="recent" <?= $filters['sort'] === 'recent' ? 'selected' : '' ?>>Plus récents</option><option value="alpha" <?= $filters['sort'] === 'alpha' ? 'selected' : '' ?>>Ordre alphabétique</option><option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Plus anciens</option></select><?= icon('chevron-down', 'select-control__chevron') ?></label>
    </form>
    <div class="library-meta"><span><strong data-result-count><?= number_format((int) $result['total'], 0, ',', ' ') ?></strong> réalisation<?= (int) $result['total'] === 1 ? '' : 's' ?> dans votre bibliothèque</span><button type="button" class="clear-filters" data-clear-filters <?= ($filters['q'] || $filters['category'] || $filters['domain'] || $filters['sort'] !== 'recent') ? '' : 'hidden' ?>>Effacer les filtres <span>×</span></button></div>
    <div class="prototype-grid" data-prototype-grid>
        <?php foreach ($result['items'] as $prototype): include __DIR__ . '/_card.php'; endforeach; ?>
    </div>
    <div class="skeleton-grid" data-skeleton-grid hidden><div class="skeleton-brand-loader"><img src="<?= e(url('/media/brand/logo')) ?>" alt=""><span>Recherche dans votre bibliothèque…</span></div><div class="skeleton-card"></div><div class="skeleton-card"></div><div class="skeleton-card"></div></div>
    <section class="empty-state empty-state--library" data-library-empty <?= $result['total'] > 0 ? 'hidden' : '' ?>><span class="empty-state__icon"><?= icon('search') ?></span><h3>Aucun prototype trouvé</h3><p>Essayez un autre mot-clé ou modifiez vos filtres pour découvrir les réalisations disponibles.</p><button type="button" class="button button-secondary" data-clear-empty>Réinitialiser les filtres</button></section>
    <nav class="pagination" aria-label="Pagination" data-pagination <?= $pages <= 1 ? 'hidden' : '' ?>>
        <button type="button" class="pagination__button" data-page="<?= max(1, $page - 1) ?>" <?= $page <= 1 ? 'disabled' : '' ?>><?= icon('chevron-left') ?> <span>Précédent</span></button>
        <div class="pagination__pages" data-pagination-pages><?php for ($number = max(1, $page - 2); $number <= min($pages, $page + 2); $number++): ?><button type="button" data-page="<?= $number ?>" class="pagination__number <?= $number === $page ? 'is-current' : '' ?>"><?= $number ?></button><?php endfor; ?></div>
        <button type="button" class="pagination__button" data-page="<?= min($pages, $page + 1) ?>" <?= $page >= $pages ? 'disabled' : '' ?>><span>Suivant</span> <?= icon('chevron-right') ?></button>
    </nav>
</section>
