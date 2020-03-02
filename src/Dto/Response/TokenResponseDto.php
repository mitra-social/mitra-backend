<?php

declare(strict_types=1);

namespace Mitra\Dto\Response;

final class TokenResponseDto
{

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $iat;

    /**
     * @var string
     */
    public $exp;
}
