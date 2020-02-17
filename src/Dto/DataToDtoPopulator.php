<?php

declare(strict_types=1);

namespace Mitra\Dto;

final class DataToDtoPopulator
{

    /**
     * @var \Closure|string
     */
    private $dtoInstantiator;

    /**
     * @var array[self]
     */
    private $creatorMap = [];

    /**
     * @param mixed $dtoInstantiator Either a callable to instantiate the DTO, a FQCN or a concrete instance of the DTO
     */
    public function __construct($dtoInstantiator)
    {
        $this->dtoInstantiator = $dtoInstantiator;
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
     * @return object
     */
    public function populate(array $data)
    {
        if ($this->dtoInstantiator instanceof \Closure) {
            $dto = ($this->dtoInstantiator)();
        } elseif(is_string($this->dtoInstantiator)) {
            $dto = new $this->dtoInstantiator();
        } else {
            $dto = $this->dtoInstantiator;
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
