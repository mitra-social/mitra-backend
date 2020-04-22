<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command\ActivityPub;

use Mitra\Dto\Response\ActivityStreams\Activity\AbstractActivity;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\Actor\Actor;

/**
 * Sets the provided actor as the actor of the provided activity
 */
final class AssignActorCommand
{
    /**
     * @var Actor
     */
    private $actor;

    /**
     * @var AbstractActivity
     */
    private $activity;

    public function __construct(Actor $actor, AbstractActivity $activity)
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
     * @return AbstractActivity
     */
    public function getActivity(): ObjectDto
    {
        return $this->activity;
    }
}
