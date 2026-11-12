<?php $currentUserId = (int) ($GLOBALS['auth']->user()['id'] ?? 0); ?>
<section class="page-intro page-intro--compact">
    <div><span class="eyebrow">L’ÉQUIPE PRIME SALES</span><h2>Les bonnes personnes,<br><em>les bons accès.</em></h2><p>Gérez les comptes, les rôles et les accès à votre bibliothèque.</p></div>
    <a class="button button-primary" href="<?= e(url('/admin/users/create')) ?>"><?= icon('plus') ?> Ajouter un collaborateur</a>
</section>
<section class="team-summary"><div class="team-summary__icon"><?= icon('users') ?></div><div><strong><?= number_format(count($usersList), 0, ',', ' ') ?> compte<?= count($usersList) === 1 ? '' : 's' ?> dans l’espace</strong><span>Les collaborateurs peuvent parcourir les réalisations et gérer leur profil.</span></div><span class="team-summary__secure"><?= icon('shield') ?> Accès protégés</span></section>
<section class="glass-panel table-panel"><div class="table-panel__heading"><div><span class="eyebrow">COMPTES & RÔLES</span><h3>Votre équipe</h3></div><span class="table-count"><?= count($usersList) ?> membre<?= count($usersList) === 1 ? '' : 's' ?></span></div>
    <div class="table-wrap"><table class="data-table team-table"><thead><tr><th>Collaborateur</th><th>Rôle</th><th>Dernière connexion</th><th>Statut</th><th class="align-right">Actions</th></tr></thead><tbody>
        <?php foreach ($usersList as $person): ?>
            <tr>
                <td><span class="table-person"><span class="avatar <?= $person['role'] === 'ADMIN' ? 'avatar--admin' : '' ?>"><?= e(initials($person['name'])) ?></span><span class="table-person__text"><strong><?= e($person['name']) ?><?= (int) $person['id'] === $currentUserId ? ' <small>(vous)</small>' : '' ?></strong><span><?= e($person['email']) ?></span></span></span></td>
                <td><span class="role-badge <?= $person['role'] === 'ADMIN' ? 'role-badge--admin' : 'role-badge--commercial' ?>"><?= $person['role'] === 'ADMIN' ? icon('shield') : icon('user') ?> <?= $person['role'] === 'ADMIN' ? 'Administrateur' : 'Commercial' ?></span></td>
                <td class="muted-cell"><?= e(format_datetime($person['last_login'])) ?></td>
                <td><span class="status-badge <?= (int) $person['is_active'] === 1 ? 'status-badge--success' : 'status-badge--muted' ?>"><i></i><?= (int) $person['is_active'] === 1 ? 'Actif' : 'Suspendu' ?></span></td>
                <td><div class="table-actions"><a class="icon-button icon-button--small" href="<?= e(url('/admin/users/' . (int) $person['id'] . '/edit')) ?>" title="Modifier le compte"><?= icon('edit') ?></a>
                    <form method="post" action="<?= e(url('/admin/users/' . (int) $person['id'] . '/reset-password')) ?>" data-confirm="Générer un mot de passe temporaire pour <?= e($person['name']) ?> ? Celui-ci sera affiché une seule fois dans le message de confirmation."><?= csrf_field() ?><button class="icon-button icon-button--small" type="submit" title="Réinitialiser le mot de passe"><?= icon('lock') ?></button></form>
                    <?php if ((int) $person['id'] !== $currentUserId): ?><form method="post" action="<?= e(url('/admin/users/' . (int) $person['id'] . '/toggle')) ?>" data-confirm="<?= (int) $person['is_active'] === 1 ? 'Suspendre' : 'Réactiver' ?> l’accès de <?= e($person['name']) ?> ?"><?= csrf_field() ?><button class="icon-button icon-button--small <?= (int) $person['is_active'] === 1 ? 'icon-button--warning' : 'icon-button--success' ?>" type="submit" title="<?= (int) $person['is_active'] === 1 ? 'Suspendre' : 'Réactiver' ?> le compte"><?= (int) $person['is_active'] === 1 ? icon('close') : icon('check') ?></button></form><form method="post" action="<?= e(url('/admin/users/' . (int) $person['id'] . '/delete')) ?>" data-confirm="Supprimer définitivement le compte de <?= e($person['name']) ?> ? Cette action est irréversible."><?= csrf_field() ?><button class="icon-button icon-button--small icon-button--danger" type="submit" title="Supprimer le compte"><?= icon('trash') ?></button></form><?php endif; ?>
                </div></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$usersList): ?><tr><td colspan="5" class="table-empty">Aucun compte à afficher.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
