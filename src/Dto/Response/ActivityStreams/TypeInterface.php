<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

use Mitra\Serialization\Encode\ArrayNormalizable;

interface TypeInterface extends ArrayNormalizable
{
    public const CONTEXT_ACTIVITY_STREAMS = 'https://www.w3.org/ns/activitystreams';
}
