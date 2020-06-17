<?php

declare(strict_types=1);

namespace Mitra\ActivityPub;

final class HashGenerator implements HashGeneratorInterface
{
    /**
     * @var string
     */
    private $algorithm;

    public function __construct(string $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    public function hash(string $content): string
    {
        return hash($this->algorithm, $content);
    }

    /**
     * @param resource $resource
     * @return string
     */
    public function hashResource($resource): string
    {
        $prevPosition = ftell($resource);
        rewind($resource);

        $ctx = hash_init($this->algorithm);
        hash_update_stream($ctx, $resource);
        $hash = hash_final($ctx);

        // Revert stream to the previous position
        if (false !== $prevPosition) {
            fseek($resource, $prevPosition);
        }

        return $hash;
    }
}
