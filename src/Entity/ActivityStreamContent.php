<?php

declare(strict_types=1);

namespace Mitra\Entity;

use Mitra\Entity\Actor\Actor;

class ActivityStreamContent
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $externalId;

    /**
     * @var string
     */
    private $externalIdHash;

    /**
     * @var string
     */
    private $type;

    /**
     * @var null|\DateTimeInterface
     */
    private $published;

    /**
     * @var null|\DateTimeInterface
     */
    private $updated;

    /**
     * @var null|Actor
     */
    private $attributedTo;

    /**
     * @var array<mixed>
     */
    private $object;

    public function __construct(
        string $id,
        string $externalId,
        string $externalIdHash,
        string $type,
        array $object,
        ?Actor $attributedTo,
        ?\DateTimeInterface $published,
        ?\DateTimeInterface $updated
    ) {
        $this->id = $id;
        $this->externalId = $externalId;
        $this->externalIdHash = $externalIdHash;
        $this->type = $type;
        $this->published = $published;
        $this->updated = $updated;
        $this->object = $object;
        $this->attributedTo = $attributedTo;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getPublished(): ?\DateTime
    {
        return $this->published;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdated(): ?\DateTime
    {
        return $this->updated;
    }

    /**
     * @return array<mixed>
     */
    public function getObject(): array
    {
        return $this->object;
    }
}
