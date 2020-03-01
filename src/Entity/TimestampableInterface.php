<?php

declare(strict_types=1);

namespace Mitra\Entity;

interface TimestampableInterface
{

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime;

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime;

    /**
     * @param \DateTime $createdAt
     * @return void
     */
    public function setCreatedAt(\DateTime $createdAt): void;

    /**
     * @param \DateTime $updatedAt
     * @return void
     */
    public function setUpdatedAt(\DateTime $updatedAt): void;
}
