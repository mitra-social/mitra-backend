<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

class OrderedCollectionDto extends ObjectDto
{
    public $type = 'OrderedCollection';

    /**
     * @var int
     */
    public $totalItems = 0;

    /**
     * @var array
     */
    public $orderedItems = [];
}
