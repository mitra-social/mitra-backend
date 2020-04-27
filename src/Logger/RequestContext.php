<?php

declare(strict_types=1);

namespace Mitra\Logger;

use Psr\Http\Message\ServerRequestInterface;

final class RequestContext
{
    /**
     * @var ServerRequestInterface|null
     */
    private $request;

    /**
     * @return ServerRequestInterface|null
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @param ServerRequestInterface|null $request
     */
    public function setRequest(?ServerRequestInterface $request): void
    {
        $this->request = $request;
    }
}
