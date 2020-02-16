<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler;

use Mitra\CommandBus\Command\CreateUserCommand;

final class CreateUserCommandHandler
{
    public function __invoke(CreateUserCommand $command)
    {
        echo 'command bus handler kicks in!';
    }
}
