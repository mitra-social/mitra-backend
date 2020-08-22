<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Event\ActivityPub;

use Mitra\MessageBus\Command\UpdateActorIconCommand;
use Mitra\MessageBus\Event\ActivityPub\ExternalUserUpdatedEvent;
use Mitra\MessageBus\Handler\Event\CommandDispatcherEventHandler;

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
