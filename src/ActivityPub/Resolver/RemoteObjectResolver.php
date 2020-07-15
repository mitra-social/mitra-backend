<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Resolver;

use Mitra\ActivityPub\Client\ActivityPubClientException;
use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\User\InternalUser;
use Mitra\Slim\UriGenerator;
use Psr\SimpleCache\CacheInterface;

final class RemoteObjectResolver
{

    private const SUPPORTED_SCHEMAS = ['http', 'https'];

    /**
     * @var ActivityPubClientInterface
     */
    private $activityPubClient;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var HashGeneratorInterface
     */
    private $hashGenerator;

    /**
     * @var UriGenerator
     */
    private $uriGenerator;

    public function __construct(
        ActivityPubClientInterface $activityPubClient,
        CacheInterface $cache,
        HashGeneratorInterface $hashGenerator,
        UriGenerator $uriGenerator
    ) {
        $this->activityPubClient = $activityPubClient;
        $this->cache = $cache;
        $this->hashGenerator = $hashGenerator;
        $this->uriGenerator = $uriGenerator;
    }

    /**
     * @param mixed $value
     * @param InternalUser|null $userContext
     * @return ObjectDto
     * @throws RemoteObjectResolverException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function resolve($value, ?InternalUser $userContext = null): ObjectDto
    {
        if ($value instanceof ObjectDto) {
            return $value;
        }

        $url = null;

        if (is_string($value)) {
            $url = $value;
        } elseif ($value instanceof LinkDto) {
            $url = $value->href;
        }

        if (null === $url) {
            throw new RemoteObjectResolverException('Could not extract url to resolve');
        }

        $urlHash = $this->hashGenerator->hash($url);

        try {
            if (null !== $cachedObject = $this->cache->get($urlHash)) {
                return $cachedObject;
            }
        } catch (\Exception $e) {
            throw new RemoteObjectResolverException(sprintf('Could not access cache: %s', $e->getMessage()), 0, $e);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (false === $scheme || !in_array(strtolower($scheme), self::SUPPORTED_SCHEMAS, true)) {
            throw new RemoteObjectResolverException(sprintf(
                'Uri `%s` has no supported schema. Supported schemas: %s',
                $url,
                implode(', ', self::SUPPORTED_SCHEMAS)
            ));
        }

        $resolvedObject = $this->fetchRemoteValueByUrl($url, $userContext);

        $this->cache->set($urlHash, $resolvedObject);

        return $resolvedObject;
    }

    /**
     * @param string $url
     * @param InternalUser|null $userContext
     * @return ObjectDto|null
     * @throws RemoteObjectResolverException
     */
    private function fetchRemoteValueByUrl(string $url, ?InternalUser $userContext): ?ObjectDto
    {
        try {
            $request = $this->activityPubClient->createRequest('GET', $url);

            if (null !== $userContext) {
                $userPublicKeyUrl = $this->uriGenerator->fullUrlFor('user-read', [
                    'username' => $userContext->getUsername(),
                ]) . '#main-key';

                $request = $this->activityPubClient->signRequest(
                    $request,
                    $userContext->getPrivateKey(),
                    $userPublicKeyUrl
                );
            }

            return $this->activityPubClient->sendRequest($request)->getReceivedObject();
        } catch (ActivityPubClientException $e) {
            throw new RemoteObjectResolverException(
                sprintf('Could not resolve remote object with url `%s`: %s', $url, $e->getMessage()),
                0,
                $e
            );
        }
    }
}
