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
    public $currentPassword;

    /**
     * @var string|null
     */
    public $newPassword;
}
