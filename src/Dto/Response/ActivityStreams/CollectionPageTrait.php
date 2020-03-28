<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

trait CollectionPageTrait
{
    /**
     * Identifies the Collection to which a CollectionPage objects items belong.
     * @var null|LinkDto|string
     */
    public $partOf;

    /**
     * In a paged Collection, identifies the previous page of items.
     * @var null|LinkDto|string
     */
    public $prev;

    /**
     * In a paged Collection, indicates the next page of items.
     * @var null|LinkDto|string
     */
    public $next;
}
