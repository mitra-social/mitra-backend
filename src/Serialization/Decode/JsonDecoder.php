<?php

declare(strict_types=1);

namespace Mitra\Serialization\Decode;

use Mitra\Serialization\UnsupportedMimeTypeException;

final class JsonDecoder implements DecoderInterface
{

    /**
     * @param string $data
     * @param string $mimeType
     * @return array
     * @throws UnsupportedMimeTypeException
     */
    public function decode(string $data, string $mimeType): array
    {
        if ('application/json' !== $mimeType) {
            throw new UnsupportedMimeTypeException($mimeType);
        }

        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }
}
