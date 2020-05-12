<?php

declare(strict_types=1);

namespace Mitra\CommandBus;

use Psr\Container\ContainerInterface;

final class SubscriptionResolver implements SubscriptionResolverInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array<string, array<string>>
     */
    private $subscriptionMap;

    /**
     * @var array<string, array<callable>>
     */
    private $resolvedSubscriptionMap = [];

    /**
     * @param ContainerInterface $container
     * @param array<string, array<string>> $subscriptionMap
     */
    public function __construct(ContainerInterface $container, array $subscriptionMap)
    {
        $this->container = $container;
        $this->subscriptionMap = $subscriptionMap;
    }

    /**
     * @inheritDoc
     */
    public function getSubscribersForEvent(string $event): array
    {
        if (!isset($this->subscriptionMap[$event])) {
            return [];
        }

        if (!isset($this->resolvedSubscriptionMap[$event])) {
            foreach ($this->subscriptionMap[$event] as $subscriberId) {
                $this->resolvedSubscriptionMap[$event][] = $this->container->get($subscriberId);
            }
        }

        return $this->resolvedSubscriptionMap[$event];
    }
}
