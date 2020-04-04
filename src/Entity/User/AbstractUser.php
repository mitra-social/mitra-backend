<?php

declare(strict_types=1);

namespace Mitra\Entity\User;

use Mitra\Entity\Actor\Actor;

abstract class AbstractUser
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var null|Actor
     */
    private $actor;

    /**
     * @var null|string
     */
    private $publicKey;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getActor(): ?Actor
    {
        return $this->actor;
    }

    public function setActor(Actor $actor): void
    {
        $this->actor = $actor;
    }

    /**
     * @return string|null
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * @param string|null $publicKey
     */
    public function setPublicKey(?string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }
}
