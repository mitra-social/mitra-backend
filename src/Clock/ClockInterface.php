<?php

declare(strict_types=1);

namespace Mitra\Clock;

interface ClockInterface
{
    public function now(): \DateTimeInterface;
}
