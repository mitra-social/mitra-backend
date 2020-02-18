<?php

declare(strict_types=1);

namespace Mitra\Http\Message;

use Mitra\Validator\ViolationListInterface;
use Psr\Http\Message\ResponseInterface;

interface ResponseFactoryInterface
{
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface;

    public function createResponseFromViolationList(
        ViolationListInterface $violationList,
        string $mimeType
    ): ResponseInterface;
}
