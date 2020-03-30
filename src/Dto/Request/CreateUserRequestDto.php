<?php

declare(strict_types=1);

namespace Mitra\Dto\Request;

final class CreateUserRequestDto
{

    /**
     * @var string
     */
    public $username;

    /**
     * @var null|string
     */
    public $displayName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;
}
