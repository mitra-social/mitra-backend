<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

interface CollectionPageInterface extends CollectionInterface
{
    /**
     * @param string|LinkDto $reference
     * @return void
     */
    public function setPartOf($reference): void;

    /**
     * @param string|LinkDto $reference
     * @return void
     */
    public function setPrev($reference): void;

    /**
     * @param string|LinkDto $reference
     * @return void
     */
    public function setNext($reference): void;
}
