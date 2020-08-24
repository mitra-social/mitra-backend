<?php

declare(strict_types=1);

namespace Mitra\Middleware;

use Mitra\ApiProblem\BadRequestApiProblem;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\InternalUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ResolveAuthenticatedUserMiddleware
{
    /**
     * @var InternalUserRepository
     */
    private $internalUserRepository;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var string
     */
    private $decodedTokenAttributeName;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        InternalUserRepository $internalUserRepository,
        string $decodedTokenAttributeName
    ) {
        $this->responseFactory = $responseFactory;
        $this->internalUserRepository = $internalUserRepository;
        $this->decodedTokenAttributeName = $decodedTokenAttributeName;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (null === $decodedToken = $request->getAttribute($this->decodedTokenAttributeName)) {
            return $handler->handle($request);
        }

        $accept = $request->getAttribute('accept');

        if (!is_array($decodedToken) || !array_key_exists('userId', $decodedToken)) {
            return $this->responseFactory->createResponseFromApiProblem(
                (new BadRequestApiProblem())->withDetail('Authentication token doesn\'t contain an user id'),
                $request,
                $accept
            );
        }

        if (null === $user = $this->internalUserRepository->findById($decodedToken['userId'])) {
            return $this->responseFactory->createResponseFromApiProblem(
                (new BadRequestApiProblem())->withDetail('Authentication token\'s user doesn\'t exist'),
                $request,
                $accept
            );
        }

        $request = $request->withAttribute('authenticatedUser', $user);

        return $handler->handle($request);
    }
}
