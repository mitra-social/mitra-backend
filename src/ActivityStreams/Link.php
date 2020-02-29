<?php

declare(strict_types=1);

namespace Mitra\ActivityStreams;

final class Link implements LinkInterface, \JsonSerializable
{
    use IdPropertyTrait;

    /**
     * @var string|null
     */
    public $href;

    /**
     * @var string|array<string>|null
     */
    public $rel;

    /**
     * @var string|null
     */
    public $mediaType;

    /**
     * @var string|array<string>
     */
    public $name;

    /**
     * @var string|null
     */
    public $hreflang;

    /**
     * @var int|null
     */
    public $height;

    /**
     * @var int|null
     */
    public $width;

    /**
     * @var static|ObjectInterface|null
     */
    public $preview;

    /**
     * @inheritDoc
     */
    public static function getType(): ?string
    {
        return 'Link';
    }

    /**
     * @inheritDoc
     */
    public function getHref(): ?string
    {
        return $this->href;
    }

    /**
     * @inheritDoc
     */
    public function getRel()
    {
        return $this->rel;
    }

    /**
     * @inheritDoc
     */
    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getHreflang(): ?string
    {
        return $this->hreflang;
    }

    /**
     * @inheritDoc
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @inheritDoc
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @inheritDoc
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $jsonData = [
            'href' => $this->href,
            'rel' => $this->rel,
            'mediaType' => $this->mediaType,
            'name' => $this->name,
            'hreflang' => $this->hreflang,
            'height' => $this->height,
            'width' => $this->width,
            'preview' => $this->preview,
        ];

        return array_filter($jsonData, function ($value) {
            return $value !== null;
        });
    }
}
