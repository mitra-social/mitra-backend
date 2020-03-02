<?php

declare(strict_types=1);

namespace Mitra\Controller\Me;

use Mitra\Dto\EntityToDtoManager;
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

    /**
     * @var EntityToDtoManager
     */
    private $entityToDtoManager;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        UserRepository $userRepository,
        EntityToDtoManager $entityToDtoManager
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->userRepository = $userRepository;
        $this->entityToDtoManager = $entityToDtoManager;
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

        $response = $this->responseFactory->createResponse(200);

        $response->getBody()->write($this->encoder->encode($this->createDtoFromEntity($user), $mimeType));

        return $response;
    }

    private function createDtoFromEntity(User $user): UserResponseDto
    {
        $userResponseDto = new UserResponseDto();
        $this->entityToDtoManager->populate($userResponseDto, $user);

        return $userResponseDto;
    }
}
