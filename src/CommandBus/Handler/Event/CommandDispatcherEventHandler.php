<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Event;

use Mitra\CommandBus\CommandBusInterface;

abstract class CommandDispatcherEventHandler
{
    /**
     * @var CommandBusInterface
     */
    protected $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }
}
