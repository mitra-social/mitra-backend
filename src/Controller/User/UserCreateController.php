<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\MessageBus\Command\UserCreateCommand;
use Mitra\MessageBus\CommandBusInterface;
use Mitra\Dto\DtoToEntityMapper;
use Mitra\Dto\Request\CreateUserRequestDto;
use Mitra\Dto\RequestToDtoTransformer;
use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\User\InternalUser;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Validator\ValidatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserCreateController
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
     * @var RequestToDtoTransformer
     */
    private $requestToDtoManager;

    /**
     * @var DtoToEntityMapper
     */
    private $dtoToEntityMapper;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        ValidatorInterface $validator,
        CommandBusInterface $commandBus,
        RequestToDtoTransformer $dataToDtoManager,
        DtoToEntityMapper $dtoToEntityMapper
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->validator = $validator;
        $this->commandBus = $commandBus;
        $this->requestToDtoManager = $dataToDtoManager;
        $this->dtoToEntityMapper = $dtoToEntityMapper;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $accept = $request->getAttribute('accept');

        /** @var CreateUserRequestDto $createUserRequestDto */
        $createUserRequestDto = $this->requestToDtoManager->fromRequest($request, CreateUserRequestDto::class);

        if (($violationList = $this->validator->validate($createUserRequestDto))->hasViolations()) {
            return $this->responseFactory->createResponseFromViolationList($violationList, $request, $accept);
        }

        /** @var InternalUser $user */
        $user = $this->dtoToEntityMapper->map($createUserRequestDto, InternalUser::class);

        $this->commandBus->handle(new UserCreateCommand($user));

        return $this->responseFactory->createResponseFromEntity($user, UserResponseDto::class, $request, $accept, 201);
    }
}
