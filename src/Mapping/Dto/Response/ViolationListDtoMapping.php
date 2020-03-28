<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\ViolationDto;
use Mitra\Dto\Response\ViolationListDto;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Mapping\Dto\InvalidEntityException;
use Mitra\Validator\ViolationList;
use Psr\Http\Message\ServerRequestInterface;
use Webmozart\Assert\Assert;

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
     * @param ServerRequestInterface $request
     * @return ViolationListDto|object
     * @throws InvalidEntityException
     */
    public function toDto(object $entity, ServerRequestInterface $request): object
    {
        if (!$entity instanceof ViolationList) {
            throw InvalidEntityException::fromEntity($entity, static::getEntityClass());
        }

        $violationListDto = new ViolationListDto();

        foreach ($entity->getViolations() as $violation) {
            /** @var ViolationDto $violationDto */
            $violationDto = $this->violationMapping->toDto($violation, $request);
            $violationListDto->violations[] = $violationDto;
        }

        return $violationListDto;
    }
}
