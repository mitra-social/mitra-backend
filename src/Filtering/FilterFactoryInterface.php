<?php

declare(strict_types=1);

namespace Mitra\Filtering;

interface FilterFactoryInterface
{
    public function create(string $filterQueryStr): Filter;
}
