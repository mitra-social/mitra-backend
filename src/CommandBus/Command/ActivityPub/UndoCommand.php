<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command\ActivityPub;

use Mitra\CommandBus\CommandInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\UndoDto;
use Mitra\Entity\Actor\Actor;

final class UndoCommand implements CommandInterface
{
    /**
     * @var Actor
     */
    private $actor;

    /**
     * @var UndoDto
     */
    private $undoDto;

    public function __construct(Actor $actor, UndoDto $undoDto)
    {
        $this->actor = $actor;
        $this->undoDto = $undoDto;
    }

    public function getActor(): Actor
    {
        return $this->actor;
    }

    public function getUndoDto(): UndoDto
    {
        return $this->undoDto;
    }
}
