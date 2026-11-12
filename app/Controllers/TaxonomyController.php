<?php

declare(strict_types=1);

namespace PrimeSales\Controllers;

use PDOException;
use PrimeSales\Core\Request;
use PrimeSales\Models\CategoryModel;
use PrimeSales\Models\DomainModel;

abstract class TaxonomyController extends BaseController
{
    abstract protected function entityName(): string;
    abstract protected function entityKey(): string;
    abstract protected function repository(): CategoryModel|DomainModel;

    public function index(Request $request): void
    {
        $name = $this->entityName();
        $items = $this->repository()->all();
        $this->page('taxonomy/index', [
            'items' => $items,
            'entityName' => $name,
            'entityKey' => $this->entityKey(),
            'descriptionText' => $this->entityKey() === 'categories'
                ? 'Regroupez vos réalisations par type de prestation.'
                : 'Organisez la bibliothèque selon les secteurs de vos clients.',
        ], $name, $this->entityKey());
    }

    public function create(Request $request): void
    {
        $this->page('taxonomy/form', [
            'entityName' => $this->entityName(),
            'entityKey' => $this->entityKey(),
            'item' => null,
            'formAction' => url('/' . $this->entityKey() . '/create'),
        ], 'Nouvelle entrée', $this->entityKey());
    }

    public function store(Request $request): void
    {
        $name = $this->textInput($request->input('name'), 120);
        $description = $this->textInput($request->input('description'), 500);
        if (mb_strlen($name, 'UTF-8') < 2) {
            remember_input(['name' => $name, 'description' => $description]);
            flash('danger', 'Le nom doit contenir au moins 2 caractères.');
            redirect('/' . $this->entityKey() . '/create');
        }
        try {
            $this->repository()->create($name, $description);
            clear_old_input();
            flash('success', $this->entityName() . ' créée avec succès.');
        } catch (PDOException $exception) {
            remember_input(['name' => $name, 'description' => $description]);
            flash('danger', $this->isDuplicate($exception) ? 'Une entrée avec ce nom existe déjà.' : 'Impossible d’enregistrer cette entrée.');
            redirect('/' . $this->entityKey() . '/create');
        }
        redirect('/' . $this->entityKey());
    }

    public function edit(Request $request, array $params): void
    {
        $item = $this->repository()->find((int) ($params['id'] ?? 0));
        if (!$item) {
            flash('danger', 'Cette entrée n’existe plus.');
            redirect('/' . $this->entityKey());
        }
        $this->page('taxonomy/form', [
            'entityName' => $this->entityName(),
            'entityKey' => $this->entityKey(),
            'item' => $item,
            'formAction' => url('/' . $this->entityKey() . '/' . (int) $item['id'] . '/edit'),
        ], 'Modifier ' . mb_strtolower($this->entityName(), 'UTF-8'), $this->entityKey());
    }

    public function update(Request $request, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $item = $this->repository()->find($id);
        if (!$item) {
            flash('danger', 'Cette entrée n’existe plus.');
            redirect('/' . $this->entityKey());
        }
        $name = $this->textInput($request->input('name'), 120);
        $description = $this->textInput($request->input('description'), 500);
        if (mb_strlen($name, 'UTF-8') < 2) {
            remember_input(['name' => $name, 'description' => $description]);
            flash('danger', 'Le nom doit contenir au moins 2 caractères.');
            redirect('/' . $this->entityKey() . '/' . $id . '/edit');
        }
        try {
            $this->repository()->update($id, $name, $description);
            clear_old_input();
            flash('success', $this->entityName() . ' mise à jour.');
        } catch (PDOException $exception) {
            remember_input(['name' => $name, 'description' => $description]);
            flash('danger', $this->isDuplicate($exception) ? 'Une entrée avec ce nom existe déjà.' : 'Impossible de mettre à jour cette entrée.');
            redirect('/' . $this->entityKey() . '/' . $id . '/edit');
        }
        redirect('/' . $this->entityKey());
    }

    public function delete(Request $request, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        try {
            $this->repository()->delete($id);
            flash('success', $this->entityName() . ' supprimée.');
        } catch (PDOException $exception) {
            flash('danger', $this->isConstraintError($exception)
                ? 'Cette entrée est utilisée par un ou plusieurs prototypes. Modifiez-les avant de la supprimer.'
                : 'Impossible de supprimer cette entrée.');
        }
        redirect('/' . $this->entityKey());
    }

    private function isDuplicate(PDOException $exception): bool
    {
        return $exception->getCode() === '23000';
    }

    private function isConstraintError(PDOException $exception): bool
    {
        return $exception->getCode() === '23000';
    }
}
