<?php

declare(strict_types=1);

namespace Mitra\Middleware;

use HttpSignatures\Verifier;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ValidateHttpSignatureMiddleware
{

    /**
     * @var Verifier
     */
    private $verifier;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(
        Verifier $verifier,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->verifier = $verifier;
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ('' === $request->getHeaderLine('signature')) {
            return $handler->handle($request);
        }

        if (!$this->verifier->isSigned($request)) {
            $response = $this->responseFactory->createResponse(401);

            $response->getBody()->write(sprintf(
                'Could not verify signature header with value `%s`: %s',
                $request->getHeaderLine('signature'),
                implode(', ', $this->verifier->getStatus())
            ));

            return $response;
        }

        return $handler->handle($request);
    }
}
