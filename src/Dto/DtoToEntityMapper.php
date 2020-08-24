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

    /**
     * @param object $dto
     * @param string|object $entity Either a FQCN or an entity instance
     * @return object
     */
    public function map(object $dto, $entity): object
    {
        $dtoClass = get_class($dto);
        $entityClass = is_object($entity) ? get_class($entity) : $entity;

        if (!isset($this->mappings[$entityClass . $dtoClass])) {
            throw new \RuntimeException(sprintf(
                'No mapping defined from dto `%s` to entity `%s',
                $entityClass,
                $dtoClass
            ));
        }

        $mappingClass = $this->mappings[$entityClass . $dtoClass];

        /** @var DtoToEntityMappingInterface $mapper */
        $mapper = $this->container->get($mappingClass);

        return $mapper->toEntity($dto, is_object($entity) ? $entity : null);
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
