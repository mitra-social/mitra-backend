<?php

declare(strict_types=1);

namespace Mitra\Entity;

use Mitra\Entity\Actor\Actor;
use Mitra\Entity\User\ExternalUser;

class ActivityStreamContentAssignment
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var Actor
     */
    private $actor;

    /**
     * @var ActivityStreamContent
     */
    private $content;

    public function __construct(string $id, Actor $actor, ActivityStreamContent $content)
    {
        $this->id = $id;
        $this->actor = $actor;
        $this->content = $content;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getActor(): Actor
    {
        return $this->actor;
    }

    public function getContent(): ActivityStreamContent
    {
        return $this->content;
    }

    public function __toString()
    {
        $user = $this->actor->getUser();

        return sprintf(
            'id:%s, actorId:%s, contentId:%s, externalActorId:%s, externalContentId:%s',
            $this->id,
            $this->actor->getUser()->getId(),
            $this->content->getId(),
            $user instanceof ExternalUser ? $user->getExternalId() : null,
            $this->content->getExternalId(),
        );
    }
}
