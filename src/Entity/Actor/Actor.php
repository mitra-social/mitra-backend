<?php

declare(strict_types=1);

namespace Mitra\Entity\Actor;

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
     * @var null|string
     */
    private $icon;

    /**
     * Checksum of the icon
     * @var null|string
     */
    private $iconChecksum;

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
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon
     */
    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return string|null
     */
    public function getIconChecksum(): ?string
    {
        return $this->iconChecksum;
    }

    /**
     * @param string|null $iconChecksum
     */
    public function setIconChecksum(?string $iconChecksum): void
    {
        $this->iconChecksum = $iconChecksum;
    }

    public function getUser(): AbstractUser
    {
        return $this->user;
    }
}
