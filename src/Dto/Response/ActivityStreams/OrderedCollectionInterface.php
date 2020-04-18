<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

interface OrderedCollectionInterface extends CollectionInterface
{
    /**
     * @param array<ObjectDto|LinkDto|string>|null $items
     * @return void
     */
    public function setOrderedItems(?array $items): void;
}
