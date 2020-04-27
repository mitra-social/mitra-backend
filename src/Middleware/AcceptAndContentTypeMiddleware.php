<?php

declare(strict_types=1);

namespace Mitra\Middleware;

use Mitra\Http\Message\ResponseFactoryInterface;
use Negotiation\AcceptEncoding;
use Negotiation\EncodingNegotiator;
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
        $defaultMimeType = 'application/json';
        $acceptHeader = $request->getHeaderLine('Accept');

        if ('' === $acceptHeader || '*/*' === $acceptHeader) {
            $acceptHeader = $defaultMimeType;
        }

        $negotiator = new EncodingNegotiator();
        /** @var AcceptEncoding|null $mediaType */
        $mediaType = $negotiator->getBest($acceptHeader, [$defaultMimeType, 'application/activity+json']);

        if (null === $mediaType) {
            $response = $this->responseFactory->createResponse(406)->withHeader('Content-Type', 'text/plain');

            $response->getBody()->write(sprintf(
                'None of the accepted MIME-types `%s` provided is supported',
                $acceptHeader
            ));

            return $response;
        }

        $request = $request->withAttribute('accept', $mediaType->getType());

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
