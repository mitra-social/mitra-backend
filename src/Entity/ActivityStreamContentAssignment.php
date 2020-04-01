<?php

declare(strict_types=1);

namespace Mitra\Entity;

use Mitra\Entity\Actor\Actor;

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
}
