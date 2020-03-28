<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\Dto\DataToDtoManager;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDto;
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
use Mitra\Entity\User;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\ActivityStreamContentAssignmentRepository;
use Mitra\Repository\UserRepository;
use Mitra\Serialization\Encode\EncoderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Webmozart\Assert\Assert;

final class InboxController
{

    private const ITEMS_PER_PAGE_LIMIT = 25;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ActivityStreamContentAssignmentRepository
     */
    private $activityStreamContentAssignmentRepository;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var RouteCollectorInterface
     */
    private $routeCollector;

    /**
     * @var DataToDtoManager
     */
    private $dataToDtoManager;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        UserRepository $userRepository,
        ActivityStreamContentAssignmentRepository $activityStreamContentAssignmentRepository,
        RouteCollectorInterface $routeCollector,
        DataToDtoManager $dataToDtoManager
    ) {
        $this->responseFactory = $responseFactory;
        $this->userRepository = $userRepository;
        $this->activityStreamContentAssignmentRepository = $activityStreamContentAssignmentRepository;
        $this->encoder = $encoder;
        $this->routeCollector = $routeCollector;
        $this->dataToDtoManager = $dataToDtoManager;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $accept = $request->getAttribute('accept');
        $username = $request->getAttribute('preferredUsername');
        $authenticatedUserId = $request->getAttribute('token')['userId'];
        $pageNo = $request->getQueryParams()['page'] ?? null;

        /** @var User|null $authenticatedUser */
        $authenticatedUser = $this->userRepository->find($authenticatedUserId);

        if (null === $authenticatedUser) {
            return $this->responseFactory->createResponse(403);
        }

        if (null === $inboxUser = $this->userRepository->findOneByPreferredUsername($username)) {
            return $this->responseFactory->createResponse(404);
        }

        $totalItems = $this->activityStreamContentAssignmentRepository->getTotalContentForUserId($inboxUser);
        $totalPages = (int) ceil($totalItems / self::ITEMS_PER_PAGE_LIMIT);
        $lastPageNo = 0 === $totalPages ? 0 : $totalPages - 1;

        if (null === $pageNo) {
            $orderedCollectionDto = new OrderedCollectionDto();
            $orderedCollectionDto->first = $this->routeCollector->getRouteParser()->fullUrlFor(
                $request->getUri(),
                'user-inbox',
                ['preferredUsername' => $inboxUser->getPreferredUsername()],
                ['page' => 0]
            );
            $orderedCollectionDto->last = $this->routeCollector->getRouteParser()->fullUrlFor(
                $request->getUri(),
                'user-inbox',
                ['preferredUsername' => $inboxUser->getPreferredUsername()],
                ['page' => $lastPageNo]
            );
        } else {
            $pageNo = (int) $pageNo;

            if ($pageNo > $lastPageNo) {
                return $this->responseFactory->createResponse(404);
            }

            $inboxUrl = $this->routeCollector->getRouteParser()->fullUrlFor(
                $request->getUri(),
                'user-inbox',
                ['preferredUsername' => $inboxUser->getPreferredUsername()]
            );

            $orderedCollectionDto = new OrderedCollectionPageDto();
            $orderedCollectionDto->partOf = $inboxUrl;

            if ($pageNo > 0) {
                $orderedCollectionDto->prev = $this->routeCollector->getRouteParser()->fullUrlFor(
                    $request->getUri(),
                    'user-inbox',
                    ['preferredUsername' => $inboxUser->getPreferredUsername()],
                    ['page' => $pageNo - 1]
                );
            }

            if ($pageNo < $lastPageNo) {
                $orderedCollectionDto->next = $this->routeCollector->getRouteParser()->fullUrlFor(
                    $request->getUri(),
                    'user-inbox',
                    ['preferredUsername' => $inboxUser->getPreferredUsername()],
                    ['page' => $pageNo + 1]
                );
            }

            $orderedCollectionDto->orderedItems = $this->getItems($inboxUser, $pageNo);
        }

        $orderedCollectionDto->context = TypeInterface::CONTEXT_ACTIVITY_STREAMS;
        $orderedCollectionDto->totalItems = $totalItems;

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->encoder->encode($orderedCollectionDto, $accept));

        return $response;
    }

    /**
     * @param User $user
     * @param int|null $page
     * @return array<ObjectDto|LinkDto>
     */
    private function getItems(User $user, ?int $page): array
    {
        $offset = null;
        $limit = null;

        if (null !== $page) {
            $offset = $page * self::ITEMS_PER_PAGE_LIMIT;
            $limit = self::ITEMS_PER_PAGE_LIMIT;
        }

        $items = $this->activityStreamContentAssignmentRepository->findContentForUserId($user, $offset, $limit);

        $dtoItems = [];

        foreach ($items as $item) {
            /** @var ActivityStreamContentAssignment $item */
            $content = $item->getContent();
            $object =  $content->getObject();

            unset($object['@context']);

            $dtoItems[] = $this->dataToDtoManager->populate(
                $this->mapActivityStreamTypeToDtoClass($content->getType()),
                $object
            );
        }

        Assert::allIsInstanceOfAny($dtoItems, [ObjectDto::class, LinkDto::class]);

        return $dtoItems;
    }

    private function mapActivityStreamTypeToDtoClass(string $type): string
    {
        $map = [
            'Object' => ObjectDto::class,
            'Article' => ArticleDto::class,
            'Audio' => AudioDto::class,
            'Document' => DocumentDto::class,
            'Event' => EventDto::class,
            'Image' => ImageDto::class,
            'Link' => LinkDto::class,
            'Mention' => MentionDto::class,
            'Note' => NoteDto::class,
            'Place' => PlaceDto::class,
            'Profile' => ProfileDto::class,
            'Relationship' => RelationshipDto::class,
            'Tombstone' => TombstoneDto::class,
            'Video' => VideoDto::class,

            'Create' => CreateDto::class,
        ];

        if (!array_key_exists($type, $map)) {
            throw new \RuntimeException(sprintf('Could not map type `%s` to DTO class', $type));
        }

        return $map[$type];
    }
}
