<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command;

use Mitra\CommandBus\CommandInterface;
use Mitra\Dto\Response\ActivityStreams\ImageDto;
use Mitra\Entity\Actor\Actor;

final class UpdateActorIconCommand implements CommandInterface
{
    /**
     * @var Actor
     */
    private $actorEntity;

    /**
     * @var string|ImageDto|array<ImageDto|string>
     */
    private $icon;

    /**
     * @param Actor $actorEntity
     * @param string|ImageDto|array<ImageDto|string> $icon
     */
    public function __construct(Actor $actorEntity, $icon)
    {
        $this->actorEntity = $actorEntity;
        $this->icon = $icon;
    }

    /**
     * @return Actor
     */
    public function getActorEntity(): Actor
    {
        return $this->actorEntity;
    }

    /**
     * @return string|ImageDto|array<ImageDto|string>
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
