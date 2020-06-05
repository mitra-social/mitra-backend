<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\CommandBus\Command\ActivityPub\UpdateExternalActorCommand;
use Mitra\Dto\Response\ActivityPub\Actor\ActorInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDto;
use Mitra\Dto\Response\ActivityStreams\ImageDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\Media;
use Mitra\Entity\User\ExternalUser;
use Mitra\Repository\MediaRepositoryInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class UpdateExternalActorCommandHandler
{

    /**
     * @var ExternalUserResolver
     */
    private $externalUserResolver;

    /**
     * @var HashGeneratorInterface
     */
    private $hashGenerator;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MediaRepositoryInterface
     */
    private $mediaRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        ExternalUserResolver $externalUserResolver,
        HashGeneratorInterface $hashGenerator,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        FilesystemInterface $filesystem,
        LoggerInterface $logger,
        MediaRepositoryInterface $mediaRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->externalUserResolver = $externalUserResolver;
        $this->hashGenerator = $hashGenerator;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->mediaRepository = $mediaRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(UpdateExternalActorCommand $command): void
    {
        $dto = $command->getActivityStreamDto();

        if (!$dto instanceof ActivityDto) {
            $this->logger->info(sprintf(
                'Skip updating user as type `%s` is not an activity',
                $dto->type
            ));
            return;
        }

        $object = $dto->object;

        if (!$object instanceof ActorInterface) {
            $this->logger->info(sprintf(
                'Skip updating user as object type `%s` is not an actor type',
                $object->type
            ));
            return;
        }

        if (null === $resolvedActor = $this->externalUserResolver->resolve($object)) {
            $this->logger->info(sprintf(
                'Skip updating user as user with external id `%s` is unknown',
                $object->id
            ));
            return;
        }

        $resolvedActor->setOutbox($object->getOutbox());
        $resolvedActor->setInbox($object->getInbox());
        $resolvedActor->setPreferredUsername($object->getPreferredUsername());

        $resolvedActor->getActor()->setName($object->getName());

        $this->updateIcon($resolvedActor, $object);
    }

    private function updateIcon(ExternalUser $externalUser, ObjectDto $object): void
    {
        $newOriginalIconUrl = $this->extractIconUrl($object);

        if (null === $newOriginalIconUrl) {
            return;
        }

        $currentIcon = $externalUser->getActor()->getIcon();

        if (null !== $currentIcon && $currentIcon->getOriginalUri() === $newOriginalIconUrl) {
            $this->logger->warning(sprintf(
                'Cannot update user\'s icon: Uri `%s` is unchanged',
                $newOriginalIconUrl
            ));
            return;
        }

        $response = $this->httpClient->sendRequest($this->requestFactory->createRequest('GET', $newOriginalIconUrl));

        if (200 !== $response->getStatusCode()) {
            $this->logger->warning(sprintf(
                'Cannot update user\'s icon: Unable to download icon `%s`, HTTP response code: %d',
                $newOriginalIconUrl,
                $response->getStatusCode()
            ));
            return;
        }

        $newIconChecksum = $this->hashGenerator->hash((string) $response->getBody());
        $fileExtension = pathinfo($newOriginalIconUrl, PATHINFO_EXTENSION);

        $newLocalIconUri = 'icons/' . $newIconChecksum . ('' !== $fileExtension ? '.' . $fileExtension : '');

        if (null === $iconMedia = $this->mediaRepository->getByLocalUri($newLocalIconUri)) {
            if (false === $this->filesystem->write($newLocalIconUri, (string)$response->getBody())) {
                throw new \RuntimeException('Could not store new icon');
            }

            $iconMedia = new Media(
                Uuid::uuid4()->toString(),
                $newIconChecksum,
                $newOriginalIconUrl,
                $this->hashGenerator->hash($newOriginalIconUrl),
                $newLocalIconUri
            );
        }

        $this->entityManager->persist($iconMedia);

        $externalUser->getActor()->setIcon($iconMedia);
    }

    private function extractIconUrl(ObjectDto $object): ?string
    {
        if (null === $object->icon) {
            return null;
        }

        $icon = is_array($object->icon) ? $object->icon[0] : $object->icon;

        if (is_string($icon)) {
            return $icon;
        }

        if ($icon instanceof LinkDto) {
            return (string) $icon;
        }

        if ($icon instanceof ImageDto) {
            return (string) (is_array($icon->url) ? $icon->url[0] : $icon->url);
        }

        return null;
    }
}
