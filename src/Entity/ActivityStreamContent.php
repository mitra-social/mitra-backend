<?php

declare(strict_types=1);

namespace Mitra\Entity;

class ActivityStreamContent
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var null|\DateTime
     */
    private $published;

    /**
     * @var null|\DateTime
     */
    private $updated;

    /**
     * @var array<mixed>
     */
    private $object;

    /**
     * ActivityStreamContent constructor.
     * @param string $id
     * @param string $type
     * @param array<mixed> $object
     * @param \DateTime|null $published
     * @param \DateTime|null $updated
     */
    public function __construct(
        string $id,
        string $type,
        array $object,
        ?\DateTime $published,
        ?\DateTime $updated
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->published = $published;
        $this->updated = $updated;
        $this->object = $object;
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
     * @return \DateTime|null
     */
    public function getPublished(): ?\DateTime
    {
        return $this->published;
    }

    /**
     * @return \DateTime|null
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
