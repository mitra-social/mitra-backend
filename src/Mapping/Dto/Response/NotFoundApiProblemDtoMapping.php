<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\ApiProblem\NotFoundApiProblem;

final class NotFoundApiProblemDtoMapping extends ApiProblemDtoMapping
{
    public static function getEntityClass(): string
    {
        return NotFoundApiProblem::class;
    }
}
