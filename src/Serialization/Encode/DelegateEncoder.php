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
        foreach ($this->encoders as $encoder) {
            if ($encoder->supports($mimeType)) {
                return $encoder->encode($data, $mimeType);
            }
        }

        throw new UnsupportedMimeTypeException($mimeType);
    }

    public function addEncoder(string $mimeType, EncoderInterface $encoder): void
    {
        $this->encoders[$mimeType] = $encoder;
    }

    public function supports(string $mimeType): bool
    {
        foreach ($this->encoders as $encoder) {
            if ($encoder->supports($mimeType)) {
                return true;
            }
        }

        return false;
    }
}
