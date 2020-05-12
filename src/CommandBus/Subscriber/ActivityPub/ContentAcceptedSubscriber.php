<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Subscriber\ActivityPub;

use Mitra\CommandBus\Command\ActivityPub\AssignActivityStreamContentToFollowersCommand;
use Mitra\CommandBus\Command\ActivityPub\AttributeActivityStreamContentCommand;
use Mitra\CommandBus\Command\ActivityPub\PersistActivityStreamContent;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\Event\ActivityPub\ContentAcceptedEvent;

final class ContentAcceptedSubscriber
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
        $this->commandBus->handle(new AttributeActivityStreamContentCommand($activityStreamContent, $objectDto));
        $this->commandBus->handle(new PersistActivityStreamContent($activityStreamContent, $objectDto));
        $this->commandBus->handle(new AssignActivityStreamContentToFollowersCommand(
            $activityStreamContent,
            $objectDto
        ));
    }
}
