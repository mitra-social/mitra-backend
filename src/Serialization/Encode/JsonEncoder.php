<?php

declare(strict_types=1);

namespace Mitra\Serialization\Encode;

use Mitra\Serialization\UnsupportedMimeTypeException;

final class JsonEncoder implements EncoderInterface
{

    /**
     * @var int
     */
    private $jsonEncodeOptions;

    public function __construct(int $jsonEncodeOptions = 0)
    {
        $this->jsonEncodeOptions = $jsonEncodeOptions;
    }

    /**
     * @param mixed $data
     * @param string $mimeType
     * @return string
     * @throws UnsupportedMimeTypeException
     * @throws EncoderException
     */
    public function encode($data, string $mimeType): string
    {
        if ('application/json' !== $mimeType) {
            throw new UnsupportedMimeTypeException($mimeType);
        }

        try {
            if (false !== ($encodedData = json_encode($data, $this->jsonEncodeOptions | JSON_THROW_ON_ERROR))) {
                return $encodedData;
            }

            throw new EncoderException(sprintf('Could not encode JSON: %s', json_last_error_msg()));
        } catch (\JsonException $e) {
            throw new EncoderException(sprintf('Could not encode JSON: %s', $e->getMessage()), 0, $e);
        }
    }
}
