<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Mitra\Entity\ActivityStreamContent;

interface ActivityStreamContentRepositoryInterface
{
    public function getByExternalId(string $externalId): ?ActivityStreamContent;
}
