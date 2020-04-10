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
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var \DateTime|null
     */
    private $endDate;

    public function __construct(string $id, Actor $subscribingActor, Actor $subscribedActor, \DateTime $startDate)
    {
        $this->id = $id;
        $this->subscribingActor = $subscribingActor;
        $this->subscribedActor = $subscribedActor;
        $this->startDate = $startDate;
    }

    /**
     * @param \DateTime|null $endDate
     */
    public function setEndDate(?\DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Actor
     */
    public function getSubscribingActor(): Actor
    {
        return $this->subscribingActor;
    }

    /**
     * @return Actor
     */
    public function getSubscribedActor(): Actor
    {
        return $this->subscribedActor;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
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
}
