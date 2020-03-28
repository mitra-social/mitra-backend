<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams\Activity;

final class CreateDto extends ActivityDto
{
    /**
     * @var string
     */
    public $type = 'Create';
}
