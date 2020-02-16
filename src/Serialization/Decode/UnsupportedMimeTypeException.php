<?php

declare(strict_types=1);

namespace Mitra\Serialization\Decode;

final class UnsupportedMimeTypeException extends \Exception
{

    /**
     * @param string $mimeType
     * @param int $code
     * @param \Throwable $previous
     */
    public function __construct(string $mimeType, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Unsupported mime type: %s', $mimeType), $code, $previous);
    }
}
