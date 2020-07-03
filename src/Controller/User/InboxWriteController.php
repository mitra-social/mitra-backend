<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\ApiProblem\BadRequestApiProblem;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentPersistedEvent;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentReceivedEvent;
use Mitra\CommandBus\EventBusInterface;
use Mitra\Dto\DataToDtoPopulatorException;
use Mitra\Dto\DataToDtoPopulatorInterface;
use Mitra\Dto\DtoToEntityMapper;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDtoInterface;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Factory\ActivityStreamContentFactoryInterface;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Normalization\NormalizerInterface;
use Mitra\Repository\ActivityStreamContentRepositoryInterface;
use Mitra\Repository\InternalUserRepository;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Validator\ValidatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final class InboxWriteController
{
    /**
     * @var EventBusInterface
     */
    private $eventBus;

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
     * @var ActivityStreamContentRepositoryInterface
     */
    private $activityStreamContentRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ActivityStreamContentFactoryInterface
     */
    private $activityStreamContentFactory;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        ValidatorInterface $validator,
        EventBusInterface $eventBus,
        DataToDtoPopulatorInterface $activityPubDataToDtoPopulator,
        DecoderInterface $decoder,
        DtoToEntityMapper $dtoToEntityMapper,
        InternalUserRepository $internalUserRepository,
        ActivityStreamContentFactoryInterface $activityStreamContentFactory,
        ActivityStreamContentRepositoryInterface $activityStreamContentRepository,
        LoggerInterface $logger
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->validator = $validator;
        $this->eventBus = $eventBus;
        $this->activityPubDataToDtoPopulator = $activityPubDataToDtoPopulator;
        $this->dtoToEntityMapper = $dtoToEntityMapper;
        $this->internalUserRepository = $internalUserRepository;
        $this->activityStreamContentFactory = $activityStreamContentFactory;
        $this->activityStreamContentRepository = $activityStreamContentRepository;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $accept = $request->getAttribute('accept');
        $username = $request->getAttribute('username');

        if (null === $inboxUser = $this->internalUserRepository->findByUsername($username)) {
            return $this->responseFactory->createResponse(404);
        }

        $body = (string) $request->getBody();
        $decodedRequestBody = $this->decoder->decode($body, $accept);

        $this->logger->info(sprintf('Write request to inbox of user %s', $inboxUser->getUsername()), [
            'request.body' => $body,
            'request.headers' => $request->getHeaders(),
        ]);

        try {
            /** @var ObjectDto $objectDto */
            $objectDto = $this->activityPubDataToDtoPopulator->populate($decodedRequestBody);
        } catch (DataToDtoPopulatorException $e) {
            $apiProblemDetail =  sprintf('Could not parse ActivityStream object: %s', $e->getMessage());
            $this->logger->error($apiProblemDetail);
            $apiProblem = (new BadRequestApiProblem())->withDetail($apiProblemDetail);

            return $this->responseFactory->createResponseFromApiProblem($apiProblem, $request, $accept);
        }

        if (!$objectDto instanceof ActivityDtoInterface) {
            $problemDetail = sprintf('Only activities are accepted, `%s` given', $objectDto->type);
            $this->logger->error($problemDetail);
            $apiProblem = (new BadRequestApiProblem())->withDetail($problemDetail);

            return $this->responseFactory->createResponseFromApiProblem($apiProblem, $request, $accept);
        }

        if (($violationList = $this->validator->validate($objectDto))->hasViolations()) {
            $response = $this->responseFactory->createResponseFromViolationList($violationList, $request, $accept);
            $this->logger->error('Violations during validation: ' . (string) $response->getBody());
            return $response;
        }

        if (null !== $activityStreamContent = $this->activityStreamContentRepository->getByExternalId($objectDto->id)) {
            $this->eventBus->dispatch(new ActivityStreamContentPersistedEvent(
                $activityStreamContent,
                $objectDto,
                $inboxUser->getActor()
            ));
            return $this->responseFactory->createResponse(201);
        }

        $activityStreamContent = $this->activityStreamContentFactory->createFromDto($objectDto);

        try {
            $this->eventBus->dispatch(new ActivityStreamContentReceivedEvent(
                $activityStreamContent,
                $objectDto,
                $inboxUser->getActor()
            ));
        } catch (HandlerFailedException $e) {
            if (!$e->getPrevious() instanceof UniqueConstraintViolationException) {
                throw $e;
            }

            $activityStreamContent = $this->activityStreamContentRepository->getByExternalId($objectDto->id);

            if (null !== $activityStreamContent) {
                $this->eventBus->dispatch(new ActivityStreamContentPersistedEvent(
                    $activityStreamContent,
                    $objectDto,
                    $inboxUser->getActor()
                ));
                return $this->responseFactory->createResponse(201);
            }
        }

        return $this->responseFactory->createResponse(201);
    }
}
