<?php

declare(strict_types=1);

namespace Mitra\Serialization\Encode;

interface ArrayNormalizable
{
    public function toArray(): array;
}
