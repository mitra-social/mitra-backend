<?php

declare(strict_types=1);

namespace Mitra\Clock;

final class Clock implements ClockInterface
{
    public function now(): \DateTimeInterface
    {
        return new \DateTimeImmutable();
    }
}
