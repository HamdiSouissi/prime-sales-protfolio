<?php
$isAdmin = $GLOBALS['auth']->isAdmin();
$total = count($items);
?>
<section class="page-intro page-intro--compact">
    <div><span class="eyebrow">ORGANISEZ VOTRE CATALOGUE</span><h2><?= e($entityName) ?> <span class="title-count"><?= (int) $total ?></span></h2><p><?= e($descriptionText) ?></p></div>
    <?php if ($isAdmin): ?><a class="button button-primary" href="<?= e(url('/' . $entityKey . '/create')) ?>"><?= icon('plus') ?><span>Créer une entrée</span></a><?php endif; ?>
</section>
<?php if ($total > 0): ?>
    <div class="taxonomy-grid">
        <?php foreach ($items as $index => $item): ?>
            <article class="taxonomy-card" style="--card-index: <?= (int) $index ?>">
                <div class="taxonomy-card__top"><span class="taxonomy-icon taxonomy-icon--<?= ['blue','purple','mint','orange'][$index % 4] ?>"><?= icon($entityKey === 'categories' ? 'layers' : 'globe') ?></span><span class="taxonomy-card__number"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span></div>
                <h3><?= e($item['name']) ?></h3>
                <p><?= e($item['description'] ?: ($entityKey === 'categories' ? 'Type de prestation pour vos réalisations.' : 'Secteur d’activité de votre clientèle.')) ?></p>
                <div class="taxonomy-card__bottom"><span class="taxonomy-count"><strong><?= (int) $item['prototype_count'] ?></strong> prototype<?= (int) $item['prototype_count'] === 1 ? '' : 's' ?></span><a class="text-link" href="<?= e(url('/prototypes?' . ($entityKey === 'categories' ? 'category' : 'domain') . '=' . (int) $item['id'])) ?>">Explorer <?= icon('arrow') ?></a></div>
                <?php if ($isAdmin): ?><div class="taxonomy-actions"><a class="icon-button icon-button--small" href="<?= e(url('/' . $entityKey . '/' . (int) $item['id'] . '/edit')) ?>" aria-label="Modifier <?= e($item['name']) ?>"><?= icon('edit') ?></a><form method="post" action="<?= e(url('/' . $entityKey . '/' . (int) $item['id'] . '/delete')) ?>" data-confirm="Supprimer cette entrée ? Les entrées utilisées par un prototype ne peuvent pas être supprimées."><?= csrf_field() ?><button class="icon-button icon-button--small icon-button--danger" type="submit" aria-label="Supprimer <?= e($item['name']) ?>"><?= icon('trash') ?></button></form></div><?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <section class="empty-state glass-panel"><span class="empty-state__icon"><?= icon($entityKey === 'categories' ? 'layers' : 'globe') ?></span><h3>Aucune entrée pour le moment</h3><p>Ajoutez une première <?= e(mb_strtolower(rtrim($entityName, 's'), 'UTF-8')) ?> pour commencer à classer les prototypes.</p><?php if ($isAdmin): ?><a class="button button-primary" href="<?= e(url('/' . $entityKey . '/create')) ?>"><?= icon('plus') ?> Créer une entrée</a><?php endif; ?></section>
<?php endif; ?>
