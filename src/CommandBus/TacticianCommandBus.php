<?php

declare(strict_types=1);

namespace Mitra\CommandBus;

use League\Tactician\CommandBus;

final class TacticianCommandBus implements CommandBusInterface
{

    /**
     * @var CommandBus
     */
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @inheritDoc
     */
    public function handle(object $command)
    {
        return $this->commandBus->handle($command);
    }
}
