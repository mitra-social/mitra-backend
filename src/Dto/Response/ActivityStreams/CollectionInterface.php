<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

interface CollectionInterface extends ObjectInterface
{
    /**
     * @param array<ObjectDto|LinkDto|string>|null $items
     * @return void
     */
    public function setItems(?array $items): void;

    public function setTotalItems(int $totalItems): void;

    /**
     * @param LinkDto|string|null $reference
     * @return void
     */
    public function setFirst($reference): void;

    /**
     * @param LinkDto|string|null $reference
     * @return void
     */
    public function setLast($reference): void;
}
