<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

class OrderedCollectionDto extends CollectionDto
{
    /**
     * @var string
     */
    public $type = 'OrderedCollection';

    /**
     * @var null|array<ObjectDto|LinkDto>
     */
    public $orderedItems;
}
