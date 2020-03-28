<?php

declare(strict_types=1);

namespace Mitra\Serialization\Encode;

interface ArrayNormalizable
{
    /**
     * @return array<mixed>
     */
    public function toArray(): array;
}
