<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Event;

use Mitra\MessageBus\CommandBusInterface;

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
