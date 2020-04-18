<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\Dto\DataToDtoTransformer;
use Mitra\Dto\Response\ActivityStreams\Activity\CreateDto;
use Mitra\Dto\Response\ActivityStreams\ArticleDto;
use Mitra\Dto\Response\ActivityStreams\AudioDto;
use Mitra\Dto\Response\ActivityStreams\DocumentDto;
use Mitra\Dto\Response\ActivityStreams\EventDto;
use Mitra\Dto\Response\ActivityStreams\ImageDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\MentionDto;
use Mitra\Dto\Response\ActivityStreams\NoteDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Dto\Response\ActivityStreams\OrderedCollectionDto;
use Mitra\Dto\Response\ActivityStreams\OrderedCollectionPageDto;
use Mitra\Dto\Response\ActivityStreams\PlaceDto;
use Mitra\Dto\Response\ActivityStreams\ProfileDto;
use Mitra\Dto\Response\ActivityStreams\RelationshipDto;
use Mitra\Dto\Response\ActivityStreams\TombstoneDto;
use Mitra\Dto\Response\ActivityStreams\TypeInterface;
use Mitra\Dto\Response\ActivityStreams\VideoDto;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\User\InternalUser;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Mapping\Dto\ActivityStreamTypeToDtoClassMapping;
use Mitra\Repository\ActivityStreamContentAssignmentRepository;
use Mitra\Repository\InternalUserRepository;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\UriGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Webmozart\Assert\Assert;

final class InboxReadController
{

    private const ITEMS_PER_PAGE_LIMIT = 25;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var InternalUserRepository
     */
    private $internalUserRepository;

    /**
     * @var ActivityStreamContentAssignmentRepository
     */
    private $activityStreamContentAssignmentRepository;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var UriGenerator
     */
    private $uriGenerator;

    /**
     * @var DataToDtoTransformer
     */
    private $dataToDtoTransformer;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        InternalUserRepository $userRepository,
        ActivityStreamContentAssignmentRepository $activityStreamContentAssignmentRepository,
        UriGenerator $uriGenerator,
        DataToDtoTransformer $dataToDtoManager
    ) {
        $this->responseFactory = $responseFactory;
        $this->internalUserRepository = $userRepository;
        $this->activityStreamContentAssignmentRepository = $activityStreamContentAssignmentRepository;
        $this->encoder = $encoder;
        $this->uriGenerator = $uriGenerator;
        $this->dataToDtoTransformer = $dataToDtoManager;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $accept = $request->getAttribute('accept');
        $username = $request->getAttribute('username');
        $pageNo = $request->getQueryParams()['page'] ?? null;

        $authenticatedUser = $this->internalUserRepository->resolveFromRequest($request);

        if (null === $authenticatedUser) {
            return $this->responseFactory->createResponse(403);
        }

        if (null === $inboxUser = $this->internalUserRepository->findByUsername($username)) {
            return $this->responseFactory->createResponse(404);
        }

        $inboxUsername = $inboxUser->getUsername();
        $inboxActor = $inboxUser->getActor();
        $inboxRouteName = 'user-inbox-read';

        $totalItems = $this->activityStreamContentAssignmentRepository->getTotalContentForUserId($inboxActor);
        $totalPages = (int) ceil($totalItems / self::ITEMS_PER_PAGE_LIMIT);
        $lastPageNo = 0 === $totalPages ? 0 : $totalPages - 1;

        if (null === $pageNo) {
            $orderedCollectionDto = new OrderedCollectionDto();
            $orderedCollectionDto->first = $this->uriGenerator->fullUrlFor(
                $inboxRouteName,
                ['username' => $inboxUsername],
                ['page' => 0]
            );
            $orderedCollectionDto->last = $this->uriGenerator->fullUrlFor(
                $inboxRouteName,
                ['username' => $inboxUsername],
                ['page' => $lastPageNo]
            );
        } else {
            $pageNo = (int) $pageNo;

            if ($pageNo > $lastPageNo) {
                return $this->responseFactory->createResponse(404);
            }

            $inboxUrl = $this->uriGenerator->fullUrlFor(
                $inboxRouteName,
                ['username' => $inboxUsername]
            );

            $orderedCollectionDto = new OrderedCollectionPageDto();
            $orderedCollectionDto->partOf = $inboxUrl;

            if ($pageNo > 0) {
                $orderedCollectionDto->prev = $this->uriGenerator->fullUrlFor(
                    'user-inbox',
                    ['username' => $inboxUsername],
                    ['page' => $pageNo - 1]
                );
            }

            if ($pageNo < $lastPageNo) {
                $orderedCollectionDto->next = $this->uriGenerator->fullUrlFor(
                    'user-inbox',
                    ['username' => $inboxUsername],
                    ['page' => $pageNo + 1]
                );
            }

            $orderedCollectionDto->orderedItems = $this->getItems($inboxActor, $pageNo);
        }

        $orderedCollectionDto->context = TypeInterface::CONTEXT_ACTIVITY_STREAMS;
        $orderedCollectionDto->totalItems = $totalItems;

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->encoder->encode($orderedCollectionDto, $accept));

        return $response;
    }

    /**
     * @param Actor $actor
     * @param int|null $page
     * @return array<ObjectDto|LinkDto>
     * @throws \Exception
     */
    private function getItems(Actor $actor, ?int $page): array
    {
        $offset = null;
        $limit = null;

        if (null !== $page) {
            $offset = $page * self::ITEMS_PER_PAGE_LIMIT;
            $limit = self::ITEMS_PER_PAGE_LIMIT;
        }

        $items = $this->activityStreamContentAssignmentRepository->findContentForActor(
            $actor,
            $offset,
            $limit
        );

        $dtoItems = [];

        foreach ($items as $item) {
            /** @var ActivityStreamContentAssignment $item */
            $content = $item->getContent();
            $object =  $content->getObject();

            unset($object['@context']);

            $dtoItems[] = $this->dataToDtoTransformer->populate(
                ActivityStreamTypeToDtoClassMapping::map($content->getType()),
                $object
            );
        }

        Assert::allIsInstanceOfAny($dtoItems, [ObjectDto::class, LinkDto::class]);

        return $dtoItems;
    }
}
