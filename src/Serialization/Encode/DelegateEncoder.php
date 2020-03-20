<?php

declare(strict_types=1);

namespace Mitra\Serialization\Encode;

use Mitra\Serialization\UnsupportedMimeTypeException;

final class DelegateEncoder implements EncoderInterface
{

    /**
     * @var array|EncoderInterface[]
     */
    private $encoders = [];

    /**
     * @param mixed $data
     * @param string $mimeType
     * @return string
     * @throws UnsupportedMimeTypeException
     * @throws EncoderException
     */
    public function encode($data, string $mimeType): string
    {
        if (!isset($this->encoders[$mimeType])) {
            throw new UnsupportedMimeTypeException($mimeType);
        }

        return $this->encoders[$mimeType]->encode(
            $data instanceof ArrayNormalizable ? $data->toArray() : $data,
            $mimeType
        );
    }

    public function addEncoder(string $mimeType, EncoderInterface $encoder): void
    {
        $this->encoders[$mimeType] = $encoder;
    }
}
