<?php

declare(strict_types=1);

namespace Mitra\CommandBus;

interface SubscriptionResolverInterface
{
    /**
     * @param string $event
     * @return array<callable>
     */
    public function getSubscribersForEvent(string $event): array;
}
