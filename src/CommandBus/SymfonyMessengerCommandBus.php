<?php

declare(strict_types=1);

namespace Mitra\CommandBus;

use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyMessengerCommandBus implements CommandBusInterface
{

    /**
     * @var MessageBusInterface
     */
    private $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @inheritDoc
     */
    public function handle(CommandInterface $command)
    {
        return $this->commandBus->dispatch($command);
    }
}
