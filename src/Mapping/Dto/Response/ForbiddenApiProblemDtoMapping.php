<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\ApiProblem\ForbiddenApiProblem;

final class ForbiddenApiProblemDtoMapping extends ApiProblemDtoMapping
{
    public static function getEntityClass(): string
    {
        return ForbiddenApiProblem::class;
    }
}
