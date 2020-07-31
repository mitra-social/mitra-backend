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
        $context = [];

        $normalizedData = $this->deepNormalization($data, $context);

        if (0 !== count($context)) {
            $context = array_values(array_intersect_key($context, array_unique(array_map('serialize', $context))));

            if (1 === count($context) && isset($context[0])) {
                $context = $context[0];
            }

            $normalizedData = ['@context' => $context] + $normalizedData;
        }

        return $normalizedData;
    }

    /**
     * @param mixed $data
     * @param array<mixed> $context
     * @return mixed
     */
    private function deepNormalization($data, array &$context)
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
            $normalizedData[$propertyName] = $this->deepNormalization($value, $context);
        }

        if (isset($normalizedData['@context'])) {
            $context = array_merge($context, (array) $normalizedData['@context']);
            unset($normalizedData['@context']);
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
