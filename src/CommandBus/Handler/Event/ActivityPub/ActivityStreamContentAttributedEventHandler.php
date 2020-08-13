<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Event\ActivityPub;

use Mitra\CommandBus\Command\ActivityPub\ValidateContentCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentAttributedEvent;

final class ActivityStreamContentAttributedEventHandler
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(ActivityStreamContentAttributedEvent $event): void
    {
        $this->commandBus->handle(new ValidateContentCommand(
            $event->getActivityStreamContentEntity(),
            $event->getActivityStreamDto(),
            $event->getActor(),
            $event->shouldDereferenceObjects()
        ));
    }
}
