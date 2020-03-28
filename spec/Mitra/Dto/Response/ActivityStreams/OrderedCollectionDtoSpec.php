<?php

declare(strict_types=1);

namespace spec\Mitra\Dto\Response\ActivityStreams;

use Mitra\Dto\Response\ActivityStreams\OrderedCollectionDto;
use PhpSpec\ObjectBehavior;

final class OrderedCollectionDtoSpec extends ObjectBehavior
{
    public function it_returns_normalized_array(): void
    {
        $this->context = 'https://www.w3.org/ns/activitystreams';
        $this->orderedItems = [];

        $this->toArray()->shouldReturn([
            '@context' => $this->context,
            'type' => 'OrderedCollection',
            'orderedItems' => $this->orderedItems,
            'totalItems' => 0,
        ]);
    }
}
