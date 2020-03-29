<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

/**
 * Describes an object of any kind. The Object type serves as the base type for most of the other kinds of objects
 * defined in the Activity Vocabulary, including other Core types such as Activity, IntransitiveActivity, Collection
 * and OrderedCollection.
 */
class ObjectDto implements TypeInterface
{
    /**
     * @var null|string
     */
    public $id;

    /**
     * @var null|string
     */
    public $context;

    /**
     * @var string
     */
    public $type = 'Object';

    /**
     * @var null|ObjectDto|LinkDto|array<ObjectDto|LinkDto>
     */
    public $attachment;

    /**
     * @var null|ObjectDto|LinkDto|array<ObjectDto|LinkDto>
     */
    public $attributedTo;

    /**
     * @var null|ObjectDto|LinkDto|array<ObjectDto|LinkDto>
     */
    public $audience;

    /**
     * @var null|string
     */
    public $content;

    /**
     * @var null|string
     */
    public $name;

    /**
     * @var null|array<string, string>
     */
    public $nameMap;

    /**
     * @var null|string
     */
    public $endTime;

    /**
     * @var null|ObjectDto|LinkDto
     */
    public $generator;

    /**
     * Indicates an entity that describes an icon for this object. The image should have an aspect ratio of
     * one (horizontal) to one (vertical) and should be suitable for presentation at a small size.
     * @var null|ImageDto|LinkDto|array<ImageDto,LinkDto>
     */
    public $icon;

    /**
     * @var null|ImageDto|LinkDto|array<ImageDto,LinkDto>
     */
    public $image;

    /**
     * @var null|ObjectDto|LinkDto|array<ObjectDto|LinkDto>
     */
    public $inReplyTo;

    /**
     * @var null|ObjectDto|LinkDto|array<ObjectDto|LinkDto>
     */
    public $location;

    /**
     * @var null|ObjectDto|LinkDto
     */
    public $preview;

    /**
     * @var null|string
     */
    public $published;

    /**
     * @var null|CollectionDto
     */
    public $replies;

    /**
     * @var null|string
     */
    public $startTime;

    /**
     * @var null|string
     */
    public $summary;

    /**
     * @var null|array<string, string>
     */
    public $summaryMap;

    /**
     * @var null|ObjectDto|LinkDto|array<ObjectDto|LinkDto>
     */
    public $tag;

    /**
     * @var null|string
     */
    public $updated;

    /**
     * @var null|string|LinkDto
     */
    public $url;

    /**
     * @var null|ObjectDto|LinkDto
     */
    public $to;

    /**
     * @var null|ObjectDto|LinkDto
     */
    public $bto;

    /**
     * @var null|ObjectDto|LinkDto
     */
    public $cc;

    /**
     * @var null|ObjectDto|LinkDto
     */
    public $bcc;

    /**
     * @var null|string
     */
    public $mediaType;

    /**
     * @var null|string
     */
    public $duration;

    /**
     * @return array<mixed>
     */
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
