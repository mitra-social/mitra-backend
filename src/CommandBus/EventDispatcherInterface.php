<?php

declare(strict_types=1);

namespace Mitra\CommandBus;

interface EventDispatcherInterface
{
    public function raise(object $event): void;

    public function releaseEvents(): void;
}
