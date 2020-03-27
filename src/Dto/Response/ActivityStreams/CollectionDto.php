<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

class CollectionDto extends ObjectDto
{
    public $type = 'Collection';

    public $current;

    public $first;

    public $last;

    /**
     * @var int
     */
    public $totalItems = 0;

    /**
     * @var null|array
     */
    public $items;
}
