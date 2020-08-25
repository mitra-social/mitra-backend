<?php

declare(strict_types=1);

namespace Mitra\Dto\Response;

use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;

final class UserResponseDto extends PersonDto
{
    public $context = [
        'https://www.w3.org/ns/activitystreams',
        'https://w3id.org/security/v1',
        [
            'mitra' => 'https://mitra.social/#',
            'internalUserId' => 'mitra:internalUserId',
        ],
    ];

    /**
     * @var string
     */
    public $internalUserId;

    /**
     * @var string
     */
    public $email;
}
