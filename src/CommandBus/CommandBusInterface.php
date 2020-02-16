<?php

declare(strict_types=1);

namespace Mitra\CommandBus;

interface CommandBusInterface
{
    /**
     * Executes the given command and optionally returns a value
     *
     * @param object $command
     * @return mixed
     */
    public function handle(object $command);
}
