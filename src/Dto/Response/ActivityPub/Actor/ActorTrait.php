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
     * @var null|string
     */
    public $publicKey;

    /**
     * @return string|null
     */
    public function getPreferredUsername(): ?string
    {
        return $this->preferredUsername;
    }

    /**
     * @return LinkDto|string
     */
    public function getInbox()
    {
        return $this->inbox;
    }

    /**
     * @return LinkDto|string
     */
    public function getOutbox()
    {
        return $this->outbox;
    }

    /**
     * @return LinkDto|string|null
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * @return LinkDto|string|null
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * @return LinkDto|string|null
     */
    public function getLiked()
    {
        return $this->liked;
    }

    /**
     * @return array|null
     */
    public function getStreams(): ?array
    {
        return $this->streams;
    }

    /**
     * @return array|null
     */
    public function getEndpoints(): ?array
    {
        return $this->endpoints;
    }
}
