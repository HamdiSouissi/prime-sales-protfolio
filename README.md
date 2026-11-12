# Prime Sales Portfolio

Bibliothèque de démonstration commerciale conçue pour un hébergement mutualisé classique.

**Stack :** PHP natif 8.1+, MySQL / MariaDB, HTML5, CSS3 et JavaScript Vanilla. Aucun framework, Node.js, compilateur ou gestionnaire de dépendances n’est nécessaire.

## Fonctionnalités

- Connexion sécurisée, sessions protégées, jetons CSRF et rôles `ADMIN` / `COMMERCIAL`.
- Gestion des utilisateurs, rôles, suspensions et mots de passe temporaires par l’administrateur.
- Catalogue de prototypes, catégories et domaines d’activité.
- Recherche instantanée (nom, description, catégorie, domaine), filtres, tri et pagination.
- Couverture ajoutée manuellement par l’administrateur, avec validation des images et visuel de secours.
- Tableau de bord administrateur avec statistiques et journal des connexions.
- Profil, changement de mot de passe, nom d’entreprise, logo, favicon et couleurs personnalisables.
- Interface claire, lumineuse et colorée, surfaces glassmorphism et mise en page responsive.

## Prérequis

- PHP **8.1 ou supérieur** avec `PDO`, `pdo_mysql`, `mbstring` et `fileinfo`.
- MySQL 5.7+ ou MariaDB 10.3+.
- Apache avec `mod_rewrite` et autorisation des `.htaccess` (ou équivalent sur l’hébergement).
- Les dossiers `storage/` et ses sous-dossiers doivent être accessibles en écriture par PHP.

> Il n’y a ni commande `npm`, ni `composer install`, ni étape de compilation.

## Installation sur un hébergement mutualisé

1. Créer la base et son utilisateur dans le panneau de l’hébergeur, puis accorder à cet utilisateur les droits sur la base. La base doit exister ; l’application créera ses tables et ses relations.
2. Copier le contenu du dépôt dans le répertoire servi par le domaine (par exemple `public_html/`). Garder la structure `app/`, `assets/`, `database/`, `storage/`, `index.php`, `install.php` et `.htaccess`.
3. Renseigner les identifiants MySQL dans `app/Config/database.php`.
4. Vérifier que `storage/`, `storage/covers/`, `storage/branding/` et `storage/logs/` sont accessibles en écriture par PHP. Utiliser les permissions indiquées par l’hébergeur et éviter `777` si possible.
5. Ouvrir `https://votre-domaine.fr/install.php`, créer l’administrateur initial et renseigner le nom de l’entreprise. L’installateur crée les tables, index, clés étrangères, paramètres par défaut et le catalogue initial de catégories et domaines.
6. Se connecter, ajouter des prototypes depuis **Bibliothèque → Ajouter un prototype**, puis créer les comptes commerciaux depuis **Équipe commerciale**.
7. Activer HTTPS avant de partager l’application. Après l’installation, vous pouvez supprimer `install.php` par précaution ; il se désactive automatiquement dès qu’un administrateur existe.

### Schéma manuel (facultatif)

Si vous préférez phpMyAdmin, importez `database/schema.sql` dans la base, puis ouvrez `/install.php` pour créer le premier administrateur et charger le catalogue de départ.

## Couvertures des prototypes

Sur le formulaire d’ajout ou de modification, l’administrateur peut importer une image JPG, PNG ou WebP de **5 Mo maximum**. Le fichier est contrôlé côté serveur, enregistré dans `storage/covers/` et servi par un contrôleur ; il n’est pas accessible directement et ne peut pas être exécuté comme du code. Sur un prototype existant, l’administrateur peut remplacer la couverture ou la retirer. Sans image, l’application affiche une illustration locale de secours.

## Organisation

```text
app/
  Config/        Configuration de l’application et accès MySQL
  Controllers/   Contrôleurs HTTP
  Core/          Router, requête, PDO, session, CSRF, rendu et helpers
  Middleware/    Authentification, rôles et CSRF
  Models/        Accès aux données via PDO préparé
  Services/      Installation et validation / stockage des images
  Views/         Vues PHP et layouts
assets/
  css/           Styles responsive et thème clair
  images/        Marque et couverture locale de secours
  js/            Interactions JavaScript Vanilla
database/
  schema.sql     Tables, index, clés étrangères et contraintes
storage/
  covers/        Couvertures importées, non publiques directement
  branding/      Logo et favicon, non publics directement
  logs/          Réservé aux journaux d’exécution
index.php        Front controller
install.php      Installateur web sécurisé
```

## Sécurité et exploitation

- Requêtes SQL préparées, échappement HTML, validation des entrées, mots de passe hashés par `password_hash()` et protection CSRF des formulaires POST.
- Sessions strictes, cookies `HttpOnly` / `SameSite=Lax`, régénération d’identifiant à la connexion et invalidation des sessions après réinitialisation du mot de passe.
- Les droits sont contrôlés côté serveur ; masquer un bouton ne remplace pas une autorisation.
- Le code, le schéma et le stockage sont protégés par `.htaccess`. Les couvertures et éléments de marque sont servis par un contrôleur.
- Sauvegarder régulièrement la base MySQL et `storage/`. Les images téléversées et les secrets de production ne sont pas versionnés.

## Vérifications de recette conseillées

1. Installer avec un nouvel administrateur et vérifier que `/install.php` se désactive ensuite.
2. Créer un compte commercial et vérifier ses droits limités.
3. Créer une catégorie, un domaine et un prototype ; tester l’import, le remplacement et le retrait de sa couverture.
4. Tester recherche, filtres, tri, pagination et ouverture du site dans un nouvel onglet sur mobile et ordinateur.
5. Bloquer puis réactiver un compte et tester la réinitialisation du mot de passe.
6. Tester les téléversements de marque, la validation des entrées et les formulaires sans jeton CSRF.
