<?php

declare(strict_types=1);

namespace Mitra\Serialization\Decode;

final class DelegateDecoder implements DecoderInterface
{

    /**
     * @var array|DecoderInterface[]
     */
    private $decoders = [];

    /**
     * @inheritDoc
     */
    public function decode(string $data, string $mimeType): array
    {
        if (!isset($this->decoders[$mimeType])) {
            throw new UnsupportedMimeTypeException($mimeType);
        }

        return $this->decoders[$mimeType]->decode($data, $mimeType);
    }

    public function addDecoder(string $mimeType, DecoderInterface $decoder): void
    {
        $this->decoders[$mimeType] = $decoder;
    }
}
