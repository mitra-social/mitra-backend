<?php

declare(strict_types=1);

namespace Mitra\ActivityStreams;

abstract class AbstractObject implements ObjectInterface, \JsonSerializable
{
    use IdPropertyTrait;

    /**
     * @var array<static|LinkInterface>|null
     */
    public $attachment;

    /**
     * @var array<static|LinkInterface>|null
     */
    public $attributedTo;

    /**
     * @var array<static|LinkInterface>|null
     */
    public $audience;

    /**
     * @var string|array<string,string>|null
     */
    public $content;

    /**
     * @var static|LinkInterface|null
     */
    public $context = 'https://www.w3.org/ns/activitystreams';

    /**
     * @var string|array<string, string>|null
     */
    public $name;

    /**
     * @var \DateTime|null
     */
    public $endTime;

    /**
     * @var static|LinkInterface|null
     */
    public $generator;

    /**
     * @var ImageInterface|LinkInterface|array<ImageInterface|LinkInterface>|null
     */
    public $icon;

    /**
     * @var ImageInterface|LinkInterface|array<ImageInterface|LinkInterface>|null
     */
    public $image;

    /**
     * @var static|LinkInterface|array<static|LinkInterface>|null
     */
    public $inReplyTo;

    /**
     * @var static|LinkInterface|array<static|LinkInterface>|null
     */
    public $location;

    /**
     * @var \DateTime|null
     */
    public $published;

    /**
     * @var CollectionInterface|null
     */
    public $replies;

    /**
     * @var \DateTime|null
     */
    public $startTime;

    /**
     * @var string|array<string,string>|null
     */
    public $summary;

    /**
     * @var static|LinkInterface|array<static|LinkInterface>|null
     */
    public $tag;

    /**
     * @var \DateTime|null
     */
    public $updated;

    /**
     * @var string|LinkInterface|array<string|LinkInterface>
     */
    public $url;

    /**
     * @var array<static|LinkInterface>
     */
    public $to;

    /**
     * @var array<static|LinkInterface>
     */
    public $bto;

    /**
     * @var array<static|LinkInterface>
     */
    public $cc;

    /**
     * @var array<static|LinkInterface>
     */
    public $bcc;

    /**
     * @var string|null
     */
    public $mediaType;

    /**
     * @var \DatePeriod|null
     */
    public $duration;

    /**
     * @var static|LinkInterface|null
     */
    public $preview;

    /**
     * @return array<static|LinkInterface>|null
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @return array<static|LinkInterface>|null
     */
    public function getAttributedTo()
    {
        return $this->attributedTo;
    }

    /**
     * @return array<static|LinkInterface>|null
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * @return string|array<string,string>|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return static|LinkInterface|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return string|array<string, string>|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @inheritDoc
     */
    public function getGenerator()
    {
        return $this->generator;
    }

    /**
     * @inheritDoc
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @inheritDoc
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @inheritDoc
     */
    public function getInReplyTo()
    {
        return $this->inReplyTo;
    }

    /**
     * @inheritDoc
     */
    public function getLocation()
    {
        return $this->location;
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
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @inheritDoc
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * @inheritDoc
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @inheritDoc
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @inheritDoc
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @inheritDoc
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @inheritDoc
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @inheritDoc
     */
    public function getBto()
    {
        return $this->bto;
    }

    /**
     * @inheritDoc
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @inheritDoc
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @inheritDoc
     */
    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    /**
     * @inheritDoc
     */
    public function getDuration(): ?\DatePeriod
    {
        return $this->duration;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $jsonData = [
            '@context' => $this->context,
            'type' => static::getType(),
            'attachment' => $this->attachment,
            'audience' => $this->audience,
            'content' => $this->content,
            'name' => $this->name,
            'endTime' => $this->endTime ? $this->endTime->format('c') : null,
            'generator' => $this->generator,
            'icon' => $this->icon,
            'image' => $this->image,
            'inReplyTo' => $this->inReplyTo,
            'location' => $this->location,
            'published' => $this->published,
            'replies' => $this->replies,
            'startTime' => $this->startTime ? $this->startTime->format('c') : null,
            'summary' => $this->summary,
            'tag' => $this->tag,
            'updated' => $this->updated ? $this->updated->format('c') : null,
            'url' => $this->url,
            'to' => $this->to,
            'bto' => $this->bto,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'mediaType' => $this->mediaType,
            'duration' => $this->duration,
        ];

        return array_filter($jsonData, function ($value) {
            return $value !== null;
        });
    }
}
