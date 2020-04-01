<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command;

use Mitra\Entity\User\InternalUser;

final class CreateUserCommand
{
    /**
     * @var InternalUser
     */
    protected $user;

    public function __construct(InternalUser $user)
    {
        $this->user = $user;
    }

    public function getUser(): InternalUser
    {
        return $this->user;
    }
}
