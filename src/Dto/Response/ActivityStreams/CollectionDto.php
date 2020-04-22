<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

class CollectionDto extends ObjectDto implements CollectionInterface
{
    /**
     * @var string
     */
    public $type = 'Collection';

    /**
     * In a paged Collection, indicates the page that contains the most recently updated member items.
     * @var null|LinkDto|string
     */
    public $current;

    /**
     * In a paged Collection, indicates the furthest preceeding page of items in the collection.
     * @var null|LinkDto|string
     */
    public $first;

    /**
     * In a paged Collection, indicates the furthest proceeding page of the collection.
     * @var null|LinkDto|string
     */
    public $last;

    /**
     * A non-negative integer specifying the total number of objects contained by the logical view of the collection.
     * This number might not reflect the actual number of items serialized within the Collection object instance.
     * Range: xsd:nonNegativeInteger
     * @var int
     */
    public $totalItems = 0;

    /**
     * Identifies the items contained in a collection. The items might be ordered or unordered.
     * @var null|array<ObjectDto|LinkDto|string>
     * @return void
     */
    public $items;

    public function setItems(?array $items): void
    {
        $this->items = $items;
    }

    public function setTotalItems(int $totalItems): void
    {
        $this->totalItems = $totalItems;
    }

    public function setFirst($reference): void
    {
        $this->first = $reference;
    }

    public function setLast($reference): void
    {
        $this->last = $reference;
    }
}
