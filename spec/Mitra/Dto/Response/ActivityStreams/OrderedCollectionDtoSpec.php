<?php

declare(strict_types=1);

namespace spec\Mitra\Dto\Response\ActivityStreams;

use Mitra\Dto\Response\ActivityStreams\OrderedCollectionDto;
use PhpSpec\ObjectBehavior;

final class OrderedCollectionDtoSpec extends ObjectBehavior
{
    public function it_returns_normalized_array(): void
    {
        $this->toArray()->shouldReturn([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'OrderedCollection',
            'totalItems' => 0,
            'orderedItems' => [],
        ]);
    }
}
