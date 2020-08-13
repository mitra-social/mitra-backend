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

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var int
     */
    private $size;

    public function __construct(
        string $id,
        string $checksum,
        string $originalUri,
        string $originalUriHash,
        string $localUri,
        string $mimeType,
        int $size
    ) {
        $this->id = $id;
        $this->checksum = $checksum;
        $this->originalUri = $originalUri;
        $this->originalUriHash = $originalUriHash;
        $this->localUri = $localUri;
        $this->mimeType = $mimeType;
        $this->size = $size;
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

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }
}
