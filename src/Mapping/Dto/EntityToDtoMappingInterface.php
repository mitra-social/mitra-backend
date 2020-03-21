<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto;

use Psr\Http\Message\ServerRequestInterface;

interface EntityToDtoMappingInterface extends DtoMappingInterface
{
    /**
     * @param object $entity
     * @param ServerRequestInterface $request
     * @return object
     */
    public function toDto(object $entity, ServerRequestInterface $request): object;
}
