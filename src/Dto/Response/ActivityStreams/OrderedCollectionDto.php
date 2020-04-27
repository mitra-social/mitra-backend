<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

class OrderedCollectionDto extends CollectionDto implements OrderedCollectionInterface
{
    /**
     * @var string
     */
    public $type = 'OrderedCollection';

    /**
     * @var null|array<ObjectDto|LinkDto|string>
     */
    public $orderedItems;

    public function setOrderedItems(?array $items): void
    {
        $this->orderedItems = $items;
    }
}
