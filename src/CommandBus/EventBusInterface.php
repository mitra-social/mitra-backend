<?php

declare(strict_types=1);

namespace Mitra\CommandBus;

interface EventBusInterface
{
    public function dispatch(EventInterface $event): void;
}
