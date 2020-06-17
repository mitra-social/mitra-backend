<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Event\ActivityPub;

use Mitra\CommandBus\Command\UpdateActorIconCommand;
use Mitra\CommandBus\Event\ActivityPub\ExternalUserUpdatedEvent;
use Mitra\CommandBus\Handler\Event\CommandDispatcherEventHandler;

final class ExternalUserUpdatedEventHandler extends CommandDispatcherEventHandler
{
    public function __invoke(ExternalUserUpdatedEvent $event): void
    {
        if (null === $icon = $event->getActorDto()->getIcon()) {
            return;
        }

        $this->commandBus->handle(new UpdateActorIconCommand($event->getActorEntity(), $icon));
    }
}
