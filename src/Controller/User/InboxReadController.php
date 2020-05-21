<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\Dto\DataToDtoTransformer;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Actor\Actor;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Mapping\Dto\ActivityStreamTypeToDtoClassMapping;
use Mitra\Repository\ActivityStreamContentAssignmentRepository;
use Mitra\Repository\InternalUserRepository;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\UriGenerator;
use Webmozart\Assert\Assert;

final class InboxReadController extends AbstractOrderedCollectionController
{
    /**
     * @var ActivityStreamContentAssignmentRepository
     */
    private $activityStreamContentAssignmentRepository;

    /**
     * @var DataToDtoTransformer
     */
    private $dataToDtoTransformer;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        InternalUserRepository $internalUserRepository,
        ActivityStreamContentAssignmentRepository $activityStreamContentAssignmentRepository,
        UriGenerator $uriGenerator,
        DataToDtoTransformer $dataToDtoManager
    ) {
        parent::__construct($internalUserRepository, $uriGenerator, $responseFactory);

        $this->activityStreamContentAssignmentRepository = $activityStreamContentAssignmentRepository;
        $this->dataToDtoTransformer = $dataToDtoManager;
    }

    /**
     * @param Actor $actor
     * @param int|null $page
     * @return array<ObjectDto|LinkDto>
     * @throws \Exception
     */
    protected function getItems(Actor $actor, ?int $page): array
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

            /** @var ObjectDto $dto */
            $dto = $this->dataToDtoTransformer->populate(
                ActivityStreamTypeToDtoClassMapping::map($content->getType()),
                $object
            );

            // Don't leak anonymous recipients
            $dto->bto = null;
            $dto->bcc = null;

            $dtoItems[]  = $dto;
        }

        return $dtoItems;
    }

    protected function getTotalItemCount(Actor $requestedActor): int
    {
        return $this->activityStreamContentAssignmentRepository->getTotalContentForUserId($requestedActor);
    }

    protected function getCollectionRouteName(): string
    {
        return 'user-inbox-read';
    }
}
