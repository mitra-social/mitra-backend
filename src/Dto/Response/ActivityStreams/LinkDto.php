<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

use Mitra\Serialization\Encode\ArrayNormalizable;

class LinkDto implements TypeInterface
{
    public $id;

    public $context;

    public $type = 'Link';

    public $href;

    public $rel;

    public $mediaType;

    public $name;

    public $hreflang;

    public $height;

    public $width;

    public $preview;

    public function toArray(): array
    {
        $data = ['type' => $this->type];

        if (null !== $this->context) {
            $data = ['@context' => $this->context] + $data;
        }

        $data += array_filter(get_object_vars($this), static function ($value, $key): bool {
            return !(null === $value || 'context' === $key || 'type' === $key);
        }, ARRAY_FILTER_USE_BOTH);

        return $data;
    }
}
