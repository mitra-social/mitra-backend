<?php

declare(strict_types=1);

namespace Mitra\Tests\Helper;

use Mitra\Clock\ClockInterface;

final class FreezableClock implements ClockInterface
{
    /**
     * @var \DateTimeInterface
     */
    private $frozenNow;

    public function now(): \DateTimeInterface
    {
        if (null === $this->frozenNow) {
            return new \DateTimeImmutable();
        }

        return $this->frozenNow;
    }

    public function freeze(\DateTimeInterface $frozenNow = null): \DateTimeInterface
    {
        $this->frozenNow = null !== $frozenNow ? $frozenNow : new \DateTimeImmutable();

        return $this->frozenNow;
    }

    public function unfreeze(): \DateTimeInterface
    {
        $frozenNow = $this->frozenNow;
        $this->frozenNow = null;

        return $frozenNow;
    }
}
