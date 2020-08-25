<?php

declare(strict_types=1);

namespace Mitra\Normalization;

interface NormalizerInterface
{
    /**
     * @param mixed $data
     * @return array<mixed>
     */
    public function normalize($data): array;
}
