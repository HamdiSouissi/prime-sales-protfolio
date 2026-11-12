<?php

declare(strict_types=1);

namespace PrimeSales\Controllers;

use PDO;
use PrimeSales\Core\Auth;
use PrimeSales\Core\View;
use PrimeSales\Models\CategoryModel;
use PrimeSales\Models\DomainModel;
use PrimeSales\Models\LoginLogModel;
use PrimeSales\Models\PrototypeModel;
use PrimeSales\Models\SettingModel;
use PrimeSales\Models\UserModel;

abstract class BaseController
{
    protected readonly UserModel $users;
    protected readonly CategoryModel $categories;
    protected readonly DomainModel $domains;
    protected readonly PrototypeModel $prototypes;
    protected readonly SettingModel $settings;
    protected readonly LoginLogModel $loginLogs;

    public function __construct(
        protected readonly PDO $db,
        protected readonly Auth $auth,
        protected readonly View $view,
    ) {
        $this->users = new UserModel($db);
        $this->categories = new CategoryModel($db);
        $this->domains = new DomainModel($db);
        $this->prototypes = new PrototypeModel($db);
        $this->settings = new SettingModel($db);
        $this->loginLogs = new LoginLogModel($db);
    }

    protected function page(string $template, array $data, string $title, string $activeNav, string $layout = 'layouts/app'): void
    {
        $data['pageTitle'] = $title;
        $data['activeNav'] = $activeNav;
        $this->view->render($template, $data, $layout);
    }

    protected function guestPage(string $template, array $data = []): void
    {
        $this->view->render($template, $data, 'layouts/guest');
    }

    protected function textInput(mixed $value, int $maxLength = 255): string
    {
        return mb_substr(trim((string) $value), 0, $maxLength, 'UTF-8');
    }
}
