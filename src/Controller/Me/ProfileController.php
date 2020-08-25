<?php

declare(strict_types=1);

namespace Mitra\Controller\Me;

use Mitra\ApiProblem\NotFoundApiProblem;
use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\User\InternalUser;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\InternalUserRepository;
use Mitra\Serialization\Encode\EncoderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProfileController
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var InternalUserRepository
     */
    private $userRepository;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        InternalUserRepository $userRepository
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->userRepository = $userRepository;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $accept = $request->getAttribute('accept');

        /** @var InternalUser|null $authenticatedUser */
        $authenticatedUser = $request->getAttribute('authenticatedUser');

        if (null === $authenticatedUser) {
            return $this->responseFactory->createResponseFromApiProblem(
                (new NotFoundApiProblem())->withDetail('The requested user cannot be found'),
                $request,
                $accept
            );
        }

        return $this->responseFactory->createResponseFromEntity(
            $authenticatedUser,
            UserResponseDto::class,
            $request,
            $accept
        );
    }
}
