<?php

declare(strict_types=1);

namespace Mitra\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, ActivityStreamContent>
     */
    private $linkedObjects;

    /**
     * ActivityStreamContent constructor.
     * @param string $id
     * @param string $externalId
     * @param string $externalIdHash
     * @param string $type
     * @param array<mixed> $object
     * @param Actor|null $attributedTo
     * @param \DateTimeInterface|null $published
     * @param \DateTimeInterface|null $updated
     */
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
        $this->linkedObjects = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function getUpdated(): ?\DateTimeInterface
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

    /**
     * @return Actor|null
     */
    public function getAttributedTo(): ?Actor
    {
        return $this->attributedTo;
    }

    /**
     * @param Actor|null $attributedTo
     */
    public function setAttributedTo(?Actor $attributedTo): void
    {
        $this->attributedTo = $attributedTo;
    }

    public function addLinkedObject(ActivityStreamContent $object): void
    {
        if ($this->linkedObjects->contains($object)) {
            return;
        }

        $this->linkedObjects->add($object);
    }

    /**
     * @return array<ActivityStreamContent>
     */
    public function getLinkedObjects(): array
    {
        return $this->linkedObjects->toArray();
    }

    public function __toString()
    {
        return sprintf(
            'id:%s, externalId:%s, type:%s, linkObjectCount:%d',
            $this->id,
            $this->externalId,
            $this->type,
            $this->linkedObjects->count()
        );
    }
}
