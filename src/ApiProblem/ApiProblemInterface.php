<?php

declare(strict_types=1);

namespace Mitra\ApiProblem;

/**
 * @link http://tools.ietf.org/html/rfc7807
 */
interface ApiProblemInterface
{
    public function getType(): string;

    public function getTitle(): string;

    public function getHttpStatusCode(): int;

    public function getDetail(): ?string;

    public function getInstance(): ?string;
}
