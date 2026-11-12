<?php

declare(strict_types=1);

namespace PrimeSales\Controllers;

use PrimeSales\Models\CategoryModel;
use PrimeSales\Models\DomainModel;

final class CategoryController extends TaxonomyController
{
    protected function entityName(): string
    {
        return 'Catégories';
    }

    protected function entityKey(): string
    {
        return 'categories';
    }

    protected function repository(): CategoryModel|DomainModel
    {
        return $this->categories;
    }
}
