<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Event\ActivityPub;

use Mitra\CommandBus\Command\ActivityPub\PersistActivityStreamContentCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\Event\ActivityPub\ContentAcceptedEvent;

final class ContentAcceptedEventHandler
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(ContentAcceptedEvent $event): void
    {
        $this->commandBus->handle(new PersistActivityStreamContentCommand(
            $event->getActivityStreamContentEntity(),
            $event->getActivityStreamDto()
        ));
    }
}
