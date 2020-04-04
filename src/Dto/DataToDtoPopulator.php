<?php

declare(strict_types=1);

namespace Mitra\Dto;

class DataToDtoPopulator implements DataToDtoPopulatorInterface
{

    /**
     * @var \Closure
     */
    private $dtoInstantiator;

    /**
     * @var array<callable>
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
     * @param callable $creator
     * @return $this
     */
    public function map(string $propertyName, callable $creator): self
    {
        $this->creatorMap[$propertyName] = $creator;

        return $this;
    }

    /**
     * @param array<mixed> $data
     * @param object|null $dto
     * @return object
     */
    public function populate(array $data): object
    {
        $dto = ($this->dtoInstantiator)();

        foreach ($data as $propertyName => $value) {
            if (!isset($data[$propertyName])) {
                $dto->$propertyName = null;
                continue;
            }

            if (!isset($this->creatorMap[$propertyName])) {
                $dto->$propertyName = $data[$propertyName];
                continue;
            }

            $dto->$propertyName = $this->creatorMap[$propertyName]($data[$propertyName]);
        }

        return $dto;
    }
}
