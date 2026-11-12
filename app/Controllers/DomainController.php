<?php

declare(strict_types=1);

namespace PrimeSales\Controllers;

use PrimeSales\Models\CategoryModel;
use PrimeSales\Models\DomainModel;

final class DomainController extends TaxonomyController
{
    protected function entityName(): string
    {
        return 'Domaines';
    }

    protected function entityKey(): string
    {
        return 'domains';
    }

    protected function repository(): CategoryModel|DomainModel
    {
        return $this->domains;
    }
}
