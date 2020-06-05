<?php

declare(strict_types=1);

namespace Mitra\ActivityPub;

interface HashGeneratorInterface
{
    public function hash(string $content): string;
}
