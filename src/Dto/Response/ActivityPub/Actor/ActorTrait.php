<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityPub\Actor;

use Mitra\Dto\Response\ActivityStreams\LinkDto;

/**
 * Actor objects MUST have, in addition to the properties mandated by 3.1 Object Identifiers, the following properties
 * @link https://www.w3.org/TR/activitypub/#actor-objects
 */
trait ActorTrait
{
    /**
     * A short username which may be used to refer to the actor, with no uniqueness guarantees.
     * @var null|string
     */
    public $preferredUsername;

    /**
     * A reference to an [ActivityStreams] OrderedCollection comprised of all the messages received by the actor;
     * see 5.2 Inbox.
     * @var string|LinkDto
     */
    public $inbox;

    /**
     * An [ActivityStreams] OrderedCollection comprised of all the messages produced by the actor; see 5.1 Outbox.
     * @var string|LinkDto
     */
    public $outbox;

    /**
     * A link to an [ActivityStreams] collection of the actors that this actor is following;
     * see 5.4 Following Collection
     * @var null|string|LinkDto
     */
    public $following;

    /**
     * A link to an [ActivityStreams] collection of the actors that follow this actor; see 5.3 Followers Collection.
     * @var null|string|LinkDto
     */
    public $followers;

    /**
     * A link to an [ActivityStreams] collection of objects this actor has liked; see 5.5 Liked Collection.
     * @var null|string|LinkDto
     */
    public $liked;

    /**
     * A list of supplementary Collections which may be of interest.
     * @var null|array<string, string|LinkDto>
     */
    public $streams;

    /**
     * A json object which maps additional (typically server/domain-wide) endpoints which may be useful either for this
     * actor or someone referencing this actor. This mapping may be nested inside the actor document as the value or may
     * be a link to a JSON-LD document with these properties.
     * @var null|array<string, string|LinkDto>
     */
    public $endpoints;

    /**
     * @var null|array<string, null|string>
     */
    public $publicKey;

    public function getId(): string
    {
        return $this->id;
    }

    public function getInbox(): string
    {
        return (string) $this->inbox;
    }

    public function getOutbox(): string
    {
        return (string) $this->outbox;
    }

    public function getPreferredUsername(): ?string
    {
        return $this->preferredUsername;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getIcon()
    {
        return $this->icon;
    }
}
