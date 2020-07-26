<?php

declare(strict_types=1);

namespace Mitra\Filtering;

final class FilterFactory implements FilterFactoryInterface
{

    public function create(string $filterQueryStr): Filter
    {
        return new Filter($filterQueryStr, FilterTokenizer::create($filterQueryStr));
    }
}
