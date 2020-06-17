<?php

declare(strict_types=1);

namespace Mitra\Entity\Actor;

use Mitra\Entity\Media;
use Mitra\Entity\User\AbstractUser;

class Actor
{
    /**
     * An optional display name of the actor
     * @var null|string
     */
    private $name;

    /**
     * An optional icon (avatar) of the actor
     * @var null|Media
     */
    private $icon;

    /**
     * Related user of the actor (internal or external user)
     * @var AbstractUser
     */
    private $user;

    public function __construct(AbstractUser $user)
    {
        $this->user = $user;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Media|null
     */
    public function getIcon(): ?Media
    {
        return $this->icon;
    }

    /**
     * @param Media|null $icon
     */
    public function setIcon(?Media $icon): void
    {
        $this->icon = $icon;
    }

    public function getUser(): AbstractUser
    {
        return $this->user;
    }
}
