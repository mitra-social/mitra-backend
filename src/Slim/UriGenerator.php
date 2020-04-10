<?php

declare(strict_types=1);

namespace Mitra\Slim;

use Psr\Http\Message\UriInterface;
use Slim\Interfaces\RouteParserInterface;

final class UriGenerator
{
    /**
     * @var UriInterface
     */
    private $baseUrl;

    /**
     * @var RouteParserInterface
     */
    private $routeParser;

    public function __construct(UriInterface $baseUrl, RouteParserInterface $routeParser)
    {
        $this->baseUrl = $baseUrl;
        $this->routeParser = $routeParser;
    }

    public function fullUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->fullUrlFor($this->baseUrl, $routeName, $data, $queryParams);
    }
}
