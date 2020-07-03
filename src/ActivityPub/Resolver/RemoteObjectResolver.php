<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Resolver;

use Mitra\ActivityPub\Client\ActivityPubClientException;
use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;

final class RemoteObjectResolver
{

    private const SUPPORTED_SCHEMAS = ['http', 'https'];

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
    public function resolve($value): ObjectDto
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

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (false === $scheme || !in_array(strtolower($scheme), self::SUPPORTED_SCHEMAS, true)) {
            throw new RemoteObjectResolverException(sprintf(
                'Uri `%s` has no supported schema. Supported schemas: %s',
                $url,
                implode(', ', self::SUPPORTED_SCHEMAS)
            ));
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
