<?php

declare(strict_types=1);

namespace Mitra\MessageBus;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;

final class SymfonyMessengerHandlersLocator implements HandlersLocatorInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array<string, array<string>|string>
     */
    private $handlerMap;

    /**
     * @param ContainerInterface $container
     * @param array<string, array<string>|string> $handlerMap
     */
    public function __construct(ContainerInterface $container, array $handlerMap)
    {
        $this->container = $container;
        $this->handlerMap = $handlerMap;
    }

    public function getHandlers(Envelope $envelope): iterable
    {
        $messageClass = get_class($envelope->getMessage());
        $handlers = (array) ($this->handlerMap[$messageClass] ?? []);

        foreach ($handlers as $handlerServiceId) {
            yield new HandlerDescriptor($this->container->get($handlerServiceId));
        }
    }
}
