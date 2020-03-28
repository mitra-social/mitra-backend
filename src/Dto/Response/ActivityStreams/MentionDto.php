<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

final class MentionDto extends LinkDto
{
    /**
     * @var string
     */
    public $type = 'Mention';
}
