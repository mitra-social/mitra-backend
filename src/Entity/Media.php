<?php

declare(strict_types=1);

namespace Mitra\Entity;

class Media
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $checksum;

    /**
     * @var string
     */
    private $originalUri;

    /**
     * @var string
     */
    private $originalUriHash;

    /**
     * @var string
     */
    private $localUri;

    public function __construct(
        string $id,
        string $checksum,
        string $originalUri,
        string $originalUriHash,
        string $localUri
    ) {
        $this->id = $id;
        $this->checksum = $checksum;
        $this->originalUri = $originalUri;
        $this->originalUriHash = $originalUriHash;
        $this->localUri = $localUri;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function getOriginalUri(): string
    {
        return $this->originalUri;
    }

    public function getOriginalUriHash(): string
    {
        return $this->originalUriHash;
    }

    public function getLocalUri(): string
    {
        return $this->localUri;
    }
}
