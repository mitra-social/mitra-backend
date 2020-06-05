<?php

declare(strict_types=1);

namespace Mitra\Controller\System;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\MediaRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Stream;

final class MediaController
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var MediaRepositoryInterface
     */
    private $mediaRepository;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        MediaRepositoryInterface $mediaRepository,
        FilesystemInterface $filesystem
    ) {
        $this->responseFactory = $responseFactory;
        $this->mediaRepository = $mediaRepository;
        $this->filesystem = $filesystem;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if (null === $media = $this->mediaRepository->getByOriginalUriHash($request->getAttribute('hash'))) {
            return $this->responseFactory->createResponse(404);
        }

        try {
            $streamResource = $this->filesystem->readStream($media->getLocalUri());
            $response = $this->responseFactory->createResponse(200);

            return $response->withBody(new Stream($streamResource));
        } catch (FileNotFoundException $e) {
            return $this->responseFactory->createResponse(404);
        }
    }
}
