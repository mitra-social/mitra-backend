<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Dto\Request\CreateUserRequestDto;
use Mitra\Dto\RequestToDtoManager;
use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\User;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Validator\ValidatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

final class CreateUserController
{

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var RequestToDtoManager
     */
    private $requestToDtoManager;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param EncoderInterface $encoder
     * @param ValidatorInterface $validator
     * @param CommandBusInterface $commandBus
     * @param RequestToDtoManager $dataToDtoManager
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        ValidatorInterface $validator,
        CommandBusInterface $commandBus,
        RequestToDtoManager $dataToDtoManager
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->validator = $validator;
        $this->commandBus = $commandBus;
        $this->requestToDtoManager = $dataToDtoManager;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if ('' === $mimeType = $request->getHeaderLine('Accept')) {
            $mimeType = 'application/json';
        }

        $createUserRequestDto = new CreateUserRequestDto();
        $this->requestToDtoManager->populate($createUserRequestDto, $request);

        if (($violationList = $this->validator->validate($createUserRequestDto))->hasViolations()) {
            return $this->responseFactory->createResponseFromViolationList($violationList, $mimeType);
        }

        $user = $this->createEntityFromDto($createUserRequestDto);

        $this->commandBus->handle(new CreateUserCommand($user));

        return $this->responseFactory->createResponseFromEntity($user, UserResponseDto::class, $mimeType, 201);
    }

    private function createEntityFromDto(CreateUserRequestDto $userDto): User
    {
        $user = new User(Uuid::uuid4()->toString(), $userDto->preferredUsername, $userDto->email);

        $user->setPlaintextPassword($userDto->password);

        return $user;
    }
}
