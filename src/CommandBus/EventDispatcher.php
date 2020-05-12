<?php

declare(strict_types=1);

namespace Mitra\CommandBus;

final class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array<object>
     */
    private $raisedEvents = [];

    /**
     * @var SubscriptionResolverInterface
     */
    private $subscriptionResolver;

    public function __construct(SubscriptionResolverInterface $subscriptionResolver)
    {
        $this->subscriptionResolver = $subscriptionResolver;
    }

    public function raise(object $event): void
    {
        $this->raisedEvents[] = $event;
    }

    public function releaseEvents(): void
    {
        while (null !== $event = array_shift($this->raisedEvents)) {
            $subscribers = $this->subscriptionResolver->getSubscribersForEvent(get_class($event));

            foreach ($subscribers as $subscriber) {
                $subscriber($event);
            }
        }
    }
}
