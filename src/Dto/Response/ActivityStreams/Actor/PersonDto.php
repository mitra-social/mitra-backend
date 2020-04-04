<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams\Actor;

use Mitra\Dto\Response\ActivityStreams\ObjectDto;

class PersonDto extends ObjectDto implements ActorDtoInterface
{
    /**
     * @var string
     */
    public $type = 'Person';
}
