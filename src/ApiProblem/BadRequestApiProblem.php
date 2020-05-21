<?php

declare(strict_types=1);

namespace Mitra\ApiProblem;

final class BadRequestApiProblem extends ApiProblem
{
    public function __construct()
    {
        parent::__construct('https://tools.ietf.org/html/rfc7231#section-6.5.1', 'Bad Request', 400);
    }
}
