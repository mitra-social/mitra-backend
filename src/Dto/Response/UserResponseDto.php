<?php

declare(strict_types=1);

namespace Mitra\Dto\Response;

use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;

final class UserResponseDto extends PersonDto
{
    /**
     * @var string
     */
    public $userId;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $registeredAt;
}
