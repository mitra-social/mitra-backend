<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityPub\Actor;

trait ActorTrait
{
    public $preferredUsername;

    public $inbox;

    public $outbox;

    public $following;

    public $followers;

    public $liked;

    public $streams;

    public $endpoints;
}
