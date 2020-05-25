<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration;

use Mitra\CommandBus\Command\ActivityPub\FollowCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Entity\Actor\Actor;
use Psr\Container\ContainerInterface;

/**
 * @method ContainerInterface getContainer()
 */
trait CreateSubscriptionTrait
{
    protected function createSubscription(Actor $subscribingActor, Actor $subscribedActor): void
    {
        $followDto = new FollowDto();
        $followDto->object = $subscribedActor->getUser()->getExternalId();

        $this->getContainer()->get(CommandBusInterface::class)->handle(
            new FollowCommand($subscribingActor, $followDto)
        );
    }
}
