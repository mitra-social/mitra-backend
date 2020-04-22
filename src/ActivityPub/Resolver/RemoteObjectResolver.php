<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Resolver;

use Mitra\ActivityPub\Client\ActivityPubClientException;
use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;

final class RemoteObjectResolver
{

    /**
     * @var ActivityPubClientInterface
     */
    private $activityPubClient;

    public function __construct(ActivityPubClientInterface $activityPubClient)
    {
        $this->activityPubClient = $activityPubClient;
    }

    /**
     * @param mixed $value
     * @return null|ObjectDto
     * @throws RemoteObjectResolverException
     */
    public function resolve($value): ?ObjectDto
    {
        $url = null;

        if (is_string($value)) {
            $url = $value;
        } elseif ($value instanceof LinkDto) {
            $url = $value->href;
        } elseif ($value instanceof ObjectDto && null !== $value->id) {
            $url = $value->id;
        }

        if (null === $url) {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (false === $scheme || !in_array(strtolower($scheme), ['http', 'https'], true)) {
            return null;
        }

        return $this->fetchRemoteValueByUrl($url);
    }

    /**
     * @param string $url
     * @return ObjectDto|null
     * @throws RemoteObjectResolverException
     */
    private function fetchRemoteValueByUrl(string $url): ?ObjectDto
    {
        try {
            return $this->activityPubClient->sendRequest(
                $this->activityPubClient->createRequest('GET', $url)
            )->getReceivedObject();
        } catch (ActivityPubClientException $e) {
            throw new RemoteObjectResolverException(
                sprintf('Could not resolve remote object with url `%s`: %s', $url, $e->getMessage()),
                0,
                $e
            );
        }
    }
}
