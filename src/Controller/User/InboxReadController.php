<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\Dto\DataToDtoPopulatorInterface;
use Mitra\Dto\EntityToDtoMapper;
use Mitra\Dto\Response\ActivityPub\Actor\OrganizationDto;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Actor\Person;
use Mitra\Filtering\Filter;
use Mitra\Filtering\FilterFactoryInterface;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\ActivityStreamContentAssignmentRepositoryInterface;
use Mitra\Repository\InternalUserRepository;
use Mitra\Slim\UriGeneratorInterface;
use Psr\Http\Message\ServerRequestInterface;

final class InboxReadController extends AbstractOrderedCollectionController
{
    /**
     * @var ActivityStreamContentAssignmentRepositoryInterface
     */
    private $activityStreamContentAssignmentRepository;

    /**
     * @var EntityToDtoMapper
     */
    private $entityToDtoMapper;

    /**
     * @var DataToDtoPopulatorInterface
     */
    private $activityPubDataToDtoPopulator;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        FilterFactoryInterface $filterFactory,
        InternalUserRepository $internalUserRepository,
        ActivityStreamContentAssignmentRepositoryInterface $activityStreamContentAssignmentRepository,
        UriGeneratorInterface $uriGenerator,
        EntityToDtoMapper $entityToDtoMapper,
        DataToDtoPopulatorInterface $activityPubDataToDtoPopulator
    ) {
        parent::__construct($internalUserRepository, $uriGenerator, $responseFactory, $filterFactory);

        $this->activityStreamContentAssignmentRepository = $activityStreamContentAssignmentRepository;
        $this->entityToDtoMapper = $entityToDtoMapper;
        $this->activityPubDataToDtoPopulator = $activityPubDataToDtoPopulator;
    }

    /**
     * @param ServerRequestInterface $request
     * @param Actor $actor
     * @param Filter|null $filter
     * @param int|null $page
     * @return array<ObjectDto|LinkDto>
     * @throws \Mitra\Dto\DataToDtoPopulatorException
     */
    protected function getItems(ServerRequestInterface $request, Actor $actor, ?Filter $filter, ?int $page): array
    {
        $offset = null;
        $limit = null;

        if (null !== $page) {
            $offset = $page * self::ITEMS_PER_PAGE_LIMIT;
            $limit = self::ITEMS_PER_PAGE_LIMIT;
        }

        $items = $this->activityStreamContentAssignmentRepository->findContentForActor(
            $actor,
            $filter,
            $offset,
            $limit
        );

        $dtoItems = [];

        foreach ($items as $item) {
            /** @var ActivityStreamContentAssignment $item */
            $content = $item->getContent();
            $object =  $content->getObject();

            /** @var ObjectDto|LinkDto $dto */
            $dto = $this->activityPubDataToDtoPopulator->populate($object);

            $itemContent = $item->getContent();

            // TODO: Inline object infos
            $linkedObjects = [];

            foreach ($item->getContent()->getLinkedObjects() as $linkedObject) {
                /** @var ActivityStreamContent $linkedObject */
                $linkedObjects[$linkedObject->getExternalId()] = $linkedObject;
            }

            if ($dto instanceof ObjectDto) {
                $dto->inReplyTo = $this->resolveLinkedObjects($linkedObjects, $dto->inReplyTo);
            }

            if ($dto instanceof ActivityDto) {
                $dto->object = $this->resolveLinkedObjects($linkedObjects, $dto->object);

                // Don't leak anonymous recipients
                $dto->bto = null;
                $dto->bcc = null;

                // Inline author infos
                $author = $itemContent->getAttributedTo()->getUser();
                $dtoClass = $author->getActor() instanceof Person ? PersonDto::class : OrganizationDto::class;
                /** @var ObjectDto $actorDto */
                $actorDto = $this->entityToDtoMapper->map(
                    $author,
                    $dtoClass,
                    $request
                );
                $dto->actor = $actorDto;
            }

            $dtoItems[]  = $dto;
        }

        return $dtoItems;
    }

    /**
     * @param array<ActivityStreamContent> $linkedObjects
     * @param null|string|ObjectDto|LinkDto|array<string|ObjectDto|LinkDto> $objects
     * @param int $level
     * @return null|string|ObjectDto|LinkDto|array<string|ObjectDto|LinkDto>
     * @throws \Mitra\Dto\DataToDtoPopulatorException
     */
    private function resolveLinkedObjects(array $linkedObjects, $objects, int $level = 0)
    {
        if (null === $objects || $level > 1) {
            return $objects;
        }

        $objects = is_array($objects) ? $objects : [$objects];
        $resolvedObjects = [];

        foreach ($objects as $object) {
            $externalId = null;

            if (is_string($object) || $object instanceof LinkDto) {
                $externalId = (string) $object;

                if (!isset($linkedObjects[$externalId])) {
                    $resolvedObjects[] = $object;
                    continue;
                }

                /** @var ObjectDto|LinkDto $resolvedObject */
                $resolvedObject = $this->activityPubDataToDtoPopulator->populate(
                    $linkedObjects[$externalId]->getObject()
                );
            } else {
                $resolvedObject = $object;
            }

            if ($resolvedObject instanceof ActivityDto) {
                $resolvedObject->object = $this->resolveLinkedObjects(
                    $linkedObjects,
                    $resolvedObject->object,
                    $level + 1
                );
            }

            if ($resolvedObject instanceof ObjectDto) {
                $resolvedObject->inReplyTo = $this->resolveLinkedObjects(
                    $linkedObjects,
                    $resolvedObject->inReplyTo,
                    $level + 1
                );
            }

            $resolvedObjects[] = $resolvedObject;
        }
        
        return 1 === count($resolvedObjects) ? $resolvedObjects[0] : $resolvedObjects;
    }

    protected function getTotalItemCount(Actor $requestedActor, ?Filter $filter): int
    {
        return $this->activityStreamContentAssignmentRepository->getTotalCountForActor($requestedActor, $filter);
    }

    protected function getCollectionRouteName(): string
    {
        return 'user-inbox-read';
    }
}
