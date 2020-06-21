<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command\ActivityPub;

use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\Actor\Actor;

final class AssignActivityStreamContentToActorCommand extends AbstractActivityStreamContentCommand
{
    public function __construct(
        ActivityStreamContent $activityStreamContentEntity,
        ObjectDto $activityStreamDto,
        Actor $actor
    ) {
        parent::__construct(
            $activityStreamContentEntity,
            $activityStreamDto,
            $actor
        );
    }
}
