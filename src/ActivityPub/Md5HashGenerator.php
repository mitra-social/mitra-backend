<?php

declare(strict_types=1);

namespace Mitra\ActivityPub;

final class Md5HashGenerator implements HashGeneratorInterface
{

    public function hash(string $content): string
    {
        return md5($content);
    }
}
