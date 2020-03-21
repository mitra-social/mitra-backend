<?php

declare(strict_types=1);

namespace Mitra\Dto\Response;

use Mitra\Dto\Response\ActivityPub\Actor\Person;

final class UserResponseDto extends Person
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
