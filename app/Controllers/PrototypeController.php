<?php

declare(strict_types=1);

namespace PrimeSales\Controllers;

use PDOException;
use PrimeSales\Core\Request;
use PrimeSales\Services\ScreenshotService;
use Throwable;

final class PrototypeController extends BaseController
{
    public function index(Request $request): void
    {
        $filters = $this->filters($request);
        $perPage = max(6, min(24, (int) ($GLOBALS['app_config']['pagination'] ?? 12)));
        $result = $this->prototypes->paginate($filters, (int) $request->query('page', 1), $perPage);
        $result['items'] = $this->withCoverUrls($result['items']);
        $this->page('prototypes/index', [
            'result' => $result,
            'filters' => $filters,
            'categoriesList' => $this->categories->all(),
            'domainsList' => $this->domains->all(),
            'isAdmin' => $this->auth->isAdmin(),
        ], 'Bibliothèque', 'prototypes');
    }

    public function search(Request $request): void
    {
        $filters = $this->filters($request);
        $perPage = max(6, min(24, (int) ($GLOBALS['app_config']['pagination'] ?? 12)));
        $result = $this->prototypes->paginate($filters, (int) $request->query('page', 1), $perPage);
        $result['items'] = array_map(static function (array $item): array {
            $item['cover_url'] = !empty($item['cover_path'])
                ? url('/media/cover/' . (int) $item['id'])
                : asset('images/cover-fallback.svg');
            return $item;
        }, $result['items']);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, private');
        echo json_encode([
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'pages' => $result['pages'],
            'per_page' => $result['per_page'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function create(Request $request): void
    {
        $this->page('prototypes/form', [
            'prototype' => null,
            'categoriesList' => $this->categories->all(),
            'domainsList' => $this->domains->all(),
            'formAction' => url('/prototypes/create'),
        ], 'Ajouter un prototype', 'prototypes');
    }

    public function store(Request $request): void
    {
        $data = $this->validatedData($request);
        if ($data === null) {
            remember_input($request->all());
            redirect('/prototypes/create');
        }

        $data['created_by'] = (int) $this->auth->user()['id'];
        try {
            $id = $this->prototypes->create($data);
            $cover = (new ScreenshotService())->capture($data['url']);
            $this->prototypes->updateCover($id, $cover);
            clear_old_input();
            flash('success', $cover
                ? 'Prototype ajouté avec sa capture de couverture.'
                : 'Prototype ajouté. Une couverture par défaut sera affichée (capture à configurer ou indisponible).');
        } catch (PDOException $exception) {
            error_log('[Prime Sales] Prototype insert failed: ' . $exception->getMessage());
            flash('danger', 'Impossible d’ajouter ce prototype. Vérifiez les informations saisies.');
        } catch (Throwable $exception) {
            error_log('[Prime Sales] Screenshot failed: ' . $exception->getMessage());
            flash('warning', 'Prototype ajouté, mais la capture a échoué. La couverture par défaut sera utilisée.');
        }
        redirect('/prototypes');
    }

    public function edit(Request $request, array $params): void
    {
        $prototype = $this->prototypes->find((int) ($params['id'] ?? 0));
        if (!$prototype) {
            flash('danger', 'Ce prototype n’existe plus.');
            redirect('/prototypes');
        }
        $this->page('prototypes/form', [
            'prototype' => $prototype,
            'categoriesList' => $this->categories->all(),
            'domainsList' => $this->domains->all(),
            'formAction' => url('/prototypes/' . (int) $prototype['id'] . '/edit'),
        ], 'Modifier le prototype', 'prototypes');
    }

    public function update(Request $request, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $prototype = $this->prototypes->find($id);
        if (!$prototype) {
            flash('danger', 'Ce prototype n’existe plus.');
            redirect('/prototypes');
        }
        $data = $this->validatedData($request);
        if ($data === null) {
            remember_input($request->all());
            redirect('/prototypes/' . $id . '/edit');
        }

        try {
            $this->prototypes->update($id, $data);
            if ((string) $prototype['url'] !== $data['url']) {
                $newCover = (new ScreenshotService())->capture($data['url']);
                $this->prototypes->updateCover($id, $newCover);
                if ($newCover !== $prototype['cover_path']) {
                    (new ScreenshotService())->delete($prototype['cover_path']);
                }
            }
            clear_old_input();
            flash('success', 'Prototype mis à jour.');
        } catch (Throwable $exception) {
            error_log('[Prime Sales] Prototype update failed: ' . $exception->getMessage());
            flash('danger', 'La mise à jour a échoué. Réessayez ou vérifiez les champs.');
        }
        redirect('/prototypes');
    }

    public function delete(Request $request, array $params): void
    {
        $prototype = $this->prototypes->find((int) ($params['id'] ?? 0));
        if ($prototype) {
            $this->prototypes->delete((int) $prototype['id']);
            (new ScreenshotService())->delete($prototype['cover_path'] ?? null);
            flash('success', 'Prototype supprimé de la bibliothèque.');
        } else {
            flash('warning', 'Ce prototype a déjà été supprimé.');
        }
        redirect('/prototypes');
    }

    private function validatedData(Request $request): ?array
    {
        $name = $this->textInput($request->input('name'), 180);
        $categoryId = filter_var($request->input('category_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $domainId = filter_var($request->input('domain_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $description = $this->textInput($request->input('description'), 6000);
        $urlValue = trim((string) $request->input('url', ''));
        $parts = parse_url($urlValue);
        $validUrl = filter_var($urlValue, FILTER_VALIDATE_URL)
            && is_array($parts)
            && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            && !isset($parts['user'])
            && !isset($parts['pass'])
            && mb_strlen($urlValue, 'UTF-8') <= 2048;

        if (mb_strlen($name, 'UTF-8') < 2) {
            flash('danger', 'Le nom du prototype doit contenir au moins 2 caractères.');
            return null;
        }
        if ($categoryId === false || !$this->categories->find((int) $categoryId)) {
            flash('danger', 'Sélectionnez une catégorie valide.');
            return null;
        }
        if ($domainId === false || !$this->domains->find((int) $domainId)) {
            flash('danger', 'Sélectionnez un domaine valide.');
            return null;
        }
        if (!$validUrl) {
            flash('danger', 'Saisissez une URL publique valide commençant par https:// ou http://.');
            return null;
        }

        return [
            'name' => $name,
            'category_id' => (int) $categoryId,
            'domain_id' => (int) $domainId,
            'description' => $description,
            'url' => $urlValue,
        ];
    }

    private function filters(Request $request): array
    {
        $sort = (string) $request->query('sort', 'recent');
        if (!in_array($sort, ['recent', 'oldest', 'alpha'], true)) {
            $sort = 'recent';
        }
        return [
            'q' => mb_substr(trim((string) $request->query('q', '')), 0, 120, 'UTF-8'),
            'category' => $request->query('category', ''),
            'domain' => $request->query('domain', ''),
            'sort' => $sort,
        ];
    }

    private function withCoverUrls(array $items): array
    {
        return array_map(static function (array $item): array {
            $item['cover_url'] = !empty($item['cover_path'])
                ? url('/media/cover/' . (int) $item['id'])
                : asset('images/cover-fallback.svg');
            return $item;
        }, $items);
    }
}
