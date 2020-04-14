<?php

declare(strict_types=1);

namespace Mitra\Entity\User;

class ExternalUser extends AbstractUser
{

    /**
     * The external actor id from the remote server
     * @var string
     */
    private $externalId;

    /**
     * A hash of the external actor id to lookup actor's faster
     * @var string
     */
    private $externalIdHash;

    /**
     * @var null|string
     */
    private $preferredUsername;

    /**
     * URL to the actor's inbox
     * @var string
     */
    private $inbox;

    /**
     * URL to the actor's outbox
     * @var string
     */
    private $outbox;

    /**
     * URL to a list of who this actor is following
     * @var null|string
     */
    private $following;

    /**
     * URL to a list of who follows this actor
     * @var null|string
     */
    private $followers;

    /**
     * @var null|string
     */
    private $url;

    public function __construct(
        string $id,
        string $externalId,
        string $externalIdHash,
        ?string $preferredUsername,
        string $inbox,
        string $outbox
    ) {
        parent::__construct($id);

        $this->externalId = $externalId;
        $this->externalIdHash = $externalIdHash;
        $this->preferredUsername = $preferredUsername;
        $this->inbox = $inbox;
        $this->outbox = $outbox;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getExternalIdHash(): ?string
    {
        return $this->externalIdHash;
    }

    public function getInbox(): string
    {
        return $this->inbox;
    }

    public function getOutbox(): string
    {
        return $this->outbox;
    }

    public function getFollowing(): ?string
    {
        return $this->following;
    }

    public function setFollowing(?string $following): void
    {
        $this->following = $following;
    }

    public function getFollowers(): ?string
    {
        return $this->followers;
    }

    public function setFollowers(?string $followers): void
    {
        $this->followers = $followers;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getPreferredUsername(): ?string
    {
        return $this->preferredUsername;
    }

    /**
     * @param string|null $publicKey
     */
    public function setPublicKey(?string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }
}
