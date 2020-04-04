<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Type;

use Mitra\Dto\Response\ActivityStreams\LinkDto;

interface ActorInterface
{
    /**
     * A short username which may be used to refer to the actor, with no uniqueness guarantees.
     * @var null|string
     */
    public  function getPreferredUsername(): ?string;

    /**
     * A reference to an [ActivityStreams] OrderedCollection comprised of all the messages received by the actor;
     * see 5.2 Inbox.
     * @var string|LinkDto
     */
    public function getInbox();

    /**
     * An [ActivityStreams] OrderedCollection comprised of all the messages produced by the actor; see 5.1 Outbox.
     * @var string|LinkDto
     */
    public function getOutbox();

    /**
     * A link to an [ActivityStreams] collection of the actors that this actor is following;
     * see 5.4 Following Collection
     * @var null|string|LinkDto
     */
    public  function getFollowing();

    /**
     * A link to an [ActivityStreams] collection of the actors that follow this actor; see 5.3 Followers Collection.
     * @var null|string|LinkDto
     */
    public  function getFollowers();

    /**
     * A link to an [ActivityStreams] collection of objects this actor has liked; see 5.5 Liked Collection.
     * @var null|string|LinkDto
     */
    public  function getLiked();

    /**
     * A list of supplementary Collections which may be of interest.
     * @var null|array<string, string|LinkDto>
     */
    public function getStreams(): ?array;

    /**
     * A json object which maps additional (typically server/domain-wide) endpoints which may be useful either for this
     * actor or someone referencing this actor. This mapping may be nested inside the actor document as the value or may
     * be a link to a JSON-LD document with these properties.
     * @var null|array<string, string|LinkDto>
     */
    public function getEndpoints(): ?array;
}
