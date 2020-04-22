<?php

declare(strict_types=1);

namespace Mitra\Entity;

use Mitra\Entity\Actor\Actor;

class Subscription
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Actor
     */
    private $subscribingActor;

    /**
     * @var Actor
     */
    private $subscribedActor;

    /**
     * @var \DateTimeInterface
     */
    private $startDate;

    /**
     * @var \DateTimeInterface|null
     */
    private $endDate;

    public function __construct(
        string $id,
        Actor $subscribingActor,
        Actor $subscribedActor,
        \DateTimeInterface $startDate
    ) {
        $this->id = $id;
        $this->subscribingActor = $subscribingActor;
        $this->subscribedActor = $subscribedActor;
        $this->startDate = $startDate;
    }

    public function setEndDate(?\DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSubscribingActor(): Actor
    {
        return $this->subscribingActor;
    }

    public function getSubscribedActor(): Actor
    {
        return $this->subscribedActor;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }
}
