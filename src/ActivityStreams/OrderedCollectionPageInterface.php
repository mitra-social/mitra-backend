<?php

declare(strict_types=1);

namespace Mitra\ActivityStreams;

interface OrderedCollectionPageInterface extends CollectionPageInterface
{
    /**
     * @return int|null
     */
    public function getStartIndex(): ?int;
}
