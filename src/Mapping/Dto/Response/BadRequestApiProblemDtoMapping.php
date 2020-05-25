<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\ApiProblem\BadRequestApiProblem;

final class BadRequestApiProblemDtoMapping extends ApiProblemDtoMapping
{

    public static function getEntityClass(): string
    {
        return BadRequestApiProblem::class;
    }
}
