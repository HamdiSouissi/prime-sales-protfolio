<section class="page-intro page-intro--compact">
    <div><a class="back-link" href="<?= e(url('/' . $entityKey)) ?>"><?= icon('chevron-left') ?> Retour aux <?= e(mb_strtolower($entityName, 'UTF-8')) ?></a><span class="eyebrow"><?= $item ? 'MISE À JOUR' : 'NOUVELLE ENTRÉE' ?></span><h2><?= $item ? 'Modifier une entrée' : 'Créer une entrée' ?></h2><p>Quelques informations suffisent pour structurer votre bibliothèque.</p></div>
</section>
<section class="form-card glass-panel form-card--narrow">
    <form method="post" action="<?= e($formAction) ?>" class="form-stack">
        <?= csrf_field() ?>
        <label class="field"><span>Nom de la <?= e(mb_strtolower(rtrim($entityName, 's'), 'UTF-8')) ?> <b class="required-mark">*</b></span><input type="text" name="name" value="<?= e(old('name', $item['name'] ?? '')) ?>" placeholder="Ex. Site Web" maxlength="120" required autofocus></label>
        <label class="field"><span>Description <small>Facultatif</small></span><textarea name="description" rows="4" maxlength="500" placeholder="Décrivez brièvement cette entrée…"><?= e(old('description', $item['description'] ?? '')) ?></textarea><small class="field-hint">Jusqu’à 500 caractères.</small></label>
        <div class="form-actions"><a class="button button-quiet" href="<?= e(url('/' . $entityKey)) ?>">Annuler</a><button class="button button-primary" type="submit"><?= icon('check') ?> <?= $item ? 'Enregistrer les changements' : 'Créer l’entrée' ?></button></div>
    </form>
</section>
