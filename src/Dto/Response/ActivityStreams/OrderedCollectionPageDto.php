<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

final class OrderedCollectionPageDto extends OrderedCollectionDto
{
    use CollectionPageTrait;

    /**
     * @var string
     */
    public $type = 'OrderedCollectionPage';

    /**
     * @var int
     */
    public $startIndex;
}
