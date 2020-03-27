<?php

declare(strict_types=1);

namespace Mitra\Entity;

class ActivityStreamContentAssignment
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var User
     */
    private $user;

    /**
     * @var ActivityStreamContent
     */
    private $content;

    public function __construct(string $id, User $user, ActivityStreamContent $content)
    {
        $this->id = $id;
        $this->user = $user;
        $this->content = $content;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getContent(): ActivityStreamContent
    {
        return $this->content;
    }
}
