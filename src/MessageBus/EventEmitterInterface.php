<?php

declare(strict_types=1);

namespace Mitra\MessageBus;

interface EventEmitterInterface
{
    public function raise(EventInterface $event): void;
}
