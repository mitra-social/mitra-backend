<?php

declare(strict_types=1);

namespace Mitra\Slim;

use Ramsey\Uuid\Uuid;

final class UuidGenerator implements IdGeneratorInterface
{
    public function getId(): string
    {
        return Uuid::uuid4()->toString();
    }
}
