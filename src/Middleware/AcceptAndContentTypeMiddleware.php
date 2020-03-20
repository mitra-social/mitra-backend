<?php

declare(strict_types=1);

namespace Mitra\Middleware;

use Mitra\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AcceptAndContentTypeMiddleware
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ('' === $accept = $request->getHeaderLine('Accept')) {
            $response = $this->responseFactory->createResponse(406);

            $response->getBody()->write('"Accept" header is missing');

            return $response;
        }

        $request = $request->withAttribute('accept', $accept);

        if ($request->getBody()->getSize() > 0 && in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            if ('' === $contentType = $request->getHeaderLine('Content-Type')) {
                $response = $this->responseFactory->createResponse(415);

                $response->getBody()->write('"Content-Type" header is missing');

                return $response;
            }

            $request = $request->withAttribute('contentType', $contentType);
        }

        return $handler->handle($request);
    }
}
