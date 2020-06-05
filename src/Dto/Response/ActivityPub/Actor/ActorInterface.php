<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityPub\Actor;

use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;

interface ActorInterface
{
    public function getId(): string;

    public function getInbox(): string;

    public function getOutbox(): string;

    public function getPreferredUsername(): ?string;

    public function getName(): ?string;

    public function getType(): string;

    /**
     * @return null|string|LinkDto|ObjectDto|array<string|LinkDto|ObjectDto>
     */
    public function getIcon();
}
