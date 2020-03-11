<?php

declare(strict_types=1);

namespace Mitra\Dto;

use Psr\Container\ContainerInterface;

final class EntityToDtoManager
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
     * @param object|string $dto
     * @param object $entity
     * @return object
     */
    public function populate($dto, object $entity): object
    {
        if (is_object($dto)) {
            $dtoClassName = get_class($dto);
            $dtoInstance = $dto;
        } elseif (is_string($dto) && class_exists($dto)) {
            $dtoClassName = $dto;
            $dtoInstance = null;
        } else {
            throw new \InvalidArgumentException('Whether a DTO object nor a FQCN of a DTO');
        }

        if (!isset($this->populatorMap[$dtoClassName])) {
            throw new \RuntimeException(sprintf('Could not find DTO populator for class `%s`', $dtoClassName));
        }

        return $this->container->get($this->populatorMap[$dtoClassName])->populate($entity, $dtoInstance);
    }
}
