<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\ApiProblem\ApiProblem;
use Mitra\ApiProblem\BadRequestApiProblem;
use Mitra\CommandBus\Command\ActivityPub\AssignActivityStreamContentToFollowersCommand;
use Mitra\CommandBus\Command\ActivityPub\AttributeActivityStreamContentCommand;
use Mitra\CommandBus\Command\ActivityPub\PersistActivityStreamContentCommand;
use Mitra\CommandBus\Command\ActivityPub\ProcessActivityStreamContentCommand;
use Mitra\CommandBus\Command\ActivityPub\ValidateContentCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentReceivedEvent;
use Mitra\CommandBus\EventBusInterface;
use Mitra\Dto\DataToDtoPopulatorException;
use Mitra\Dto\DataToDtoPopulatorInterface;
use Mitra\Dto\DtoToEntityMapper;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDtoInterface;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
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
     * @var EventBusInterface
     */
    private $eventBus;

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
        EventBusInterface $eventBus,
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
        $this->eventBus = $eventBus;
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

        try {
            /** @var ObjectDto $objectDto */
            $objectDto = $this->activityPubDataToDtoPopulator->populate($decodedRequestBody);
        } catch (DataToDtoPopulatorException $e) {
            $apiProblem = (new BadRequestApiProblem())->withDetail(
                sprintf('Could not parse ActivityStream object: %s', $e->getMessage())
            );

            return $this->responseFactory->createResponseFromApiProblem($apiProblem, $request, $accept);
        }

        if (!$objectDto instanceof ActivityDtoInterface) {
            $apiProblem = (new BadRequestApiProblem())->withDetail('Only activities are accepted');

            return $this->responseFactory->createResponseFromApiProblem($apiProblem, $request, $accept);
        }

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
            $this->eventBus->dispatch(new ActivityStreamContentReceivedEvent($activityStreamContent, $objectDto));

            return $this->responseFactory->createResponse(201);
        } catch (\Exception $e) {
            $response = $this->responseFactory->createResponse(500)->withHeader('Content-Type', 'text/plain');

            $response->getBody()->write('ERROR: ' . $e->getMessage() . PHP_EOL . PHP_EOL . $e->getTraceAsString());

            return $response;
        }
    }
}
