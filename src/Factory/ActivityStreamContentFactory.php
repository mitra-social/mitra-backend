<?php

declare(strict_types=1);

namespace Mitra\Factory;

use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Normalization\NormalizerInterface;
use Mitra\Slim\IdGeneratorInterface;

final class ActivityStreamContentFactory implements ActivityStreamContentFactoryInterface
{
    /**
     * @var IdGeneratorInterface
     */
    private $idGenerator;

    /**
     * @var HashGeneratorInterface
     */
    private $hashGenerator;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    public function __construct(
        IdGeneratorInterface $idGenerator,
        HashGeneratorInterface $hashGenerator,
        NormalizerInterface $normalizer
    ) {
        $this->idGenerator = $idGenerator;
        $this->hashGenerator = $hashGenerator;
        $this->normalizer = $normalizer;
    }

    public function createFromDto(ObjectDto $objectDto): ActivityStreamContent
    {
        return new ActivityStreamContent(
            $this->idGenerator->getId(),
            $objectDto->id,
            $this->hashGenerator->hash($objectDto->id),
            $objectDto->type,
            $this->normalizer->normalize($objectDto),
            null,
            null !== $objectDto->published ? new \DateTimeImmutable($objectDto->published) : null,
            null !== $objectDto->updated ? new \DateTimeImmutable($objectDto->updated) : null,
        );
    }
}
