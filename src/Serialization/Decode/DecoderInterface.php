<?php

declare(strict_types=1);

namespace Mitra\Serialization\Decode;

interface DecoderInterface
{
    /**
     * @param string $data
     * @param string $mimeType
     * @return mixed
     * @throw UnsupportedMimeTypeException
     */
    public function decode(string $data, string $mimeType);

    public function supports(string $mimeType): bool;
}
