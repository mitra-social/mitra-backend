<?php

declare(strict_types=1);

namespace Mitra\Dto;

use Mitra\Mapping\Dto\DtoToEntityMappingInterface;
use Psr\Container\ContainerInterface;

final class DtoToEntityMapper
{

    /**
     * @var array<string>
     */
    private $mappings = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     * @param array<string> $mappings
     */
    public function __construct(ContainerInterface $container, array $mappings = [])
    {
        $this->container = $container;

        foreach ($mappings as $mapping) {
            $this->addMapping($mapping);
        }
    }

    public function map(object $dto, string $entityClass): object
    {
        $dtoClass = get_class($dto);

        if (!isset($this->mappings[$entityClass . $dtoClass])) {
            throw new \RuntimeException(sprintf(
                'No mapping defined from dto `%s` to entity `%s',
                $entityClass,
                $dtoClass
            ));
        }

        $mappingClass = $this->mappings[$entityClass . $dtoClass];

        return $this->container->get($mappingClass)->toEntity($dto);
    }

    /**
     * @param string $mappingClass
     * @return static
     */
    public function addMapping(string $mappingClass): self
    {
        if (!in_array(DtoToEntityMappingInterface::class, class_implements($mappingClass), true)) {
            throw new \InvalidArgumentException(sprintf(
                'Mapping class `%s` does not implement `%s`',
                $mappingClass,
                DtoToEntityMappingInterface::class
            ));
        }

        $this->mappings[$mappingClass::getEntityClass() . $mappingClass::getDtoClass()] = $mappingClass;
        return $this;
    }
}
