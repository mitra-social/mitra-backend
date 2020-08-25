<?php

declare(strict_types=1);

namespace Mitra\ApiProblem;

final class UnauthorizedApiProblem extends ApiProblem
{
    public function __construct()
    {
        parent::__construct('https://tools.ietf.org/html/rfc7231#section-6.5.3', 'Unauthorized', 401);
    }
}
