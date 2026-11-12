# Prime Sales Portfolio

Bibliothèque de démonstration commerciale premium, conçue pour un hébergement mutualisé classique.

**Stack :** PHP natif 8.1+, MySQL / MariaDB, HTML5, CSS3 et JavaScript Vanilla. Aucun framework, Node.js, compilateur ou gestionnaire de dépendances n’est nécessaire.

## Fonctionnalités

- Connexion sécurisée par mot de passe hashé, sessions protégées, jetons CSRF et accès `ADMIN` / `COMMERCIAL`.
- Gestion administrateur des comptes, rôles, suspensions et mots de passe temporaires avec changement obligatoire à la prochaine connexion.
- Catalogue administrable de prototypes, catégories et domaines d’activité.
- Recherche instantanée (nom, description, catégorie, domaine), filtres, tri et pagination.
- Présentation des prototypes en cartes responsive, avec ouverture du site dans un nouvel onglet.
- Capture de couverture à l’ajout via l’API ScreenshotOne, cache local des images et couverture SVG de secours.
- Tableau de bord administrateur avec statistiques et journal récent des connexions.
- Profil, changement de mot de passe, nom d’entreprise, logo, favicon et couleurs personnalisables.
- Interface sombre glassmorphism, responsive et accessible, sans dépendance front-end.

## Prérequis

- PHP **8.1 ou supérieur** avec les extensions `PDO`, `pdo_mysql`, `mbstring` et `fileinfo` activées.
- MySQL 5.7+ ou MariaDB 10.3+.
- Apache avec `mod_rewrite` et autorisation des fichiers `.htaccess` (ou équivalent sur l’hébergement).
- Les dossiers `storage/` et ses sous-dossiers doivent être accessibles en écriture par PHP.
- cURL est recommandé pour les captures d’écran ; l’application utilise un flux HTTPS PHP si cURL n’est pas disponible.

> Il n’y a ni commande `npm`, ni `composer install`, ni étape de compilation.

## Installation sur un hébergement mutualisé

1. **Créer la base et son utilisateur** depuis le panneau de l’hébergeur (cPanel, OVH, Ionos, Hostinger…). Accordez à cet utilisateur les droits sur cette base. L’utilisateur MySQL n’a souvent pas le droit `CREATE DATABASE` ; l’application crée elle-même les tables et relations.
2. Copier le contenu du dépôt dans le répertoire servi par le domaine (par exemple `public_html/`). La structure complète doit rester intacte : `app/`, `assets/`, `database/`, `storage/`, `index.php`, `install.php` et `.htaccess`.
3. Éditer `app/Config/database.php` avec le nom de base, l’hôte, l’utilisateur et le mot de passe MySQL fournis par l’hébergeur.
4. Vérifier que `storage/`, `storage/covers/`, `storage/branding/` et `storage/logs/` sont accessibles en écriture par PHP. Les permissions usuelles sont `750` ou `755` selon l’hébergeur ; évitez `777` si possible.
5. Ouvrir `https://votre-domaine.fr/install.php`, créer l’administrateur initial et renseigner le nom de l’entreprise. L’installateur crée les tables, les clés étrangères, les index, les valeurs de marque par défaut ainsi que les catégories et domaines de départ.
6. Se connecter avec le compte administrateur créé. Ajouter des prototypes depuis **Bibliothèque → Ajouter un prototype**, puis créer les comptes commerciaux depuis **Équipe commerciale**.
7. Activer HTTPS avant de partager l’adresse aux collaborateurs. Après l’installation, vous pouvez supprimer `install.php` par mesure de précaution ; il se désactive déjà automatiquement dès qu’un compte administrateur existe.

### Installation manuelle du schéma (facultatif)

Si vous préférez phpMyAdmin, importez `database/schema.sql` dans la base que vous venez de créer, puis ouvrez quand même `/install.php` pour créer le premier administrateur et charger le catalogue de départ. Le schéma est également créé automatiquement par l’installateur.

## Captures d’écran des sites

Les hébergements mutualisés ne fournissent généralement pas de navigateur headless. `app/Services/ScreenshotService.php` intègre donc un client PHP léger de l’API de capture ScreenshotOne (requêtes HTTPS via cURL ou les flux PHP), sans Node.js ni paquet Composer.

Pour activer les miniatures automatiques, créez une clé ScreenshotOne puis renseignez-la dans `app/Config/app.php` :

```php
'screenshot' => [
    'access_key' => 'VOTRE_CLE_SCREENSHOTONE',
    'endpoint' => 'https://api.screenshotone.com/take',
    'timeout' => 75,
],
```

La capture est effectuée lors de la création du prototype (et régénérée si son URL change), puis enregistrée dans `storage/covers/`. Elle n’est pas recalculée à l’affichage. Sans clé, si le service est inaccessible ou si le site refuse la capture, l’application conserve une couverture graphique locale par défaut. Le fournisseur peut facturer les captures selon son offre ; la clé ne doit jamais être publiée dans le dépôt.

## Organisation du projet

```text
app/
  Config/        Configuration application et accès MySQL
  Controllers/   Contrôleurs HTTP et logique applicative
  Core/          Router, requête, PDO, session, CSRF, rendu et helpers
  Middleware/    Contrôles d’authentification, rôle et CSRF
  Models/        Accès aux données via PDO préparé
  Services/      Installation, capture et téléversement de marque
  Views/         Vues PHP et layouts
assets/
  css/           Styles responsive et animations
  images/        Marque et couverture locale de secours
  js/            Interactions Vanilla (recherche, navigation, formulaires)
database/
  schema.sql     Tables, index, clés étrangères et contraintes
storage/
  covers/        Captures mises en cache (non publiques directement)
  branding/      Logo et favicon uploadés (non publics directement)
  logs/          Réservé aux journaux d’exécution
index.php        Front controller
install.php      Installateur web sécurisé, désactivé après création admin
```

## Sécurité et exploitation

- Requêtes SQL préparées, échappement HTML, validation stricte des entrées, mots de passe de 12 caractères minimum et 72 octets maximum via `password_hash()` / `password_verify()`, et protection CSRF sur les formulaires POST.
- Sessions strictes, cookies `HttpOnly` et `SameSite=Lax`, régénération de l’identifiant après connexion, invalidation des sessions lors d’une réinitialisation du mot de passe et limitation des tentatives d’authentification.
- Les rôles sont vérifiés côté serveur ; masquer un bouton dans l’interface ne remplace jamais une autorisation.
- Les fichiers de code, le schéma et le stockage sont protégés par `.htaccess`. Les couvertures et éléments de marque sont servis par un contrôleur, jamais exécutés comme PHP.
- Faites régulièrement une sauvegarde de la base MySQL et de `storage/`. Ne versionnez pas les captures, logos privés ni les secrets de production.

## Tests de recette conseillés

1. Installer avec un nouvel administrateur puis vérifier que `/install.php` renvoie vers la connexion.
2. Créer un compte commercial et vérifier qu’il ne voit pas les écrans de gestion ni les statistiques administrateur.
3. Créer une catégorie, un domaine et un prototype ; tester la capture avec et sans clé API.
4. Tester recherche, filtres, ordre alphabétique, pagination et ouverture externe sur mobile et ordinateur.
5. Bloquer puis réactiver un compte, réinitialiser son mot de passe et vérifier l’obligation de le modifier.
6. Tester les téléversements de marque, les validations et les formulaires sans jeton CSRF.
