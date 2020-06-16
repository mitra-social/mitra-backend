<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\ActivityPub\Resolver\RemoteObjectResolver;
use Mitra\CommandBus\Command\UpdateActorIconCommand;
use Mitra\Dto\Response\ActivityStreams\ImageDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Entity\Media;
use Mitra\Entity\User\AbstractUser;
use Mitra\Repository\MediaRepositoryInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class UpdateActorIconCommandHandler
{

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

    /**
     * @var RemoteObjectResolver
     */
    private $remoteObjectResolver;

    public function __construct(
        RemoteObjectResolver $remoteObjectResolver,
        HashGeneratorInterface $hashGenerator,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        FilesystemInterface $filesystem,
        LoggerInterface $logger,
        MediaRepositoryInterface $mediaRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->remoteObjectResolver = $remoteObjectResolver;
        $this->hashGenerator = $hashGenerator;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->mediaRepository = $mediaRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(UpdateActorIconCommand $command): void
    {
        $this->updateIcon($command->getActorEntity()->getUser(), $command->getIcon());
    }

    /**
     * @param AbstractUser $externalUser
     * @param string|ImageDto|array<ImageDto|string> $icon
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    private function updateIcon(AbstractUser $externalUser, $icon): void
    {
        $newOriginalIconUrl = $this->extractIconUrl($icon);

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

        $fileResponse = $this->httpClient->sendRequest(
            $this->requestFactory->createRequest('GET', $newOriginalIconUrl)
        );

        if (200 !== $fileResponse->getStatusCode()) {
            $this->logger->warning(sprintf(
                'Cannot update user\'s icon: Unable to download icon `%s`, HTTP fileResponse code: %d',
                $newOriginalIconUrl,
                $fileResponse->getStatusCode()
            ));
            return;
        }

        $streamResource = $fileResponse->getBody()->detach();

        $newIconChecksum = $this->hashGenerator->hashResource($streamResource);
        $fileExtension = pathinfo($newOriginalIconUrl, PATHINFO_EXTENSION);

        $newLocalIconUri = 'icons/' . $newIconChecksum . ('' !== $fileExtension ? '.' . $fileExtension : '');

        if (null === $iconMedia = $this->mediaRepository->getByLocalUri($newLocalIconUri)) {
            try {
                if (false === $this->filesystem->writeStream($newLocalIconUri, $streamResource)) {
                    $this->logger->error(sprintf(
                        'Unable to store icon to path `%s`',
                        $newLocalIconUri
                    ));
                    throw new \RuntimeException('Could not store new icon');
                }
            } catch (FileExistsException $e) {
                $this->logger->info(sprintf(
                    'Unable to store icon to path, file already exists at path `%s`: %s',
                    $newLocalIconUri,
                    $e->getMessage()
                ));
            }

            $mimeType = $fileResponse->getHeaderLine('Content-Type');
            $sizeHeader = $fileResponse->getHeaderLine('Content-Length');
            $size = '' !== $sizeHeader ? (int) $sizeHeader : null;

            try {
                // Only request file info from filesystem if we didn't get it within the response headers
                if ('' === $mimeType) {
                    if (false === $mimeType = $this->filesystem->getMimetype($newLocalIconUri)) {
                        throw new \RuntimeException(sprintf(
                            'Could not fetch mime-type for icon with path `%s`',
                            $newLocalIconUri
                        ));
                    }
                }

                if (null === $size) {
                    if (false === $size = $this->filesystem->getSize($newLocalIconUri)) {
                        throw new \RuntimeException(sprintf(
                            'Could not fetch file size for icon with path `%s`',
                            $newLocalIconUri
                        ));
                    }
                }
            } catch (FileNotFoundException $e) {
                throw new \RuntimeException(sprintf(
                    'Could not find file with path `%s`',
                    $newLocalIconUri
                ));
            }

            $iconMedia = new Media(
                Uuid::uuid4()->toString(),
                $newIconChecksum,
                $newOriginalIconUrl,
                $this->hashGenerator->hash($newOriginalIconUrl),
                $newLocalIconUri,
                $mimeType,
                $size
            );
        }

        $this->entityManager->persist($iconMedia);

        $externalUser->getActor()->setIcon($iconMedia);
    }

    private function extractIconUrl($icon): ?string
    {
        if (null === $icon) {
            return null;
        }

        $icon = is_array($icon) ? $icon[0] : $icon;

        if (is_string($icon)) {
            return $icon;
        }

        if ($icon instanceof LinkDto) {
            return (string) $icon;
        }

        // Must be ImageDto then
        return (string) (is_array($icon->url) ? $icon->url[0] : $icon->url);
    }
}
