<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\Entity\ActivityStreamContent;

final class ActivityStreamContentRepository implements ActivityStreamContentRepositoryInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var HashGeneratorInterface
     */
    private $hashGenerator;

    public function __construct(EntityManagerInterface $entityManager, HashGeneratorInterface $hashGenerator)
    {
        $this->entityManager = $entityManager;
        $this->hashGenerator = $hashGenerator;
    }

    public function getByExternalId(string $externalId): ?ActivityStreamContent
    {
        $query = $this->entityManager->createQuery(sprintf(
            'SELECT c FROM %s c WHERE c.externalIdHash = :externalIdHash AND c.externalId = :externalId',
            ActivityStreamContent::class
        ))->setParameters([
            'externalIdHash' => $this->hashGenerator->hash($externalId),
            'externalId' => $externalId,
        ]);

        /** @var ActivityStreamContent|null $content */
        $content = $query = $query->getOneOrNullResult();

        return $content;
    }
}
