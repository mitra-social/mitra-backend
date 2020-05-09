<?php

declare(strict_types=1);

namespace Mitra\Normalization;

use Mitra\Serialization\Encode\ArrayNormalizable;

final class Normalizer implements NormalizerInterface
{

    /**
     * @inheritDoc
     */
    public function normalize($data): array
    {
        return $this->deepNormalization($data);
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
}
