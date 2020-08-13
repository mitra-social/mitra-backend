<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Event\ActivityPub;

use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\Actor\Actor;

final class ActivityStreamContentReceivedEvent extends AbstractActivityStreamContentEvent
{
    public function __construct(
        ActivityStreamContent $activityStreamContentEntity,
        ObjectDto $activityStreamDto,
        ?Actor $actor,
        bool $dereferenceObjects = true
    ) {
        parent::__construct($activityStreamContentEntity, $activityStreamDto, $actor, $dereferenceObjects);
    }
}
