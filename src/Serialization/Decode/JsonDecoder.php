<?php

declare(strict_types=1);

namespace Mitra\Serialization\Decode;

use Mitra\Serialization\UnsupportedMimeTypeException;

final class JsonDecoder implements DecoderInterface
{
    /**
     * @param string $data
     * @param string $mimeType
     * @return mixed
     * @throws UnsupportedMimeTypeException
     */
    public function decode(string $data, string $mimeType)
    {
        if (!$this->supports($mimeType)) {
            throw new UnsupportedMimeTypeException($mimeType);
        }

        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }

    public function supports(string $mimeType): bool
    {
        return 1 === preg_match('~^application/(?:.+\+)?json$~', $mimeType);
    }
}
