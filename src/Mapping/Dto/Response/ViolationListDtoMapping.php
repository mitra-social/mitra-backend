<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\ViolationListDto;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Validator\ViolationList;

final class ViolationListDtoMapping implements EntityToDtoMappingInterface
{

    /**
     * @var ViolationDtoMapping
     */
    private $violationMapping;

    public function __construct(ViolationDtoMapping $violationMapping)
    {
        $this->violationMapping = $violationMapping;
    }

    public static function getDtoClass(): string
    {
        return ViolationListDto::class;
    }

    public static function getEntityClass(): string
    {
        return ViolationList::class;
    }

    /**
     * @param ViolationList|object $entity
     * @return ViolationListDto|object
     */
    public function toDto(object $entity): object
    {
        $violationListDto = new ViolationListDto();

        foreach ($entity->getViolations() as $violation) {
            $violationListDto->violations[] = $this->violationMapping->toDto($violation);
        }

        return $violationListDto;
    }
}
