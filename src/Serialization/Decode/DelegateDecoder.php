<?php

declare(strict_types=1);

namespace Mitra\Serialization\Decode;

use Mitra\Serialization\UnsupportedMimeTypeException;

final class DelegateDecoder implements DecoderInterface
{

    /**
     * @var array|DecoderInterface[]
     */
    private $decoders = [];

    /**
     * @inheritDoc
     * @throws UnsupportedMimeTypeException
     */
    public function decode(string $data, string $mimeType): array
    {
        foreach ($this->decoders as $decoder) {
            if ($decoder->supports($mimeType)) {
                return $decoder->decode(
                    $data,
                    $mimeType
                );
            }
        }

        throw new UnsupportedMimeTypeException($mimeType);
    }

    public function addDecoder(string $mimeType, DecoderInterface $decoder): void
    {
        $this->decoders[$mimeType] = $decoder;
    }

    public function supports(string $mimeType): bool
    {
        foreach ($this->decoders as $decoder) {
            if ($decoder->supports($mimeType)) {
                return true;
            }
        }

        return false;
    }
}
