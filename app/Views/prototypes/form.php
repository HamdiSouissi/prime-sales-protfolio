<?php
$prototype = $prototype ?? null;
$coverPreview = $prototype && !empty($prototype['cover_path'])
    ? url('/media/cover/' . (int) $prototype['id'])
    : asset('images/cover-fallback.svg');
?>
<section class="page-intro page-intro--compact">
    <div><a class="back-link" href="<?= e(url('/prototypes')) ?>"><?= icon('chevron-left') ?> Retour à la bibliothèque</a><span class="eyebrow"><?= $prototype ? 'ÉDITER LA RÉALISATION' : 'ENRICHIR LA BIBLIOTHÈQUE' ?></span><h2><?= $prototype ? 'Modifier le prototype' : 'Ajouter un prototype' ?></h2><p>Décrivez le projet, classez-le et ajoutez l’image qui le mettra en valeur.</p></div>
</section>
<section class="form-card glass-panel form-card--wide">
    <form method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="form-stack" data-prototype-form>
        <?= csrf_field() ?>
        <div class="form-section-heading"><span class="form-section-heading__icon"><?= icon('image') ?></span><div><strong>La réalisation</strong><small>Les informations visibles par l’équipe commerciale.</small></div></div>
        <label class="field"><span>Nom du prototype <b class="required-mark">*</b></span><input type="text" name="name" value="<?= e(old('name', $prototype['name'] ?? '')) ?>" placeholder="Ex. Le Bistrot — expérience digitale" maxlength="180" required autofocus></label>
        <div class="field-row">
            <label class="field"><span>Catégorie de prestation <b class="required-mark">*</b></span><span class="select-wrap"><select name="category_id" required><option value="">Choisir une catégorie</option><?php foreach ($categoriesList as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (string) old('category_id', $prototype['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select><?= icon('chevron-down') ?></span></label>
            <label class="field"><span>Domaine d’activité <b class="required-mark">*</b></span><span class="select-wrap"><select name="domain_id" required><option value="">Choisir un domaine</option><?php foreach ($domainsList as $domain): ?><option value="<?= (int) $domain['id'] ?>" <?= (string) old('domain_id', $prototype['domain_id'] ?? '') === (string) $domain['id'] ? 'selected' : '' ?>><?= e($domain['name']) ?></option><?php endforeach; ?></select><?= icon('chevron-down') ?></span></label>
        </div>
        <label class="field"><span>Description <small>Optionnel</small></span><textarea name="description" rows="4" maxlength="6000" placeholder="Le contexte, les points forts et la valeur créée pour le client…"><?= e(old('description', $prototype['description'] ?? '')) ?></textarea></label>
        <label class="field"><span>Adresse du site <b class="required-mark">*</b></span><div class="input-with-icon"><?= icon('globe') ?><input type="url" name="url" value="<?= e(old('url', $prototype['url'] ?? '')) ?>" placeholder="https://www.exemple.fr" maxlength="2048" required></div><small class="field-hint">Le site s’ouvrira dans un nouvel onglet depuis la carte du prototype.</small></label>

        <div class="cover-upload-block">
            <span class="field-label">Image de couverture <small>Optionnelle</small></span>
            <label class="upload-drop upload-drop--cover" for="prototype-cover-upload">
                <span class="upload-drop__preview upload-drop__preview--cover"><img src="<?= e($coverPreview) ?>" alt="Aperçu de la couverture <?= $prototype ? 'actuelle' : 'par défaut' ?>"></span>
                <span class="upload-drop__copy"><strong><?= $prototype && !empty($prototype['cover_path']) ? 'Remplacer la couverture' : 'Choisir une image de couverture' ?></strong><small>JPG, PNG ou WebP · 5 Mo maximum</small><small class="upload-file-name" data-file-name>Aucune image sélectionnée</small></span>
                <span class="button button-quiet button-small"><?= icon('image') ?> Parcourir</span>
                <input id="prototype-cover-upload" type="file" name="cover" accept="image/jpeg,image/png,image/webp" data-image-preview data-cover-input>
            </label>
            <?php if ($prototype && !empty($prototype['cover_path'])): ?><label class="checkbox-field"><input type="checkbox" name="remove_cover" value="1" data-remove-cover><span>Retirer cette couverture et utiliser l’image par défaut</span></label><?php endif; ?>
        </div>
        <div class="cover-note"><span class="cover-note__icon"><?= icon('sparkle') ?></span><p><strong>Votre image, votre choix.</strong><br>La couverture est fournie directement par l’administrateur. Si aucune image n’est ajoutée, une illustration de remplacement sera utilisée.</p></div>
        <div class="form-actions"><a class="button button-quiet" href="<?= e(url('/prototypes')) ?>">Annuler</a><button class="button button-primary" type="submit" data-submit-button><?= icon('check') ?> <?= $prototype ? 'Enregistrer les changements' : 'Ajouter à la bibliothèque' ?></button></div>
    </form>
</section>
