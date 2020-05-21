<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams\Activity;

use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;

interface ActivityDtoInterface
{
    /**
     * @return array|LinkDto|ObjectDto|string|null
     */
    public function getActor();
}
