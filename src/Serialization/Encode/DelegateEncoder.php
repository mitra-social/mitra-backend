<?php

declare(strict_types=1);

namespace Mitra\Serialization\Encode;

use Mitra\Serialization\UnsupportedMimeTypeException;

final class DelegateEncoder implements EncoderInterface
{

    /**
     * @var array|EncoderInterface[]
     */
    private $decoders = [];

    /**
     * @param mixed $data
     * @param string $mimeType
     * @return string
     * @throws UnsupportedMimeTypeException
     */
    public function encode($data, string $mimeType): string
    {
        if (!isset($this->decoders[$mimeType])) {
            throw new UnsupportedMimeTypeException($mimeType);
        }

        return $this->decoders[$mimeType]->encode($data, $mimeType);
    }

    public function addEncoder(string $mimeType, EncoderInterface $decoder): void
    {
        $this->decoders[$mimeType] = $decoder;
    }
}
