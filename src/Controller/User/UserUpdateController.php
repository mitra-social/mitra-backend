<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\ApiProblem\ForbiddenApiProblem;
use Mitra\ApiProblem\NotFoundApiProblem;
use Mitra\Dto\DtoToEntityMapper;
use Mitra\Dto\Request\UpdateUserRequestDto;
use Mitra\Dto\RequestToDtoTransformer;
use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\User\InternalUser;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\MessageBus\Command\UserCreateCommand;
use Mitra\MessageBus\Command\UserUpdateCommand;
use Mitra\MessageBus\CommandBusInterface;
use Mitra\Repository\InternalUserRepository;
use Mitra\Security\PasswordVerifierInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Validator\ValidatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserUpdateController
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
    private $internalUserRepository;

    /**
     * @var RequestToDtoTransformer
     */
    private $requestToDtoManager;

    /**
     * @var DtoToEntityMapper
     */
    private $dtoToEntityMapper;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var PasswordVerifierInterface
     */
    private $passwordVerifier;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        ValidatorInterface $validator,
        CommandBusInterface $commandBus,
        RequestToDtoTransformer $dataToDtoManager,
        DtoToEntityMapper $dtoToEntityMapper,
        InternalUserRepository $internalUserRepository,
        PasswordVerifierInterface $passwordVerifier
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->validator = $validator;
        $this->commandBus = $commandBus;
        $this->dtoToEntityMapper = $dtoToEntityMapper;
        $this->requestToDtoManager = $dataToDtoManager;
        $this->internalUserRepository = $internalUserRepository;
        $this->passwordVerifier = $passwordVerifier;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $accept = $request->getAttribute('accept');
        $username = $request->getAttribute('username');

        /** @var InternalUser|null $authenticatedUser */
        $authenticatedUser = $request->getAttribute('authenticatedUser');

        if (null === $requestedUser = $this->internalUserRepository->findByUsername($username)) {
            return $this->responseFactory->createResponseFromApiProblem(
                (new NotFoundApiProblem())->withDetail('Could not find requested user'),
                $request,
                $accept
            );
        }

        if (null !== $authenticatedUser && $authenticatedUser->getId() !== $requestedUser->getId()) {
            return $this->responseFactory->createResponseFromApiProblem(
                (new ForbiddenApiProblem())->withDetail('Authenticated user is not allowed to modify requested user'),
                $request,
                $accept
            );
        }

        /** @var UpdateUserRequestDto $updateUserRequestDto */
        $updateUserRequestDto = $this->requestToDtoManager->fromRequest($request, UpdateUserRequestDto::class);

        if (($violationList = $this->validator->validate($updateUserRequestDto))->hasViolations()) {
            return $this->responseFactory->createResponseFromViolationList($violationList, $request, $accept);
        }

        if (
            false === $this->passwordVerifier->verify(
                $updateUserRequestDto->password,
                $requestedUser->getHashedPassword()
            )
        ) {
            return $this->responseFactory->createResponseFromApiProblem(
                (new ForbiddenApiProblem())->withDetail('Old password does not match'),
                $request,
                $accept
            );
        }

        /** @var InternalUser $user */
        $user = $this->dtoToEntityMapper->map($updateUserRequestDto, $authenticatedUser);

        $this->commandBus->handle(new UserUpdateCommand($user));

        return $this->responseFactory->createResponseFromEntity($user, UserResponseDto::class, $request, $accept, 200);
    }
}
