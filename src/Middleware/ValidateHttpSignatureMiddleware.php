<?php

declare(strict_types=1);

namespace Mitra\Middleware;

use HttpSignatures\Verifier;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Verifier $verifier,
        ResponseFactoryInterface $responseFactory,
        LoggerInterface $logger
    ) {
        $this->verifier = $verifier;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ('' === $request->getHeaderLine('signature')) {
            return $handler->handle($request);
        }

        try {
            if (!$this->verifier->isSigned($request)) {
                $reason = sprintf(
                    'Could not verify signature header with value `%s`: %s',
                    $request->getHeaderLine('signature'),
                    implode(', ', $this->verifier->getStatus())
                );

                $this->logger->info($reason, [
                    'request.headers' => $request->getHeaders(),
                    'request.body' => (string) $request->getBody(),
                ]);

                $response = $this->responseFactory->createResponse(401);
                $response->getBody()->write($reason);

                return $response;
            }
        } catch (\Exception $e) {
            $reason = sprintf(
                'Could not verify signature header with value `%s`: %s',
                $request->getHeaderLine('signature'),
                $e->getMessage()
            );

            $this->logger->info($reason, [
                'request.headers' => $request->getHeaders(),
                'request.body' => (string) $request->getBody(),
            ]);

            $response = $this->responseFactory->createResponse(401);
            $response->getBody()->write($reason);

            return $response;
        }

        return $handler->handle($request);
    }
}
