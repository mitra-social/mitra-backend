<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Resolver;

use Psr\Http\Message\RequestInterface;
use Throwable;

final class RemoteObjectResolverRequestException extends RemoteObjectResolverException
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(RequestInterface $request, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->request = $request;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
