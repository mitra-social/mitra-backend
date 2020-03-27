<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use ActivityPhp\Type\Extended\Object\Video;
use Doctrine\Common\Util\Debug;
use Mitra\Dto\DataToDtoManager;
use Mitra\Dto\EntityToDtoMapper;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
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
use Mitra\Dto\Response\ActivityStreams\PageDto;
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
        $pageNo = (int) $request->getQueryParams()['page'] ?? null;

        /** @var User|null $authenticatedUser */
        $authenticatedUser = $this->userRepository->find($authenticatedUserId);

        if (null === $authenticatedUser) {
            return $this->responseFactory->createResponse(403);
        }

        if (null === $inboxUser = $this->userRepository->findOneByPreferredUsername($username)) {
            return $this->responseFactory->createResponse(404);
        }

        if (null !== $pageNo) {
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
        } else {
            $orderedCollectionDto = new OrderedCollectionDto();
        }

        $orderedCollectionDto->context = TypeInterface::CONTEXT_ACTIVITY_STREAMS;
        $orderedCollectionDto->orderedItems = $this->getItems($inboxUser, $pageNo);
        $orderedCollectionDto->totalItems = count($orderedCollectionDto->orderedItems);

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->encoder->encode($orderedCollectionDto, $accept));

        return $response;
    }

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

        return $dtoItems;
    }

    private function getSampleItems(): array
    {
        $personBen = new PersonDto();
        $personBen->name = 'Ben';

        $personSally = new PersonDto();
        $personSally->name = 'Sally';

        $imageLinkJpg = new LinkDto();
        $imageLinkJpg->href = 'http://example.org/image.jpeg';
        $imageLinkJpg->mediaType = 'image/jpeg';

        $imageLinkPng = new LinkDto();
        $imageLinkPng->href = 'http://example.org/image.png';
        $imageLinkPng->mediaType = 'image/png';

        $image = new ImageDto();
        $image->attributedTo = $personSally;
        $image->name = 'Cat Jumping on Wagon';
        $image->url = [
            $imageLinkJpg,
            $imageLinkPng,
        ];

        $video = new VideoDto();
        $image->attributedTo = $personBen;
        $video->name = 'Puppy Plays With Ball';
        $video->url = 'http://example.org/video.mkv';
        $video->duration = 'PT2H';

        $article = new ArticleDto();
        $image->attributedTo = $personSally;
        $article->name = 'What a Crazy Day I Had';
        $article->content = '<div>... you will never believe ...</div>';
        $article->attributedTo = 'http://sally.example.org';

        $page = new PageDto();
        $image->attributedTo = $personSally;
        $page->name = 'Omaha Weather Report';
        $page->url = 'http://example.org/weather-in-omaha.html';

        $note = new NoteDto();
        $image->attributedTo = $personBen;
        $note->name = 'A Word of Warning';
        $note->content = 'Looks like it is going to rain today. Bring an umbrella!';

        return [$image, $video, $article, $page, $note];
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
