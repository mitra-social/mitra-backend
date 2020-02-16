<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command;

use Mitra\Entity\User;

final class CreateUserCommand
{
    /**
     * @var User
     */
    protected $user;

    /**
     * CreateUserCommand constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
