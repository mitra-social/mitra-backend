<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityRepository;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\Entity\ActivityStreamContent;

final class ActivityStreamContentRepository implements ActivityStreamContentRepositoryInterface
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var HashGeneratorInterface
     */
    private $hashGenerator;

    public function __construct(EntityRepository $entityRepository, HashGeneratorInterface $hashGenerator)
    {
        $this->entityRepository = $entityRepository;
        $this->hashGenerator = $hashGenerator;
    }

    public function getByExternalId(string $externalId): ?ActivityStreamContent
    {
        /** @var null|ActivityStreamContent $content */
        $content = $this->entityRepository->findOneBy([
            'externalIdHash' => $this->hashGenerator->hash($externalId),
            'externalId' => $externalId,
        ]);

        return $content;
    }
}
