<?php

declare(strict_types=1);

namespace PrimeSales\Models;

use PDO;

abstract class Model
{
    public function __construct(protected readonly PDO $db)
    {
    }
}
