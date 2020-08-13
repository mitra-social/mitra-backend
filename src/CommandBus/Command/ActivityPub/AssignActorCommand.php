<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command\ActivityPub;

use Mitra\CommandBus\CommandInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\AbstractActivityDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\Actor\Actor;

/**
 * Sets the provided actor as the actor of the provided activity
 */
final class AssignActorCommand implements CommandInterface
{
    /**
     * @var Actor
     */
    private $actor;

    /**
     * @var AbstractActivityDto
     */
    private $activity;

    public function __construct(Actor $actor, AbstractActivityDto $activity)
    {
        $this->actor = $actor;
        $this->activity = $activity;
    }

    /**
     * @return Actor
     */
    public function getActor(): Actor
    {
        return $this->actor;
    }

    /**
     * @return AbstractActivityDto
     */
    public function getActivity(): ObjectDto
    {
        return $this->activity;
    }
}
