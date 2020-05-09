<?php

declare(strict_types=1);

namespace Mitra\Http\Message;

use Mitra\Validator\ViolationListInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResponseFactoryInterface
{
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface;

    public function createResponseFromViolationList(
        ViolationListInterface $violationList,
        ServerRequestInterface $request,
        string $mimeType
    ): ResponseInterface;

    public function createResponseFromEntity(
        object $entity,
        string $dtoClass,
        ServerRequestInterface $request,
        string $mimeType,
        int $code = 200
    ): ResponseInterface;

    public function createResponseFromDto(
        object $dto,
        ServerRequestInterface $request,
        string $mimeType,
        int $code = 200
    ): ResponseInterface;
}
