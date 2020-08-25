<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Command\ActivityPub;

use Mitra\MessageBus\CommandInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Entity\Actor\Actor;

final class FollowCommand implements CommandInterface
{
    /**
     * @var Actor
     */
    private $actor;

    /**
     * @var FollowDto
     */
    private $followDto;

    public function __construct(Actor $actor, FollowDto $followDto)
    {
        $this->actor = $actor;
        $this->followDto = $followDto;
    }

    public function getActor(): Actor
    {
        return $this->actor;
    }

    public function getFollowDto(): FollowDto
    {
        return $this->followDto;
    }
}
