<?php

declare(strict_types=1);

namespace Mitra\ActivityStreams;

final class Note extends AbstractObject implements NoteInterface
{
    public static function getType(): ?string
    {
        return 'Note';
    }
}
