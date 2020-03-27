<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

class OrderedCollectionDto extends CollectionDto
{
    public $type = 'OrderedCollection';

    /**
     * @var null|array
     */
    public $orderedItems;
}
