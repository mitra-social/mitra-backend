<?php

declare(strict_types=1);

namespace Mitra\CommandBus;

interface EventEmitterInterface
{
    public function raise(EventInterface $event): void;
}
