<?php

declare(strict_types=1);

namespace Mitra\Entity\Actor;

use Mitra\Entity\User\AbstractUser;

class Actor
{
    /**
     * The internal id of the external or internal actor
     * @var string
     */
    private $id;

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
     * Related user of the actor (internal or external user)
     * @var AbstractUser
     */
    private $user;

    public function __construct(string $id, AbstractUser $user)
    {
        $this->id = $id;
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
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

    public function getUser(): AbstractUser
    {
        return $this->user;
    }
}
