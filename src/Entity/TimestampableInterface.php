<?php

declare(strict_types=1);

namespace Mitra\Entity;

interface TimestampableInterface
{

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface;

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface;

    /**
     * @param \DateTimeInterface $createdAt
     * @return void
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): void;

    /**
     * @param \DateTimeInterface $updatedAt
     * @return void
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): void;
}
