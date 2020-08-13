<?php

declare(strict_types=1);

namespace Mitra\Factory;

use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;

interface ActivityStreamContentFactoryInterface
{
    public function createFromDto(ObjectDto $objectDto): ActivityStreamContent;
}
