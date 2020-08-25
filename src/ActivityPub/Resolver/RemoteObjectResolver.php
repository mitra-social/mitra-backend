<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Resolver;

use Mitra\ActivityPub\Client\ActivityPubClientException;
use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\ActivityPub\RequestSignerInterface;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\User\InternalUser;
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
     * @var RequestSignerInterface
     */
    private $requestSigner;

    public function __construct(
        ActivityPubClientInterface $activityPubClient,
        CacheInterface $cache,
        HashGeneratorInterface $hashGenerator,
        RequestSignerInterface $requestSigner
    ) {
        $this->activityPubClient = $activityPubClient;
        $this->cache = $cache;
        $this->hashGenerator = $hashGenerator;
        $this->requestSigner = $requestSigner;
    }

    /**
     * @param mixed $value
     * @param InternalUser|null $userContext
     * @return ObjectDto
     * @throws RemoteObjectResolverException
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
        } catch (\Throwable $e) {
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
        $request = $this->activityPubClient->createRequest('GET', $url);
        $request = $this->requestSigner->signRequest($request, $userContext);

        try {
            return $this->activityPubClient->sendRequest($request)->getReceivedObject();
        } catch (ActivityPubClientException $e) {
            throw new RemoteObjectResolverRequestException(
                $request,
                sprintf('Could not resolve remote object with url `%s`: %s', $url, $e->getMessage()),
                0,
                $e
            );
        }
    }
}
