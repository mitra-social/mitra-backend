<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

final class CollectionPageDto extends CollectionDto implements CollectionPageInterface
{
    use CollectionPageTrait;

    /**
     * @var string
     */
    public $type = 'CollectionPage';

    /**
     * @var int
     */
    public $startIndex;
}
