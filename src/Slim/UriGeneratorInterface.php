<?php

declare(strict_types=1);

namespace Mitra\Slim;

interface UriGeneratorInterface
{
    /**
     * @param string $routeName
     * @param array<string, string|int|double> $data
     * @param array<string, string|int|double> $queryParams
     * @return string
     */
    public function fullUrlFor(string $routeName, array $data = [], array $queryParams = []): string;
}
