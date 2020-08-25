<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\Dto\Response\ActivityStreams\CollectionInterface;
use Mitra\Dto\Response\ActivityStreams\CollectionPageInterface;
use Mitra\Dto\Response\ActivityStreams\OrderedCollectionDto;
use Mitra\Dto\Response\ActivityStreams\OrderedCollectionPageDto;

abstract class AbstractOrderedCollectionController extends AbstractCollectionController
{
    protected function getCollectionDto(): CollectionInterface
    {
        return new OrderedCollectionDto();
    }

    protected function getCollectionPageDto(): CollectionPageInterface
    {
        return new OrderedCollectionPageDto();
    }
}
