<?php

declare(strict_types=1);

namespace Mitra\ActivityPub;

use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\Dto\Response\ActivityStreams\CollectionDto;
use Mitra\Dto\Response\ActivityStreams\CollectionPageDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Dto\Response\ActivityStreams\OrderedCollectionDto;
use Mitra\Dto\Response\ActivityStreams\OrderedCollectionPageDto;

/**
 * @implements \Iterator<string|ObjectDto|LinkDto>
 */
final class CollectionIterator implements \Iterator
{
    /**
     * @var ActivityPubClientInterface
     */
    private $client;

    /**
     * @var \Generator<string|ObjectDto|LinkDto>
     */
    private $iterable;

    public function __construct(ActivityPubClientInterface $client, CollectionDto $collection)
    {
        $this->client = $client;
        $this->iterable = $this->createGenerator($collection);
    }

    /**
     * @param CollectionDto $collection
     * @return \Generator<string|ObjectDto|LinkDto>
     */
    private function createGenerator(CollectionDto $collection): \Generator
    {
        if ($collection instanceof OrderedCollectionDto && null !== $collection->orderedItems) {
            yield from $collection->orderedItems;
        } elseif (null !== $collection->items) {
            yield from $collection->items;
        } elseif (null !== $collection->first) {
            $next = $collection->first;

            while (null !== $next) {
                $response = $this->client->sendRequest(
                    $this->client->createRequest('GET', (string) $next)
                );

                $objectResponse = $response->getReceivedObject();
                $collectionItems = null;
                $next = null;

                if ($objectResponse instanceof OrderedCollectionPageDto) {
                    $collectionItems = $objectResponse->orderedItems;
                    $next = $objectResponse->next;
                }

                if ($objectResponse instanceof CollectionPageDto) {
                    $collectionItems = $objectResponse->items;
                    $next = $objectResponse->next;
                }

                if (null !== $collectionItems) {
                    yield from $collectionItems;
                }
            }
        }
    }

    /**
     * @return string|LinkDto|ObjectDto
     */
    public function current()
    {
        return $this->iterable->current();
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->iterable->next();
    }

    /**
     * @return int|null
     */
    public function key()
    {
        return $this->iterable->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->iterable->valid();
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->iterable->rewind();
    }
}
