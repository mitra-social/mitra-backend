<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

use Mitra\Serialization\Encode\ArrayNormalizable;

/**
 * A Link is an indirect, qualified reference to a resource identified by a URL. The fundamental model for links is
 * established by [ RFC5988]. Many of the properties defined by the Activity Vocabulary allow values that are either
 * instances of Object or Link. When a Link is used, it establishes a qualified relation connecting the subject
 * (the containing object) to the resource identified by the href. Properties of the Link are properties of the
 * reference as opposed to properties of the resource.
 */
class LinkDto implements TypeInterface
{
    /**
     * Provides the globally unique identifier for an Object or Link.
     * Range: anyURI
     * @var null|string
     */
    public $id;

    /**
     * @var null|string
     */
    public $context;

    /**
     * Identifies the Object or Link type. Multiple values may be specified.
     * Range: anyURI
     * @var string
     */
    public $type = 'Link';

    /**
     * The target resource pointed to by a Link.
     * Range: xsd:anyURI
     * @var null|string
     */
    public $href;

    /**
     * A link relation associated with a Link. The value MUST conform to both the [HTML5] and [RFC5988] "link relation"
     * definitions.
     * In the [HTML5], any string not containing the "space" U+0020, "tab" (U+0009), "LF" (U+000A), "FF" (U+000C), "CR"
     * (U+000D) or "," (U+002C) characters can be used as a valid link relation.
     * Range: [RFC5988] or [HTML5] Link Relation
     * @var null|string
     */
    public $rel;

    /**
     * When used on a Link, identifies the MIME media type of the referenced resource.
     * Range: MIME Media Type
     * @var null|string
     */
    public $mediaType;

    /**
     * A simple, human-readable, plain-text name for the object. HTML markup MUST NOT be included.
     * The name MAY be expressed using multiple language-tagged values.
     * Range: xsd:string
     * @var null|string
     */
    public $name;

    /**
     * A simple, human-readable, plain-text name for the object. HTML markup MUST NOT be included.
     * The name MAY be expressed using multiple language-tagged values.
     * Range: rdf:langString
     * @var null|array<string, string>
     */
    public $nameMap;

    /**
     * Hints as to the language used by the target resource. Value MUST be a [BCP47] Language-Tag.
     * Range: [BCP47] Language Tag
     * @var null|string
     */
    public $hreflang;

    /**
     * On a Link, specifies a hint as to the rendering height in device-independent pixels of the linked resource.
     * Range: xsd:nonNegativeInteger
     * @var null|int
     */
    public $height;

    /**
     * On a Link, specifies a hint as to the rendering width in device-independent pixels of the linked resource.
     * Range: xsd:nonNegativeInteger
     * @var null|int
     */
    public $width;

    /**
     * Identifies an entity that provides a preview of this object.
     * @var null|ObjectDto|LinkDto
     */
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
