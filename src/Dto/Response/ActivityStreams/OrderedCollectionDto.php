<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

final class OrderedCollectionDto extends ObjectDto
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
