<?php

declare(strict_types=1);

namespace Mitra\Dto;

final class EntityToDtoPopulator
{

    /**
     * @var \Closure
     */
    private $dtoInstantiator;

    /**
     * @var array|self[]
     */
    private $creatorMap = [];

    /**
     * @var array<string, callable>
     */
    private $propertyMap = [];

    /**
     * @param \Closure|string $dtoInstantiator Either a callable to instantiate the DTO, a FQCN
     *                                         or a concrete instance of the DTO
     */
    public function __construct($dtoInstantiator)
    {
        if (is_string($dtoInstantiator)) {
            $this->dtoInstantiator = function () use ($dtoInstantiator) {
                return new $dtoInstantiator();
            };
        } elseif ($dtoInstantiator instanceof \Closure) {
            $this->dtoInstantiator = $dtoInstantiator;
        } else {
            throw new \InvalidArgumentException('DTO instantiator must be either a closure or a FQCN');
        }
    }

    /**
     * @param string $propertyName
     * @param EntityToDtoPopulator $creator
     * @return $this
     */
    public function mapSubEntity(string $propertyName, self $creator): self
    {
        $this->creatorMap[$propertyName] = $creator;

        return $this;
    }

    /**
     * @param string $dtoPropertyName
     * @param string|callable $entityPropertyName
     * @return $this
     */
    public function mapProperty(string $dtoPropertyName, $entityPropertyName): self
    {
        if (is_callable($entityPropertyName)) {
            $propertyTransformer = $entityPropertyName;
        } elseif (is_string($entityPropertyName)) {
            $propertyTransformer = function ($entity) use ($entityPropertyName) {
                return static::getEntityValue($entity, $entityPropertyName);
            };
        } else {
            throw new \InvalidArgumentException('Argument 1 must be either a string or a callable');
        }

        $this->propertyMap[$dtoPropertyName] = $propertyTransformer;

        return $this;
    }

    public function populate(object $entity, object $dto = null): object
    {
        if (null === $dto) {
            $dto = ($this->dtoInstantiator)();
        }

        foreach ($dto as $propertyName => $value) {
            $mappedPropertyName = $propertyName;

            if (isset($this->propertyMap[$propertyName])) {
                $entityValue = $this->propertyMap[$propertyName]($entity);
            } else {
                $entityValue = static::getEntityValue($entity, $mappedPropertyName);
            }

            if (is_object($entityValue)) {
                if ($entityValue instanceof \DateTime) {
                    $entityValue = $entityValue->format('c');
                } elseif (is_callable([$entityValue, '__toString'])) {
                    $entityValue = (string) $entityValue;
                } else {
                    throw new \RuntimeException(sprintf(
                        'Cannot convert value of class `%s` into a scalar value',
                        get_class($entityValue)
                    ));
                }
            }

            if (!isset($this->creatorMap[$propertyName])) {
                $dto->$propertyName = $entityValue;
                continue;
            }

            $dto->$propertyName = $this->creatorMap[$propertyName]->populate($entityValue);
        }

        return $dto;
    }

    /**
     * @param object $entity
     * @param string $entityPropertyName
     * @return mixed
     */
    private static function getEntityValue(object $entity, string $entityPropertyName)
    {
        $getter = array_filter([
            [$entity, 'get' . ucfirst($entityPropertyName)],
            [$entity, 'is' . ucfirst($entityPropertyName)]
        ], static function ($getter): bool {
            return is_callable($getter);
        })[0];

        return call_user_func($getter);
    }
}
