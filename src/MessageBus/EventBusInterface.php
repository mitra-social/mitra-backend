<?php

declare(strict_types=1);

namespace Mitra\MessageBus;

interface EventBusInterface
{
    public function dispatch(EventInterface $event): void;
}
