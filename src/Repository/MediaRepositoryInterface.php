<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Mitra\Entity\Media;

interface MediaRepositoryInterface
{
    public function getByLocalUri(string $localUri): ?Media;

    public function getByOriginalUriHash(string $originalUriHash): ?Media;
}
