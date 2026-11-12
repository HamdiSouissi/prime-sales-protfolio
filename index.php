<?php

declare(strict_types=1);

use PrimeSales\Controllers\AuthController;
use PrimeSales\Controllers\CategoryController;
use PrimeSales\Controllers\DashboardController;
use PrimeSales\Controllers\DomainController;
use PrimeSales\Controllers\MediaController;
use PrimeSales\Controllers\ProfileController;
use PrimeSales\Controllers\PrototypeController;
use PrimeSales\Controllers\SettingsController;
use PrimeSales\Controllers\UserController;
use PrimeSales\Core\Auth;
use PrimeSales\Core\Database;
use PrimeSales\Core\Request;
use PrimeSales\Core\Router;
use PrimeSales\Core\View;
use PrimeSales\Models\SettingModel;
use PrimeSales\Models\UserModel;
use PrimeSales\Services\Installer;

require __DIR__ . '/app/bootstrap.php';

try {
    $db = Database::connection();
    if (!Installer::hasAdmin($db)) {
        redirect('/install.php');
    }
} catch (Throwable $exception) {
    error_log('[Prime Sales] Database bootstrap error: ' . $exception->getMessage());
    http_response_code(503);
    $setupUrl = url('/install.php');
    $title = e((string) (($GLOBALS['app_config']['name'] ?? 'Prime Sales')));
    $asset = e(asset('css/app.css'));
    echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Configuration requise · ' . $title . '</title><link rel="stylesheet" href="' . $asset . '"></head><body class="guest-body"><main class="setup-error"><div class="brand-mark">P</div><span class="eyebrow">PREMIÈRE CONFIGURATION</span><h1>La bibliothèque se prépare.</h1><p>Configurez les identifiants MySQL dans <code>app/Config/database.php</code>, créez la base depuis votre hébergeur, puis lancez l’installation sécurisée.</p><a class="button button-primary" href="' . e($setupUrl) . '">Ouvrir l’installation ' . icon('arrow-right') . '</a></main></body></html>';
    exit;
}

$view = new View();
$userModel = new UserModel($db);
$auth = new Auth($userModel);
$GLOBALS['auth'] = $auth;
$GLOBALS['view_settings'] = (new SettingModel($db))->all();

$authController = new AuthController($db, $auth, $view);
$dashboardController = new DashboardController($db, $auth, $view);
$categoryController = new CategoryController($db, $auth, $view);
$domainController = new DomainController($db, $auth, $view);
$prototypeController = new PrototypeController($db, $auth, $view);
$userController = new UserController($db, $auth, $view);
$profileController = new ProfileController($db, $auth, $view);
$settingsController = new SettingsController($db, $auth, $view);
$mediaController = new MediaController($db, $auth, $view);

$router = new Router($auth, $view);
$router->get('/', [$dashboardController, 'home']);
$router->get('/dashboard', [$dashboardController, 'index']);
$router->get('/login', [$authController, 'showLogin'], false);
$router->post('/login', [$authController, 'login'], false);
$router->post('/logout', [$authController, 'logout']);

$router->get('/categories', [$categoryController, 'index']);
$router->get('/categories/create', [$categoryController, 'create'], true, true);
$router->post('/categories/create', [$categoryController, 'store'], true, true);
$router->get('/categories/{id}/edit', [$categoryController, 'edit'], true, true);
$router->post('/categories/{id}/edit', [$categoryController, 'update'], true, true);
$router->post('/categories/{id}/delete', [$categoryController, 'delete'], true, true);

$router->get('/domains', [$domainController, 'index']);
$router->get('/domains/create', [$domainController, 'create'], true, true);
$router->post('/domains/create', [$domainController, 'store'], true, true);
$router->get('/domains/{id}/edit', [$domainController, 'edit'], true, true);
$router->post('/domains/{id}/edit', [$domainController, 'update'], true, true);
$router->post('/domains/{id}/delete', [$domainController, 'delete'], true, true);

$router->get('/prototypes', [$prototypeController, 'index']);
$router->get('/api/prototypes', [$prototypeController, 'search']);
$router->get('/prototypes/create', [$prototypeController, 'create'], true, true);
$router->post('/prototypes/create', [$prototypeController, 'store'], true, true);
$router->get('/prototypes/{id}/edit', [$prototypeController, 'edit'], true, true);
$router->post('/prototypes/{id}/edit', [$prototypeController, 'update'], true, true);
$router->post('/prototypes/{id}/delete', [$prototypeController, 'delete'], true, true);

$router->get('/admin/users', [$userController, 'index'], true, true);
$router->get('/admin/users/create', [$userController, 'create'], true, true);
$router->post('/admin/users/create', [$userController, 'store'], true, true);
$router->get('/admin/users/{id}/edit', [$userController, 'edit'], true, true);
$router->post('/admin/users/{id}/edit', [$userController, 'update'], true, true);
$router->post('/admin/users/{id}/toggle', [$userController, 'toggle'], true, true);
$router->post('/admin/users/{id}/reset-password', [$userController, 'resetPassword'], true, true);
$router->post('/admin/users/{id}/delete', [$userController, 'delete'], true, true);

$router->get('/profile', [$profileController, 'index']);
$router->post('/profile', [$profileController, 'update']);
$router->post('/profile/password', [$profileController, 'changePassword']);
$router->get('/settings', [$settingsController, 'index'], true, true);
$router->post('/settings', [$settingsController, 'update'], true, true);

$router->get('/media/cover/{id}', [$mediaController, 'cover']);
$router->get('/media/brand/{kind}', [$mediaController, 'brand'], false);

try {
    $router->dispatch(Request::fromGlobals());
} catch (Throwable $exception) {
    error_log('[Prime Sales] Unhandled application error: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
    http_response_code(500);
    $message = ($GLOBALS['app_config']['debug'] ?? false)
        ? $exception->getMessage()
        : 'Une erreur inattendue est survenue. Réessayez dans quelques instants.';
    $view->render('errors/error', [
        'pageTitle' => 'Une erreur est survenue',
        'message' => $message,
        'statusCode' => 500,
    ], $auth->user() ? 'layouts/app' : 'layouts/guest');
}
