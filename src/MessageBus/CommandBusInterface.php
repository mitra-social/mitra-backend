<?php

declare(strict_types=1);

namespace Mitra\MessageBus;

interface CommandBusInterface
{
    /**
     * Executes the given command and optionally returns a value
     *
     * @param CommandInterface $command
     * @return mixed
     */
    public function handle(CommandInterface $command);
}
