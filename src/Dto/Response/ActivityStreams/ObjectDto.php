<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

abstract class ObjectDto implements TypeInterface
{
    public $id;

    public $context;

    public $type = 'Object';

    public $attachment;

    public $attributedTo;

    public $audience;

    public $content;

    public $name;

    public $endTime;

    public $generator;

    public $icon;

    public $image;

    public $inReplyTo;

    public $location;

    public $preview;

    public $published;

    public $replies;

    public $startTime;

    public $summary;

    public $tag;

    public $updated;

    public $url;

    public $to;

    public $bto;

    public $cc;

    public $bcc;

    public $mediaType;

    public $duration;

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
