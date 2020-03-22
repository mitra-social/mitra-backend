<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams\Activity;

use Mitra\Dto\Response\ActivityStreams\ObjectDto;

abstract class AbstractActivity extends ObjectDto
{
    public $type;

    public $actor;

    public $target;

    public $result;

    public $origin;

    public $instrument;
}
