<?php

declare(strict_types=1);

namespace Mitra\Dto;

use Psr\Container\ContainerInterface;

final class DataToDtoTransformer
{

    /**
     * @var array<string>
     */
    private $populatorMap = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     * @param array<string> $populatorMap
     */
    public function __construct(ContainerInterface $container, array $populatorMap = [])
    {
        $this->container = $container;
        $this->populatorMap = $populatorMap;
    }

    public function register(string $dtoClassName, string $populatorClassName): self
    {
        $this->populatorMap[$dtoClassName] = $populatorClassName;
        return $this;
    }

    /**
     * @param string $dtoClassName
     * @param array<mixed> $data
     * @return object
     */
    public function populate(string $dtoClassName, array $data): object
    {
        if (!isset($this->populatorMap[$dtoClassName])) {
            throw new \RuntimeException(sprintf('Could not find DTO populator for class `%s`', $dtoClassName));
        }

        return $this->container->get($this->populatorMap[$dtoClassName])->populate($data);
    }
}
