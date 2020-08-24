<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Command;

use Mitra\Entity\User\InternalUser;
use Mitra\MessageBus\CommandInterface;

abstract class AbstractUserCommand implements CommandInterface
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
