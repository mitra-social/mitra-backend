<?php

declare(strict_types=1);

namespace Mitra\Dto\Request;

final class UpdateUserRequestDto
{
    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string|null
     */
    public $newPassword;
}
