<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Dto\DataToDtoManager;
use Mitra\Dto\UserDto;
use Mitra\Dto\ViolationDto;
use Mitra\Dto\ViolationListDto;
use Mitra\Entity\User;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Validator\ValidatorInterface;
use Mitra\Validator\ViolationInterface;
use Mitra\Validator\ViolationListInterface;
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
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var DataToDtoManager
     */
    private $dataToDtoManager;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param EncoderInterface $encoder
     * @param DecoderInterface $decoder
     * @param ValidatorInterface $validator
     * @param CommandBusInterface $commandBus
     * @param DataToDtoManager $dataToDtoManager
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        ValidatorInterface $validator,
        CommandBusInterface $commandBus,
        DataToDtoManager $dataToDtoManager
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->validator = $validator;
        $this->commandBus = $commandBus;
        $this->dataToDtoManager = $dataToDtoManager;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $mimeType = 'application/json';
        $decodedBody = $this->decoder->decode((string) $request->getBody(), $mimeType);

        $userDto = new UserDto();
        $this->dataToDtoManager->populate($userDto, $decodedBody);

        if (($violationList = $this->validator->validate($userDto))->hasViolations()) {
            return $this->responseFactory->createResponseFromViolationList($violationList, $mimeType);
        }

        $user = $this->createEntityFromDto($userDto);

        $this->commandBus->handle(new CreateUserCommand($user));

        $userDto->id = $user->getId();

        $response = $this->responseFactory->createResponse(201);

        $response->getBody()->write($this->encoder->encode($userDto, $mimeType));

        return $response;
    }

    private function createEntityFromDto(UserDto $userDto): User
    {
        return new User(Uuid::uuid4()->toString(), $userDto->preferredUsername, $userDto->email);
    }
}
