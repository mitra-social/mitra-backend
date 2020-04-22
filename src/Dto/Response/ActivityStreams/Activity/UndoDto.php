<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams\Activity;

final class UndoDto extends ActivityDto
{
    /**
     * @var string
     */
    public $type = 'Undo';
}
