<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\Entity\Media;

final class MediaRepository implements MediaRepositoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getByLocalUri(string $localUri): ?Media
    {
        $query = $this->entityManager->createQuery(sprintf(
            'SELECT m FROM %s m WHERE m.localUri = :localUri',
            Media::class
        ))->setParameter('localUri', $localUri);

        /** @var null|Media $media */
        $media = $query->getOneOrNullResult();

        return $media;
    }

    public function getByOriginalUriHash(string $originalUriHash): ?Media
    {
        $query = $this->entityManager->createQuery(sprintf(
            'SELECT m FROM %s m WHERE m.originalUriHash = :originalUriHash',
            Media::class
        ))->setParameter('originalUriHash', $originalUriHash);

        /** @var null|Media $media */
        $media = $query->getOneOrNullResult();

        return $media;
    }
}
