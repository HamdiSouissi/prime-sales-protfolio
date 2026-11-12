<article class="prototype-card">
    <a class="prototype-card__cover" href="<?= e($prototype['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Ouvrir <?= e($prototype['name']) ?> dans un nouvel onglet">
        <img src="<?= e($prototype['cover_url']) ?>" alt="Aperçu de <?= e($prototype['name']) ?>" loading="lazy">
        <span class="prototype-card__overlay"><span><?= icon('external') ?> Découvrir le site</span></span>
        <span class="prototype-card__corner"><?= icon('arrow') ?></span>
    </a>
    <div class="prototype-card__body">
        <div class="tag-row"><span class="tag tag--blue"><?= e($prototype['category_name']) ?></span><span class="tag tag--subtle"><?= e($prototype['domain_name']) ?></span></div>
        <h3><a href="<?= e($prototype['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($prototype['name']) ?></a></h3>
        <p><?= e(mb_strlen((string) ($prototype['description'] ?? ''), 'UTF-8') > 112 ? mb_substr((string) $prototype['description'], 0, 112, 'UTF-8') . '…' : ($prototype['description'] ?: 'Une réalisation de notre bibliothèque.')) ?></p>
        <div class="prototype-card__footer"><span class="prototype-date"><?= icon('clock') ?> <?= e(format_date($prototype['created_at'])) ?></span><a class="prototype-visit" href="<?= e($prototype['url']) ?>" target="_blank" rel="noopener noreferrer">Voir le site <?= icon('arrow-right') ?></a></div>
        <?php if (!empty($isAdmin)): ?><div class="prototype-admin-actions"><a href="<?= e(url('/prototypes/' . (int) $prototype['id'] . '/edit')) ?>" class="prototype-admin-link"><?= icon('edit') ?> Modifier</a><form method="post" action="<?= e(url('/prototypes/' . (int) $prototype['id'] . '/delete')) ?>" data-confirm="Supprimer définitivement ce prototype ?"><input type="hidden" name="_token" value="<?= e(\PrimeSales\Core\Csrf::token()) ?>"><button class="prototype-admin-link prototype-admin-link--danger" type="submit"><?= icon('trash') ?> Supprimer</button></form></div><?php endif; ?>
    </div>
</article>
