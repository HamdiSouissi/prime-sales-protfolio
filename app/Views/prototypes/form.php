<?php
$prototype = $prototype ?? null;
?>
<section class="page-intro page-intro--compact">
    <div><a class="back-link" href="<?= e(url('/prototypes')) ?>"><?= icon('chevron-left') ?> Retour à la bibliothèque</a><span class="eyebrow"><?= $prototype ? 'ÉDITER LA RÉALISATION' : 'ENRICHIR LA BIBLIOTHÈQUE' ?></span><h2><?= $prototype ? 'Modifier le prototype' : 'Ajouter un prototype' ?></h2><p>Décrivez le projet, classez-le et partagez son adresse. La couverture sera générée automatiquement.</p></div>
</section>
<section class="form-card glass-panel form-card--wide">
    <form method="post" action="<?= e($formAction) ?>" class="form-stack" data-prototype-form>
        <?= csrf_field() ?>
        <div class="form-section-heading"><span class="form-section-heading__icon"><?= icon('image') ?></span><div><strong>La réalisation</strong><small>Les informations visibles par l’équipe commerciale.</small></div></div>
        <label class="field"><span>Nom du prototype <b class="required-mark">*</b></span><input type="text" name="name" value="<?= e(old('name', $prototype['name'] ?? '')) ?>" placeholder="Ex. Le Bistrot — expérience digitale" maxlength="180" required autofocus></label>
        <div class="field-row">
            <label class="field"><span>Catégorie de prestation <b class="required-mark">*</b></span><span class="select-wrap"><select name="category_id" required><option value="">Choisir une catégorie</option><?php foreach ($categoriesList as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (string) old('category_id', $prototype['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select><?= icon('chevron-down') ?></span></label>
            <label class="field"><span>Domaine d’activité <b class="required-mark">*</b></span><span class="select-wrap"><select name="domain_id" required><option value="">Choisir un domaine</option><?php foreach ($domainsList as $domain): ?><option value="<?= (int) $domain['id'] ?>" <?= (string) old('domain_id', $prototype['domain_id'] ?? '') === (string) $domain['id'] ? 'selected' : '' ?>><?= e($domain['name']) ?></option><?php endforeach; ?></select><?= icon('chevron-down') ?></span></label>
        </div>
        <label class="field"><span>Description <small>Optionnel</small></span><textarea name="description" rows="4" maxlength="6000" placeholder="Le contexte, les points forts et la valeur créée pour le client…"><?= e(old('description', $prototype['description'] ?? '')) ?></textarea></label>
        <label class="field"><span>Adresse du site <b class="required-mark">*</b></span><div class="input-with-icon"><?= icon('globe') ?><input type="url" name="url" value="<?= e(old('url', $prototype['url'] ?? '')) ?>" placeholder="https://www.exemple.fr" maxlength="2048" required></div><small class="field-hint">Une capture d’écran sera demandée lors de l’enregistrement. En cas d’échec, une couverture élégante sera affichée par défaut.</small></label>
        <div class="capture-note"><span class="capture-note__icon"><?= icon('sparkle') ?></span><p><strong>Couverture automatique</strong><br>Si une clé ScreenshotOne est renseignée dans <code>app/Config/app.php</code>, la miniature sera générée et mise en cache à l’ajout. Aucun appel ne sera effectué pendant l’affichage de la bibliothèque.</p></div>
        <div class="form-actions"><a class="button button-quiet" href="<?= e(url('/prototypes')) ?>">Annuler</a><button class="button button-primary" type="submit" data-submit-button><?= icon('check') ?> <?= $prototype ? 'Enregistrer les changements' : 'Ajouter à la bibliothèque' ?></button></div>
    </form>
</section>
