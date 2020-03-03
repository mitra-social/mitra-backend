<?php

declare(strict_types=1);

namespace Mitra\Dto\Response;

final class UserResponseDto
{
    /**
     * @var string
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
    public $registeredAt;
}
