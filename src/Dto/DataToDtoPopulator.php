<?php

declare(strict_types=1);

namespace Mitra\Dto;

final class DataToDtoPopulator
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
     * @param mixed $dtoInstantiator Either a callable to instantiate the DTO, a FQCN or a concrete instance of the DTO
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
     * @param DataToDtoPopulator $creator
     * @return $this
     */
    public function map(string $propertyName, self $creator): self
    {
        $this->creatorMap[$propertyName] = $creator;

        return $this;
    }

    /**
     * @param array $data
     * @param object|null $dto
     * @return object
     */
    public function populate(array $data, object $dto = null)
    {
        if (null === $dto) {
            $dto = ($this->dtoInstantiator)();
        }

        foreach (get_object_vars($dto) as $propertyName => $value) {
            if (!isset($data[$propertyName])) {
                $dto->$propertyName = null;
                continue;
            }

            if (!isset($this->creatorMap[$propertyName])) {
                $dto->$propertyName = $data[$propertyName];
                continue;
            }

            $dto->$propertyName = $this->creatorMap[$propertyName]->populate($data[$propertyName]);
        }

        return $dto;
    }
}
