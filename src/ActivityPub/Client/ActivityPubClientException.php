<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class ActivityPubClientException extends \Exception
{
    /**
     * @var null|RequestInterface
     */
    private $request;

    /**
     * @var null|ResponseInterface
     */
    private $response;

    public function __construct(
        ?RequestInterface $request,
        ?ResponseInterface $response,
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return null|RequestInterface
     */
    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }

    /**
     * @return null|ResponseInterface
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
