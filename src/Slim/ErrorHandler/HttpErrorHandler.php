<?php

declare(strict_types=1);

namespace Mitra\Slim\ErrorHandler;

use Mitra\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Slim\Exception\HttpSpecializedException;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;
use Webmozart\Assert\Assert;

final class HttpErrorHandler implements ErrorHandlerInterface
{

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ResponseFactoryInterface $responseFactory, LoggerInterface $logger)
    {
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param Throwable|HttpException $exception
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        Assert::isInstanceOf($exception, HttpException::class);

        $requestMethod = $request->getMethod();
        $requestPath = $request->getUri()->getPath();

        if ($exception->getCode() > 499) {
            $this->logger->error($exception->getMessage(), [
                'request.method' => $requestMethod,
                'request.path' => $requestPath,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'stacktrace' => $exception->getTraceAsString(),
            ]);
        }

        $response = $this->responseFactory->createResponse($exception->getCode())
            ->withHeader('Content-Type', 'text/plain');

        $response->getBody()->write(sprintf("%s\n%s", $exception->getTitle(), $exception->getDescription()));

        return $response;
    }
}
