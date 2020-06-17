<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityRepository;
use Mitra\Entity\Media;

final class MediaRepository implements MediaRepositoryInterface
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    public function getByLocalUri(string $localUri): ?Media
    {
        /** @var null|Media $media */
        $media = $this->entityRepository->findOneBy(['localUri' => $localUri]);

        return $media;
    }

    public function getByOriginalUriHash(string $originalUriHash): ?Media
    {
        /** @var null|Media $media */
        $media = $this->entityRepository->findOneBy(['originalUriHash' => $originalUriHash]);

        return $media;
    }
}
