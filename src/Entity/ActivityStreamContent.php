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
     * @var null|\DateTime
     */
    private $startDate;

    /**
     * @var null|\DateTime
     */
    private $endDate;

    /**
     * @var array
     */
    private $object;

    /**
     * ActivityStreamContent constructor.
     * @param string $id
     * @param string $type
     * @param \DateTime|null $published
     * @param \DateTime|null $updated
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @param array $object
     */
    public function __construct(
        string $id,
        string $type,
        array $object,
        ?\DateTime $published,
        ?\DateTime $updated,
        ?\DateTime $startDate,
        ?\DateTime $endDate
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->published = $published;
        $this->updated = $updated;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
     * @return \DateTime|null
     */
    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * @return array
     */
    public function getObject(): array
    {
        return $this->object;
    }
}
