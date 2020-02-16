<?php

declare(strict_types=1);

namespace Mitra\CommandBus;

use League\Tactician\Handler\Mapping\CommandToHandlerMapping;
use League\Tactician\Handler\Mapping\FailedToMapCommand;

/**
 * The mapping array should be in the following format:
 *
 *      [
 *          SomeCommand::class => SomeHandler::class,
 *          OtherCommand::class => WhateverHandler::class,
 *          ...
 *      ]
 */
final class TacticianMapByStaticClassList implements CommandToHandlerMapping
{
    /** @var array<string, array<string>> */
    private $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function getClassName(string $commandClassName): string
    {
        if (!array_key_exists($commandClassName, $this->mapping)) {
            throw FailedToMapCommand::className($commandClassName);
        }

        return $this->mapping[$commandClassName];
    }

    public function getMethodName(string $commandClassName): string
    {
        return '__invoke';
    }
}
