<?php

declare(strict_types=1);

namespace Mitra\Dto\Request;

final class CreateUserRequestDto
{

    /**
     * @var string|null
     */
    public $id;

    /**
     * @var string
     */
    public $preferredUsername;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;
}
