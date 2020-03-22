<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

final class ProfileDto extends ObjectDto
{
    public $type = 'Profile';

    public $describes;
}
