<?php

declare(strict_types=1);

namespace Mitra\ActivityStreams;

interface CollectionPageInterface
{
    /**
     * @return LinkInterface|null|ObjectInterface
     */
    public function getPartOf();

    /**
     * @return LinkInterface|null|ObjectInterface
     */
    public function getNext();

    /**
     * @return LinkInterface|null|ObjectInterface
     */
    public function getPrev();
}
