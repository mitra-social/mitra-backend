<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Doctrine\Common\Util\Debug;
use Mitra\CommandBus\Command\ActivityPub\AssignActorCommand;
use Mitra\CommandBus\Command\ActivityPub\FollowCommand;
use Mitra\CommandBus\Command\ActivityPub\SendObjectToRecipientsCommand;
use Mitra\CommandBus\Command\ActivityPub\UndoCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\CommandInterface;
use Mitra\Dto\DataToDtoPopulatorInterface;
use Mitra\Dto\DtoToEntityMapper;
use Mitra\Dto\Response\ActivityStreams\Activity\AbstractActivityDto;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Dto\Response\ActivityStreams\Activity\UndoDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\Actor\Actor;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\InternalUserRepository;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\IdGeneratorInterface;
use Mitra\Slim\UriGenerator;
use Mitra\Validator\ValidatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

final class OutboxWriteController
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
     * @var DataToDtoPopulatorInterface
     */
    private $activityPubDataToDtoPopulator;

    /**
     * @var DtoToEntityMapper
     */
    private $dtoToEntityMapper;

    /**
     * @var InternalUserRepository
     */
    private $internalUserRepository;

    /**
     * @var UriGenerator
     */
    private $uriGenerator;

    /**
     * @var IdGeneratorInterface
     */
    private $idGenerator;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        ValidatorInterface $validator,
        CommandBusInterface $commandBus,
        DataToDtoPopulatorInterface $activityPubDataToDtoPopulator,
        DecoderInterface $decoder,
        DtoToEntityMapper $dtoToEntityMapper,
        InternalUserRepository $internalUserRepository,
        UriGenerator $uriGenerator,
        IdGeneratorInterface $idGenerator
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->validator = $validator;
        $this->commandBus = $commandBus;
        $this->activityPubDataToDtoPopulator = $activityPubDataToDtoPopulator;
        $this->dtoToEntityMapper = $dtoToEntityMapper;
        $this->internalUserRepository = $internalUserRepository;
        $this->uriGenerator = $uriGenerator;
        $this->idGenerator = $idGenerator;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $accept = $request->getAttribute('accept');
        $username = $request->getAttribute('username');
        $decodedRequestBody = $this->decoder->decode((string) $request->getBody(), $accept);

        if (null === $outboxUser = $this->internalUserRepository->findByUsername($username)) {
            return $this->responseFactory->createResponse(404);
        }

        if (!is_array($decodedRequestBody) || !array_key_exists('type', $decodedRequestBody)) {
            return $this->responseFactory->createResponse(400);
        }

        /** @var ObjectDto $objectDto */
        $objectDto = $this->activityPubDataToDtoPopulator->populate($decodedRequestBody);

        if (($violationList = $this->validator->validate($objectDto))->hasViolations()) {
            return $this->responseFactory->createResponseFromViolationList($violationList, $request, $accept);
        }

        if ($objectDto instanceof AbstractActivityDto) {
            $this->commandBus->handle(new AssignActorCommand($outboxUser->getActor(), $objectDto));
        }

        // @TODO not just generate a fake URI but actually save the object in the database
        $objectDto->id = $this->uriGenerator->fullUrlFor(
            'user-activity-read',
            ['username' => $outboxUser->getUsername(), 'activityId' => $this->idGenerator->getId()]
        );

        $objectCommand = $this->getCommandForObject($outboxUser->getActor(), $objectDto);

        if (null !== $objectCommand) {
            $this->commandBus->handle($objectCommand);
        }

        $this->commandBus->handle(new SendObjectToRecipientsCommand($outboxUser, $objectDto));

        return $this->responseFactory->createResponse(201);
    }

    private function getCommandForObject(Actor $outboxActor, object $object): ?CommandInterface
    {
        if ($object instanceof FollowDto) {
            return new FollowCommand($outboxActor, $object);
        } elseif ($object instanceof UndoDto) {
            return new UndoCommand($outboxActor, $object);
        }

        return null;
    }
}
