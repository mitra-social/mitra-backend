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
                return $encoder->encode(
                    $this->deepNormalization($data),
                    $mimeType
                );
            }
        }

        throw new UnsupportedMimeTypeException($mimeType);
    }

    public function addEncoder(string $mimeType, EncoderInterface $encoder): void
    {
        $this->encoders[$mimeType] = $encoder;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    private function deepNormalization($data)
    {
        if (null === $data || is_scalar($data)) {
            return $data;
        }

        if (is_object($data)) {
            $normalizedData = $data instanceof ArrayNormalizable ? $data->toArray() : $this->convertToArray($data);
        } else {
            $normalizedData = $data;
        }

        foreach ($normalizedData as $propertyName => $value) {
            $normalizedData[$propertyName] = $this->deepNormalization($value);
        }

        return $normalizedData;
    }

    /**
     * @param object $data
     * @return array<mixed>
     */
    private function convertToArray(object $data): array
    {
        return get_object_vars($data);
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
