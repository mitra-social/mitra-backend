<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Dto\NestedDto;
use Mitra\Dto\UserDto;
use Mitra\Entity\User;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Validator\ValidatorInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CreateUserController
{

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

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
     * CreateUserController constructor.
     * @param ResponseFactoryInterface $responseFactory
     * @param DecoderInterface $decoder
     * @param ValidatorInterface $validator
     * @param CommandBusInterface $commandBus
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        DecoderInterface $decoder,
        ValidatorInterface $validator,
        CommandBusInterface $commandBus
    ) {
        $this->responseFactory = $responseFactory;
        $this->decoder = $decoder;
        $this->validator = $validator;
        $this->commandBus = $commandBus;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $decodedBody = $this->decoder->decode((string) $request->getBody(), 'application/json');
        $userDto = $this->createDtoFromDecodedRequestBody($decodedBody);

        if (($violationList = $this->validator->validate($userDto))->hasViolations()) {
            $response = $this->responseFactory->createResponse(400);

            $response->getBody()->write(print_r($violationList->getViolations(), true));

            return $response;
        }

        $user = $this->createEntityFromDto($userDto);

        $this->commandBus->handle(new CreateUserCommand($user));

        return $this->responseFactory->createResponse(201);
    }

    private function createDtoFromDecodedRequestBody(array $data): UserDto
    {
        $userDto = new UserDto();

        $userDto->preferredUsername = $data['preferredUsername'];
        $userDto->email = $data['email'];
        $userDto->password = $data['password'];

        $nestedDto = new NestedDto();
        $nestedDto->something = $data['nested']['something'];

        $userDto->nested = $nestedDto;

        return $userDto;
    }

    private function createEntityFromDto(UserDto $userDto): User
    {
        return new User($userDto->preferredUsername, $userDto->email);
    }
}
