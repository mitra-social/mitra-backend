<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Command\ActivityPub;

use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\Actor\Actor;

final class DereferenceCommand extends AbstractActivityStreamContentCommand
{
    /**
     * @var int
     */
    private $currentDereferenceDepth;

    /**
     * @var int
     */
    private $maxDereferenceDepth;

    public function __construct(
        ActivityStreamContent $activityStreamContentEntity,
        ObjectDto $activityStreamDto,
        ?Actor $actor,
        bool $dereferenceObjects,
        int $maxDereferenceDepth,
        int $currentDereferenceDepth
    ) {
        parent::__construct($activityStreamContentEntity, $activityStreamDto, $actor, $dereferenceObjects);

        $this->currentDereferenceDepth = $currentDereferenceDepth;
        $this->maxDereferenceDepth = $maxDereferenceDepth;
    }

    /**
     * @return int
     */
    public function getCurrentDereferenceDepth(): int
    {
        return $this->currentDereferenceDepth;
    }

    /**
     * @return int
     */
    public function getMaxDereferenceDepth(): int
    {
        return $this->maxDereferenceDepth;
    }
}
