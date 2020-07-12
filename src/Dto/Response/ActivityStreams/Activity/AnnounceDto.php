<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams\Activity;

final class AnnounceDto extends ActivityDto
{
    /**
     * @var string
     */
    public $type = 'Announce';
}
