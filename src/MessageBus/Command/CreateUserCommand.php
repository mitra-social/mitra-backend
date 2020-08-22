<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Command;

use Mitra\MessageBus\CommandInterface;
use Mitra\Entity\User\InternalUser;

final class CreateUserCommand implements CommandInterface
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
