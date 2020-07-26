<?php

declare(strict_types=1);

namespace Mitra\Filtering;

interface FilterRendererInterface
{
    public function apply(Filter $filter): void;
}
