<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration;

use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentReceivedEvent;
use Mitra\CommandBus\EventBusInterface;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Normalization\NormalizerInterface;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;

/**
 * @method ContainerInterface getContainer()
 */
trait CreateContentTrait
{
    public function createContent(ObjectDto $objectDto): ActivityStreamContent
    {
        /** @var NormalizerInterface $normalizer */
        $normalizer = $this->getContainer()->get(NormalizerInterface::class);
        /** @var EventBusInterface $eventBus */
        $eventBus = $this->getContainer()->get(EventBusInterface::class);

        $activityStreamContentEntity = new ActivityStreamContent(
            Uuid::uuid4()->toString(),
            $objectDto->id,
            md5($objectDto->id),
            $objectDto->type,
            $normalizer->normalize($objectDto),
            null,
            null !== $objectDto->published ? new \DateTimeImmutable($objectDto->published) : null,
            null !== $objectDto->updated ? new \DateTimeImmutable($objectDto->updated) : null,
        );

        $eventBus->dispatch(new ActivityStreamContentReceivedEvent(
            $activityStreamContentEntity,
            $objectDto
        ));

        return $activityStreamContentEntity;
    }
}
