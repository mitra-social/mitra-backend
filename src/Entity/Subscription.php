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
    private $start;

    /**
     * @var \DateTime|null
     */
    private $end;

    /**
     * Subscription constructor.
     * @param string $id
     * @param Actor $subscribingActor
     * @param Actor $subscribedActor
     * @param \DateTime $start
     */
    public function __construct(string $id, Actor $subscribingActor, Actor $subscribedActor, \DateTime $start)
    {
        $this->id = $id;
        $this->subscribingActor = $subscribingActor;
        $this->subscribedActor = $subscribedActor;
        $this->start = $start;
    }

    /**
     * @param \DateTime|null $end
     */
    public function setEnd(?\DateTime $end): void
    {
        $this->end = $end;
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
    public function getStart(): \DateTime
    {
        return $this->start;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }
}
