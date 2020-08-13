<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityPub\Actor;

use Mitra\Dto\Response\ActivityStreams\ImageDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;

interface ActorInterface
{
    public function getId(): string;

    public function getInbox(): string;

    public function getOutbox(): string;

    public function getPreferredUsername(): ?string;

    public function getName(): ?string;

    public function getType(): string;

    /**
     * @return null|string|LinkDto|ImageDto|array<string|LinkDto|ImageDto>
     */
    public function getIcon();
}
