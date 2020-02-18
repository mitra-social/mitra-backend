<?php

declare(strict_types=1);

namespace Mitra\Serialization\Encode;

interface EncoderInterface
{
    /**
     * @param mixed $data
     * @param string $mimeType
     * @return string
     * @throw UnsupportedMimeTypeException
     * @throws EncoderException
     */
    public function encode($data, string $mimeType): string;
}
