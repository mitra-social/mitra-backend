<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\ActivityPub;

use Mitra\CommandBus\Command\ActivityPub\AttributeActivityStreamContentCommand;
use Mitra\Dto\Response\ActivityStreams\Activity\AbstractActivity;

final class AttributeActivityStreamContentHandler
{
    public function __invoke(AttributeActivityStreamContentCommand $command): void
    {
        $content = $command->getActivityStreamContent();
        $object = $command->getActivityStreamObject();

        if ($object instanceof AbstractActivity && null !== $object->actor) {

        } elseif (null !== $object->attributedTo) {

        }
    }
}
