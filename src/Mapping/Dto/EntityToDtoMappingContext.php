<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto;

use Psr\Http\Message\ServerRequestInterface;

final class EntityToDtoMappingContext
{
    /**
     * @var ServerRequestInterface|null
     */
    private $request;

    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(?ServerRequestInterface $request): void
    {
        $this->request = $request;
    }
}
