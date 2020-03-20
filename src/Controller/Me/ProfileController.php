<?php

declare(strict_types=1);

namespace Mitra\Controller\Me;

use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\User;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\UserRepository;
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
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        UserRepository $userRepository
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->userRepository = $userRepository;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if ('' === $mimeType = $request->getHeaderLine('Accept')) {
            $mimeType = 'application/json';
        }

        $userId = $request->getAttribute('token')['userId'];

        /** @var User|null $user */
        $user = $this->userRepository->find($userId);

        if (null === $user) {
            return $this->responseFactory->createResponse(404);
        }

        return $this->responseFactory->createResponseFromEntity($user, UserResponseDto::class, $mimeType);
    }
}
