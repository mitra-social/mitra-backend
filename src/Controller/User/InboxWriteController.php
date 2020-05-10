<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\CommandBus\Command\ActivityPub\AssignActivityStreamContentToFollowersCommand;
use Mitra\CommandBus\Command\ActivityPub\AssignActorCommand;
use Mitra\CommandBus\Command\ActivityPub\AttributeActivityStreamContentCommand;
use Mitra\CommandBus\Command\ActivityPub\FollowCommand;
use Mitra\CommandBus\Command\ActivityPub\PersistActivityStreamContent;
use Mitra\CommandBus\Command\ActivityPub\SendObjectToRecipientsCommand;
use Mitra\CommandBus\Command\ActivityPub\UndoCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Dto\DataToDtoPopulatorInterface;
use Mitra\Dto\DtoToEntityMapper;
use Mitra\Dto\Response\ActivityStreams\Activity\AbstractActivity;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Dto\Response\ActivityStreams\Activity\UndoDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\Actor\Actor;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Normalization\NormalizerInterface;
use Mitra\Repository\InternalUserRepository;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Validator\ValidatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class InboxWriteController
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        NormalizerInterface $normalizer,
        EncoderInterface $encoder,
        ValidatorInterface $validator,
        CommandBusInterface $commandBus,
        DataToDtoPopulatorInterface $activityPubDataToDtoPopulator,
        DecoderInterface $decoder,
        DtoToEntityMapper $dtoToEntityMapper,
        InternalUserRepository $internalUserRepository,
        LoggerInterface $logger
    ) {
        $this->responseFactory = $responseFactory;
        $this->normalizer = $normalizer;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->validator = $validator;
        $this->commandBus = $commandBus;
        $this->activityPubDataToDtoPopulator = $activityPubDataToDtoPopulator;
        $this->dtoToEntityMapper = $dtoToEntityMapper;
        $this->internalUserRepository = $internalUserRepository;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $accept = $request->getAttribute('accept');
        $decodedRequestBody = $this->decoder->decode((string) $request->getBody(), $accept);

        $this->logger->info('Write request to inbox', [
            'request.body' => (string) $request->getBody(),
            'request.headers' => $request->getHeaders(),
        ]);

        if (!is_array($decodedRequestBody) || !array_key_exists('type', $decodedRequestBody)) {
            return $this->responseFactory->createResponse(400);
        }

        /** @var ObjectDto $objectDto */
        $objectDto = $this->activityPubDataToDtoPopulator->populate($decodedRequestBody);

        if (($violationList = $this->validator->validate($objectDto))->hasViolations()) {
            return $this->responseFactory->createResponseFromViolationList($violationList, $request, $accept);
        }

        $activityStreamContent = new ActivityStreamContent(
            Uuid::uuid4()->toString(),
            $objectDto->id,
            md5($objectDto->id),
            $objectDto->type,
            $this->normalizer->normalize($objectDto),
            null,
            null !== $objectDto->published ? new \DateTimeImmutable($objectDto->published) : null,
            null !== $objectDto->updated ? new \DateTimeImmutable($objectDto->updated) : null,
        );

        try {
            $this->commandBus->handle(new AttributeActivityStreamContentCommand($activityStreamContent, $objectDto));
            $this->commandBus->handle(new PersistActivityStreamContent($activityStreamContent, $objectDto));
            $this->commandBus->handle(new AssignActivityStreamContentToFollowersCommand(
                $activityStreamContent,
                $objectDto
            ));

            $objectCommand = $this->getCommandForObject($activityStreamContent);

            if (null !== $objectCommand) {
                $this->commandBus->handle($objectCommand);
            }

            return $this->responseFactory->createResponse(201);
        } catch (\Exception $e) {
            $response = $this->responseFactory->createResponse(500)->withHeader('Content-Type', 'text/plain');

            $response->getBody()->write('ERROR: ' . $e->getMessage() . PHP_EOL . PHP_EOL . $e->getTraceAsString());

            return $response;
        }
    }

    private function getCommandForObject(ActivityStreamContent $activityStreamContent): ?object
    {
        return null;
    }
}
